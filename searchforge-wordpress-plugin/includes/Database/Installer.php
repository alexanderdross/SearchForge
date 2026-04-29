<?php

namespace SearchForge\Database;

use SearchForge\Models\Property;

defined( 'ABSPATH' ) || exit;

class Installer {

	public function install(): void {
		$this->create_tables();
		$this->migrate_to_properties();
		update_option( 'searchforge_db_version', SEARCHFORGE_DB_VERSION );
	}

	private function create_tables(): void {
		global $wpdb;

		$charset = $wpdb->get_charset_collate();

		$sql = "
			CREATE TABLE {$wpdb->prefix}sf_properties (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				label VARCHAR(255) NOT NULL,
				domain VARCHAR(255) NOT NULL,
				is_default TINYINT(1) NOT NULL DEFAULT 0,
				gsc_client_id VARCHAR(255) DEFAULT '',
				gsc_client_secret TEXT DEFAULT NULL,
				gsc_access_token TEXT DEFAULT NULL,
				gsc_refresh_token TEXT DEFAULT NULL,
				gsc_token_expires BIGINT UNSIGNED NOT NULL DEFAULT 0,
				gsc_property VARCHAR(512) DEFAULT '',
				bing_api_key TEXT DEFAULT NULL,
				bing_site_url VARCHAR(512) DEFAULT '',
				bing_enabled TINYINT(1) NOT NULL DEFAULT 0,
				ga4_property_id VARCHAR(100) DEFAULT '',
				ga4_enabled TINYINT(1) NOT NULL DEFAULT 0,
				created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				KEY idx_domain (domain),
				KEY idx_default (is_default)
			) {$charset};

			CREATE TABLE {$wpdb->prefix}sf_snapshots (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				property_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
				page_url VARCHAR(2048) NOT NULL,
				page_path VARCHAR(512) NOT NULL,
				snapshot_date DATE NOT NULL,
				clicks INT UNSIGNED NOT NULL DEFAULT 0,
				impressions INT UNSIGNED NOT NULL DEFAULT 0,
				ctr DECIMAL(5,4) NOT NULL DEFAULT 0,
				position DECIMAL(6,2) NOT NULL DEFAULT 0,
				device VARCHAR(10) NOT NULL DEFAULT 'all',
				source VARCHAR(20) NOT NULL DEFAULT 'gsc',
				created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				KEY idx_page_date (page_path, snapshot_date),
				KEY idx_source_date (source, snapshot_date),
				KEY idx_snapshot_date (snapshot_date),
				KEY idx_property_page_date (property_id, page_path, snapshot_date),
				KEY idx_property_source_date (property_id, source, snapshot_date)
			) {$charset};

			CREATE TABLE {$wpdb->prefix}sf_keywords (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				property_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
				page_path VARCHAR(512) NOT NULL,
				query VARCHAR(512) NOT NULL,
				snapshot_date DATE NOT NULL,
				clicks INT UNSIGNED NOT NULL DEFAULT 0,
				impressions INT UNSIGNED NOT NULL DEFAULT 0,
				ctr DECIMAL(5,4) NOT NULL DEFAULT 0,
				position DECIMAL(6,2) NOT NULL DEFAULT 0,
				device VARCHAR(10) NOT NULL DEFAULT 'all',
				source VARCHAR(20) NOT NULL DEFAULT 'gsc',
				search_volume INT UNSIGNED DEFAULT NULL,
				competition VARCHAR(10) DEFAULT NULL,
				created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				KEY idx_page_query (page_path, query(100)),
				KEY idx_query (query(100)),
				KEY idx_source_date (source, snapshot_date),
				KEY idx_property_page_date (property_id, page_path, snapshot_date),
				KEY idx_property_source_date (property_id, source, snapshot_date)
			) {$charset};

			CREATE TABLE {$wpdb->prefix}sf_sync_log (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				property_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
				source VARCHAR(20) NOT NULL,
				status VARCHAR(20) NOT NULL DEFAULT 'running',
				pages_synced INT UNSIGNED NOT NULL DEFAULT 0,
				keywords_synced INT UNSIGNED NOT NULL DEFAULT 0,
				error_message TEXT DEFAULT NULL,
				started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				completed_at DATETIME DEFAULT NULL,
				PRIMARY KEY (id),
				KEY idx_source_status (source, status),
				KEY idx_property_source (property_id, source)
			) {$charset};

			CREATE TABLE {$wpdb->prefix}sf_briefs_cache (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				property_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
				page_path VARCHAR(512) NOT NULL,
				brief_type VARCHAR(30) NOT NULL DEFAULT 'page',
				content LONGTEXT NOT NULL,
				generated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				expires_at DATETIME NOT NULL,
				PRIMARY KEY (id),
				UNIQUE KEY idx_prop_page_type (property_id, page_path, brief_type),
				KEY idx_expires (expires_at)
			) {$charset};

			CREATE TABLE {$wpdb->prefix}sf_alerts (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				property_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
				alert_type VARCHAR(30) NOT NULL,
				title VARCHAR(255) NOT NULL,
				severity VARCHAR(10) NOT NULL DEFAULT 'info',
				data LONGTEXT DEFAULT NULL,
				is_read TINYINT(1) NOT NULL DEFAULT 0,
				created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				KEY idx_type_read (alert_type, is_read),
				KEY idx_created (created_at),
				KEY idx_property (property_id)
			) {$charset};

			CREATE TABLE {$wpdb->prefix}sf_ga4_metrics (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				property_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
				page_path VARCHAR(512) NOT NULL,
				snapshot_date DATE NOT NULL,
				sessions INT UNSIGNED NOT NULL DEFAULT 0,
				bounce_rate DECIMAL(5,1) DEFAULT NULL,
				avg_session_dur DECIMAL(8,1) DEFAULT NULL,
				engaged_sessions INT UNSIGNED NOT NULL DEFAULT 0,
				conversions INT UNSIGNED NOT NULL DEFAULT 0,
				pageviews INT UNSIGNED NOT NULL DEFAULT 0,
				organic_sessions INT UNSIGNED NOT NULL DEFAULT 0,
				organic_bounce DECIMAL(5,1) DEFAULT NULL,
				organic_conversions INT UNSIGNED NOT NULL DEFAULT 0,
				created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				KEY idx_page_date (page_path, snapshot_date),
				KEY idx_snapshot_date (snapshot_date),
				KEY idx_property_page_date (property_id, page_path, snapshot_date)
			) {$charset};

			CREATE TABLE {$wpdb->prefix}sf_settings (
				setting_name VARCHAR(100) NOT NULL,
				setting_value LONGTEXT DEFAULT NULL,
				PRIMARY KEY (setting_name)
			) {$charset};

			CREATE TABLE {$wpdb->prefix}sf_audit_log (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
				user_login VARCHAR(60) NOT NULL DEFAULT 'system',
				action VARCHAR(50) NOT NULL,
				details TEXT DEFAULT NULL,
				ip_address VARCHAR(45) DEFAULT NULL,
				created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				KEY idx_action (action),
				KEY idx_created (created_at)
			) {$charset};

			CREATE TABLE {$wpdb->prefix}sf_competitors (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				property_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
				domain VARCHAR(255) NOT NULL,
				label VARCHAR(100) DEFAULT NULL,
				added_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				UNIQUE KEY idx_prop_domain (property_id, domain)
			) {$charset};

			CREATE TABLE {$wpdb->prefix}sf_competitor_keywords (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				competitor_id BIGINT UNSIGNED NOT NULL,
				query VARCHAR(512) NOT NULL,
				position DECIMAL(6,2) DEFAULT NULL,
				snapshot_date DATE NOT NULL,
				created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				KEY idx_competitor_date (competitor_id, snapshot_date),
				KEY idx_query (query(100))
			) {$charset};
		";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		$wpdb->query( "CREATE INDEX IF NOT EXISTS idx_keywords_page_date ON {$wpdb->prefix}sf_keywords (page_path, snapshot_date)" );
		$wpdb->query( "CREATE INDEX IF NOT EXISTS idx_keywords_query_source ON {$wpdb->prefix}sf_keywords (query(100), source)" );
		$wpdb->query( "CREATE INDEX IF NOT EXISTS idx_alerts_read_created ON {$wpdb->prefix}sf_alerts (is_read, created_at)" );
	}

	private function migrate_to_properties(): void {
		global $wpdb;

		$table = $wpdb->prefix . 'sf_properties';

		$exists = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
		if ( $exists > 0 ) {
			return;
		}

		$settings = get_option( 'searchforge_settings', [] );

		$domain = '';
		if ( ! empty( $settings['gsc_property'] ) ) {
			$parsed = wp_parse_url( $settings['gsc_property'] );
			$domain = $parsed['host'] ?? '';
		}
		if ( empty( $domain ) ) {
			$parsed = wp_parse_url( home_url() );
			$domain = $parsed['host'] ?? 'default';
		}

		$wpdb->insert( $table, [
			'label'            => $domain,
			'domain'           => $domain,
			'is_default'       => 1,
			'gsc_client_id'    => $settings['gsc_client_id'] ?? '',
			'gsc_client_secret' => $settings['gsc_client_secret'] ?? '',
			'gsc_access_token' => $settings['gsc_access_token'] ?? '',
			'gsc_refresh_token' => $settings['gsc_refresh_token'] ?? '',
			'gsc_token_expires' => $settings['gsc_token_expires'] ?? 0,
			'gsc_property'     => $settings['gsc_property'] ?? '',
			'bing_api_key'     => $settings['bing_api_key'] ?? '',
			'bing_site_url'    => $settings['bing_site_url'] ?? '',
			'bing_enabled'     => ! empty( $settings['bing_enabled'] ) ? 1 : 0,
			'ga4_property_id'  => $settings['ga4_property_id'] ?? '',
			'ga4_enabled'      => ! empty( $settings['ga4_enabled'] ) ? 1 : 0,
		] );
	}
}
