<?php

namespace SearchForge\Analysis;

use SearchForge\Models\Property;

defined( 'ABSPATH' ) || exit;

class PropertyComparison {

	public static function compare_summaries( array $property_ids ): array {
		global $wpdb;

		$result = [];

		foreach ( $property_ids as $pid ) {
			$prop = Property::get( (int) $pid );
			if ( ! $prop ) {
				continue;
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$latest_date = $wpdb->get_var( $wpdb->prepare(
				"SELECT MAX(snapshot_date) FROM {$wpdb->prefix}sf_snapshots
				WHERE property_id = %d AND source = 'gsc'",
				$pid
			) );

			$stats = null;
			if ( $latest_date ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$stats = $wpdb->get_row( $wpdb->prepare(
					"SELECT
						COUNT(DISTINCT page_path) as total_pages,
						SUM(clicks) as total_clicks,
						SUM(impressions) as total_impressions,
						AVG(position) as avg_position,
						AVG(ctr) as avg_ctr
					FROM {$wpdb->prefix}sf_snapshots
					WHERE property_id = %d AND source = 'gsc' AND snapshot_date = %s AND device = 'all'",
					$pid,
					$latest_date
				) );
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$keyword_count = $latest_date ? (int) $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(DISTINCT query) FROM {$wpdb->prefix}sf_keywords
				WHERE property_id = %d AND source = 'gsc' AND snapshot_date = %s",
				$pid,
				$latest_date
			) ) : 0;

			$result[ $pid ] = [
				'property_id'      => (int) $pid,
				'label'            => $prop['label'],
				'domain'           => $prop['domain'],
				'total_pages'      => $stats ? (int) $stats->total_pages : 0,
				'total_clicks'     => $stats ? (int) $stats->total_clicks : 0,
				'total_impressions' => $stats ? (int) $stats->total_impressions : 0,
				'avg_position'     => $stats ? round( (float) $stats->avg_position, 1 ) : 0,
				'avg_ctr'          => $stats ? round( (float) $stats->avg_ctr * 100, 2 ) : 0,
				'total_keywords'   => $keyword_count,
				'snapshot_date'    => $latest_date,
			];
		}

		return $result;
	}

	public static function compare_pages( array $property_ids, int $limit = 50 ): array {
		global $wpdb;

		if ( count( $property_ids ) < 2 ) {
			return [];
		}

		$placeholders = implode( ',', array_fill( 0, count( $property_ids ), '%d' ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT
				s.property_id,
				s.page_path,
				s.clicks,
				s.impressions,
				s.position,
				s.ctr,
				s.snapshot_date
			FROM {$wpdb->prefix}sf_snapshots s
			INNER JOIN (
				SELECT property_id, MAX(snapshot_date) as max_date
				FROM {$wpdb->prefix}sf_snapshots
				WHERE property_id IN ({$placeholders}) AND source = 'gsc' AND device = 'all'
				GROUP BY property_id
			) latest ON s.property_id = latest.property_id AND s.snapshot_date = latest.max_date
			WHERE s.source = 'gsc' AND s.device = 'all'
			ORDER BY s.clicks DESC
			LIMIT %d",
			...[ ...$property_ids, ...$property_ids, $limit * count( $property_ids ) ]
		), ARRAY_A ) ?: [];

		$pages = [];
		foreach ( $rows as $row ) {
			$path = $row['page_path'];
			if ( ! isset( $pages[ $path ] ) ) {
				$pages[ $path ] = [];
			}
			$pages[ $path ][ (int) $row['property_id'] ] = [
				'clicks'      => (int) $row['clicks'],
				'impressions' => (int) $row['impressions'],
				'position'    => round( (float) $row['position'], 1 ),
				'ctr'         => round( (float) $row['ctr'] * 100, 2 ),
			];
		}

		// Keep only pages appearing in 2+ properties, sort by total clicks.
		$shared = [];
		foreach ( $pages as $path => $props ) {
			if ( count( $props ) >= 2 ) {
				$total_clicks = array_sum( array_column( $props, 'clicks' ) );
				$shared[] = [
					'page_path'   => $path,
					'properties'  => $props,
					'total_clicks' => $total_clicks,
				];
			}
		}

		usort( $shared, fn( $a, $b ) => $b['total_clicks'] <=> $a['total_clicks'] );

		return array_slice( $shared, 0, $limit );
	}

	public static function compare_keywords( array $property_ids, int $limit = 50 ): array {
		global $wpdb;

		if ( count( $property_ids ) < 2 ) {
			return [];
		}

		$placeholders = implode( ',', array_fill( 0, count( $property_ids ), '%d' ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT
				k.property_id,
				k.query,
				SUM(k.clicks) as clicks,
				SUM(k.impressions) as impressions,
				AVG(k.position) as position
			FROM {$wpdb->prefix}sf_keywords k
			INNER JOIN (
				SELECT property_id, MAX(snapshot_date) as max_date
				FROM {$wpdb->prefix}sf_keywords
				WHERE property_id IN ({$placeholders}) AND source = 'gsc'
				GROUP BY property_id
			) latest ON k.property_id = latest.property_id AND k.snapshot_date = latest.max_date
			WHERE k.source = 'gsc'
			GROUP BY k.property_id, k.query
			ORDER BY clicks DESC
			LIMIT %d",
			...[ ...$property_ids, ...$property_ids, $limit * count( $property_ids ) ]
		), ARRAY_A ) ?: [];

		$keywords = [];
		foreach ( $rows as $row ) {
			$query = $row['query'];
			if ( ! isset( $keywords[ $query ] ) ) {
				$keywords[ $query ] = [];
			}
			$keywords[ $query ][ (int) $row['property_id'] ] = [
				'clicks'      => (int) $row['clicks'],
				'impressions' => (int) $row['impressions'],
				'position'    => round( (float) $row['position'], 1 ),
			];
		}

		$shared = [];
		foreach ( $keywords as $query => $props ) {
			if ( count( $props ) >= 2 ) {
				$total_clicks = array_sum( array_column( $props, 'clicks' ) );
				$shared[] = [
					'query'        => $query,
					'properties'   => $props,
					'total_clicks' => $total_clicks,
				];
			}
		}

		usort( $shared, fn( $a, $b ) => $b['total_clicks'] <=> $a['total_clicks'] );

		return array_slice( $shared, 0, $limit );
	}

	public static function aggregate_totals(): array {
		global $wpdb;

		$properties = Property::get_all();
		$totals     = [
			'properties'       => count( $properties ),
			'total_pages'      => 0,
			'total_clicks'     => 0,
			'total_impressions' => 0,
			'total_keywords'   => 0,
		];

		foreach ( $properties as $prop ) {
			$pid = (int) $prop['id'];
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$latest_date = $wpdb->get_var( $wpdb->prepare(
				"SELECT MAX(snapshot_date) FROM {$wpdb->prefix}sf_snapshots
				WHERE property_id = %d AND source = 'gsc'",
				$pid
			) );

			if ( ! $latest_date ) {
				continue;
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$stats = $wpdb->get_row( $wpdb->prepare(
				"SELECT
					COUNT(DISTINCT page_path) as pages,
					SUM(clicks) as clicks,
					SUM(impressions) as impressions
				FROM {$wpdb->prefix}sf_snapshots
				WHERE property_id = %d AND source = 'gsc' AND snapshot_date = %s AND device = 'all'",
				$pid,
				$latest_date
			) );

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$kw_count = (int) $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(DISTINCT query) FROM {$wpdb->prefix}sf_keywords
				WHERE property_id = %d AND source = 'gsc' AND snapshot_date = %s",
				$pid,
				$latest_date
			) );

			$totals['total_pages']       += (int) $stats->pages;
			$totals['total_clicks']      += (int) $stats->clicks;
			$totals['total_impressions'] += (int) $stats->impressions;
			$totals['total_keywords']    += $kw_count;
		}

		return $totals;
	}
}
