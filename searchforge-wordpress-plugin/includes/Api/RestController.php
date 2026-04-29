<?php

namespace SearchForge\Api;

use SearchForge\Admin\Dashboard;
use SearchForge\Admin\Settings;
use SearchForge\Export\MarkdownExporter;
use SearchForge\Models\Property;

defined( 'ABSPATH' ) || exit;

class RestController {

	private const NAMESPACE = 'searchforge/v1';

	public function register_routes(): void {
		register_rest_route( self::NAMESPACE, '/status', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'get_status' ],
			'permission_callback' => [ $this, 'check_permissions' ],
		] );

		register_rest_route( self::NAMESPACE, '/pages', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'get_pages' ],
			'permission_callback' => [ $this, 'check_permissions' ],
		] );

		register_rest_route( self::NAMESPACE, '/keywords', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'get_keywords' ],
			'permission_callback' => [ $this, 'check_permissions' ],
		] );

		register_rest_route( self::NAMESPACE, '/export/page', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'export_page' ],
			'permission_callback' => [ $this, 'check_permissions' ],
			'args'                => [
				'path' => [
					'required'          => true,
					'sanitize_callback' => 'sanitize_text_field',
				],
			],
		] );

		register_rest_route( self::NAMESPACE, '/export/site', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'export_site' ],
			'permission_callback' => [ $this, 'check_permissions' ],
		] );

		register_rest_route( self::NAMESPACE, '/sync', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'trigger_sync' ],
			'permission_callback' => [ $this, 'check_admin_permissions' ],
		] );

		register_rest_route( self::NAMESPACE, '/cannibalization', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'get_cannibalization' ],
			'permission_callback' => [ $this, 'check_permissions' ],
		] );

		register_rest_route( self::NAMESPACE, '/clusters', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'get_clusters' ],
			'permission_callback' => [ $this, 'check_permissions' ],
		] );

		register_rest_route( self::NAMESPACE, '/content-brief', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'get_content_brief' ],
			'permission_callback' => [ $this, 'check_permissions' ],
			'args'                => [
				'path' => [
					'required'          => true,
					'sanitize_callback' => 'sanitize_text_field',
				],
			],
		] );

		register_rest_route( self::NAMESPACE, '/content-gaps', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'get_content_gaps' ],
			'permission_callback' => [ $this, 'check_permissions' ],
		] );

		register_rest_route( self::NAMESPACE, '/performance', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'get_performance' ],
			'permission_callback' => [ $this, 'check_permissions' ],
			'args'                => [
				'days' => [
					'default'           => 30,
					'sanitize_callback' => 'absint',
				],
			],
		] );

		register_rest_route( self::NAMESPACE, '/quota', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'get_quota' ],
			'permission_callback' => [ $this, 'check_permissions' ],
		] );

		register_rest_route( self::NAMESPACE, '/ssl', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'get_ssl_status' ],
			'permission_callback' => [ $this, 'check_permissions' ],
		] );

		register_rest_route( self::NAMESPACE, '/audit-log', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'get_audit_log' ],
			'permission_callback' => [ $this, 'check_admin_permissions' ],
			'args'                => [
				'limit'  => [ 'default' => 50, 'sanitize_callback' => 'absint' ],
				'offset' => [ 'default' => 0, 'sanitize_callback' => 'absint' ],
			],
		] );

		register_rest_route( self::NAMESPACE, '/trends', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'get_trends' ],
			'permission_callback' => [ $this, 'check_permissions' ],
			'args'                => [
				'keyword' => [
					'required'          => true,
					'sanitize_callback' => 'sanitize_text_field',
				],
			],
		] );

		register_rest_route( self::NAMESPACE, '/page-detail', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'get_page_detail' ],
			'permission_callback' => [ $this, 'check_permissions' ],
			'args'                => [
				'path' => [
					'required'          => true,
					'sanitize_callback' => 'sanitize_text_field',
				],
			],
		] );

		register_rest_route( self::NAMESPACE, '/competitors', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'get_competitors' ],
			'permission_callback' => [ $this, 'check_permissions' ],
		] );

		register_rest_route( self::NAMESPACE, '/competitors/overlap', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'get_competitor_overlap' ],
			'permission_callback' => [ $this, 'check_permissions' ],
			'args'                => [
				'limit' => [ 'default' => 50, 'sanitize_callback' => 'absint' ],
			],
		] );

		register_rest_route( self::NAMESPACE, '/competitors/gaps', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'get_competitor_gaps' ],
			'permission_callback' => [ $this, 'check_permissions' ],
			'args'                => [
				'limit' => [ 'default' => 50, 'sanitize_callback' => 'absint' ],
			],
		] );

		register_rest_route( self::NAMESPACE, '/competitors/visibility', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'get_competitor_visibility' ],
			'permission_callback' => [ $this, 'check_permissions' ],
		] );

		register_rest_route( self::NAMESPACE, '/properties', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'get_properties' ],
			'permission_callback' => [ $this, 'check_permissions' ],
		] );

		register_rest_route( self::NAMESPACE, '/properties', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'create_property' ],
			'permission_callback' => [ $this, 'check_admin_permissions' ],
			'args'                => [
				'label'  => [ 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ],
				'domain' => [ 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ],
			],
		] );

		register_rest_route( self::NAMESPACE, '/properties/(?P<id>\d+)', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'get_property' ],
			'permission_callback' => [ $this, 'check_permissions' ],
		] );

		register_rest_route( self::NAMESPACE, '/properties/(?P<id>\d+)', [
			'methods'             => 'DELETE',
			'callback'            => [ $this, 'delete_property' ],
			'permission_callback' => [ $this, 'check_admin_permissions' ],
		] );

		register_rest_route( self::NAMESPACE, '/comparison', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'get_comparison' ],
			'permission_callback' => [ $this, 'check_permissions' ],
		] );

		register_rest_route( self::NAMESPACE, '/merger-analysis', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'get_merger_analysis' ],
			'permission_callback' => [ $this, 'check_permissions' ],
		] );
	}

	public function check_permissions( \WP_REST_Request $request = null ) {
		if ( ! Settings::is_pro() ) {
			return new \WP_Error(
				'rest_forbidden',
				'You do not have permission to access this endpoint.',
				[ 'status' => 403 ]
			);
		}

		// Allow API key auth for external access.
		if ( $request && ApiKeyAuth::validate( $request ) ) {
			return true;
		}

		return current_user_can( 'edit_posts' );
	}

	public function check_admin_permissions( \WP_REST_Request $request = null ): bool {
		if ( $request && ApiKeyAuth::validate( $request ) ) {
			return true;
		}
		return current_user_can( 'manage_options' );
	}

	public function get_status( \WP_REST_Request $request ): \WP_REST_Response {
		$pid     = $this->get_property_id( $request );
		$summary = Dashboard::get_summary( $pid );
		$summary['version']       = SEARCHFORGE_VERSION;
		$summary['tier']          = Settings::get( 'license_tier' );
		$summary['property_id']   = $pid;
		$summary['properties']    = Property::count();

		$prop = Property::get( $pid );
		$summary['gsc_connected'] = $prop && ! empty( $prop['gsc_access_token'] );

		return new \WP_REST_Response( $summary );
	}

	public function get_pages( \WP_REST_Request $request ): \WP_REST_Response {
		$limit = min( absint( $request->get_param( 'limit' ) ?: 50 ), 500 );
		$pid   = $this->get_property_id( $request );
		$pages = Dashboard::get_top_pages( $limit, 0, 'clicks', 'DESC', $pid );

		return new \WP_REST_Response( [
			'pages' => $pages,
			'total' => count( $pages ),
		] );
	}

	public function get_keywords( \WP_REST_Request $request ): \WP_REST_Response {
		$limit    = min( absint( $request->get_param( 'limit' ) ?: 50 ), 500 );
		$pid      = $this->get_property_id( $request );
		$keywords = Dashboard::get_top_keywords( $limit, 0, 'clicks', 'DESC', $pid );

		return new \WP_REST_Response( [
			'keywords' => $keywords,
			'total'    => count( $keywords ),
		] );
	}

	public function export_page( \WP_REST_Request $request ): \WP_REST_Response {
		$path     = $request->get_param( 'path' );
		$pid      = $this->get_property_id( $request );
		$exporter = new MarkdownExporter();
		$markdown = $exporter->generate_page_brief( $path, $pid );

		if ( is_wp_error( $markdown ) ) {
			return new \WP_REST_Response( [
				'error' => $markdown->get_error_message(),
			], 404 );
		}

		return new \WP_REST_Response( [
			'markdown' => $markdown,
			'path'     => $path,
		] );
	}

	public function export_site( \WP_REST_Request $request ): \WP_REST_Response {
		$pid      = $this->get_property_id( $request );
		$exporter = new MarkdownExporter();
		$markdown = $exporter->generate_site_brief( $pid );

		if ( is_wp_error( $markdown ) ) {
			return new \WP_REST_Response( [
				'error' => $markdown->get_error_message(),
			], 404 );
		}

		return new \WP_REST_Response( [ 'markdown' => $markdown ] );
	}

	public function trigger_sync( \WP_REST_Request $request ): \WP_REST_Response {
		$pid    = $this->get_property_id( $request );
		$syncer = new \SearchForge\Integrations\GSC\Syncer( $pid );
		$result = $syncer->sync_all();

		if ( is_wp_error( $result ) ) {
			return new \WP_REST_Response( [
				'error' => $result->get_error_message(),
			], 500 );
		}

		return new \WP_REST_Response( $result );
	}

	public function get_cannibalization( \WP_REST_Request $request ): \WP_REST_Response {
		$limit  = min( absint( $request->get_param( 'limit' ) ?: 50 ), 200 );
		$pid    = $this->get_property_id( $request );
		$result = \SearchForge\Analysis\Cannibalization::detect( $limit, $pid );

		return new \WP_REST_Response( [
			'cannibalization' => $result,
			'total'           => count( $result ),
		] );
	}

	public function get_clusters( \WP_REST_Request $request ): \WP_REST_Response {
		$limit  = min( absint( $request->get_param( 'limit' ) ?: 500 ), 1000 );
		$pid    = $this->get_property_id( $request );
		$result = \SearchForge\Analysis\Clustering::cluster_keywords( 0.3, $limit, $pid );

		return new \WP_REST_Response( [
			'clusters' => $result,
			'total'    => count( $result ),
		] );
	}

	public function get_content_brief( \WP_REST_Request $request ): \WP_REST_Response {
		$path   = $request->get_param( 'path' );
		$pid    = $this->get_property_id( $request );
		$result = \SearchForge\Analysis\ContentBrief::generate( $path, $pid );

		if ( is_wp_error( $result ) ) {
			return new \WP_REST_Response( [
				'error' => $result->get_error_message(),
			], 400 );
		}

		return new \WP_REST_Response( $result );
	}

	public function get_content_gaps( \WP_REST_Request $request ): \WP_REST_Response {
		$limit   = min( absint( $request->get_param( 'limit' ) ?: 20 ), 100 );
		$enricher = new \SearchForge\Integrations\KeywordPlanner\Enricher();
		$result   = $enricher->get_content_gaps( $limit );

		return new \WP_REST_Response( [
			'gaps'  => $result,
			'total' => count( $result ),
		] );
	}

	public function get_performance( \WP_REST_Request $request ): \WP_REST_Response {
		$days = min( absint( $request->get_param( 'days' ) ?: 30 ), 365 );
		$pid  = $this->get_property_id( $request );

		return new \WP_REST_Response( [
			'daily'      => \SearchForge\Monitoring\PerformanceTrend::get_daily_trends( $days, $pid ),
			'comparison' => \SearchForge\Monitoring\PerformanceTrend::get_period_comparison( min( $days, 30 ), $pid ),
		] );
	}

	public function get_quota(): \WP_REST_Response {
		return new \WP_REST_Response( \SearchForge\Monitoring\QuotaTracker::get_summary() );
	}

	public function get_ssl_status(): \WP_REST_Response {
		$result = \SearchForge\Monitoring\SslChecker::check();
		return new \WP_REST_Response( $result ?: [ 'status' => 'not_https' ] );
	}

	public function get_audit_log( \WP_REST_Request $request ): \WP_REST_Response {
		$limit  = min( absint( $request->get_param( 'limit' ) ?: 50 ), 200 );
		$offset = absint( $request->get_param( 'offset' ) ?: 0 );

		return new \WP_REST_Response( [
			'entries' => \SearchForge\Monitoring\AuditLog::get_entries( $limit, $offset ),
			'total'   => \SearchForge\Monitoring\AuditLog::get_total(),
		] );
	}

	public function get_trends( \WP_REST_Request $request ): \WP_REST_Response {
		$keyword = $request->get_param( 'keyword' );
		$geo     = sanitize_text_field( $request->get_param( 'geo' ) ?? '' );

		$interest = \SearchForge\Integrations\Trends\Client::get_interest_over_time( $keyword, $geo );
		if ( is_wp_error( $interest ) ) {
			return new \WP_REST_Response( [ 'error' => $interest->get_error_message() ], 400 );
		}

		$related = \SearchForge\Integrations\Trends\Client::get_related_queries( $keyword, $geo );
		$seasonality = \SearchForge\Integrations\Trends\Client::detect_seasonality( $keyword, $geo );

		return new \WP_REST_Response( [
			'interest'    => $interest,
			'related'     => is_wp_error( $related ) ? null : $related,
			'seasonality' => $seasonality,
		] );
	}

	public function get_competitors( \WP_REST_Request $request ): \WP_REST_Response {
		$pid         = $this->get_property_id( $request );
		$competitors = \SearchForge\Analysis\Competitors::get_all( $pid );

		return new \WP_REST_Response( [
			'competitors' => $competitors,
			'total'       => count( $competitors ),
		] );
	}

	public function get_competitor_overlap( \WP_REST_Request $request ): \WP_REST_Response {
		$limit = min( absint( $request->get_param( 'limit' ) ?: 50 ), 200 );
		$pid   = $this->get_property_id( $request );

		return new \WP_REST_Response( [
			'overlap' => \SearchForge\Analysis\Competitors::get_keyword_overlap( $limit, $pid ),
		] );
	}

	public function get_competitor_gaps( \WP_REST_Request $request ): \WP_REST_Response {
		$limit = min( absint( $request->get_param( 'limit' ) ?: 50 ), 200 );
		$pid   = $this->get_property_id( $request );

		return new \WP_REST_Response( [
			'gaps' => \SearchForge\Analysis\Competitors::get_competitor_only_keywords( $limit, $pid ),
		] );
	}

	public function get_competitor_visibility( \WP_REST_Request $request ): \WP_REST_Response {
		$pid = $this->get_property_id( $request );
		return new \WP_REST_Response(
			\SearchForge\Analysis\Competitors::get_visibility_comparison( $pid )
		);
	}

	public function get_page_detail( \WP_REST_Request $request ): \WP_REST_Response {
		$path = $request->get_param( 'path' );
		$pid  = $this->get_property_id( $request );

		$page_data = \SearchForge\Admin\PageDetail::get_page_data( $path, $pid );
		if ( ! $page_data ) {
			return new \WP_REST_Response( [ 'error' => 'No data for this page.' ], 404 );
		}

		$response = [
			'page'         => $page_data,
			'keywords'     => \SearchForge\Admin\PageDetail::get_page_keywords( $path, $pid ),
			'devices'      => \SearchForge\Admin\PageDetail::get_device_breakdown( $path, $pid ),
			'daily_trend'  => \SearchForge\Admin\PageDetail::get_daily_trend( $path, $pid ),
			'position_distribution' => \SearchForge\Admin\PageDetail::get_position_distribution( $path, $pid ),
		];

		$bing = \SearchForge\Admin\PageDetail::get_bing_data( $path, $pid );
		if ( $bing ) {
			$response['bing'] = $bing;
		}

		$ga4 = \SearchForge\Admin\PageDetail::get_ga4_data( $path, $pid );
		if ( $ga4 ) {
			$response['ga4'] = $ga4;
		}

		$score = \SearchForge\Scoring\Score::calculate_page_score( $path, $pid );
		if ( $score ) {
			$response['score'] = $score;
		}

		return new \WP_REST_Response( $response );
	}

	private function get_property_id( \WP_REST_Request $request ): int {
		$id = absint( $request->get_param( 'property_id' ) );
		if ( $id ) {
			return $id;
		}
		return Property::get_active_property_id();
	}

	public function get_properties(): \WP_REST_Response {
		$properties = Property::get_all();
		$safe = array_map( function( $p ) {
			unset( $p['gsc_client_secret'], $p['gsc_access_token'], $p['gsc_refresh_token'], $p['bing_api_key'] );
			return $p;
		}, $properties );
		return new \WP_REST_Response( [ 'properties' => $safe, 'total' => count( $safe ) ] );
	}

	public function create_property( \WP_REST_Request $request ): \WP_REST_Response {
		$id = Property::create( [
			'label'  => $request->get_param( 'label' ),
			'domain' => $request->get_param( 'domain' ),
		] );
		if ( ! $id ) {
			return new \WP_REST_Response( [ 'error' => 'Could not create property.' ], 400 );
		}
		return new \WP_REST_Response( [ 'id' => $id ], 201 );
	}

	public function get_property( \WP_REST_Request $request ): \WP_REST_Response {
		$property = Property::get( (int) $request['id'] );
		if ( ! $property ) {
			return new \WP_REST_Response( [ 'error' => 'Property not found.' ], 404 );
		}
		unset( $property['gsc_client_secret'], $property['gsc_access_token'], $property['gsc_refresh_token'], $property['bing_api_key'] );
		return new \WP_REST_Response( $property );
	}

	public function delete_property( \WP_REST_Request $request ): \WP_REST_Response {
		$result = Property::delete( (int) $request['id'] );
		if ( ! $result ) {
			return new \WP_REST_Response( [ 'error' => 'Cannot delete default property or property not found.' ], 400 );
		}
		return new \WP_REST_Response( [ 'deleted' => true ] );
	}

	public function get_comparison( \WP_REST_Request $request ): \WP_REST_Response {
		$ids = array_map( 'absint', (array) $request->get_param( 'property_ids' ) );
		$ids = array_filter( $ids );
		if ( empty( $ids ) ) {
			$all = Property::get_all();
			$ids = array_column( $all, 'id' );
		}
		return new \WP_REST_Response( [
			'summaries' => \SearchForge\Analysis\PropertyComparison::compare_summaries( $ids ),
			'pages'     => \SearchForge\Analysis\PropertyComparison::compare_pages( $ids, 20 ),
			'keywords'  => \SearchForge\Analysis\PropertyComparison::compare_keywords( $ids, 20 ),
		] );
	}

	public function get_merger_analysis( \WP_REST_Request $request ): \WP_REST_Response {
		$ids = array_map( 'absint', (array) $request->get_param( 'property_ids' ) );
		$ids = array_filter( $ids );
		if ( count( $ids ) < 2 ) {
			return new \WP_REST_Response( [ 'error' => 'At least 2 property IDs required.' ], 400 );
		}
		$analyzer = new \SearchForge\Analysis\MergerAnalysis( $ids );
		return new \WP_REST_Response( [ 'markdown' => $analyzer->generate_markdown() ] );
	}
}
