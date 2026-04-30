<?php

namespace SearchForge\Integrations\GSC;

use SearchForge\Admin\Settings;
use SearchForge\Models\Property;

defined( 'ABSPATH' ) || exit;

class OAuth {

	private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
	private const AUTH_URL  = 'https://accounts.google.com/o/oauth2/v2/auth';
	private const SCOPES    = 'https://www.googleapis.com/auth/webmasters.readonly';

	public function __construct() {
		add_action( 'admin_init', [ $this, 'handle_oauth_callback' ] );
	}

	/**
	 * Generate the OAuth authorization URL.
	 *
	 * @param int $property_id Property ID to include in state. 0 = active property.
	 */
	public static function get_auth_url( int $property_id = 0 ): string {
		$property_id = $property_id ?: Property::get_active_property_id();
		$prop        = Property::get( $property_id );
		$settings    = Settings::get_all();
		$redirect    = self::get_redirect_uri();

		// Use property-level client_id if available, fall back to global settings.
		$client_id = $prop['gsc_client_id'] ?? $settings['gsc_client_id'] ?? '';

		$state_data = wp_json_encode( [
			'nonce'       => wp_create_nonce( 'searchforge_oauth' ),
			'property_id' => $property_id,
		] );

		$params = [
			'client_id'     => $client_id,
			'redirect_uri'  => $redirect,
			'response_type' => 'code',
			'scope'         => self::SCOPES,
			'access_type'   => 'offline',
			'prompt'        => 'consent',
			'state'         => base64_encode( $state_data ),
		];

		return self::AUTH_URL . '?' . http_build_query( $params );
	}

	/**
	 * Get the redirect URI for OAuth callback.
	 */
	public static function get_redirect_uri(): string {
		return admin_url( 'admin.php?page=searchforge-settings' );
	}

	/**
	 * Handle OAuth callback — exchange code for tokens.
	 */
	public function handle_oauth_callback(): void {
		if ( ! isset( $_GET['page'], $_GET['code'] ) || 'searchforge-settings' !== sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$raw_state = sanitize_text_field( wp_unslash( $_GET['state'] ?? '' ) );
		$state     = json_decode( base64_decode( $raw_state ), true );

		if ( ! is_array( $state ) || empty( $state['nonce'] ) ) {
			add_action( 'admin_notices', function () {
				echo '<div class="notice notice-error"><p>' .
					esc_html__( 'SearchForge: OAuth verification failed. Please try again.', 'searchforge-wordpress-plugin' ) .
					'</p></div>';
			} );
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( $state['nonce'] ), 'searchforge_oauth' ) ) {
			add_action( 'admin_notices', function () {
				echo '<div class="notice notice-error"><p>' .
					esc_html__( 'SearchForge: OAuth verification failed. Please try again.', 'searchforge-wordpress-plugin' ) .
					'</p></div>';
			} );
			return;
		}

		$property_id = (int) ( $state['property_id'] ?? 0 );
		$code        = sanitize_text_field( wp_unslash( $_GET['code'] ) );

		// Load property config for client credentials, fall back to global settings.
		$prop     = $property_id ? Property::get( $property_id ) : null;
		$settings = Settings::get_all();

		$client_id     = $prop['gsc_client_id'] ?? $settings['gsc_client_id'] ?? '';
		$client_secret = $prop['gsc_client_secret'] ?? $settings['gsc_client_secret'] ?? '';

		$response = wp_remote_post( self::TOKEN_URL, [
			'body' => [
				'code'          => $code,
				'client_id'     => $client_id,
				'client_secret' => $client_secret,
				'redirect_uri'  => self::get_redirect_uri(),
				'grant_type'    => 'authorization_code',
			],
		] );

		if ( is_wp_error( $response ) ) {
			add_action( 'admin_notices', function () use ( $response ) {
				echo '<div class="notice notice-error"><p>' .
					esc_html( sprintf(
						/* translators: %s: error message from token exchange */
						__( 'SearchForge: Token exchange failed: %s', 'searchforge-wordpress-plugin' ),
						$response->get_error_message()
					) ) .
					'</p></div>';
			} );
			return;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['error'] ) ) {
			add_action( 'admin_notices', function () use ( $body ) {
				echo '<div class="notice notice-error"><p>' .
					esc_html( sprintf(
						/* translators: %s: Google OAuth error description */
						__( 'SearchForge: Google OAuth error: %s', 'searchforge-wordpress-plugin' ),
						$body['error_description'] ?? $body['error']
					) ) .
					'</p></div>';
			} );
			return;
		}

		$token_data = [
			'gsc_access_token'  => $body['access_token'],
			'gsc_refresh_token' => $body['refresh_token'] ?? '',
			'gsc_token_expires' => time() + ( $body['expires_in'] ?? 3600 ),
		];

		if ( $property_id ) {
			// Preserve existing refresh token if new one not provided.
			if ( empty( $token_data['gsc_refresh_token'] ) && $prop ) {
				$token_data['gsc_refresh_token'] = $prop['gsc_refresh_token'] ?? '';
			}
			Property::update( $property_id, $token_data );
		} else {
			// Legacy fallback: store in global settings.
			if ( empty( $token_data['gsc_refresh_token'] ) ) {
				$token_data['gsc_refresh_token'] = $settings['gsc_refresh_token'] ?? '';
			}
			Settings::update_many( $token_data );
		}

		// Redirect to remove code from URL.
		wp_safe_redirect( admin_url( 'admin.php?page=searchforge-settings&gsc_connected=1' ) );
		exit;
	}

	/**
	 * Get a valid access token, refreshing if needed.
	 *
	 * @param array|null $property Optional property config array with gsc_* fields.
	 */
	public static function get_access_token( ?array $property = null ): string|\WP_Error {
		if ( null === $property ) {
			$settings = Settings::get_all();
		} else {
			$settings = $property;
		}

		if ( empty( $settings['gsc_access_token'] ) ) {
			return new \WP_Error( 'no_token', __( 'GSC not connected.', 'searchforge-wordpress-plugin' ) );
		}

		// Token still valid.
		$expires = $settings['gsc_token_expires'] ?? 0;
		if ( $expires > time() + 60 ) {
			return $settings['gsc_access_token'];
		}

		// Refresh.
		if ( empty( $settings['gsc_refresh_token'] ) ) {
			return new \WP_Error( 'no_refresh_token', __( 'No refresh token. Please reconnect GSC.', 'searchforge-wordpress-plugin' ) );
		}

		$client_id     = $settings['gsc_client_id'] ?? '';
		$client_secret = $settings['gsc_client_secret'] ?? '';

		$response = wp_remote_post( self::TOKEN_URL, [
			'body' => [
				'client_id'     => $client_id,
				'client_secret' => $client_secret,
				'refresh_token' => $settings['gsc_refresh_token'],
				'grant_type'    => 'refresh_token',
			],
		] );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['error'] ) ) {
			return new \WP_Error( 'refresh_failed', $body['error_description'] ?? $body['error'] );
		}

		$new_token = $body['access_token'];
		$new_expires = time() + ( $body['expires_in'] ?? 3600 );

		$update_data = [
			'gsc_access_token'  => $new_token,
			'gsc_token_expires' => $new_expires,
		];

		// Persist refreshed token to the correct store.
		if ( null !== $property && ! empty( $property['id'] ) ) {
			Property::update( (int) $property['id'], $update_data );
		} else {
			Settings::update_many( $update_data );
		}

		return $new_token;
	}
}
