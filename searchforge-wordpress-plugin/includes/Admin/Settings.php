<?php

namespace SearchForge\Admin;

use SearchForge\Database\Encryption;
use SearchForge\Models\Property;

defined( 'ABSPATH' ) || exit;

class Settings {

	private const OPTION_KEY = 'searchforge_settings';

	private const ENCRYPTED_FIELDS = [
		'kwp_developer_token',
		'serpapi_key',
		'ai_api_key',
	];

	private const PROPERTY_FIELDS = [
		'gsc_client_id', 'gsc_client_secret', 'gsc_access_token',
		'gsc_refresh_token', 'gsc_token_expires', 'gsc_property',
		'bing_api_key', 'bing_site_url', 'bing_enabled',
		'ga4_property_id', 'ga4_enabled',
	];

	private const DEFAULTS = [
		'gsc_max_pages'     => 0,
		// Keyword Planner (Pro) - global.
		'kwp_customer_id'     => '',
		'kwp_developer_token' => '',
		'kwp_language_id'     => '1000',
		'kwp_geo_target'      => '2840',
		'kwp_enabled'         => false,
		// Google Trends (Pro) - global.
		'serpapi_key'         => '',
		'trends_enabled'      => false,
		// AI Content Briefs (Pro) - global.
		'ai_api_key'          => '',
		'ai_provider'         => 'openai',
		// Webhooks (Pro) - global.
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
		'alert_ranking_drop_threshold' => 3,
		'alert_traffic_anomaly'        => true,
		'weekly_digest_enabled'        => false,
		'sync_frequency'    => 'daily',
		'data_retention'    => 30,
		'llms_txt_enabled'  => true,
		'license_key'       => '',
		'license_tier'      => 'free',
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
		$current   = self::get_all();
		$sanitized = [];

		$sanitized['llms_txt_enabled']  = ! empty( $input['llms_txt_enabled'] );
		$sanitized['license_key']       = sanitize_text_field( $input['license_key'] ?? $current['license_key'] );
		$sanitized['gsc_max_pages']     = absint( $input['gsc_max_pages'] ?? $current['gsc_max_pages'] );

		// Keyword Planner.
		$sanitized['kwp_customer_id']     = sanitize_text_field( $input['kwp_customer_id'] ?? $current['kwp_customer_id'] );
		$sanitized['kwp_developer_token'] = sanitize_text_field( $input['kwp_developer_token'] ?? $current['kwp_developer_token'] );
		$sanitized['kwp_language_id']     = sanitize_text_field( $input['kwp_language_id'] ?? $current['kwp_language_id'] );
		$sanitized['kwp_geo_target']      = sanitize_text_field( $input['kwp_geo_target'] ?? $current['kwp_geo_target'] );
		$sanitized['kwp_enabled']         = ! empty( $input['kwp_enabled'] );

		// Google Trends.
		$sanitized['serpapi_key']    = sanitize_text_field( $input['serpapi_key'] ?? $current['serpapi_key'] );
		$sanitized['trends_enabled'] = ! empty( $input['trends_enabled'] );

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

		foreach ( self::ENCRYPTED_FIELDS as $field ) {
			if ( ! empty( $settings[ $field ] ) && is_string( $settings[ $field ] ) ) {
				$decrypted = Encryption::decrypt( $settings[ $field ] );
				if ( false !== $decrypted ) {
					$settings[ $field ] = $decrypted;
				}
			}
		}

		return $settings;
	}

	public static function get( string $key, $default = null ) {
		// Backward compat: per-property fields fall back to default property.
		if ( in_array( $key, self::PROPERTY_FIELDS, true ) ) {
			$property = Property::get_default();
			return $property[ $key ] ?? $default;
		}

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

	private static function encrypt_settings( array $settings ): array {
		foreach ( self::ENCRYPTED_FIELDS as $field ) {
			if ( ! empty( $settings[ $field ] ) && is_string( $settings[ $field ] ) ) {
				$encrypted = Encryption::encrypt( $settings[ $field ] );
				if ( false !== $encrypted ) {
					$settings[ $field ] = $encrypted;
				}
			}
		}
		return $settings;
	}

	private static function resolve_license_tier( string $key, string $fallback ): string {
		if ( empty( $key ) ) {
			return 'free';
		}

		if ( function_exists( 'sflm_validate_license' ) ) {
			$result = sflm_validate_license( $key );
			if ( ! empty( $result['tier'] ) ) {
				return strtolower( $result['tier'] );
			}
		}

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
			'pro', 'agency', 'enterprise' => 0,
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
