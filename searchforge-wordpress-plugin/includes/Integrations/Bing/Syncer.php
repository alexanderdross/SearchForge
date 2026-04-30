<?php

namespace SearchForge\Integrations\Bing;

use SearchForge\Admin\Settings;
use SearchForge\Models\Property;

defined( 'ABSPATH' ) || exit;

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
	 * Run a full sync of Bing Webmaster data.
	 *
	 * @return array|\WP_Error
	 */
	public function sync_all(): array|\WP_Error {
		global $wpdb;

		if ( ! Settings::is_pro() ) {
			return new \WP_Error( 'not_pro', __( 'Bing integration requires a Pro license.', 'searchforge-wordpress-plugin' ) );
		}

		$prop = Property::get( $this->property_id );
		if ( ! $prop ) {
			return new \WP_Error( 'no_property', __( 'Property not found.', 'searchforge-wordpress-plugin' ) );
		}

		$site_url = $prop['bing_site_url'] ?? '';
		if ( empty( $site_url ) ) {
			return new \WP_Error( 'no_site', __( 'No Bing site URL configured.', 'searchforge-wordpress-plugin' ) );
		}

		$api_key = $prop['bing_api_key'] ?? '';
		if ( empty( $api_key ) ) {
			return new \WP_Error( 'no_api_key', __( 'Bing API key not configured.', 'searchforge-wordpress-plugin' ) );
		}

		// Log sync start.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->insert( "{$wpdb->prefix}sf_sync_log", [
			'source'      => 'bing',
			'status'      => 'running',
			'property_id' => $this->property_id,
		] );
		$log_id = $wpdb->insert_id;
		$today  = gmdate( 'Y-m-d' );

		try {
			// Sync page stats.
			$page_stats = Client::get_page_stats( $site_url, $prop );
			if ( is_wp_error( $page_stats ) ) {
				$this->log_failure( $log_id, $page_stats->get_error_message() );
				return $page_stats;
			}

			$pages_synced = $this->store_page_data( $page_stats, $today );

			// Sync query stats.
			$query_stats = Client::get_query_stats( $site_url, $prop );
			if ( is_wp_error( $query_stats ) ) {
				$this->log_failure( $log_id, $query_stats->get_error_message() );
				return $query_stats;
			}

			$keywords_synced = $this->store_keyword_data( $query_stats, $today );

			// Log success.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->update( "{$wpdb->prefix}sf_sync_log", [
				'status'          => 'completed',
				'pages_synced'    => $pages_synced,
				'keywords_synced' => $keywords_synced,
				'completed_at'    => current_time( 'mysql', true ),
			], [ 'id' => $log_id ] );

			return [
				'pages_synced'    => $pages_synced,
				'keywords_synced' => $keywords_synced,
				'source'          => 'bing',
			];

		} catch ( \Exception $e ) {
			$this->log_failure( $log_id, $e->getMessage() );
			return new \WP_Error( 'sync_error', $e->getMessage() );
		}
	}

	private function store_page_data( array $stats, string $snapshot_date ): int {
		global $wpdb;
		$table = "{$wpdb->prefix}sf_snapshots";
		$count = 0;

		// Bing returns an array of page stat objects.
		$pages = is_array( $stats ) ? $stats : [];

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( 'START TRANSACTION' );

		try {
			foreach ( $pages as $entry ) {
				$page_url  = $entry['Query'] ?? $entry['Url'] ?? '';
				if ( empty( $page_url ) ) {
					continue;
				}

				$page_path = wp_parse_url( $page_url, PHP_URL_PATH ) ?: '/';

				$clicks      = (int) ( $entry['Clicks'] ?? 0 );
				$impressions = (int) ( $entry['Impressions'] ?? 0 );
				$ctr         = $impressions > 0 ? $clicks / $impressions : 0;
				$position    = (float) ( $entry['AvgImpressionPosition'] ?? $entry['Position'] ?? 0 );

				// Upsert.
				// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
				$wpdb->query( $wpdb->prepare(
					"DELETE FROM {$table} WHERE page_path = %s AND snapshot_date = %s AND source = 'bing' AND device = 'all' AND property_id = %d",
					$page_path,
					$snapshot_date,
					$this->property_id
				) );
				// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$result = $wpdb->insert( $table, [
					'page_url'      => $page_url,
					'page_path'     => $page_path,
					'snapshot_date' => $snapshot_date,
					'clicks'        => $clicks,
					'impressions'   => $impressions,
					'ctr'           => $ctr,
					'position'      => $position,
					'device'        => 'all',
					'source'        => 'bing',
					'property_id'   => $this->property_id,
				] );

				if ( false === $result ) {
					throw new \RuntimeException( "Failed to insert Bing page data for: {$page_path}" );
				}

				$count++;
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->query( 'COMMIT' );
		} catch ( \Exception $e ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->query( 'ROLLBACK' );
			throw $e;
		}

		return $count;
	}

	private function store_keyword_data( array $stats, string $snapshot_date ): int {
		global $wpdb;
		$table = "{$wpdb->prefix}sf_keywords";
		$count = 0;

		$queries = is_array( $stats ) ? $stats : [];

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( 'START TRANSACTION' );

		try {
			// Delete existing Bing keywords for this date and property.
			// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$wpdb->query( $wpdb->prepare(
				"DELETE FROM {$table} WHERE snapshot_date = %s AND source = 'bing' AND property_id = %d",
				$snapshot_date,
				$this->property_id
			) );
			// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter

			foreach ( $queries as $entry ) {
				$query = $entry['Query'] ?? '';
				if ( empty( $query ) ) {
					continue;
				}

				$clicks      = (int) ( $entry['Clicks'] ?? 0 );
				$impressions = (int) ( $entry['Impressions'] ?? 0 );
				$ctr         = $impressions > 0 ? $clicks / $impressions : 0;
				$position    = (float) ( $entry['AvgImpressionPosition'] ?? $entry['Position'] ?? 0 );

				// Bing query stats don't always include page info.
				$page_path = '/';
				if ( ! empty( $entry['Url'] ) ) {
					$page_path = wp_parse_url( $entry['Url'], PHP_URL_PATH ) ?: '/';
				}

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$result = $wpdb->insert( $table, [
					'page_path'     => $page_path,
					'query'         => $query,
					'snapshot_date' => $snapshot_date,
					'clicks'        => $clicks,
					'impressions'   => $impressions,
					'ctr'           => $ctr,
					'position'      => $position,
					'device'        => 'all',
					'source'        => 'bing',
					'property_id'   => $this->property_id,
				] );

				if ( false === $result ) {
					throw new \RuntimeException( "Failed to insert Bing keyword data for: {$query}" );
				}

				$count++;
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->query( 'COMMIT' );
		} catch ( \Exception $e ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->query( 'ROLLBACK' );
			throw $e;
		}

		return $count;
	}

	private function log_failure( int $log_id, string $message ): void {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update( "{$wpdb->prefix}sf_sync_log", [
			'status'        => 'failed',
			'error_message' => $message,
			'completed_at'  => current_time( 'mysql', true ),
		], [ 'id' => $log_id ] );
	}
}
