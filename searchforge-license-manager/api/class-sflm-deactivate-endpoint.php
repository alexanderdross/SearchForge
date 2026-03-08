<?php
/**
 * POST /deactivate – Installation freigeben.
 *
 * @package SearchForge_License_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SFLM_Deactivate_Endpoint extends SFLM_REST_Controller {

    public function register_routes(): void {
        register_rest_route( $this->namespace, '/deactivate', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'handle' ],
            'permission_callback' => '__return_true',
        ] );
    }

    public function handle( \WP_REST_Request $request ): \WP_REST_Response {
        $rate_check = $this->check_rate_limit( 'deactivate', $request );
        if ( $rate_check !== true ) {
            return $rate_check;
        }

        $license_key = sanitize_text_field( $request->get_param( 'license_key' ) ?? '' );
        $fingerprint = sanitize_text_field( $request->get_param( 'fingerprint' ) ?? '' );

        if ( ! SFLM_License_Manager::validate_key_format( $license_key ) ) {
            return $this->add_cors_headers(
                $this->error( 'INVALID_KEY', 'Ungültiges Lizenzschlüssel-Format.', 400 )
            );
        }

        if ( ! SFLM_Installation_Tracker::validate_fingerprint( $fingerprint ) ) {
            return $this->add_cors_headers(
                $this->error( 'INVALID_FINGERPRINT', 'Ungültiger Fingerprint.', 400 )
            );
        }

        $manager = new SFLM_License_Manager();
        $license = $manager->find_by_key( $license_key );

        if ( ! $license ) {
            return $this->add_cors_headers(
                $this->error( 'INVALID_KEY', 'Lizenzschlüssel nicht gefunden.', 404 )
            );
        }

        $tracker     = new SFLM_Installation_Tracker();
        $deactivated = $tracker->deactivate( (int) $license->id, $fingerprint );

        if ( ! $deactivated ) {
            return $this->add_cors_headers(
                $this->error( 'INVALID_FINGERPRINT', 'Installation nicht gefunden oder bereits deaktiviert.', 404 )
            );
        }

        // Aktualisierte Lizenz-Daten laden
        $updated_license = $manager->find_by_key( $license_key );

        $audit = new SFLM_Audit_Logger();
        $audit->log( 'license.deactivated', 'api', null, (int) $license->id, null, [
            'fingerprint' => substr( $fingerprint, 0, 12 ) . '...',
        ] );

        return $this->add_cors_headers( $this->success( [
            'deactivated'  => true,
            'active_sites' => (int) $updated_license->active_sites,
            'max_sites'    => (int) $updated_license->max_sites,
        ] ) );
    }
}
