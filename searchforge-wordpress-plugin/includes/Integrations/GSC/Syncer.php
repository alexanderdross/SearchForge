<?php

namespace SearchForge\Integrations\GSC;

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
	 * Run a full sync: pages + keywords from GSC.
	 *
	 * @return array|\WP_Error
	 */
	public function sync_all(): array|\WP_Error {
		global $wpdb;

		$prop = Property::get( $this->property_id );
		if ( ! $prop ) {
			return new \WP_Error( 'no_property', __( 'Property not found.', 'searchforge-wordpress-plugin' ) );
		}

		$gsc_property = $prop['gsc_property'] ?? '';
		if ( empty( $gsc_property ) ) {
			return new \WP_Error( 'no_property', __( 'No GSC property selected.', 'searchforge-wordpress-plugin' ) );
		}

		// Log sync start.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->insert( "{$wpdb->prefix}sf_sync_log", [
			'source'      => 'gsc',
			'status'      => 'running',
			'property_id' => $this->property_id,
		] );
		$log_id = $wpdb->insert_id;

		$end_date   = gmdate( 'Y-m-d', strtotime( '-2 days' ) );
		$start_date = gmdate( 'Y-m-d', strtotime( '-28 days' ) );
		$today      = gmdate( 'Y-m-d' );

		try {
			// Sync page data.
			$page_limit = Settings::get_page_limit();
			$limit      = $page_limit > 0 ? $page_limit : 25000;

			$pages = Client::get_page_data( $gsc_property, $start_date, $end_date, $limit, $prop );
			if ( is_wp_error( $pages ) ) {
				$this->log_failure( $log_id, $pages->get_error_message() );
				return $pages;
			}

			$pages_synced = $this->store_page_data( $pages, $today );

			// Sync keyword data.
			$keywords = Client::get_keyword_data( $gsc_property, $start_date, $end_date, '', $limit * 5, $prop );
			if ( is_wp_error( $keywords ) ) {
				$this->log_failure( $log_id, $keywords->get_error_message() );
				return $keywords;
			}

			$keywords_synced = $this->store_keyword_data( $keywords, $today );

			// Clean old data beyond retention period.
			$this->cleanup_old_data();

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
				'date_range'      => "$start_date to $end_date",
			];

		} catch ( \Exception $e ) {
			$this->log_failure( $log_id, $e->getMessage() );
			return new \WP_Error( 'sync_error', $e->getMessage() );
		}
	}

	private function store_page_data( array $pages, string $snapshot_date ): int {
		global $wpdb;
		$table = "{$wpdb->prefix}sf_snapshots";
		$count = 0;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( 'START TRANSACTION' );

		try {
			foreach ( $pages as $page ) {
				$page_url  = $page['page'];
				$page_path = wp_parse_url( $page_url, PHP_URL_PATH ) ?: '/';

				// Upsert: delete existing for this page+date+source+property, then insert.
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$wpdb->query( $wpdb->prepare(
					"DELETE FROM {$table} WHERE page_path = %s AND snapshot_date = %s AND source = 'gsc' AND device = 'all' AND property_id = %d",
					$page_path,
					$snapshot_date,
					$this->property_id
				) );

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$result = $wpdb->insert( $table, [
					'page_url'      => $page_url,
					'page_path'     => $page_path,
					'snapshot_date' => $snapshot_date,
					'clicks'        => $page['clicks'],
					'impressions'   => $page['impressions'],
					'ctr'           => $page['ctr'],
					'position'      => $page['position'],
					'device'        => 'all',
					'source'        => 'gsc',
					'property_id'   => $this->property_id,
				] );

				if ( false === $result ) {
					throw new \RuntimeException( "Failed to insert page data for: {$page_path}" );
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

	private function store_keyword_data( array $keywords, string $snapshot_date ): int {
		global $wpdb;
		$table = "{$wpdb->prefix}sf_keywords";
		$count = 0;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( 'START TRANSACTION' );

		try {
			// Delete existing keywords for this snapshot date and property.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( $wpdb->prepare(
				"DELETE FROM {$table} WHERE snapshot_date = %s AND source = 'gsc' AND property_id = %d",
				$snapshot_date,
				$this->property_id
			) );

			foreach ( $keywords as $kw ) {
				$page_path = wp_parse_url( $kw['page'], PHP_URL_PATH ) ?: '/';

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$result = $wpdb->insert( $table, [
					'page_path'     => $page_path,
					'query'         => $kw['query'],
					'snapshot_date' => $snapshot_date,
					'clicks'        => $kw['clicks'],
					'impressions'   => $kw['impressions'],
					'ctr'           => $kw['ctr'],
					'position'      => $kw['position'],
					'device'        => 'all',
					'source'        => 'gsc',
					'property_id'   => $this->property_id,
				] );

				if ( false === $result ) {
					throw new \RuntimeException( "Failed to insert keyword data for: {$kw['query']}" );
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

	private function cleanup_old_data(): void {
		global $wpdb;

		$retention_days = Settings::get_retention_days();
		$cutoff         = gmdate( 'Y-m-d', strtotime( "-{$retention_days} days" ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}sf_snapshots WHERE snapshot_date < %s AND property_id = %d",
			$cutoff,
			$this->property_id
		) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}sf_keywords WHERE snapshot_date < %s AND property_id = %d",
			$cutoff,
			$this->property_id
		) );
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
