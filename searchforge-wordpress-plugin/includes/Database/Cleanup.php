<?php

namespace SearchForge\Database;

use SearchForge\Admin\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Data retention cleanup.
 *
 * Automatically removes old data based on the tier's retention limit.
 * Free: 30 days, Pro: 365 days.
 */
class Cleanup {

	/**
	 * Run cleanup of expired data.
	 *
	 * @return array  [ 'snapshots' => int, 'keywords' => int, 'ga4' => int, 'alerts' => int, 'briefs' => int ]
	 */
	public static function run(): array {
		$retention_days = Settings::get( 'data_retention', 30 );
		$cutoff_date    = gmdate( 'Y-m-d', strtotime( "-{$retention_days} days" ) );

		global $wpdb;
		$deleted = [];

		// Clean snapshots.
		$deleted['snapshots'] = (int) $wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}sf_snapshots WHERE snapshot_date < %s",
			$cutoff_date
		) );

		// Clean keywords.
		$deleted['keywords'] = (int) $wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}sf_keywords WHERE snapshot_date < %s",
			$cutoff_date
		) );

		// Clean GA4 metrics.
		$deleted['ga4'] = (int) $wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}sf_ga4_metrics WHERE snapshot_date < %s",
			$cutoff_date
		) );

		// Clean old alerts (keep 2x retention period).
		$alert_cutoff = gmdate( 'Y-m-d H:i:s', strtotime( "-" . ( $retention_days * 2 ) . " days" ) );
		$deleted['alerts'] = (int) $wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}sf_alerts WHERE created_at < %s AND is_read = 1",
			$alert_cutoff
		) );

		// Clean expired brief caches.
		$deleted['briefs'] = (int) $wpdb->query(
			"DELETE FROM {$wpdb->prefix}sf_briefs_cache WHERE expires_at < NOW()"
		);

		// Clean old sync logs (keep last 90 entries).
		$log_count = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}sf_sync_log"
		);
		if ( $log_count > 90 ) {
			$keep_id = $wpdb->get_var(
				"SELECT id FROM {$wpdb->prefix}sf_sync_log ORDER BY id DESC LIMIT 1 OFFSET 89"
			);
			if ( $keep_id ) {
				$deleted['logs'] = (int) $wpdb->query( $wpdb->prepare(
					"DELETE FROM {$wpdb->prefix}sf_sync_log WHERE id < %d",
					$keep_id
				) );
			}
		}

		return $deleted;
	}
}
