<?php
/**
 * Plugin Name:       SearchForge License Manager
 * Plugin URI:        https://searchforge.drossmedia.de
 * Description:       Zentrales Lizenzverwaltungssystem für SearchForge – verwaltet Lizenzschlüssel, Installationen, Stripe-Zahlungen und Feature-Gating.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.2
 * Author:            Alexander Dross / DrossMedia
 * Author URI:        https://drossmedia.de
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       sflm
 * Domain Path:       /languages
 *
 * @package SearchForge_License_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin-Konstanten
define( 'SFLM_VERSION', '1.0.0' );
define( 'SFLM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SFLM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SFLM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'SFLM_DB_PREFIX', 'sflm_' );

/**
 * Überprüfe Mindestanforderungen.
 */
function sflm_check_requirements() {
    if ( version_compare( PHP_VERSION, '8.2', '<' ) ) {
        add_action( 'admin_notices', function () {
            echo '<div class="notice notice-error"><p>';
            echo esc_html__( 'SearchForge License Manager benötigt PHP 8.2 oder höher.', 'sflm' );
            echo '</p></div>';
        } );
        return false;
    }
    return true;
}

/**
 * Plugin-Aktivierung.
 */
function sflm_activate() {
    if ( ! sflm_check_requirements() ) {
        return;
    }
    require_once SFLM_PLUGIN_DIR . 'includes/class-sflm-activator.php';
    SFLM_Activator::activate();
}
register_activation_hook( __FILE__, 'sflm_activate' );

/**
 * Plugin-Deaktivierung.
 */
function sflm_deactivate() {
    require_once SFLM_PLUGIN_DIR . 'includes/class-sflm-deactivator.php';
    SFLM_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'sflm_deactivate' );

/**
 * Plugin initialisieren.
 */
function sflm_init() {
    if ( ! sflm_check_requirements() ) {
        return;
    }

    // Composer-Autoloader zentral laden (statt in jeder Klasse einzeln)
    $autoload = SFLM_PLUGIN_DIR . 'vendor/autoload.php';
    if ( file_exists( $autoload ) ) {
        require_once $autoload;
    }

    // Autoload-Klassen laden
    require_once SFLM_PLUGIN_DIR . 'includes/class-sflm-loader.php';
    require_once SFLM_PLUGIN_DIR . 'includes/class-sflm-settings.php';
    require_once SFLM_PLUGIN_DIR . 'includes/class-sflm-database.php';
    require_once SFLM_PLUGIN_DIR . 'includes/class-sflm-license-manager.php';
    require_once SFLM_PLUGIN_DIR . 'includes/class-sflm-installation-tracker.php';
    require_once SFLM_PLUGIN_DIR . 'includes/class-sflm-rate-limiter.php';
    require_once SFLM_PLUGIN_DIR . 'includes/class-sflm-jwt-handler.php';
    require_once SFLM_PLUGIN_DIR . 'includes/class-sflm-audit-logger.php';
    require_once SFLM_PLUGIN_DIR . 'includes/class-sflm-feature-flags.php';
    require_once SFLM_PLUGIN_DIR . 'includes/class-sflm-email.php';
    require_once SFLM_PLUGIN_DIR . 'includes/class-sflm-geoip.php';

    // REST API laden
    require_once SFLM_PLUGIN_DIR . 'api/class-sflm-rest-controller.php';
    require_once SFLM_PLUGIN_DIR . 'api/class-sflm-health-endpoint.php';
    require_once SFLM_PLUGIN_DIR . 'api/class-sflm-validate-endpoint.php';
    require_once SFLM_PLUGIN_DIR . 'api/class-sflm-activate-endpoint.php';
    require_once SFLM_PLUGIN_DIR . 'api/class-sflm-deactivate-endpoint.php';
    require_once SFLM_PLUGIN_DIR . 'api/class-sflm-check-endpoint.php';
    require_once SFLM_PLUGIN_DIR . 'api/class-sflm-stripe-webhook.php';

    // Admin-Bereich laden
    if ( is_admin() ) {
        require_once SFLM_PLUGIN_DIR . 'admin/class-sflm-admin.php';
        $admin = new SFLM_Admin();
        $admin->init();
    }

    // REST-Routen registrieren
    add_action( 'rest_api_init', 'sflm_register_rest_routes' );

    // Cronjobs registrieren
    sflm_register_cron_events();
}
add_action( 'plugins_loaded', 'sflm_init' );

/**
 * REST API Routen registrieren.
 */
function sflm_register_rest_routes() {
    $health     = new SFLM_Health_Endpoint();
    $validate   = new SFLM_Validate_Endpoint();
    $activate   = new SFLM_Activate_Endpoint();
    $deactivate = new SFLM_Deactivate_Endpoint();
    $check      = new SFLM_Check_Endpoint();
    $stripe     = new SFLM_Stripe_Webhook();

    $health->register_routes();
    $validate->register_routes();
    $activate->register_routes();
    $deactivate->register_routes();
    $check->register_routes();
    $stripe->register_routes();
}

/**
 * Eigene Cron-Intervalle registrieren (WordPress kennt kein 'weekly').
 */
add_filter( 'cron_schedules', function ( array $schedules ): array {
    if ( ! isset( $schedules['weekly'] ) ) {
        $schedules['weekly'] = [
            'interval' => WEEK_IN_SECONDS,
            'display'  => __( 'Einmal wöchentlich', 'sflm' ),
        ];
    }
    return $schedules;
} );

/**
 * Cronjob-Events registrieren.
 */
function sflm_register_cron_events() {
    $events = [
        'sflm_check_expired_licenses'    => 'daily',
        'sflm_cleanup_old_data'          => 'weekly',
        'sflm_cleanup_rate_limits'       => 'hourly',
        'sflm_check_stale_installations' => 'daily',
        'sflm_send_expiry_warnings'      => 'daily',
    ];

    foreach ( $events as $hook => $recurrence ) {
        if ( ! wp_next_scheduled( $hook ) ) {
            wp_schedule_event( time(), $recurrence, $hook );
        }
    }
}

// Cronjob-Callbacks
add_action( 'sflm_check_expired_licenses', function () {
    $manager = new SFLM_License_Manager();
    $manager->process_expired_licenses();
} );

add_action( 'sflm_cleanup_old_data', function () {
    $db = new SFLM_Database();
    $db->cleanup_old_data( 24 ); // 24 Monate
} );

add_action( 'sflm_cleanup_rate_limits', function () {
    $limiter = new SFLM_Rate_Limiter();
    $limiter->cleanup_expired();
} );

add_action( 'sflm_check_stale_installations', function () {
    $tracker = new SFLM_Installation_Tracker();
    $tracker->deactivate_stale_installations( 7 ); // 7 Tage ohne Heartbeat
} );

add_action( 'sflm_send_expiry_warnings', function () {
    $email = new SFLM_Email();
    $email->send_expiry_warnings( 7 ); // 7 Tage vor Ablauf
} );
