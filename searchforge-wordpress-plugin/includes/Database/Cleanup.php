<?php

namespace SearchForge\Database;

use SearchForge\Admin\Settings;
use SearchForge\Models\Property;

defined( 'ABSPATH' ) || exit;

/**
 * Data retention cleanup.
 *
 * Automatically removes old data based on the tier's retention limit.
 * Free: 30 days, Pro: 365 days.
 *
 * Cleanup runs across all properties since retention policy is global.
 */
class Cleanup {

	/**
	 * Run cleanup of expired data.
	 *
	 * @param int $property_id Optional property ID. 0 = clean all properties (default).
	 * @return array  [ 'snapshots' => int, 'keywords' => int, 'ga4' => int, 'alerts' => int, 'briefs' => int ]
	 */
	public static function run( int $property_id = 0 ): array {
		$retention_days = Settings::get( 'data_retention', 30 );
		$cutoff_date    = gmdate( 'Y-m-d', strtotime( "-{$retention_days} days" ) );

		global $wpdb;
		$deleted = [];

		$property_clause = '';
		$property_args   = [];
		if ( $property_id > 0 ) {
			$property_clause = ' AND property_id = %d';
			$property_args   = [ $property_id ];
		}

		// Clean snapshots.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$deleted['snapshots'] = (int) $wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}sf_snapshots WHERE snapshot_date < %s{$property_clause}",
			$cutoff_date,
			...$property_args
		) );

		// Clean keywords.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$deleted['keywords'] = (int) $wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}sf_keywords WHERE snapshot_date < %s{$property_clause}",
			$cutoff_date,
			...$property_args
		) );

		// Clean GA4 metrics.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$deleted['ga4'] = (int) $wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}sf_ga4_metrics WHERE snapshot_date < %s{$property_clause}",
			$cutoff_date,
			...$property_args
		) );

		// Clean old alerts (keep 2x retention period).
		$alert_cutoff = gmdate( 'Y-m-d H:i:s', strtotime( "-" . ( $retention_days * 2 ) . " days" ) );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$deleted['alerts'] = (int) $wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}sf_alerts WHERE created_at < %s AND is_read = 1{$property_clause}",
			$alert_cutoff,
			...$property_args
		) );

		// Clean expired brief caches.
		if ( $property_id > 0 ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$deleted['briefs'] = (int) $wpdb->query( $wpdb->prepare(
				"DELETE FROM {$wpdb->prefix}sf_briefs_cache WHERE expires_at < NOW() AND property_id = %d",
				$property_id
			) );
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$deleted['briefs'] = (int) $wpdb->query(
				"DELETE FROM {$wpdb->prefix}sf_briefs_cache WHERE expires_at < NOW()"
			);
		}

		// Clean old sync logs (keep last 90 entries per property or globally).
		if ( $property_id > 0 ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$log_count = (int) $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}sf_sync_log WHERE property_id = %d",
				$property_id
			) );
			if ( $log_count > 90 ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$keep_id = $wpdb->get_var( $wpdb->prepare(
					"SELECT id FROM {$wpdb->prefix}sf_sync_log WHERE property_id = %d ORDER BY id DESC LIMIT 1 OFFSET 89",
					$property_id
				) );
				if ( $keep_id ) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
					$deleted['logs'] = (int) $wpdb->query( $wpdb->prepare(
						"DELETE FROM {$wpdb->prefix}sf_sync_log WHERE id < %d AND property_id = %d",
						$keep_id,
						$property_id
					) );
				}
			}
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$log_count = (int) $wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->prefix}sf_sync_log"
			);
			if ( $log_count > 90 ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$keep_id = $wpdb->get_var(
					"SELECT id FROM {$wpdb->prefix}sf_sync_log ORDER BY id DESC LIMIT 1 OFFSET 89"
				);
				if ( $keep_id ) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
					$deleted['logs'] = (int) $wpdb->query( $wpdb->prepare(
						"DELETE FROM {$wpdb->prefix}sf_sync_log WHERE id < %d",
						$keep_id
					) );
				}
			}
		}

		return $deleted;
	}
}
