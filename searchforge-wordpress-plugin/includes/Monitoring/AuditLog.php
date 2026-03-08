<?php

namespace SearchForge\Monitoring;

use SearchForge\Admin\Settings;

defined( 'ABSPATH' ) || exit;

class AuditLog {

	public function __construct() {
		// Sync events.
		add_action( 'searchforge_sync_completed', [ $this, 'on_sync_completed' ], 99, 2 );
		add_action( 'searchforge_sync_failed', [ $this, 'on_sync_failed' ], 99, 2 );

		// Settings changes.
		add_action( 'update_option_searchforge_settings', [ $this, 'on_settings_updated' ], 10, 2 );

		// Data export.
		add_action( 'wp_ajax_searchforge_export_data', [ $this, 'on_export' ], 1 );

		// Alert dismissal.
		add_action( 'wp_ajax_searchforge_dismiss_alert', [ $this, 'on_alert_dismissed' ], 1 );
	}

	public function on_sync_completed( string $source, $result ): void {
		$pages = 0;
		$keywords = 0;
		if ( is_array( $result ) ) {
			$pages    = $result['pages'] ?? $result['pages_synced'] ?? 0;
			$keywords = $result['keywords'] ?? $result['keywords_synced'] ?? 0;
		}

		self::log( 'sync_completed', sprintf(
			'%s sync completed: %d pages, %d keywords',
			strtoupper( $source ),
			$pages,
			$keywords
		) );
	}

	public function on_sync_failed( string $source, string $error ): void {
		self::log( 'sync_failed', sprintf(
			'%s sync failed: %s',
			strtoupper( $source ),
			$error
		) );
	}

	public function on_settings_updated( $old_value, $new_value ): void {
		$sensitive = [ 'gsc_access_token', 'gsc_refresh_token', 'gsc_client_secret', 'bing_api_key',
			'kwp_developer_token', 'serpapi_key', 'ai_api_key', 'webhook_url', 'license_key' ];

		$changes = [];
		foreach ( $new_value as $key => $val ) {
			$old = $old_value[ $key ] ?? '';
			if ( $val !== $old ) {
				if ( in_array( $key, $sensitive, true ) ) {
					$changes[] = $key . ': [redacted]';
				} else {
					$changes[] = $key . ': ' . wp_json_encode( $old ) . ' → ' . wp_json_encode( $val );
				}
			}
		}

		if ( ! empty( $changes ) ) {
			self::log( 'settings_updated', implode( '; ', $changes ) );
		}
	}

	public function on_export(): void {
		$type   = sanitize_text_field( $_POST['export_type'] ?? 'unknown' );
		$format = sanitize_text_field( $_POST['export_format'] ?? 'unknown' );
		self::log( 'data_export', "Exported {$type} as {$format}" );
	}

	public function on_alert_dismissed(): void {
		$alert_id = absint( $_POST['alert_id'] ?? 0 );
		if ( $alert_id ) {
			self::log( 'alert_dismissed', "Alert #{$alert_id} dismissed" );
		}
	}

	/**
	 * Write an audit log entry.
	 */
	public static function log( string $action, string $details = '' ): void {
		global $wpdb;

		$user = wp_get_current_user();

		$wpdb->insert( "{$wpdb->prefix}sf_audit_log", [
			'user_id'    => $user->ID ?? 0,
			'user_login' => $user->user_login ?? 'system',
			'action'     => $action,
			'details'    => mb_substr( $details, 0, 2000 ),
			'ip_address' => self::get_ip(),
			'created_at' => current_time( 'mysql', true ),
		] );
	}

	/**
	 * Get audit log entries.
	 *
	 * @param int    $limit  Maximum entries.
	 * @param int    $offset Offset for pagination.
	 * @param string $action Filter by action type (optional).
	 * @return array Log entries.
	 */
	public static function get_entries( int $limit = 50, int $offset = 0, string $action = '' ): array {
		global $wpdb;

		$where = '';
		$params = [];

		if ( $action ) {
			$where = 'WHERE action = %s';
			$params[] = $action;
		}

		$params[] = $limit;
		$params[] = $offset;

		return $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}sf_audit_log
			{$where}
			ORDER BY created_at DESC
			LIMIT %d OFFSET %d",
			...$params
		), ARRAY_A ) ?: [];
	}

	/**
	 * Get total count of audit log entries.
	 */
	public static function get_total( string $action = '' ): int {
		global $wpdb;

		if ( $action ) {
			return (int) $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}sf_audit_log WHERE action = %s",
				$action
			) );
		}

		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}sf_audit_log" );
	}

	/**
	 * Get the client IP address.
	 */
	private static function get_ip(): string {
		$ip = $_SERVER['REMOTE_ADDR'] ?? '';
		return filter_var( $ip, FILTER_VALIDATE_IP ) ?: '';
	}
}
