<?php

namespace SearchForge\Monitoring;

defined( 'ABSPATH' ) || exit;

class PerformanceTrend {

	/**
	 * Get daily click/impression trends for the last N days.
	 *
	 * @param int    $days   Number of days.
	 * @param string $source Data source ('gsc', 'bing').
	 * @return array Daily data points.
	 */
	public static function get_daily_trends( int $days = 30, string $source = 'gsc' ): array {
		global $wpdb;

		return $wpdb->get_results( $wpdb->prepare(
			"SELECT
				snapshot_date,
				SUM(clicks) AS clicks,
				SUM(impressions) AS impressions,
				ROUND(AVG(position), 2) AS avg_position,
				ROUND(AVG(ctr), 4) AS avg_ctr
			FROM {$wpdb->prefix}sf_snapshots
			WHERE source = %s AND device = 'all'
				AND snapshot_date >= DATE_SUB(CURDATE(), INTERVAL %d DAY)
			GROUP BY snapshot_date
			ORDER BY snapshot_date ASC",
			$source,
			$days
		), ARRAY_A ) ?: [];
	}

	/**
	 * Get weekly aggregated trends for longer periods.
	 *
	 * @param int    $weeks  Number of weeks.
	 * @param string $source Data source.
	 * @return array Weekly data points.
	 */
	public static function get_weekly_trends( int $weeks = 12, string $source = 'gsc' ): array {
		global $wpdb;

		return $wpdb->get_results( $wpdb->prepare(
			"SELECT
				YEARWEEK(snapshot_date, 1) AS year_week,
				MIN(snapshot_date) AS week_start,
				SUM(clicks) AS clicks,
				SUM(impressions) AS impressions,
				ROUND(AVG(position), 2) AS avg_position,
				ROUND(AVG(ctr), 4) AS avg_ctr,
				COUNT(DISTINCT page_path) AS pages_tracked
			FROM {$wpdb->prefix}sf_snapshots
			WHERE source = %s AND device = 'all'
				AND snapshot_date >= DATE_SUB(CURDATE(), INTERVAL %d WEEK)
			GROUP BY YEARWEEK(snapshot_date, 1)
			ORDER BY year_week ASC",
			$source,
			$weeks
		), ARRAY_A ) ?: [];
	}

	/**
	 * Get per-page performance trends.
	 *
	 * @param string $page_path Page path.
	 * @param int    $days      Number of days.
	 * @return array Daily data for the page.
	 */
	public static function get_page_trends( string $page_path, int $days = 30 ): array {
		global $wpdb;

		return $wpdb->get_results( $wpdb->prepare(
			"SELECT
				snapshot_date,
				clicks,
				impressions,
				position,
				ctr,
				source
			FROM {$wpdb->prefix}sf_snapshots
			WHERE page_path = %s AND device = 'all'
				AND snapshot_date >= DATE_SUB(CURDATE(), INTERVAL %d DAY)
			ORDER BY snapshot_date ASC",
			$page_path,
			$days
		), ARRAY_A ) ?: [];
	}

	/**
	 * Get period-over-period comparison.
	 *
	 * @param int $days Compare last N days vs previous N days.
	 * @return array Comparison data with changes.
	 */
	public static function get_period_comparison( int $days = 7 ): array {
		global $wpdb;

		$current = $wpdb->get_row( $wpdb->prepare(
			"SELECT
				SUM(clicks) AS clicks,
				SUM(impressions) AS impressions,
				ROUND(AVG(position), 2) AS avg_position,
				ROUND(AVG(ctr), 4) AS avg_ctr,
				COUNT(DISTINCT page_path) AS pages
			FROM {$wpdb->prefix}sf_snapshots
			WHERE source = 'gsc' AND device = 'all'
				AND snapshot_date >= DATE_SUB(CURDATE(), INTERVAL %d DAY)",
			$days
		), ARRAY_A );

		$previous = $wpdb->get_row( $wpdb->prepare(
			"SELECT
				SUM(clicks) AS clicks,
				SUM(impressions) AS impressions,
				ROUND(AVG(position), 2) AS avg_position,
				ROUND(AVG(ctr), 4) AS avg_ctr,
				COUNT(DISTINCT page_path) AS pages
			FROM {$wpdb->prefix}sf_snapshots
			WHERE source = 'gsc' AND device = 'all'
				AND snapshot_date >= DATE_SUB(CURDATE(), INTERVAL %d DAY)
				AND snapshot_date < DATE_SUB(CURDATE(), INTERVAL %d DAY)",
			$days * 2,
			$days
		), ARRAY_A );

		$calc_change = function ( $curr, $prev ) {
			if ( ! $prev || (float) $prev === 0.0 ) {
				return null;
			}
			return round( ( ( (float) $curr - (float) $prev ) / (float) $prev ) * 100, 1 );
		};

		return [
			'current'  => $current,
			'previous' => $previous,
			'changes'  => [
				'clicks'      => $calc_change( $current['clicks'] ?? 0, $previous['clicks'] ?? 0 ),
				'impressions' => $calc_change( $current['impressions'] ?? 0, $previous['impressions'] ?? 0 ),
				'position'    => $calc_change( $current['avg_position'] ?? 0, $previous['avg_position'] ?? 0 ),
				'ctr'         => $calc_change( $current['avg_ctr'] ?? 0, $previous['avg_ctr'] ?? 0 ),
			],
		];
	}
}
