<?php

namespace SearchForge\Monitoring;

defined( 'ABSPATH' ) || exit;

class SslChecker {

	/**
	 * Check the SSL certificate of the site.
	 *
	 * @return array|null Certificate info or null on failure.
	 */
	public static function check(): ?array {
		$site_url = home_url();
		$parsed   = wp_parse_url( $site_url );

		if ( empty( $parsed['host'] ) || ( $parsed['scheme'] ?? '' ) !== 'https' ) {
			return null;
		}

		$host = $parsed['host'];
		$port = $parsed['port'] ?? 443;

		$context = stream_context_create( [
			'ssl' => [
				'capture_peer_cert' => true,
				'verify_peer'       => false,
				'verify_peer_name'  => false,
			],
		] );

		$client = @stream_socket_client(
			"ssl://{$host}:{$port}",
			$errno,
			$errstr,
			10,
			STREAM_CLIENT_CONNECT,
			$context
		);

		if ( ! $client ) {
			return [
				'status'  => 'error',
				'message' => $errstr ?: __( 'Could not connect to SSL endpoint.', 'searchforge' ),
			];
		}

		$params = stream_context_get_params( $client );
		fclose( $client );

		if ( empty( $params['options']['ssl']['peer_certificate'] ) ) {
			return [
				'status'  => 'error',
				'message' => __( 'No SSL certificate found.', 'searchforge' ),
			];
		}

		$cert_info = openssl_x509_parse( $params['options']['ssl']['peer_certificate'] );
		if ( ! $cert_info ) {
			return [
				'status'  => 'error',
				'message' => __( 'Could not parse SSL certificate.', 'searchforge' ),
			];
		}

		$valid_from = $cert_info['validFrom_time_t'] ?? 0;
		$valid_to   = $cert_info['validTo_time_t'] ?? 0;
		$issuer     = $cert_info['issuer']['O'] ?? $cert_info['issuer']['CN'] ?? __( 'Unknown', 'searchforge' );
		$subject    = $cert_info['subject']['CN'] ?? $host;
		$now        = time();
		$days_left  = max( 0, (int) floor( ( $valid_to - $now ) / 86400 ) );

		$status = 'valid';
		if ( $now > $valid_to ) {
			$status = 'expired';
		} elseif ( $days_left <= 7 ) {
			$status = 'critical';
		} elseif ( $days_left <= 30 ) {
			$status = 'warning';
		}

		return [
			'status'     => $status,
			'host'       => $host,
			'subject'    => $subject,
			'issuer'     => $issuer,
			'valid_from' => gmdate( 'Y-m-d', $valid_from ),
			'valid_to'   => gmdate( 'Y-m-d', $valid_to ),
			'days_left'  => $days_left,
		];
	}

	/**
	 * Create an alert if certificate is expiring soon.
	 */
	public static function check_and_alert(): void {
		$result = self::check();
		if ( ! $result || $result['status'] === 'valid' ) {
			return;
		}

		global $wpdb;

		$severity = match ( $result['status'] ) {
			'expired'  => 'high',
			'critical' => 'high',
			'warning'  => 'medium',
			default    => 'info',
		};

		if ( $result['status'] === 'expired' ) {
			$title = __( 'SSL certificate has expired!', 'searchforge' );
		} elseif ( $result['status'] === 'error' ) {
			$title = __( 'SSL certificate check failed', 'searchforge' );
		} else {
			$title = sprintf(
				__( 'SSL certificate expires in %d days', 'searchforge' ),
				$result['days_left']
			);
		}

		// Avoid duplicate alerts within 24h.
		$existing = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}sf_alerts
			WHERE alert_type = 'ssl_expiry'
			AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
			AND title = %s",
			$title
		) );

		if ( $existing > 0 ) {
			return;
		}

		$wpdb->insert( "{$wpdb->prefix}sf_alerts", [
			'alert_type' => 'ssl_expiry',
			'title'      => $title,
			'severity'   => $severity,
			'data'       => wp_json_encode( $result ),
			'created_at' => current_time( 'mysql', true ),
			'is_read'    => 0,
		] );
	}
}
