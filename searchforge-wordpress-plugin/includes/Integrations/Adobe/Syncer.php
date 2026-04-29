<?php

namespace SearchForge\Integrations\Adobe;

use SearchForge\Admin\Settings;
use SearchForge\Models\Property;

defined( 'ABSPATH' ) || exit;

class Syncer {

	private int $property_id;

	public function __construct( int $property_id = 0 ) {
		$this->property_id = $property_id ?: Property::get_active_property_id();
	}

	public function sync(): array|\WP_Error {
		if ( ! Settings::is_pro() ) {
			return new \WP_Error( 'not_pro', __( 'Adobe Analytics integration requires a Pro license.', 'searchforge' ) );
		}

		$prop = Property::get( $this->property_id );
		if ( ! $prop ) {
			return new \WP_Error( 'no_property', __( 'Property not found.', 'searchforge' ) );
		}

		if ( empty( $prop['adobe_enabled'] ) || empty( $prop['adobe_client_id'] ) ) {
			return new \WP_Error( 'no_adobe', __( 'Adobe Analytics not configured for this property.', 'searchforge' ) );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'sf_ga4_metrics';
		$today = gmdate( 'Y-m-d' );

		$page_metrics = Client::get_page_metrics( 28, 500, $prop );
		if ( is_wp_error( $page_metrics ) ) {
			return $page_metrics;
		}

		$synced = 0;

		$wpdb->query( 'START TRANSACTION' );

		try {
			foreach ( $page_metrics as $path => $metrics ) {
				$existing = $wpdb->get_var( $wpdb->prepare(
					"SELECT id FROM {$table} WHERE page_path = %s AND snapshot_date = %s AND property_id = %d",
					$path,
					$today,
					$this->property_id
				) );

				if ( $existing ) {
					$wpdb->update(
						$table,
						[
							'sessions'        => $metrics['visits'],
							'bounce_rate'     => $metrics['bounce_rate'],
							'avg_session_dur' => $metrics['avg_time_page'],
							'conversions'     => $metrics['conversions'],
							'pageviews'       => $metrics['pageviews'],
						],
						[ 'id' => $existing ],
						[ '%d', '%f', '%f', '%d', '%d' ],
						[ '%d' ]
					);
				} else {
					$wpdb->insert( $table, [
						'page_path'       => $path,
						'snapshot_date'   => $today,
						'sessions'        => $metrics['visits'],
						'bounce_rate'     => $metrics['bounce_rate'],
						'avg_session_dur' => $metrics['avg_time_page'],
						'conversions'     => $metrics['conversions'],
						'pageviews'       => $metrics['pageviews'],
						'engaged_sessions' => 0,
						'organic_sessions' => 0,
						'organic_bounce'   => null,
						'organic_conversions' => 0,
						'property_id'     => $this->property_id,
					] );
				}

				$synced++;
			}

			$wpdb->query( 'COMMIT' );
		} catch ( \Throwable $e ) {
			$wpdb->query( 'ROLLBACK' );
			throw $e;
		}

		$wpdb->insert( $wpdb->prefix . 'sf_sync_log', [
			'source'          => 'adobe',
			'status'          => 'completed',
			'pages_synced'    => $synced,
			'keywords_synced' => 0,
			'started_at'      => current_time( 'mysql', true ),
			'completed_at'    => current_time( 'mysql', true ),
			'property_id'     => $this->property_id,
		] );

		return [ 'pages_synced' => $synced ];
	}
}
