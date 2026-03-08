<?php
/**
 * Plugin-Deaktivierung: Cronjobs entfernen.
 *
 * @package SearchForge_License_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SFLM_Deactivator {

    /**
     * Plugin-Deaktivierung durchführen.
     */
    public static function deactivate(): void {
        wp_clear_scheduled_hook( 'sflm_check_expired_licenses' );
        wp_clear_scheduled_hook( 'sflm_cleanup_old_data' );
        wp_clear_scheduled_hook( 'sflm_cleanup_rate_limits' );
        wp_clear_scheduled_hook( 'sflm_check_stale_installations' );
        wp_clear_scheduled_hook( 'sflm_send_expiry_warnings' );
        flush_rewrite_rules();
    }
}
