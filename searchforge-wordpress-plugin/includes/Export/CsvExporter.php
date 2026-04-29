<?php

namespace SearchForge\Export;

use SearchForge\Admin\Settings;
use SearchForge\Models\Property;

defined( 'ABSPATH' ) || exit;

/**
 * CSV/JSON data exporter for pages, keywords, and alerts.
 */
class CsvExporter {

	/**
	 * Export pages data as CSV string.
	 */
	public static function export_pages_csv( int $property_id = 0 ): string {
		$property_id = $property_id ?: Property::get_active_property_id();

		global $wpdb;

		$latest_date = $wpdb->get_var( $wpdb->prepare(
			"SELECT MAX(snapshot_date) FROM {$wpdb->prefix}sf_snapshots WHERE source = 'gsc' AND property_id = %d",
			$property_id
		) );

		if ( ! $latest_date ) {
			return '';
		}

		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT page_path, source, device, clicks, impressions, ctr, position, snapshot_date
			FROM {$wpdb->prefix}sf_snapshots
			WHERE snapshot_date = %s AND property_id = %d
			ORDER BY clicks DESC",
			$latest_date,
			$property_id
		), ARRAY_A );

		return self::array_to_csv( $rows, [
			'Page Path', 'Source', 'Device', 'Clicks', 'Impressions', 'CTR', 'Position', 'Date',
		] );
	}

	/**
	 * Export keywords data as CSV string.
	 */
	public static function export_keywords_csv( int $property_id = 0 ): string {
		$property_id = $property_id ?: Property::get_active_property_id();

		global $wpdb;

		$latest_date = $wpdb->get_var( $wpdb->prepare(
			"SELECT MAX(snapshot_date) FROM {$wpdb->prefix}sf_keywords WHERE source = 'gsc' AND property_id = %d",
			$property_id
		) );

		if ( ! $latest_date ) {
			return '';
		}

		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT query, page_path, source, clicks, impressions, ctr, position, search_volume, competition, snapshot_date
			FROM {$wpdb->prefix}sf_keywords
			WHERE snapshot_date = %s AND property_id = %d
			ORDER BY clicks DESC",
			$latest_date,
			$property_id
		), ARRAY_A );

		return self::array_to_csv( $rows, [
			'Keyword', 'Page Path', 'Source', 'Clicks', 'Impressions', 'CTR', 'Position',
			'Search Volume', 'Competition', 'Date',
		] );
	}

	/**
	 * Export pages data as JSON.
	 */
	public static function export_pages_json( int $property_id = 0 ): string {
		$property_id = $property_id ?: Property::get_active_property_id();

		global $wpdb;

		$latest_date = $wpdb->get_var( $wpdb->prepare(
			"SELECT MAX(snapshot_date) FROM {$wpdb->prefix}sf_snapshots WHERE source = 'gsc' AND property_id = %d",
			$property_id
		) );

		if ( ! $latest_date ) {
			return '[]';
		}

		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT page_path, source, device, clicks, impressions, ctr, position, snapshot_date
			FROM {$wpdb->prefix}sf_snapshots
			WHERE snapshot_date = %s AND property_id = %d
			ORDER BY clicks DESC",
			$latest_date,
			$property_id
		), ARRAY_A );

		return wp_json_encode( $rows, JSON_PRETTY_PRINT );
	}

	/**
	 * Export keywords data as JSON.
	 */
	public static function export_keywords_json( int $property_id = 0 ): string {
		$property_id = $property_id ?: Property::get_active_property_id();

		global $wpdb;

		$latest_date = $wpdb->get_var( $wpdb->prepare(
			"SELECT MAX(snapshot_date) FROM {$wpdb->prefix}sf_keywords WHERE source = 'gsc' AND property_id = %d",
			$property_id
		) );

		if ( ! $latest_date ) {
			return '[]';
		}

		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT query, page_path, source, clicks, impressions, ctr, position, search_volume, competition, snapshot_date
			FROM {$wpdb->prefix}sf_keywords
			WHERE snapshot_date = %s AND property_id = %d
			ORDER BY clicks DESC",
			$latest_date,
			$property_id
		), ARRAY_A );

		return wp_json_encode( $rows, JSON_PRETTY_PRINT );
	}

	/**
	 * Export alerts as CSV.
	 */
	public static function export_alerts_csv( int $property_id = 0 ): string {
		$property_id = $property_id ?: Property::get_active_property_id();

		global $wpdb;

		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT alert_type, title, severity, is_read, created_at
			FROM {$wpdb->prefix}sf_alerts
			WHERE property_id = %d
			ORDER BY created_at DESC
			LIMIT 500",
			$property_id
		), ARRAY_A );

		return self::array_to_csv( $rows, [
			'Type', 'Title', 'Severity', 'Read', 'Created At',
		] );
	}

	/**
	 * Convert an array of rows to CSV string.
	 */
	private static function array_to_csv( array $rows, array $headers ): string {
		if ( empty( $rows ) ) {
			return '';
		}

		$output = fopen( 'php://temp', 'r+' );
		fputcsv( $output, $headers );

		foreach ( $rows as $row ) {
			fputcsv( $output, array_values( $row ) );
		}

		rewind( $output );
		$csv = stream_get_contents( $output );
		fclose( $output );

		return $csv;
	}
}
