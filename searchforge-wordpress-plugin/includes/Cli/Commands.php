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
	 * : Sync a specific source only. Accepts: gsc, bing, ga4, kwp.
	 *
	 * ## EXAMPLES
	 *
	 *     wp searchforge sync
	 *     wp searchforge sync --source=gsc
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Named args.
	 */
	public function sync( $args, $assoc_args ): void {
		$source   = $assoc_args['source'] ?? 'all';
		$settings = Settings::get_all();

		if ( in_array( $source, [ 'all', 'gsc' ], true ) ) {
			if ( empty( $settings['gsc_access_token'] ) ) {
				\WP_CLI::warning( 'GSC not connected. Skipping.' );
			} else {
				\WP_CLI::log( 'Syncing Google Search Console...' );
				$syncer = new \SearchForge\Integrations\GSC\Syncer();
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
			if ( ! Settings::is_pro() || empty( $settings['bing_enabled'] ) || empty( $settings['bing_api_key'] ) ) {
				\WP_CLI::warning( 'Bing not configured or requires Pro. Skipping.' );
			} else {
				\WP_CLI::log( 'Syncing Bing Webmaster Tools...' );
				$syncer = new \SearchForge\Integrations\Bing\Syncer();
				$result = $syncer->sync_all();
				if ( is_wp_error( $result ) ) {
					\WP_CLI::warning( 'Bing sync failed: ' . $result->get_error_message() );
				} else {
					\WP_CLI::success( 'Bing sync completed.' );
				}
			}
		}

		if ( in_array( $source, [ 'all', 'ga4' ], true ) ) {
			if ( ! Settings::is_pro() || empty( $settings['ga4_enabled'] ) || empty( $settings['ga4_property_id'] ) ) {
				\WP_CLI::warning( 'GA4 not configured or requires Pro. Skipping.' );
			} else {
				\WP_CLI::log( 'Syncing Google Analytics 4...' );
				$syncer = new \SearchForge\Integrations\GA4\Syncer();
				$result = $syncer->sync();
				if ( is_wp_error( $result ) ) {
					\WP_CLI::warning( 'GA4 sync failed: ' . $result->get_error_message() );
				} else {
					\WP_CLI::success( 'GA4 sync completed.' );
				}
			}
		}

		if ( in_array( $source, [ 'all', 'kwp' ], true ) ) {
			if ( ! Settings::is_pro() || empty( $settings['kwp_enabled'] ) || empty( $settings['kwp_customer_id'] ) ) {
				\WP_CLI::warning( 'Keyword Planner not configured or requires Pro. Skipping.' );
			} else {
				\WP_CLI::log( 'Enriching keywords via Keyword Planner...' );
				$enricher = new \SearchForge\Integrations\KeywordPlanner\Enricher();
				$enricher->enrich_keywords();
				\WP_CLI::success( 'Keyword enrichment completed.' );
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
		\WP_CLI::log( 'GSC Connected: ' . ( ! empty( $settings['gsc_access_token'] ) ? 'Yes' : 'No' ) );
		\WP_CLI::log( 'GSC Property:  ' . ( $settings['gsc_property'] ?: '(none)' ) );
		\WP_CLI::log( 'Bing Enabled:  ' . ( $settings['bing_enabled'] ? 'Yes' : 'No' ) );
		\WP_CLI::log( 'GA4 Enabled:   ' . ( $settings['ga4_enabled'] ? 'Yes' : 'No' ) );
		\WP_CLI::log( 'Sync Schedule: ' . $settings['sync_frequency'] );
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
	 * [--file=<file>]
	 * : Output file path. Defaults to stdout.
	 *
	 * ## EXAMPLES
	 *
	 *     wp searchforge export pages --format=csv --file=pages.csv
	 *     wp searchforge export keywords --format=json
	 *     wp searchforge export brief --page=/about/ --format=md
	 */
	public function export( $args, $assoc_args ): void {
		$type   = $args[0] ?? 'pages';
		$format = $assoc_args['format'] ?? 'csv';
		$file   = $assoc_args['file'] ?? null;

		$data = '';

		switch ( $type ) {
			case 'pages':
				$data = $format === 'json'
					? \SearchForge\Export\CsvExporter::export_pages_json()
					: \SearchForge\Export\CsvExporter::export_pages_csv();
				break;

			case 'keywords':
				$data = $format === 'json'
					? \SearchForge\Export\CsvExporter::export_keywords_json()
					: \SearchForge\Export\CsvExporter::export_keywords_csv();
				break;

			case 'alerts':
				$data = \SearchForge\Export\CsvExporter::export_alerts_csv();
				break;

			case 'brief':
				$page_path = $assoc_args['page'] ?? '';
				if ( empty( $page_path ) ) {
					\WP_CLI::error( 'The --page argument is required for brief export.' );
				}
				$exporter = new \SearchForge\Export\MarkdownExporter();
				$data = $exporter->generate_page_brief( $page_path );
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
}
