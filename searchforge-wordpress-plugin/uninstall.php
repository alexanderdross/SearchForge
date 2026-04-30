<?php
/**
 * SearchForge uninstall handler.
 * Removes all plugin data when the plugin is deleted via WP admin.
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb;

// Drop custom tables.
$searchforge_tables = [
	'sf_properties',
	'sf_snapshots',
	'sf_keywords',
	'sf_sync_log',
	'sf_briefs_cache',
	'sf_alerts',
	'sf_ga4_metrics',
	'sf_settings',
	'sf_audit_log',
	'sf_competitors',
	'sf_competitor_keywords',
];

foreach ( $searchforge_tables as $searchforge_table ) {
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}{$searchforge_table}" );
}

// Remove options.
delete_option( 'searchforge_settings' );
delete_option( 'searchforge_db_version' );

// Remove scheduled events.
wp_clear_scheduled_hook( 'searchforge_daily_sync' );
wp_clear_scheduled_hook( 'searchforge_weekly_digest' );
