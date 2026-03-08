<?php

namespace SearchForge\Monitoring;

use SearchForge\Admin\Settings;

defined( 'ABSPATH' ) || exit;

class QuotaTracker {

	private const TRANSIENT_PREFIX = 'sf_quota_';

	/**
	 * Record API usage for a service.
	 *
	 * @param string $service Service name (gsc, bing, ga4, kwp, trends).
	 * @param int    $count   Number of API calls made.
	 */
	public static function record( string $service, int $count = 1 ): void {
		$key     = self::TRANSIENT_PREFIX . $service . '_' . gmdate( 'Y-m-d' );
		$current = (int) get_transient( $key );
		set_transient( $key, $current + $count, DAY_IN_SECONDS );
	}

	/**
	 * Get today's usage for a service.
	 */
	public static function get_today( string $service ): int {
		$key = self::TRANSIENT_PREFIX . $service . '_' . gmdate( 'Y-m-d' );
		return (int) get_transient( $key );
	}

	/**
	 * Get quota limits per service.
	 */
	public static function get_limits(): array {
		return [
			'gsc'    => [
				'label'       => __( 'Google Search Console', 'searchforge' ),
				'daily_limit' => 25000,
				'unit'        => __( 'requests', 'searchforge' ),
			],
			'bing'   => [
				'label'       => __( 'Bing Webmaster Tools', 'searchforge' ),
				'daily_limit' => 10000,
				'unit'        => __( 'requests', 'searchforge' ),
			],
			'ga4'    => [
				'label'       => __( 'Google Analytics 4', 'searchforge' ),
				'daily_limit' => 10000,
				'unit'        => __( 'tokens', 'searchforge' ),
			],
			'kwp'    => [
				'label'       => __( 'Keyword Planner', 'searchforge' ),
				'daily_limit' => 10000,
				'unit'        => __( 'operations', 'searchforge' ),
			],
			'trends' => [
				'label'       => __( 'Google Trends (SerpApi)', 'searchforge' ),
				'daily_limit' => 100,
				'unit'        => __( 'searches', 'searchforge' ),
			],
		];
	}

	/**
	 * Get usage summary for all services.
	 */
	public static function get_summary(): array {
		$limits  = self::get_limits();
		$summary = [];

		foreach ( $limits as $service => $info ) {
			$used = self::get_today( $service );
			$summary[ $service ] = [
				'label'       => $info['label'],
				'used'        => $used,
				'limit'       => $info['daily_limit'],
				'unit'        => $info['unit'],
				'pct'         => $info['daily_limit'] > 0 ? round( ( $used / $info['daily_limit'] ) * 100, 1 ) : 0,
				'status'      => self::get_status( $used, $info['daily_limit'] ),
			];
		}

		return $summary;
	}

	/**
	 * Check if a service is within quota.
	 */
	public static function has_quota( string $service ): bool {
		$limits = self::get_limits();
		if ( ! isset( $limits[ $service ] ) ) {
			return true;
		}

		return self::get_today( $service ) < $limits[ $service ]['daily_limit'];
	}

	/**
	 * Get status label based on usage percentage.
	 */
	private static function get_status( int $used, int $limit ): string {
		if ( $limit <= 0 ) {
			return 'ok';
		}

		$pct = ( $used / $limit ) * 100;

		if ( $pct >= 100 ) {
			return 'exhausted';
		}
		if ( $pct >= 80 ) {
			return 'warning';
		}
		return 'ok';
	}

	/**
	 * Check quotas and create alerts when approaching limits.
	 */
	public static function check_and_alert(): void {
		$summary = self::get_summary();

		foreach ( $summary as $service => $info ) {
			if ( $info['status'] === 'exhausted' ) {
				self::create_alert(
					$service,
					sprintf(
						__( '%s daily quota exhausted (%s/%s %s)', 'searchforge' ),
						$info['label'],
						number_format( $info['used'] ),
						number_format( $info['limit'] ),
						$info['unit']
					),
					'high'
				);
			} elseif ( $info['status'] === 'warning' ) {
				self::create_alert(
					$service,
					sprintf(
						__( '%s quota at %s%% (%s/%s %s)', 'searchforge' ),
						$info['label'],
						$info['pct'],
						number_format( $info['used'] ),
						number_format( $info['limit'] ),
						$info['unit']
					),
					'medium'
				);
			}
		}
	}

	/**
	 * Create a quota alert if one doesn't already exist today.
	 */
	private static function create_alert( string $service, string $title, string $severity ): void {
		global $wpdb;

		$existing = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}sf_alerts
			WHERE alert_type = 'quota_warning'
			AND created_at >= CURDATE()
			AND data LIKE %s",
			'%"service":"' . $service . '"%'
		) );

		if ( $existing > 0 ) {
			return;
		}

		$wpdb->insert( "{$wpdb->prefix}sf_alerts", [
			'alert_type' => 'quota_warning',
			'title'      => $title,
			'severity'   => $severity,
			'data'       => wp_json_encode( [ 'service' => $service ] ),
			'created_at' => current_time( 'mysql', true ),
			'is_read'    => 0,
		] );
	}
}
