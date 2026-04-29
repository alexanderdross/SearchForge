<?php

namespace SearchForge\Cli;

use SearchForge\Admin\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * WP-CLI commands for SearchForge.
 *
 * ## EXAMPLES
 *
 *     wp searchforge sync
 *     wp searchforge status
 *     wp searchforge export pages --format=csv
 */
class Commands {

	/**
	 * Run a data sync from configured sources.
	 *
	 * ## OPTIONS
	 *
	 * [--source=<source>]
	 * : Sync a specific source only. Accepts: gsc, bing, ga4, adobe, kwp.
	 *
	 * [--property=<id>]
	 * : Sync a specific property only. If omitted, syncs all properties.
	 *
	 * ## EXAMPLES
	 *
	 *     wp searchforge sync
	 *     wp searchforge sync --source=gsc
	 *     wp searchforge sync --property=2
	 *     wp searchforge sync --source=gsc --property=1
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Named args.
	 */
	public function sync( $args, $assoc_args ): void {
		$source      = $assoc_args['source'] ?? 'all';
		$property_id = absint( $assoc_args['property'] ?? 0 );

		if ( $property_id ) {
			$property = \SearchForge\Models\Property::get( $property_id );
			if ( ! $property ) {
				\WP_CLI::error( "Property {$property_id} not found." );
			}
			$properties = [ $property ];
		} else {
			$properties = \SearchForge\Models\Property::get_all();
			if ( empty( $properties ) ) {
				// Fallback to legacy settings-based sync.
				$properties = [ null ];
			}
		}

		foreach ( $properties as $prop ) {
			$pid = $prop ? (int) $prop['id'] : 0;
			if ( $prop ) {
				\WP_CLI::log( "--- Property: {$prop['label']} ({$prop['domain']}) ---" );
			}

			if ( in_array( $source, [ 'all', 'gsc' ], true ) ) {
				$gsc_token = $prop ? ( $prop['gsc_access_token'] ?? '' ) : Settings::get( 'gsc_access_token' );
				if ( empty( $gsc_token ) ) {
					\WP_CLI::warning( 'GSC not connected. Skipping.' );
				} else {
					\WP_CLI::log( 'Syncing Google Search Console...' );
					$syncer = new \SearchForge\Integrations\GSC\Syncer( $pid );
					$result = $syncer->sync_all();
					if ( is_wp_error( $result ) ) {
						\WP_CLI::warning( 'GSC sync failed: ' . $result->get_error_message() );
					} else {
						$pages = $result['pages'] ?? $result['pages_synced'] ?? 0;
						$kw    = $result['keywords'] ?? $result['keywords_synced'] ?? 0;
						\WP_CLI::success( "GSC: {$pages} pages, {$kw} keywords synced." );
					}
				}
			}

			if ( in_array( $source, [ 'all', 'bing' ], true ) ) {
				$bing_enabled = $prop ? ! empty( $prop['bing_enabled'] ) : ! empty( Settings::get( 'bing_enabled' ) );
				$bing_key     = $prop ? ( $prop['bing_api_key'] ?? '' ) : Settings::get( 'bing_api_key' );
				if ( ! Settings::is_pro() || ! $bing_enabled || empty( $bing_key ) ) {
					\WP_CLI::warning( 'Bing not configured or requires Pro. Skipping.' );
				} else {
					\WP_CLI::log( 'Syncing Bing Webmaster Tools...' );
					$syncer = new \SearchForge\Integrations\Bing\Syncer( $pid );
					$result = $syncer->sync_all();
					if ( is_wp_error( $result ) ) {
						\WP_CLI::warning( 'Bing sync failed: ' . $result->get_error_message() );
					} else {
						\WP_CLI::success( 'Bing sync completed.' );
					}
				}
			}

			if ( in_array( $source, [ 'all', 'ga4' ], true ) ) {
				$ga4_enabled = $prop ? ! empty( $prop['ga4_enabled'] ) : ! empty( Settings::get( 'ga4_enabled' ) );
				$ga4_prop_id = $prop ? ( $prop['ga4_property_id'] ?? '' ) : Settings::get( 'ga4_property_id' );
				if ( ! Settings::is_pro() || ! $ga4_enabled || empty( $ga4_prop_id ) ) {
					\WP_CLI::warning( 'GA4 not configured or requires Pro. Skipping.' );
				} else {
					\WP_CLI::log( 'Syncing Google Analytics 4...' );
					$syncer = new \SearchForge\Integrations\GA4\Syncer( $pid );
					$result = $syncer->sync();
					if ( is_wp_error( $result ) ) {
						\WP_CLI::warning( 'GA4 sync failed: ' . $result->get_error_message() );
					} else {
						\WP_CLI::success( 'GA4 sync completed.' );
					}
				}
			}

			if ( in_array( $source, [ 'all', 'adobe' ], true ) ) {
				$adobe_enabled = $prop ? ! empty( $prop['adobe_enabled'] ) : false;
				$adobe_client  = $prop ? ( $prop['adobe_client_id'] ?? '' ) : '';
				if ( ! Settings::is_pro() || ! $adobe_enabled || empty( $adobe_client ) ) {
					\WP_CLI::warning( 'Adobe Analytics not configured or requires Pro. Skipping.' );
				} else {
					\WP_CLI::log( 'Syncing Adobe Analytics...' );
					$syncer = new \SearchForge\Integrations\Adobe\Syncer( $pid );
					$result = $syncer->sync();
					if ( is_wp_error( $result ) ) {
						\WP_CLI::warning( 'Adobe sync failed: ' . $result->get_error_message() );
					} else {
						\WP_CLI::success( 'Adobe Analytics sync completed.' );
					}
				}
			}

			if ( in_array( $source, [ 'all', 'kwp' ], true ) ) {
				$settings = Settings::get_all();
				if ( ! Settings::is_pro() || empty( $settings['kwp_enabled'] ) || empty( $settings['kwp_customer_id'] ) ) {
					\WP_CLI::warning( 'Keyword Planner not configured or requires Pro. Skipping.' );
				} else {
					\WP_CLI::log( 'Enriching keywords via Keyword Planner...' );
					$enricher = new \SearchForge\Integrations\KeywordPlanner\Enricher( $pid );
					$enricher->enrich_keywords();
					\WP_CLI::success( 'Keyword enrichment completed.' );
				}
			}
		}

		\WP_CLI::log( 'Running data retention cleanup...' );
		\SearchForge\Database\Cleanup::run();
		\WP_CLI::success( 'Sync finished.' );
	}

	/**
	 * Display plugin status and configuration summary.
	 *
	 * ## EXAMPLES
	 *
	 *     wp searchforge status
	 */
	public function status( $args, $assoc_args ): void {
		$settings = Settings::get_all();
		$summary  = \SearchForge\Admin\Dashboard::get_summary();

		\WP_CLI::log( '--- SearchForge Status ---' );
		\WP_CLI::log( 'Version:       ' . SEARCHFORGE_VERSION );
		\WP_CLI::log( 'License Tier:  ' . ucfirst( $settings['license_tier'] ) );
		\WP_CLI::log( 'Sync Schedule: ' . $settings['sync_frequency'] );
		\WP_CLI::log( '' );

		$properties = \SearchForge\Models\Property::get_all();
		\WP_CLI::log( 'Properties:    ' . count( $properties ) );
		foreach ( $properties as $prop ) {
			$pid = (int) $prop['id'];
			$gsc = ! empty( $prop['gsc_access_token'] ) ? 'Connected' : 'No';
			$bing = ! empty( $prop['bing_enabled'] ) ? 'Yes' : 'No';
			$ga4 = ! empty( $prop['ga4_enabled'] ) ? 'Yes' : 'No';
			$adobe = ! empty( $prop['adobe_enabled'] ) ? 'Yes' : 'No';
			\WP_CLI::log( "  [{$pid}] {$prop['label']} ({$prop['domain']}) — GSC: {$gsc}, Bing: {$bing}, GA4: {$ga4}, Adobe: {$adobe}" );
		}

		\WP_CLI::log( '' );
		\WP_CLI::log( '--- Data Summary ---' );
		\WP_CLI::log( 'Total Pages:       ' . number_format( $summary['total_pages'] ) );
		\WP_CLI::log( 'Total Keywords:    ' . number_format( $summary['total_keywords'] ) );
		\WP_CLI::log( 'Total Clicks:      ' . number_format( $summary['total_clicks'] ) );
		\WP_CLI::log( 'Total Impressions: ' . number_format( $summary['total_impressions'] ) );
		\WP_CLI::log( 'Avg Position:      ' . $summary['avg_position'] );
		\WP_CLI::log( 'Avg CTR:           ' . $summary['avg_ctr'] . '%' );
		\WP_CLI::log( 'Last Sync:         ' . ( $summary['last_sync'] ?: 'Never' ) );

		$next_run = \SearchForge\Scheduler\Manager::get_next_run();
		\WP_CLI::log( 'Next Sync:         ' . ( $next_run ?: 'Not scheduled' ) );
	}

	/**
	 * Export data (pages, keywords, alerts).
	 *
	 * ## OPTIONS
	 *
	 * <type>
	 * : What to export. Accepts: pages, keywords, alerts, brief.
	 *
	 * [--format=<format>]
	 * : Output format. Accepts: csv, json, md.
	 * ---
	 * default: csv
	 * ---
	 *
	 * [--page=<page_path>]
	 * : Page path for brief export.
	 *
	 * [--property=<id>]
	 * : Property ID to export data for. Defaults to active property.
	 *
	 * [--file=<file>]
	 * : Output file path. Defaults to stdout.
	 *
	 * ## EXAMPLES
	 *
	 *     wp searchforge export pages --format=csv --file=pages.csv
	 *     wp searchforge export keywords --format=json
	 *     wp searchforge export brief --page=/about/ --format=md
	 *     wp searchforge export pages --property=2 --format=csv
	 */
	public function export( $args, $assoc_args ): void {
		$type        = $args[0] ?? 'pages';
		$format      = $assoc_args['format'] ?? 'csv';
		$file        = $assoc_args['file'] ?? null;
		$property_id = absint( $assoc_args['property'] ?? 0 );

		if ( $property_id ) {
			$property = \SearchForge\Models\Property::get( $property_id );
			if ( ! $property ) {
				\WP_CLI::error( "Property {$property_id} not found." );
			}
		} else {
			$property_id = \SearchForge\Models\Property::get_active_property_id();
		}

		$data = '';

		switch ( $type ) {
			case 'pages':
				$data = $format === 'json'
					? \SearchForge\Export\CsvExporter::export_pages_json( $property_id )
					: \SearchForge\Export\CsvExporter::export_pages_csv( $property_id );
				break;

			case 'keywords':
				$data = $format === 'json'
					? \SearchForge\Export\CsvExporter::export_keywords_json( $property_id )
					: \SearchForge\Export\CsvExporter::export_keywords_csv( $property_id );
				break;

			case 'alerts':
				$data = \SearchForge\Export\CsvExporter::export_alerts_csv( $property_id );
				break;

			case 'brief':
				$page_path = $assoc_args['page'] ?? '';
				if ( empty( $page_path ) ) {
					\WP_CLI::error( 'The --page argument is required for brief export.' );
				}
				$exporter = new \SearchForge\Export\MarkdownExporter();
				$data = $exporter->generate_page_brief( $page_path, $property_id );
				if ( is_wp_error( $data ) ) {
					\WP_CLI::error( $data->get_error_message() );
				}
				break;

			default:
				\WP_CLI::error( "Unknown export type: {$type}. Use pages, keywords, alerts, or brief." );
		}

		if ( empty( $data ) ) {
			\WP_CLI::warning( 'No data to export.' );
			return;
		}

		if ( $file ) {
			file_put_contents( $file, $data );
			\WP_CLI::success( "Exported to {$file}" );
		} else {
			\WP_CLI::log( $data );
		}
	}

	/**
	 * Scan pages for broken links.
	 *
	 * ## OPTIONS
	 *
	 * [--limit=<limit>]
	 * : Maximum pages to scan.
	 * ---
	 * default: 20
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp searchforge scan-links --limit=50
	 */
	public function scan_links( $args, $assoc_args ): void {
		if ( ! Settings::is_pro() ) {
			\WP_CLI::error( 'Broken link scanning requires a Pro license.' );
		}

		$limit = absint( $assoc_args['limit'] ?? 20 );
		\WP_CLI::log( "Scanning up to {$limit} pages for broken links..." );

		$broken = \SearchForge\Monitoring\BrokenLinks::scan( $limit );

		if ( empty( $broken ) ) {
			\WP_CLI::success( 'No broken links found.' );
			return;
		}

		$table_data = array_map( function ( $link ) {
			return [
				'Page'   => $link['page_path'],
				'URL'    => mb_substr( $link['url'], 0, 80 ),
				'Status' => $link['status_code'] ?: 'Error',
				'Type'   => $link['type'],
			];
		}, $broken );

		\WP_CLI\Utils\format_items( 'table', $table_data, [ 'Page', 'URL', 'Status', 'Type' ] );
		\WP_CLI::warning( count( $broken ) . ' broken link(s) found.' );
	}

	/**
	 * Show API quota usage for today.
	 *
	 * ## EXAMPLES
	 *
	 *     wp searchforge quota
	 */
	public function quota( $args, $assoc_args ): void {
		$summary = \SearchForge\Monitoring\QuotaTracker::get_summary();

		$table_data = [];
		foreach ( $summary as $service => $info ) {
			$table_data[] = [
				'Service' => $info['label'],
				'Used'    => number_format( $info['used'] ),
				'Limit'   => number_format( $info['limit'] ),
				'Pct'     => $info['pct'] . '%',
				'Status'  => strtoupper( $info['status'] ),
			];
		}

		\WP_CLI\Utils\format_items( 'table', $table_data, [ 'Service', 'Used', 'Limit', 'Pct', 'Status' ] );
	}

	/**
	 * List all registered properties.
	 *
	 * ## EXAMPLES
	 *
	 *     wp searchforge properties
	 */
	public function properties( $args, $assoc_args ): void {
		$properties = \SearchForge\Models\Property::get_all();
		if ( empty( $properties ) ) {
			\WP_CLI::warning( 'No properties registered.' );
			return;
		}
		$table_data = array_map( function( $p ) {
			return [
				'ID'      => $p['id'],
				'Label'   => $p['label'],
				'Domain'  => $p['domain'],
				'Default' => $p['is_default'] ? 'Yes' : '',
				'GSC'     => ! empty( $p['gsc_access_token'] ) ? 'Connected' : '—',
				'Bing'    => ! empty( $p['bing_enabled'] ) ? 'Enabled' : '—',
				'GA4'     => ! empty( $p['ga4_enabled'] ) ? 'Enabled' : '—',
				'Adobe'   => ! empty( $p['adobe_enabled'] ) ? 'Enabled' : '—',
			];
		}, $properties );
		\WP_CLI\Utils\format_items( 'table', $table_data, [ 'ID', 'Label', 'Domain', 'Default', 'GSC', 'Bing', 'GA4', 'Adobe' ] );
	}

	/**
	 * Generate a CMS backend merger analysis brief.
	 *
	 * ## OPTIONS
	 *
	 * --properties=<ids>
	 * : Comma-separated property IDs to analyze.
	 *
	 * [--file=<file>]
	 * : Output file path. Defaults to stdout.
	 *
	 * ## EXAMPLES
	 *
	 *     wp searchforge merger --properties=1,2,3
	 *     wp searchforge merger --properties=1,2 --file=merger.md
	 */
	public function merger( $args, $assoc_args ): void {
		if ( ! Settings::is_pro() ) {
			\WP_CLI::error( 'Merger analysis requires a Pro license.' );
		}
		$ids_str = $assoc_args['properties'] ?? '';
		$ids = array_map( 'absint', explode( ',', $ids_str ) );
		$ids = array_filter( $ids );
		if ( count( $ids ) < 2 ) {
			\WP_CLI::error( 'Provide at least 2 property IDs (--properties=1,2).' );
		}
		\WP_CLI::log( 'Generating merger analysis for properties: ' . implode( ', ', $ids ) . '...' );
		$analyzer = new \SearchForge\Analysis\MergerAnalysis( $ids );
		$markdown = $analyzer->generate_markdown();
		$file = $assoc_args['file'] ?? null;
		if ( $file ) {
			file_put_contents( $file, $markdown );
			\WP_CLI::success( "Merger analysis exported to {$file}" );
		} else {
			\WP_CLI::log( $markdown );
		}
	}
}
