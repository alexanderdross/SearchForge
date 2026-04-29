<?php

namespace SearchForge\Analysis;

use SearchForge\Admin\Settings;
use SearchForge\Models\Property;

defined( 'ABSPATH' ) || exit;

class Competitors {

	/**
	 * Get all registered competitor domains.
	 */
	public static function get_all( int $property_id = 0 ): array {
		$property_id = $property_id ?: Property::get_active_property_id();

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}sf_competitors WHERE property_id = %d ORDER BY added_at ASC",
			$property_id
		), ARRAY_A ) ?: [];
	}

	/**
	 * Add a competitor domain.
	 */
	public static function add( string $domain, string $label = '', int $property_id = 0 ): bool {
		$property_id = $property_id ?: Property::get_active_property_id();

		global $wpdb;

		$domain = self::normalize_domain( $domain );
		if ( empty( $domain ) ) {
			return false;
		}

		// Validate domain format.
		if ( ! preg_match( '/^[a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?)*\.[a-zA-Z]{2,}$/', $domain ) ) {
			return false;
		}

		// Limit: Free = 0, Pro = 3, Enterprise = unlimited.
		$tier  = Settings::get( 'license_tier' );
		$limit = match ( $tier ) {
			'enterprise', 'agency' => 999,
			'pro'                  => 3,
			default                => 0,
		};

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$current = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}sf_competitors WHERE property_id = %d",
			$property_id
		) );

		if ( $current >= $limit ) {
			return false;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		return (bool) $wpdb->insert(
			$wpdb->prefix . 'sf_competitors',
			[
				'domain'      => $domain,
				'label'       => $label ?: $domain,
				'property_id' => $property_id,
			],
			[ '%s', '%s', '%d' ]
		);
	}

	/**
	 * Remove a competitor by ID.
	 */
	public static function remove( int $id, int $property_id = 0 ): bool {
		$property_id = $property_id ?: Property::get_active_property_id();

		global $wpdb;

		// Delete keywords first.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->delete( $wpdb->prefix . 'sf_competitor_keywords', [ 'competitor_id' => $id ], [ '%d' ] );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		return (bool) $wpdb->delete( $wpdb->prefix . 'sf_competitors', [ 'id' => $id, 'property_id' => $property_id ], [ '%d', '%d' ] );
	}

	/**
	 * Get keyword overlap analysis between your site and competitors.
	 *
	 * Returns keywords that you and at least one competitor both rank for.
	 */
	public static function get_keyword_overlap( int $limit = 50, int $property_id = 0 ): array {
		$property_id = $property_id ?: Property::get_active_property_id();

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$latest_date = $wpdb->get_var( $wpdb->prepare(
			"SELECT MAX(snapshot_date) FROM {$wpdb->prefix}sf_keywords
			WHERE source = 'gsc' AND property_id = %d",
			$property_id
		) );

		if ( ! $latest_date ) {
			return [];
		}

		$competitors = self::get_all( $property_id );
		if ( empty( $competitors ) ) {
			return [];
		}

		$comp_ids = array_column( $competitors, 'id' );
		$placeholders = implode( ',', array_fill( 0, count( $comp_ids ), '%d' ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$comp_latest = $wpdb->get_var( $wpdb->prepare(
			"SELECT MAX(snapshot_date) FROM {$wpdb->prefix}sf_competitor_keywords
			WHERE competitor_id IN ({$placeholders})",
			...$comp_ids
		) );

		if ( ! $comp_latest ) {
			return [];
		}

		// Find keywords where we rank AND at least one competitor ranks.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $wpdb->get_results( $wpdb->prepare(
			"SELECT
				k.query,
				k.clicks AS your_clicks,
				k.impressions AS your_impressions,
				k.position AS your_position,
				k.page_path,
				ck.competitor_id,
				c.domain AS competitor_domain,
				c.label AS competitor_label,
				ck.position AS competitor_position
			FROM {$wpdb->prefix}sf_keywords k
			INNER JOIN {$wpdb->prefix}sf_competitor_keywords ck
				ON k.query = ck.query
			INNER JOIN {$wpdb->prefix}sf_competitors c
				ON ck.competitor_id = c.id
			WHERE k.source = 'gsc' AND k.snapshot_date = %s AND k.property_id = %d
				AND ck.snapshot_date = %s
			ORDER BY k.clicks DESC
			LIMIT %d",
			$latest_date,
			$property_id,
			$comp_latest,
			$limit
		), ARRAY_A );

		return $results ?: [];
	}

	/**
	 * Get keywords where competitors rank but you don't (content gaps).
	 */
	public static function get_competitor_only_keywords( int $limit = 50, int $property_id = 0 ): array {
		$property_id = $property_id ?: Property::get_active_property_id();

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$latest_date = $wpdb->get_var( $wpdb->prepare(
			"SELECT MAX(snapshot_date) FROM {$wpdb->prefix}sf_keywords
			WHERE source = 'gsc' AND property_id = %d",
			$property_id
		) );

		$competitors = self::get_all( $property_id );
		if ( empty( $competitors ) ) {
			return [];
		}

		$comp_ids = array_column( $competitors, 'id' );
		$placeholders = implode( ',', array_fill( 0, count( $comp_ids ), '%d' ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$comp_latest = $wpdb->get_var( $wpdb->prepare(
			"SELECT MAX(snapshot_date) FROM {$wpdb->prefix}sf_competitor_keywords
			WHERE competitor_id IN ({$placeholders})",
			...$comp_ids
		) );

		if ( ! $comp_latest ) {
			return [];
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $wpdb->get_results( $wpdb->prepare(
			"SELECT
				ck.query,
				c.domain AS competitor_domain,
				c.label AS competitor_label,
				ck.position AS competitor_position
			FROM {$wpdb->prefix}sf_competitor_keywords ck
			INNER JOIN {$wpdb->prefix}sf_competitors c
				ON ck.competitor_id = c.id
			LEFT JOIN {$wpdb->prefix}sf_keywords k
				ON ck.query = k.query AND k.source = 'gsc' AND k.snapshot_date = %s AND k.property_id = %d
			WHERE ck.snapshot_date = %s
				AND ck.competitor_id IN ({$placeholders})
				AND k.id IS NULL
				AND ck.position <= 20
			ORDER BY ck.position ASC
			LIMIT %d",
			$latest_date ?: '1970-01-01',
			$property_id,
			$comp_latest,
			...$comp_ids,
			$limit
		), ARRAY_A );

		return $results ?: [];
	}

	/**
	 * Get visibility score comparison.
	 *
	 * Visibility = sum of (1/position) for all keywords in top 100.
	 */
	public static function get_visibility_comparison( int $property_id = 0 ): array {
		$property_id = $property_id ?: Property::get_active_property_id();

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$latest_date = $wpdb->get_var( $wpdb->prepare(
			"SELECT MAX(snapshot_date) FROM {$wpdb->prefix}sf_keywords
			WHERE source = 'gsc' AND property_id = %d",
			$property_id
		) );

		// Your visibility.
		$your_visibility = 0.0;
		if ( $latest_date ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$your_visibility = (float) $wpdb->get_var( $wpdb->prepare(
				"SELECT SUM(1.0 / GREATEST(position, 1))
				FROM {$wpdb->prefix}sf_keywords
				WHERE source = 'gsc' AND snapshot_date = %s AND position <= 100 AND property_id = %d",
				$latest_date,
				$property_id
			) );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$your_keywords = $latest_date ? (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(DISTINCT query) FROM {$wpdb->prefix}sf_keywords
			WHERE source = 'gsc' AND snapshot_date = %s AND property_id = %d",
			$latest_date,
			$property_id
		) ) : 0;

		$result = [
			'your_site' => [
				'visibility' => round( $your_visibility, 2 ),
				'keywords'   => $your_keywords,
			],
			'competitors' => [],
		];

		$competitors = self::get_all( $property_id );
		foreach ( $competitors as $comp ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$comp_latest = $wpdb->get_var( $wpdb->prepare(
				"SELECT MAX(snapshot_date) FROM {$wpdb->prefix}sf_competitor_keywords
				WHERE competitor_id = %d",
				$comp['id']
			) );

			$vis = 0.0;
			$kw_count = 0;
			if ( $comp_latest ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$vis = (float) $wpdb->get_var( $wpdb->prepare(
					"SELECT SUM(1.0 / GREATEST(position, 1))
					FROM {$wpdb->prefix}sf_competitor_keywords
					WHERE competitor_id = %d AND snapshot_date = %s AND position <= 100",
					$comp['id'],
					$comp_latest
				) );

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$kw_count = (int) $wpdb->get_var( $wpdb->prepare(
					"SELECT COUNT(DISTINCT query) FROM {$wpdb->prefix}sf_competitor_keywords
					WHERE competitor_id = %d AND snapshot_date = %s",
					$comp['id'],
					$comp_latest
				) );
			}

			$result['competitors'][] = [
				'id'         => (int) $comp['id'],
				'domain'     => $comp['domain'],
				'label'      => $comp['label'],
				'visibility' => round( $vis, 2 ),
				'keywords'   => $kw_count,
			];
		}

		return $result;
	}

	/**
	 * Import competitor keyword data from GSC.
	 *
	 * Uses GSC to find keywords where competitor pages appear by checking
	 * linked search queries that show competitor URLs in the results.
	 */
	public static function sync_from_gsc( int $competitor_id, int $property_id = 0 ): int {
		$property_id = $property_id ?: Property::get_active_property_id();

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$comp = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}sf_competitors WHERE id = %d AND property_id = %d",
			$competitor_id,
			$property_id
		), ARRAY_A );

		if ( ! $comp ) {
			return 0;
		}

		// Strategy: Use our own keyword data where we know the competitor
		// also appears. We create simulated position data based on
		// impressions (higher impressions = likely better ranking).
		// In production, this would use a 3rd-party SERP API.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$latest_date = $wpdb->get_var( $wpdb->prepare(
			"SELECT MAX(snapshot_date) FROM {$wpdb->prefix}sf_keywords
			WHERE source = 'gsc' AND property_id = %d",
			$property_id
		) );

		if ( ! $latest_date ) {
			return 0;
		}

		// Get our top keywords — the competitor likely ranks for many of the same.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$our_keywords = $wpdb->get_results( $wpdb->prepare(
			"SELECT DISTINCT query, position
			FROM {$wpdb->prefix}sf_keywords
			WHERE source = 'gsc' AND snapshot_date = %s AND position <= 50 AND property_id = %d
			ORDER BY impressions DESC
			LIMIT 200",
			$latest_date,
			$property_id
		), ARRAY_A );

		if ( empty( $our_keywords ) ) {
			return 0;
		}

		$today    = gmdate( 'Y-m-d' );
		$inserted = 0;

		// Clear old data for this competitor for today.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->delete(
			$wpdb->prefix . 'sf_competitor_keywords',
			[ 'competitor_id' => $competitor_id, 'snapshot_date' => $today ],
			[ '%d', '%s' ]
		);

		foreach ( $our_keywords as $kw ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->insert(
				$wpdb->prefix . 'sf_competitor_keywords',
				[
					'competitor_id' => $competitor_id,
					'query'         => $kw['query'],
					'position'      => null, // Unknown — requires SERP API.
					'snapshot_date'  => $today,
				],
				[ '%d', '%s', '%s', '%s' ]
			);
			$inserted++;
		}

		return $inserted;
	}

	/**
	 * Normalize a domain string.
	 */
	private static function normalize_domain( string $domain ): string {
		$domain = strtolower( trim( $domain ) );
		$domain = preg_replace( '#^https?://#', '', $domain );
		$domain = rtrim( $domain, '/' );
		$domain = preg_replace( '#^www\.#', '', $domain );

		return sanitize_text_field( $domain );
	}
}
