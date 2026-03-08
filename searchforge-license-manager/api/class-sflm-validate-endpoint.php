<?php
/**
 * POST /validate – Lizenz prüfen ohne Aktivierung.
 *
 * @package SearchForge_License_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SFLM_Validate_Endpoint extends SFLM_REST_Controller {

    public function register_routes(): void {
        register_rest_route( $this->namespace, '/validate', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'handle' ],
            'permission_callback' => '__return_true',
        ] );
    }

    public function handle( \WP_REST_Request $request ): \WP_REST_Response {
        $rate_check = $this->check_rate_limit( 'validate', $request );
        if ( $rate_check !== true ) {
            return $rate_check;
        }

        $license_key = sanitize_text_field( $request->get_param( 'license_key' ) ?? '' );
        $platform    = sanitize_text_field( $request->get_param( 'platform' ) ?? '' );

        // Validierung
        if ( ! SFLM_License_Manager::validate_key_format( $license_key ) ) {
            return $this->add_cors_headers(
                $this->error( 'INVALID_KEY', 'Ungültiges Lizenzschlüssel-Format.', 400 )
            );
        }

        if ( ! SFLM_Installation_Tracker::validate_platform( $platform ) ) {
            return $this->add_cors_headers(
                $this->error( 'INVALID_PLATFORM', 'Ungültige Plattform.', 400 )
            );
        }

        // Lizenz suchen
        $manager = new SFLM_License_Manager();
        $license = $manager->find_by_key( $license_key );

        if ( ! $license ) {
            return $this->add_cors_headers(
                $this->error( 'INVALID_KEY', 'Lizenzschlüssel nicht gefunden.', 404 )
            );
        }

        // Features ermitteln
        $feature_flags = new SFLM_Feature_Flags();
        $features      = $feature_flags->get_features( $license );

        return $this->add_cors_headers( $this->success( [
            'valid'        => $manager->is_valid( $license ),
            'tier'         => $license->tier,
            'plan'         => $license->plan,
            'status'       => $license->status,
            'expires_at'   => $license->expires_at,
            'max_sites'    => (int) $license->max_sites,
            'active_sites' => (int) $license->active_sites,
            'features'     => $features,
        ] ) );
    }
}
