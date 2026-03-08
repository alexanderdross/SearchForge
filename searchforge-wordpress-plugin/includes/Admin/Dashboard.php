<?php

namespace SearchForge\Admin;

defined( 'ABSPATH' ) || exit;

class Dashboard {

	public function __construct() {
		// Invalidate cache after sync.
		add_action( 'searchforge_sync_completed', [ __CLASS__, 'invalidate_cache' ] );
	}

	/**
	 * Get summary stats for the dashboard (cached for 5 minutes).
	 */
	public static function get_summary(): array {
		$cached = get_transient( 'searchforge_dashboard_summary' );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		global $wpdb;

		$latest_date = $wpdb->get_var(
			"SELECT MAX(snapshot_date) FROM {$wpdb->prefix}sf_snapshots WHERE source = 'gsc'"
		);

		if ( ! $latest_date ) {
			return [
				'total_pages'       => 0,
				'total_clicks'      => 0,
				'total_impressions' => 0,
				'avg_position'      => 0,
				'avg_ctr'           => 0,
				'total_keywords'    => 0,
				'last_sync'         => null,
				'date_range'        => null,
			];
		}

		$page_stats = $wpdb->get_row( $wpdb->prepare(
			"SELECT
				COUNT(DISTINCT page_path) as total_pages,
				SUM(clicks) as total_clicks,
				SUM(impressions) as total_impressions,
				AVG(position) as avg_position,
				AVG(ctr) as avg_ctr
			FROM {$wpdb->prefix}sf_snapshots
			WHERE source = 'gsc' AND snapshot_date = %s AND device = 'all'",
			$latest_date
		) );

		$keyword_count = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(DISTINCT query) FROM {$wpdb->prefix}sf_keywords
			WHERE source = 'gsc' AND snapshot_date = %s",
			$latest_date
		) );

		$last_sync = $wpdb->get_row(
			"SELECT started_at, status FROM {$wpdb->prefix}sf_sync_log
			WHERE source = 'gsc' ORDER BY id DESC LIMIT 1"
		);

		$summary = [
			'total_pages'       => (int) ( $page_stats->total_pages ?? 0 ),
			'total_clicks'      => (int) ( $page_stats->total_clicks ?? 0 ),
			'total_impressions' => (int) ( $page_stats->total_impressions ?? 0 ),
			'avg_position'      => round( (float) ( $page_stats->avg_position ?? 0 ), 1 ),
			'avg_ctr'           => round( (float) ( $page_stats->avg_ctr ?? 0 ) * 100, 1 ),
			'total_keywords'    => $keyword_count,
			'last_sync'         => $last_sync->started_at ?? null,
			'sync_status'       => $last_sync->status ?? null,
			'date_range'        => $latest_date,
		];

		set_transient( 'searchforge_dashboard_summary', $summary, 5 * MINUTE_IN_SECONDS );

		return $summary;
	}

	/**
	 * Invalidate dashboard cache.
	 */
	public static function invalidate_cache(): void {
		delete_transient( 'searchforge_dashboard_summary' );
	}

	/**
	 * Get top pages by clicks with pagination and search.
	 */
	public static function get_top_pages( int $limit = 10, string $date = '', int $offset = 0, string $search = '' ): array {
		global $wpdb;

		if ( ! $date ) {
			$date = $wpdb->get_var(
				"SELECT MAX(snapshot_date) FROM {$wpdb->prefix}sf_snapshots WHERE source = 'gsc'"
			);
		}

		if ( ! $date ) {
			return [];
		}

		$page_limit = Settings::get_page_limit();
		$query_limit = $page_limit > 0 ? min( $limit, $page_limit ) : $limit;

		$where = $wpdb->prepare(
			"source = 'gsc' AND snapshot_date = %s AND device = 'all'",
			$date
		);

		if ( $search ) {
			$where .= $wpdb->prepare( " AND page_path LIKE %s", '%' . $wpdb->esc_like( $search ) . '%' );
		}

		return $wpdb->get_results(
			"SELECT page_path, clicks, impressions, ctr, position
			FROM {$wpdb->prefix}sf_snapshots
			WHERE {$where}
			ORDER BY clicks DESC
			LIMIT {$query_limit} OFFSET {$offset}",
			ARRAY_A
		); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/**
	 * Count total pages for pagination.
	 */
	public static function count_pages( string $search = '' ): int {
		global $wpdb;

		$date = $wpdb->get_var(
			"SELECT MAX(snapshot_date) FROM {$wpdb->prefix}sf_snapshots WHERE source = 'gsc'"
		);

		if ( ! $date ) {
			return 0;
		}

		$where = $wpdb->prepare(
			"source = 'gsc' AND snapshot_date = %s AND device = 'all'",
			$date
		);

		if ( $search ) {
			$where .= $wpdb->prepare( " AND page_path LIKE %s", '%' . $wpdb->esc_like( $search ) . '%' );
		}

		return (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}sf_snapshots WHERE {$where}" // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
	}

	/**
	 * Get top keywords by clicks with pagination and search.
	 */
	public static function get_top_keywords( int $limit = 20, string $date = '', int $offset = 0, string $search = '' ): array {
		global $wpdb;

		if ( ! $date ) {
			$date = $wpdb->get_var(
				"SELECT MAX(snapshot_date) FROM {$wpdb->prefix}sf_keywords WHERE source = 'gsc'"
			);
		}

		if ( ! $date ) {
			return [];
		}

		$where = $wpdb->prepare(
			"source = 'gsc' AND snapshot_date = %s",
			$date
		);

		if ( $search ) {
			$like = '%' . $wpdb->esc_like( $search ) . '%';
			$where .= $wpdb->prepare( " AND (query LIKE %s OR page_path LIKE %s)", $like, $like );
		}

		return $wpdb->get_results(
			"SELECT query, page_path, clicks, impressions, ctr, position
			FROM {$wpdb->prefix}sf_keywords
			WHERE {$where}
			ORDER BY clicks DESC
			LIMIT {$limit} OFFSET {$offset}",
			ARRAY_A
		); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/**
	 * Count total keywords for pagination.
	 */
	public static function count_keywords( string $search = '' ): int {
		global $wpdb;

		$date = $wpdb->get_var(
			"SELECT MAX(snapshot_date) FROM {$wpdb->prefix}sf_keywords WHERE source = 'gsc'"
		);

		if ( ! $date ) {
			return 0;
		}

		$where = $wpdb->prepare(
			"source = 'gsc' AND snapshot_date = %s",
			$date
		);

		if ( $search ) {
			$like = '%' . $wpdb->esc_like( $search ) . '%';
			$where .= $wpdb->prepare( " AND (query LIKE %s OR page_path LIKE %s)", $like, $like );
		}

		return (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}sf_keywords WHERE {$where}" // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
	}
}
