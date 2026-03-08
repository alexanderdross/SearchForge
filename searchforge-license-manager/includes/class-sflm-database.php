<?php
/**
 * Datenbank-Hilfsfunktionen und Bereinigung.
 *
 * @package SearchForge_License_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SFLM_Database {

    private string $prefix;

    public function __construct() {
        global $wpdb;
        $this->prefix = $wpdb->prefix . SFLM_DB_PREFIX;
    }

    /**
     * Tabellen-Prefix zurückgeben.
     */
    public function get_prefix(): string {
        return $this->prefix;
    }

    /**
     * Alte Daten bereinigen (DSGVO-konform).
     *
     * @param int $months Aufbewahrungsfrist in Monaten.
     */
    public function cleanup_old_data( int $months = 24 ): void {
        global $wpdb;

        $cutoff = gmdate( 'Y-m-d H:i:s', strtotime( "-{$months} months" ) );

        // Deaktivierte Installationen älter als Aufbewahrungsfrist
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$this->prefix}installations WHERE is_active = 0 AND deactivated_at < %s",
                $cutoff
            )
        );

        // Verwaiste Geodaten
        $wpdb->query(
            "DELETE g FROM {$this->prefix}geo_data g
             LEFT JOIN {$this->prefix}installations i ON g.installation_id = i.id
             WHERE i.id IS NULL"
        );

        // Audit-Logs älter als Aufbewahrungsfrist
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$this->prefix}audit_logs WHERE created_at < %s",
                $cutoff
            )
        );

        // Stripe Events älter als Aufbewahrungsfrist
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$this->prefix}stripe_events WHERE received_at < %s",
                $cutoff
            )
        );

        // Abgelaufene Rate-Limits
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$this->prefix}rate_limits WHERE window_end < %s",
                gmdate( 'Y-m-d H:i:s' )
            )
        );
    }

    /**
     * Kundendaten vollständig löschen (Recht auf Löschung).
     */
    public function delete_customer_data( string $email ): int {
        global $wpdb;

        $licenses = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT id FROM {$this->prefix}licenses WHERE customer_email = %s",
                $email
            )
        );

        if ( empty( $licenses ) ) {
            return 0;
        }

        $placeholders = implode( ',', array_fill( 0, count( $licenses ), '%d' ) );

        // Geodaten der Installationen löschen
        $wpdb->query(
            $wpdb->prepare(
                "DELETE g FROM {$this->prefix}geo_data g
                 INNER JOIN {$this->prefix}installations i ON g.installation_id = i.id
                 WHERE i.license_id IN ($placeholders)",
                ...$licenses
            )
        );

        // Installationen löschen
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$this->prefix}installations WHERE license_id IN ($placeholders)",
                ...$licenses
            )
        );

        // Audit-Logs löschen
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$this->prefix}audit_logs WHERE license_id IN ($placeholders)",
                ...$licenses
            )
        );

        // Lizenzen löschen
        $deleted = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$this->prefix}licenses WHERE customer_email = %s",
                $email
            )
        );

        return (int) $deleted;
    }
}
