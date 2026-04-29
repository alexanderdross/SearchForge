<?php

namespace SearchForge\Admin;

defined( 'ABSPATH' ) || exit;

class Ajax {

	public function __construct() {
		add_action( 'wp_ajax_searchforge_sync_gsc', [ $this, 'sync_gsc' ] );
		add_action( 'wp_ajax_searchforge_sync_bing', [ $this, 'sync_bing' ] );
		add_action( 'wp_ajax_searchforge_disconnect_gsc', [ $this, 'disconnect_gsc' ] );
		add_action( 'wp_ajax_searchforge_export_brief', [ $this, 'export_brief' ] );
		add_action( 'wp_ajax_searchforge_dismiss_alert', [ $this, 'dismiss_alert' ] );
		add_action( 'wp_ajax_searchforge_generate_content_brief', [ $this, 'generate_content_brief' ] );
		add_action( 'wp_ajax_searchforge_export_data', [ $this, 'export_data' ] );
		add_action( 'wp_ajax_searchforge_discover_sitemaps', [ $this, 'discover_sitemaps' ] );
		add_action( 'wp_ajax_searchforge_scan_broken_links', [ $this, 'scan_broken_links' ] );
		add_action( 'wp_ajax_searchforge_generate_api_key', [ $this, 'generate_api_key' ] );
		add_action( 'wp_ajax_searchforge_revoke_api_key', [ $this, 'revoke_api_key' ] );
		add_action( 'wp_ajax_searchforge_add_competitor', [ $this, 'add_competitor' ] );
		add_action( 'wp_ajax_searchforge_remove_competitor', [ $this, 'remove_competitor' ] );
		add_action( 'wp_ajax_searchforge_sync_competitor', [ $this, 'sync_competitor' ] );
		add_action( 'wp_ajax_searchforge_switch_property', [ $this, 'switch_property' ] );
		add_action( 'wp_ajax_searchforge_add_property', [ $this, 'add_property' ] );
		add_action( 'wp_ajax_searchforge_remove_property', [ $this, 'remove_property' ] );
		add_action( 'wp_ajax_searchforge_sync_property', [ $this, 'sync_property' ] );
		add_action( 'wp_ajax_searchforge_generate_merger_brief', [ $this, 'generate_merger_brief' ] );
	}

	public function sync_gsc(): void {
		check_ajax_referer( 'searchforge_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'searchforge' ) ], 403 );
		}

		$property_id = absint( $_POST['property_id'] ?? 0 ) ?: \SearchForge\Models\Property::get_active_property_id();
		$property    = \SearchForge\Models\Property::get( $property_id );

		if ( ! $property || empty( $property['gsc_access_token'] ) ) {
			wp_send_json_error( [ 'message' => __( 'GSC not connected. Please authenticate first.', 'searchforge' ) ] );
		}

		$syncer = new \SearchForge\Integrations\GSC\Syncer( $property_id );
		$result = $syncer->sync_all();

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ] );
		}

		wp_send_json_success( $result );
	}

	public function disconnect_gsc(): void {
		check_ajax_referer( 'searchforge_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'searchforge' ) ], 403 );
		}

		$property_id = absint( $_POST['property_id'] ?? 0 ) ?: \SearchForge\Models\Property::get_active_property_id();

		\SearchForge\Models\Property::update( $property_id, [
			'gsc_access_token'  => '',
			'gsc_refresh_token' => '',
			'gsc_token_expires' => 0,
			'gsc_property'      => '',
		] );

		wp_send_json_success( [ 'message' => __( 'GSC disconnected.', 'searchforge' ) ] );
	}

	public function sync_bing(): void {
		check_ajax_referer( 'searchforge_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'searchforge' ) ], 403 );
		}

		if ( ! Settings::is_pro() ) {
			wp_send_json_error( [ 'message' => __( 'Bing integration requires a Pro license.', 'searchforge' ) ] );
		}

		$property_id = absint( $_POST['property_id'] ?? 0 ) ?: \SearchForge\Models\Property::get_active_property_id();

		$syncer = new \SearchForge\Integrations\Bing\Syncer( $property_id );
		$result = $syncer->sync_all();

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ] );
		}

		wp_send_json_success( $result );
	}

	public function dismiss_alert(): void {
		check_ajax_referer( 'searchforge_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'searchforge' ) ], 403 );
		}

		$alert_id = absint( $_POST['alert_id'] ?? 0 );
		if ( ! $alert_id ) {
			wp_send_json_error( [ 'message' => __( 'Invalid alert ID.', 'searchforge' ) ] );
		}

		global $wpdb;
		$wpdb->update( "{$wpdb->prefix}sf_alerts", [ 'is_read' => 1 ], [ 'id' => $alert_id ] );

		wp_send_json_success();
	}

	public function export_brief(): void {
		check_ajax_referer( 'searchforge_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'searchforge' ) ], 403 );
		}

		$page_path   = sanitize_text_field( $_POST['page_path'] ?? '' );
		$brief_type  = sanitize_text_field( $_POST['brief_type'] ?? 'page' );
		$property_id = absint( $_POST['property_id'] ?? 0 ) ?: \SearchForge\Models\Property::get_active_property_id();

		if ( empty( $page_path ) ) {
			wp_send_json_error( [ 'message' => __( 'Page path is required.', 'searchforge' ) ] );
		}

		$exporter = new \SearchForge\Export\MarkdownExporter( $property_id );
		$markdown = $exporter->generate_page_brief( $page_path );

		if ( is_wp_error( $markdown ) ) {
			wp_send_json_error( [ 'message' => $markdown->get_error_message() ] );
		}

		wp_send_json_success( [
			'markdown' => $markdown,
			'filename' => 'searchforge-' . sanitize_file_name( trim( $page_path, '/' ) ?: 'homepage' ) . '.md',
		] );
	}

	public function generate_content_brief(): void {
		check_ajax_referer( 'searchforge_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'searchforge' ) ], 403 );
		}

		if ( ! Settings::is_pro() ) {
			wp_send_json_error( [ 'message' => __( 'Content briefs require a Pro license.', 'searchforge' ) ] );
		}

		$page_path   = sanitize_text_field( $_POST['page_path'] ?? '' );
		$property_id = absint( $_POST['property_id'] ?? 0 ) ?: \SearchForge\Models\Property::get_active_property_id();

		if ( empty( $page_path ) ) {
			wp_send_json_error( [ 'message' => __( 'Page path is required.', 'searchforge' ) ] );
		}

		$result = \SearchForge\Analysis\ContentBrief::generate( $page_path, $property_id );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ] );
		}

		wp_send_json_success( [
			'brief'    => $result['brief'],
			'method'   => $result['method'],
			'filename' => 'content-brief-' . sanitize_file_name( trim( $page_path, '/' ) ?: 'homepage' ) . '.md',
		] );
	}

	public function discover_sitemaps(): void {
		check_ajax_referer( 'searchforge_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'searchforge' ) ], 403 );
		}

		$sitemaps = \SearchForge\Sitemap\Discovery::discover();

		$results = [];
		foreach ( $sitemaps as $url ) {
			$count = \SearchForge\Sitemap\Discovery::count_urls( $url );
			$results[] = [
				'url'       => $url,
				'url_count' => $count,
			];
		}

		wp_send_json_success( [ 'sitemaps' => $results ] );
	}

	public function scan_broken_links(): void {
		check_ajax_referer( 'searchforge_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'searchforge' ) ], 403 );
		}

		if ( ! Settings::is_pro() ) {
			wp_send_json_error( [ 'message' => __( 'Broken link scanning requires a Pro license.', 'searchforge' ) ] );
		}

		$broken = \SearchForge\Monitoring\BrokenLinks::scan( 20 );

		wp_send_json_success( [
			'count'  => count( $broken ),
			'broken' => array_slice( $broken, 0, 50 ),
		] );
	}

	public function export_data(): void {
		check_ajax_referer( 'searchforge_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'searchforge' ) ], 403 );
		}

		if ( ! Settings::is_pro() ) {
			wp_send_json_error( [ 'message' => __( 'Data export requires a Pro license.', 'searchforge' ) ] );
		}

		$type        = sanitize_text_field( $_POST['export_type'] ?? 'pages' );
		$format      = sanitize_text_field( $_POST['export_format'] ?? 'csv' );
		$property_id = absint( $_POST['property_id'] ?? 0 ) ?: \SearchForge\Models\Property::get_active_property_id();

		switch ( $type ) {
			case 'keywords':
				$data     = $format === 'json' ? \SearchForge\Export\CsvExporter::export_keywords_json( $property_id ) : \SearchForge\Export\CsvExporter::export_keywords_csv( $property_id );
				$filename = 'searchforge-keywords.' . $format;
				break;
			case 'alerts':
				$data     = \SearchForge\Export\CsvExporter::export_alerts_csv( $property_id );
				$filename = 'searchforge-alerts.csv';
				$format   = 'csv';
				break;
			default:
				$data     = $format === 'json' ? \SearchForge\Export\CsvExporter::export_pages_json( $property_id ) : \SearchForge\Export\CsvExporter::export_pages_csv( $property_id );
				$filename = 'searchforge-pages.' . $format;
				break;
		}

		if ( empty( $data ) ) {
			wp_send_json_error( [ 'message' => __( 'No data to export.', 'searchforge' ) ] );
		}

		$mime = $format === 'json' ? 'application/json' : 'text/csv';

		wp_send_json_success( [
			'data'     => $data,
			'filename' => $filename,
			'mime'     => $mime,
		] );
	}

	public function generate_api_key(): void {
		check_ajax_referer( 'searchforge_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'searchforge' ) ], 403 );
		}

		if ( ! Settings::is_pro() ) {
			wp_send_json_error( [ 'message' => __( 'REST API access requires a Pro license.', 'searchforge' ) ] );
		}

		$key = \SearchForge\Api\ApiKeyAuth::generate_key();

		wp_send_json_success( [ 'key' => $key ] );
	}

	public function revoke_api_key(): void {
		check_ajax_referer( 'searchforge_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'searchforge' ) ], 403 );
		}

		\SearchForge\Api\ApiKeyAuth::revoke();

		wp_send_json_success( [ 'message' => __( 'API key revoked.', 'searchforge' ) ] );
	}

	public function add_competitor(): void {
		check_ajax_referer( 'searchforge_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'searchforge' ) ], 403 );
		}

		if ( ! Settings::is_pro() ) {
			wp_send_json_error( [ 'message' => __( 'Competitor tracking requires a Pro license.', 'searchforge' ) ] );
		}

		$domain      = sanitize_text_field( $_POST['domain'] ?? '' );
		$label       = sanitize_text_field( $_POST['label'] ?? '' );
		$property_id = absint( $_POST['property_id'] ?? 0 ) ?: \SearchForge\Models\Property::get_active_property_id();

		if ( empty( $domain ) ) {
			wp_send_json_error( [ 'message' => __( 'Domain is required.', 'searchforge' ) ] );
		}

		$result = \SearchForge\Analysis\Competitors::add( $domain, $label, $property_id );

		if ( ! $result ) {
			wp_send_json_error( [ 'message' => __( 'Could not add competitor. Limit reached or domain already exists.', 'searchforge' ) ] );
		}

		wp_send_json_success( [ 'message' => __( 'Competitor added.', 'searchforge' ) ] );
	}

	public function remove_competitor(): void {
		check_ajax_referer( 'searchforge_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'searchforge' ) ], 403 );
		}

		$id = absint( $_POST['competitor_id'] ?? 0 );
		if ( ! $id ) {
			wp_send_json_error( [ 'message' => __( 'Invalid competitor ID.', 'searchforge' ) ] );
		}

		\SearchForge\Analysis\Competitors::remove( $id );

		wp_send_json_success( [ 'message' => __( 'Competitor removed.', 'searchforge' ) ] );
	}

	public function sync_competitor(): void {
		check_ajax_referer( 'searchforge_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'searchforge' ) ], 403 );
		}

		if ( ! Settings::is_pro() ) {
			wp_send_json_error( [ 'message' => __( 'Competitor tracking requires a Pro license.', 'searchforge' ) ] );
		}

		$id = absint( $_POST['competitor_id'] ?? 0 );
		if ( ! $id ) {
			wp_send_json_error( [ 'message' => __( 'Invalid competitor ID.', 'searchforge' ) ] );
		}

		$count = \SearchForge\Analysis\Competitors::sync_from_gsc( $id );

		wp_send_json_success( [
			'message'  => sprintf( __( 'Synced %d keywords.', 'searchforge' ), $count ),
			'keywords' => $count,
		] );
	}

	public function switch_property(): void {
		check_ajax_referer( 'searchforge_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'searchforge' ) ], 403 );
		}

		$property_id = absint( $_POST['property_id'] ?? 0 );
		if ( ! $property_id || ! \SearchForge\Models\Property::get( $property_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid property.', 'searchforge' ) ] );
		}

		\SearchForge\Models\Property::set_active_property_id( $property_id );

		wp_send_json_success( [ 'message' => __( 'Property switched.', 'searchforge' ) ] );
	}

	public function add_property(): void {
		check_ajax_referer( 'searchforge_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'searchforge' ) ], 403 );
		}

		if ( ! Settings::is_pro() ) {
			wp_send_json_error( [ 'message' => __( 'Multiple properties require a Pro license.', 'searchforge' ) ] );
		}

		$label  = sanitize_text_field( $_POST['label'] ?? '' );
		$domain = sanitize_text_field( $_POST['domain'] ?? '' );

		if ( empty( $label ) || empty( $domain ) ) {
			wp_send_json_error( [ 'message' => __( 'Label and domain are required.', 'searchforge' ) ] );
		}

		$id = \SearchForge\Models\Property::create( [
			'label'  => $label,
			'domain' => $domain,
		] );

		if ( ! $id ) {
			wp_send_json_error( [ 'message' => __( 'Could not create property.', 'searchforge' ) ] );
		}

		wp_send_json_success( [ 'id' => $id, 'message' => __( 'Property added.', 'searchforge' ) ] );
	}

	public function remove_property(): void {
		check_ajax_referer( 'searchforge_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'searchforge' ) ], 403 );
		}

		$id = absint( $_POST['property_id'] ?? 0 );
		if ( ! $id ) {
			wp_send_json_error( [ 'message' => __( 'Invalid property ID.', 'searchforge' ) ] );
		}

		$result = \SearchForge\Models\Property::delete( $id );
		if ( ! $result ) {
			wp_send_json_error( [ 'message' => __( 'Cannot delete default property or property not found.', 'searchforge' ) ] );
		}

		wp_send_json_success( [ 'message' => __( 'Property removed.', 'searchforge' ) ] );
	}

	public function sync_property(): void {
		check_ajax_referer( 'searchforge_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'searchforge' ) ], 403 );
		}

		$property_id = absint( $_POST['property_id'] ?? 0 );
		$property    = $property_id ? \SearchForge\Models\Property::get( $property_id ) : null;

		if ( ! $property ) {
			wp_send_json_error( [ 'message' => __( 'Invalid property.', 'searchforge' ) ] );
		}

		if ( ! empty( $property['gsc_access_token'] ) ) {
			$syncer = new \SearchForge\Integrations\GSC\Syncer( $property_id );
			$result = $syncer->sync_all();

			if ( is_wp_error( $result ) ) {
				wp_send_json_error( [ 'message' => $result->get_error_message() ] );
			}

			wp_send_json_success( $result );
		} else {
			wp_send_json_error( [ 'message' => __( 'Property not connected to GSC.', 'searchforge' ) ] );
		}
	}

	public function generate_merger_brief(): void {
		check_ajax_referer( 'searchforge_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'searchforge' ) ], 403 );
		}

		if ( ! Settings::is_pro() ) {
			wp_send_json_error( [ 'message' => __( 'Merger analysis requires a Pro license.', 'searchforge' ) ] );
		}

		$ids = array_map( 'absint', (array) ( $_POST['property_ids'] ?? [] ) );
		$ids = array_filter( $ids );

		if ( count( $ids ) < 2 ) {
			wp_send_json_error( [ 'message' => __( 'Select at least 2 properties.', 'searchforge' ) ] );
		}

		$nav_uploads = $this->parse_nav_csv_uploads();

		$analyzer = new \SearchForge\Analysis\MergerAnalysis( $ids, $nav_uploads );
		$markdown = $analyzer->generate_markdown();

		wp_send_json_success( [
			'markdown' => $markdown,
			'filename' => 'searchforge-merger-analysis-' . implode( '-', $ids ) . '.md',
		] );
	}

	private function parse_nav_csv_uploads(): array {
		$nav_data = [];

		if ( empty( $_FILES['nav_csv_files'] ) ) {
			return $nav_data;
		}

		$files = $_FILES['nav_csv_files'];
		$labels = $_POST['nav_csv_labels'] ?? [];

		$file_count = is_array( $files['name'] ) ? count( $files['name'] ) : 0;

		for ( $i = 0; $i < $file_count; $i++ ) {
			if ( $files['error'][ $i ] !== UPLOAD_ERR_OK ) {
				continue;
			}

			$tmp  = $files['tmp_name'][ $i ];
			$label = sanitize_text_field( $labels[ $i ] ?? $files['name'][ $i ] );

			$ext = strtolower( pathinfo( $files['name'][ $i ], PATHINFO_EXTENSION ) );
			if ( $ext !== 'csv' ) {
				continue;
			}

			$handle = fopen( $tmp, 'r' );
			if ( ! $handle ) {
				continue;
			}

			$header = fgetcsv( $handle );
			if ( ! $header ) {
				fclose( $handle );
				continue;
			}
			$header = array_map( 'strtolower', array_map( 'trim', $header ) );

			$items = [];
			while ( ( $row = fgetcsv( $handle ) ) !== false ) {
				if ( count( $row ) < 2 ) {
					continue;
				}
				$mapped = array_combine( $header, array_pad( $row, count( $header ), '' ) );
				$items[] = [
					'label'    => sanitize_text_field( $mapped['label'] ?? $mapped['text'] ?? $mapped['name'] ?? $row[0] ),
					'url'      => esc_url_raw( $mapped['url'] ?? $mapped['link'] ?? $mapped['href'] ?? $row[1] ),
					'location' => sanitize_text_field( $mapped['location'] ?? $mapped['position'] ?? $mapped['type'] ?? 'header' ),
				];
			}

			fclose( $handle );

			if ( ! empty( $items ) ) {
				$nav_data[] = [
					'domain' => $label,
					'items'  => $items,
				];
			}
		}

		return $nav_data;
	}
}
