<?php

namespace SearchForge\Admin;

defined( 'ABSPATH' ) || exit;

class Settings {

	private const OPTION_KEY = 'searchforge_settings';

	/**
	 * Fields that should be encrypted at rest.
	 */
	private const ENCRYPTED_FIELDS = [
		'gsc_access_token',
		'gsc_refresh_token',
		'gsc_client_secret',
		'bing_api_key',
		'kwp_developer_token',
		'serpapi_key',
		'ai_api_key',
	];

	private const DEFAULTS = [
		'gsc_client_id'     => '',
		'gsc_client_secret' => '',
		'gsc_access_token'  => '',
		'gsc_refresh_token' => '',
		'gsc_token_expires' => 0,
		'gsc_property'      => '',
		'gsc_max_pages'     => 0, // 0 = unlimited (Pro), limited in Free.
		'bing_api_key'      => '',
		'bing_site_url'     => '',
		'bing_enabled'      => false,
		// Keyword Planner (Pro).
		'kwp_customer_id'     => '',
		'kwp_developer_token' => '',
		'kwp_language_id'     => '1000',
		'kwp_geo_target'      => '2840',
		'kwp_enabled'         => false,
		// Google Trends (Pro).
		'serpapi_key'         => '',
		'trends_enabled'      => false,
		// GA4 (Pro).
		'ga4_property_id'     => '',
		'ga4_enabled'         => false,
		// AI Content Briefs (Pro).
		'ai_api_key'          => '',
		'ai_provider'         => 'openai',
		// Webhooks (Pro).
		'webhook_enabled'    => false,
		'webhook_url'        => '',
		'webhook_format'     => 'json',
		'webhook_on_alerts'  => true,
		// API key for external REST access (Pro).
		'api_key'              => '',
		// Monitoring (Pro).
		'broken_links_enabled' => false,
		// Alerts.
		'alerts_enabled'    => false,
		'alert_email'       => '',
		'alert_ranking_drop_threshold' => 3, // positions
		'alert_traffic_anomaly'        => true,
		'weekly_digest_enabled'        => false,
		'sync_frequency'    => 'daily',
		'data_retention'    => 30, // days. Free = 30, Pro = 365.
		'llms_txt_enabled'  => true,
		'license_key'       => '',
		'license_tier'      => 'free',
		// Competitor tracking (Pro).
		'competitors'       => [],
	];

	public function __construct() {
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	public function register_settings(): void {
		register_setting( 'searchforge_settings', self::OPTION_KEY, [
			'sanitize_callback' => [ $this, 'sanitize' ],
			'default'           => self::DEFAULTS,
		] );
	}

	public function sanitize( array $input ): array {
		$current  = self::get_all();
		$sanitized = [];

		$sanitized['gsc_client_id']     = sanitize_text_field( $input['gsc_client_id'] ?? $current['gsc_client_id'] );
		$sanitized['gsc_client_secret'] = sanitize_text_field( $input['gsc_client_secret'] ?? $current['gsc_client_secret'] );
		$sanitized['gsc_property']      = esc_url_raw( $input['gsc_property'] ?? $current['gsc_property'] );
		$sanitized['llms_txt_enabled']  = ! empty( $input['llms_txt_enabled'] );
		$sanitized['license_key']       = sanitize_text_field( $input['license_key'] ?? $current['license_key'] );

		// Preserve tokens — these are set programmatically via OAuth callback.
		$sanitized['gsc_access_token']  = $current['gsc_access_token'];
		$sanitized['gsc_refresh_token'] = $current['gsc_refresh_token'];
		$sanitized['gsc_token_expires'] = $current['gsc_token_expires'];

		$sanitized['gsc_max_pages']   = absint( $input['gsc_max_pages'] ?? $current['gsc_max_pages'] );
		$sanitized['bing_api_key']    = sanitize_text_field( $input['bing_api_key'] ?? $current['bing_api_key'] );
		$sanitized['bing_site_url']   = esc_url_raw( $input['bing_site_url'] ?? $current['bing_site_url'] );
		$sanitized['bing_enabled']    = ! empty( $input['bing_enabled'] );

		// Keyword Planner.
		$sanitized['kwp_customer_id']     = sanitize_text_field( $input['kwp_customer_id'] ?? $current['kwp_customer_id'] );
		$sanitized['kwp_developer_token'] = sanitize_text_field( $input['kwp_developer_token'] ?? $current['kwp_developer_token'] );
		$sanitized['kwp_language_id']     = sanitize_text_field( $input['kwp_language_id'] ?? $current['kwp_language_id'] );
		$sanitized['kwp_geo_target']      = sanitize_text_field( $input['kwp_geo_target'] ?? $current['kwp_geo_target'] );
		$sanitized['kwp_enabled']         = ! empty( $input['kwp_enabled'] );

		// Google Trends.
		$sanitized['serpapi_key']    = sanitize_text_field( $input['serpapi_key'] ?? $current['serpapi_key'] );
		$sanitized['trends_enabled'] = ! empty( $input['trends_enabled'] );

		// GA4.
		$sanitized['ga4_property_id'] = sanitize_text_field( $input['ga4_property_id'] ?? $current['ga4_property_id'] );
		$sanitized['ga4_enabled']     = ! empty( $input['ga4_enabled'] );

		// AI Content Briefs.
		$sanitized['ai_api_key']  = sanitize_text_field( $input['ai_api_key'] ?? $current['ai_api_key'] );
		$sanitized['ai_provider'] = in_array( $input['ai_provider'] ?? '', [ 'openai', 'anthropic' ], true )
			? $input['ai_provider']
			: $current['ai_provider'];

		// Webhooks.
		$sanitized['webhook_enabled']   = ! empty( $input['webhook_enabled'] );
		$sanitized['webhook_url']       = esc_url_raw( $input['webhook_url'] ?? $current['webhook_url'] );
		$sanitized['webhook_format']    = in_array( $input['webhook_format'] ?? '', [ 'json', 'slack' ], true )
			? $input['webhook_format']
			: $current['webhook_format'];
		$sanitized['webhook_on_alerts'] = ! empty( $input['webhook_on_alerts'] );

		$sanitized['broken_links_enabled'] = ! empty( $input['broken_links_enabled'] );
		$sanitized['alerts_enabled']  = ! empty( $input['alerts_enabled'] );
		$sanitized['alert_email']     = sanitize_email( $input['alert_email'] ?? $current['alert_email'] );
		$sanitized['alert_ranking_drop_threshold'] = absint( $input['alert_ranking_drop_threshold'] ?? $current['alert_ranking_drop_threshold'] );
		$sanitized['alert_traffic_anomaly']        = ! empty( $input['alert_traffic_anomaly'] );
		$sanitized['weekly_digest_enabled']        = ! empty( $input['weekly_digest_enabled'] );

		$valid_frequencies = [ 'daily', 'twicedaily', 'weekly' ];
		if ( self::is_pro() ) {
			$valid_frequencies = array_merge( [ 'every_four_hours', 'every_six_hours' ], $valid_frequencies );
		}
		$sanitized['sync_frequency']  = in_array( $input['sync_frequency'] ?? '', $valid_frequencies, true )
			? $input['sync_frequency']
			: $current['sync_frequency'];
		$sanitized['data_retention']  = absint( $input['data_retention'] ?? $current['data_retention'] );
		$sanitized['license_tier']    = self::resolve_license_tier( $sanitized['license_key'], $current['license_tier'] );

		return self::encrypt_settings( $sanitized );
	}

	public static function get_all(): array {
		$settings = wp_parse_args( get_option( self::OPTION_KEY, [] ), self::DEFAULTS );

		// Decrypt sensitive fields.
		foreach ( self::ENCRYPTED_FIELDS as $field ) {
			if ( ! empty( $settings[ $field ] ) && is_string( $settings[ $field ] ) ) {
				$decrypted = self::decrypt( $settings[ $field ] );
				if ( false !== $decrypted ) {
					$settings[ $field ] = $decrypted;
				}
			}
		}

		return $settings;
	}

	public static function get( string $key, $default = null ) {
		$settings = self::get_all();
		return $settings[ $key ] ?? $default;
	}

	public static function update( string $key, $value ): bool {
		$settings          = self::get_all();
		$settings[ $key ]  = $value;
		return update_option( self::OPTION_KEY, self::encrypt_settings( $settings ) );
	}

	public static function update_many( array $values ): bool {
		$settings = self::get_all();
		$settings = array_merge( $settings, $values );
		return update_option( self::OPTION_KEY, self::encrypt_settings( $settings ) );
	}

	/**
	 * Encrypt sensitive fields before storing.
	 */
	private static function encrypt_settings( array $settings ): array {
		foreach ( self::ENCRYPTED_FIELDS as $field ) {
			if ( ! empty( $settings[ $field ] ) && is_string( $settings[ $field ] ) ) {
				$encrypted = self::encrypt( $settings[ $field ] );
				if ( false !== $encrypted ) {
					$settings[ $field ] = $encrypted;
				}
			}
		}
		return $settings;
	}

	/**
	 * Encrypt a value using AES-256-CBC.
	 */
	private static function encrypt( string $value ): string|false {
		if ( empty( $value ) ) {
			return $value;
		}

		$key = self::get_encryption_key();
		$iv  = openssl_random_pseudo_bytes( 16 );

		$encrypted = openssl_encrypt( $value, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv );
		if ( false === $encrypted ) {
			return false;
		}

		// Prefix with 'enc:' so we can detect already-encrypted values.
		return 'enc:' . base64_encode( $iv . $encrypted );
	}

	/**
	 * Decrypt a value encrypted with encrypt().
	 */
	private static function decrypt( string $value ): string|false {
		// Not encrypted (legacy plaintext value).
		if ( ! str_starts_with( $value, 'enc:' ) ) {
			return $value;
		}

		$key  = self::get_encryption_key();
		$data = base64_decode( substr( $value, 4 ), true );

		if ( false === $data || strlen( $data ) < 17 ) {
			return false;
		}

		$iv        = substr( $data, 0, 16 );
		$encrypted = substr( $data, 16 );

		return openssl_decrypt( $encrypted, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv );
	}

	/**
	 * Derive a 32-byte encryption key from WordPress auth salts.
	 */
	private static function get_encryption_key(): string {
		return hash( 'sha256', wp_salt( 'auth' ) . 'searchforge_encrypt', true );
	}

	/**
	 * Resolve license tier from key format or license manager API.
	 */
	private static function resolve_license_tier( string $key, string $fallback ): string {
		if ( empty( $key ) ) {
			return 'free';
		}

		// Try license manager API if the plugin is active.
		if ( function_exists( 'sflm_validate_license' ) ) {
			$result = sflm_validate_license( $key );
			if ( ! empty( $result['tier'] ) ) {
				return strtolower( $result['tier'] );
			}
		}

		// Parse tier from key format: SF-{TIER}-{16 hex}.
		$tier_map = [
			'FREE' => 'free',
			'PRO'  => 'pro',
			'ENT'  => 'enterprise',
			'DEV'  => 'pro',
		];

		if ( preg_match( '/^SF-(FREE|PRO|ENT|DEV)-[A-F0-9]{16}$/', $key, $matches ) ) {
			return $tier_map[ $matches[1] ] ?? $fallback;
		}

		return $fallback;
	}

	public static function is_pro(): bool {
		return in_array( self::get( 'license_tier' ), [ 'pro', 'agency', 'enterprise' ], true );
	}

	public static function get_page_limit(): int {
		$tier = self::get( 'license_tier' );
		return match ( $tier ) {
			'pro', 'agency', 'enterprise' => 0, // unlimited
			default                        => 10,
		};
	}

	public static function get_retention_days(): int {
		$user_setting = (int) self::get( 'data_retention' );
		if ( $user_setting > 0 ) {
			return $user_setting;
		}

		$tier = self::get( 'license_tier' );
		return match ( $tier ) {
			'enterprise' => 730,
			'agency'     => 730,
			'pro'        => 365,
			default      => 30,
		};
	}
}
