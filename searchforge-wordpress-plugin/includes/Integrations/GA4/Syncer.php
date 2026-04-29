<?php

namespace SearchForge\Integrations\GA4;

use SearchForge\Admin\Settings;
use SearchForge\Models\Property;

defined( 'ABSPATH' ) || exit;

/**
 * Syncs GA4 behavior data and stores it for brief enrichment.
 */
class Syncer {

	/**
	 * @var int Property ID to sync.
	 */
	private int $property_id;

	/**
	 * @param int $property_id Property ID. 0 = use active property.
	 */
	public function __construct( int $property_id = 0 ) {
		$this->property_id = $property_id ?: Property::get_active_property_id();
	}

	/**
	 * Sync GA4 page-level behavior data.
	 *
	 * @return array|\WP_Error  [ 'pages_synced' => int ]
	 */
	public function sync(): array|\WP_Error {
		if ( ! Settings::is_pro() ) {
			return new \WP_Error( 'not_pro', __( 'GA4 integration requires a Pro license.', 'searchforge-wordpress-plugin' ) );
		}

		$prop = Property::get( $this->property_id );
		if ( ! $prop ) {
			return new \WP_Error( 'no_property', __( 'Property not found.', 'searchforge-wordpress-plugin' ) );
		}

		$ga4_property_id = $prop['ga4_property_id'] ?? '';
		if ( empty( $ga4_property_id ) ) {
			return new \WP_Error( 'no_ga4', __( 'GA4 property ID not configured.', 'searchforge-wordpress-plugin' ) );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'sf_ga4_metrics';
		$today = gmdate( 'Y-m-d' );

		// Fetch page metrics.
		$page_metrics = Client::get_page_metrics( 28, 500, $prop );
		if ( is_wp_error( $page_metrics ) ) {
			return $page_metrics;
		}

		// Fetch organic landing page data.
		$landing_pages = Client::get_landing_pages( 28, 500, $prop );
		$landing_data  = is_wp_error( $landing_pages ) ? [] : $landing_pages;

		$synced = 0;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->query( 'START TRANSACTION' );

		try {
			foreach ( $page_metrics as $path => $metrics ) {
				$organic = $landing_data[ $path ] ?? [];

				// Delete existing data for this date + path + property.
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->query( $wpdb->prepare(
					"DELETE FROM {$table} WHERE page_path = %s AND snapshot_date = %s AND property_id = %d",
					$path,
					$today,
					$this->property_id
				) );

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->insert( $table, [
					'page_path'           => $path,
					'snapshot_date'       => $today,
					'sessions'            => $metrics['sessions'],
					'bounce_rate'         => $metrics['bounce_rate'],
					'avg_session_dur'     => $metrics['avg_session_dur'],
					'engaged_sessions'    => $metrics['engaged_sessions'],
					'conversions'         => $metrics['conversions'],
					'pageviews'           => $metrics['pageviews'],
					'organic_sessions'    => $organic['organic_sessions'] ?? 0,
					'organic_bounce'      => $organic['bounce_rate'] ?? null,
					'organic_conversions' => $organic['conversions'] ?? 0,
					'property_id'         => $this->property_id,
				] );

				$synced++;
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->query( 'COMMIT' );
		} catch ( \Throwable $e ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->query( 'ROLLBACK' );
			throw $e;
		}

		// Log the sync.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->insert( $wpdb->prefix . 'sf_sync_log', [
			'source'          => 'ga4',
			'status'          => 'completed',
			'pages_synced'    => $synced,
			'keywords_synced' => 0,
			'started_at'      => current_time( 'mysql', true ),
			'completed_at'    => current_time( 'mysql', true ),
			'property_id'     => $this->property_id,
		] );

		return [ 'pages_synced' => $synced ];
	}

	/**
	 * Get GA4 behavior data for a specific page.
	 */
	public static function get_page_behavior( string $page_path ): ?array {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}sf_ga4_metrics
			WHERE page_path = %s
			ORDER BY snapshot_date DESC
			LIMIT 1",
			$page_path
		), ARRAY_A );
	}
}
