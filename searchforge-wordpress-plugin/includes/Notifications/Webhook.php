<?php

namespace SearchForge\Notifications;

use SearchForge\Admin\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Webhook notification system.
 *
 * Sends notifications to configured webhook URLs (Slack, custom)
 * on sync completion, errors, and alerts.
 */
class Webhook {

	public function __construct() {
		add_action( 'searchforge_sync_completed', [ $this, 'on_sync_completed' ], 10, 2 );
		add_action( 'searchforge_sync_failed', [ $this, 'on_sync_failed' ], 10, 2 );
		add_action( 'searchforge_alert_created', [ $this, 'on_alert_created' ], 10, 1 );
	}

	/**
	 * Handle sync completion.
	 */
	public function on_sync_completed( string $source, array $result ): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		$this->send( [
			'event'   => 'sync_completed',
			'source'  => $source,
			'pages'   => $result['pages_synced'] ?? 0,
			'keywords' => $result['keywords_synced'] ?? 0,
			'message' => sprintf(
				'SearchForge sync completed (%s): %d pages, %d keywords.',
				$source,
				$result['pages_synced'] ?? 0,
				$result['keywords_synced'] ?? 0
			),
		] );
	}

	/**
	 * Handle sync failure.
	 */
	public function on_sync_failed( string $source, string $error ): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		$this->send( [
			'event'   => 'sync_failed',
			'source'  => $source,
			'error'   => $error,
			'message' => sprintf(
				'SearchForge sync failed (%s): %s',
				$source,
				$error
			),
		] );
	}

	/**
	 * Handle new alert.
	 */
	public function on_alert_created( array $alert ): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		if ( Settings::get( 'webhook_on_alerts', true ) ) {
			$this->send( [
				'event'    => 'alert',
				'type'     => $alert['alert_type'] ?? '',
				'severity' => $alert['severity'] ?? 'info',
				'title'    => $alert['title'] ?? '',
				'message'  => sprintf(
					'[%s] %s: %s',
					strtoupper( $alert['severity'] ?? 'INFO' ),
					$alert['alert_type'] ?? '',
					$alert['title'] ?? ''
				),
			] );
		}
	}

	/**
	 * Send a webhook notification.
	 */
	private function send( array $payload ): void {
		$url    = Settings::get( 'webhook_url', '' );
		$format = Settings::get( 'webhook_format', 'json' );

		if ( empty( $url ) ) {
			return;
		}

		$payload['site']      = get_bloginfo( 'name' );
		$payload['site_url']  = home_url();
		$payload['timestamp'] = gmdate( 'c' );

		$body = $this->format_payload( $payload, $format );

		wp_remote_post( $url, [
			'timeout' => 10,
			'headers' => [ 'Content-Type' => 'application/json' ],
			'body'    => $body,
		] );
	}

	/**
	 * Format payload based on webhook format.
	 */
	private function format_payload( array $payload, string $format ): string {
		if ( $format === 'slack' ) {
			return wp_json_encode( [
				'text'   => $payload['message'],
				'blocks' => [
					[
						'type' => 'section',
						'text' => [
							'type' => 'mrkdwn',
							'text' => '*' . $payload['event'] . '* | ' . $payload['site'] . "\n" . $payload['message'],
						],
					],
				],
			] );
		}

		return wp_json_encode( $payload );
	}

	/**
	 * Check if webhooks are enabled.
	 */
	private function is_enabled(): bool {
		return Settings::is_pro()
			&& ! empty( Settings::get( 'webhook_enabled' ) )
			&& ! empty( Settings::get( 'webhook_url' ) );
	}
}
