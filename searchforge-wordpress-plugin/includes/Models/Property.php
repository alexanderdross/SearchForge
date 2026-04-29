<?php

namespace SearchForge\Models;

use SearchForge\Database\Encryption;

defined( 'ABSPATH' ) || exit;

class Property {

	private const TABLE = 'sf_properties';

	private const ENCRYPTED_FIELDS = [
		'gsc_client_secret',
		'gsc_access_token',
		'gsc_refresh_token',
		'bing_api_key',
		'adobe_client_secret',
	];

	public static function get( int $id ): ?array {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$row = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}" . self::TABLE . " WHERE id = %d",
			$id
		), ARRAY_A );

		return $row ? self::decrypt_row( $row ) : null;
	}

	public static function get_default(): ?array {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$row = $wpdb->get_row(
			"SELECT * FROM {$wpdb->prefix}" . self::TABLE . " WHERE is_default = 1 LIMIT 1",
			ARRAY_A
		);

		return $row ? self::decrypt_row( $row ) : null;
	}

	public static function get_all(): array {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$rows = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}" . self::TABLE . " ORDER BY is_default DESC, label ASC",
			ARRAY_A
		) ?: [];

		return array_map( [ self::class, 'decrypt_row' ], $rows );
	}

	public static function create( array $data ): int|false {
		global $wpdb;

		$data = self::encrypt_row( $data );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->insert(
			$wpdb->prefix . self::TABLE,
			$data
		);

		return $result ? (int) $wpdb->insert_id : false;
	}

	public static function update( int $id, array $data ): bool {
		global $wpdb;

		$data = self::encrypt_row( $data );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		return (bool) $wpdb->update(
			$wpdb->prefix . self::TABLE,
			$data,
			[ 'id' => $id ],
			null,
			[ '%d' ]
		);
	}

	public static function delete( int $id ): bool {
		global $wpdb;

		$property = self::get( $id );
		if ( ! $property || $property['is_default'] ) {
			return false;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->delete( $wpdb->prefix . 'sf_snapshots', [ 'property_id' => $id ], [ '%d' ] );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->delete( $wpdb->prefix . 'sf_keywords', [ 'property_id' => $id ], [ '%d' ] );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->delete( $wpdb->prefix . 'sf_ga4_metrics', [ 'property_id' => $id ], [ '%d' ] );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->delete( $wpdb->prefix . 'sf_briefs_cache', [ 'property_id' => $id ], [ '%d' ] );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->delete( $wpdb->prefix . 'sf_alerts', [ 'property_id' => $id ], [ '%d' ] );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->delete( $wpdb->prefix . 'sf_sync_log', [ 'property_id' => $id ], [ '%d' ] );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->delete( $wpdb->prefix . 'sf_competitors', [ 'property_id' => $id ], [ '%d' ] );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		return (bool) $wpdb->delete(
			$wpdb->prefix . self::TABLE,
			[ 'id' => $id ],
			[ '%d' ]
		);
	}

	public static function get_active_property_id(): int {
		$user_id = get_current_user_id();
		if ( $user_id ) {
			$active = (int) get_user_meta( $user_id, 'searchforge_active_property', true );
			if ( $active && self::get( $active ) ) {
				return $active;
			}
		}

		$default = self::get_default();
		return $default ? (int) $default['id'] : 1;
	}

	public static function set_active_property_id( int $id ): void {
		$user_id = get_current_user_id();
		if ( $user_id ) {
			update_user_meta( $user_id, 'searchforge_active_property', $id );
		}
	}

	public static function count(): int {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}" . self::TABLE
		);
	}

	private static function encrypt_row( array $data ): array {
		foreach ( self::ENCRYPTED_FIELDS as $field ) {
			if ( ! empty( $data[ $field ] ) && is_string( $data[ $field ] ) ) {
				$encrypted = Encryption::encrypt( $data[ $field ] );
				if ( false !== $encrypted ) {
					$data[ $field ] = $encrypted;
				}
			}
		}
		return $data;
	}

	private static function decrypt_row( array $data ): array {
		foreach ( self::ENCRYPTED_FIELDS as $field ) {
			if ( ! empty( $data[ $field ] ) && is_string( $data[ $field ] ) ) {
				$decrypted = Encryption::decrypt( $data[ $field ] );
				if ( false !== $decrypted ) {
					$data[ $field ] = $decrypted;
				}
			}
		}
		return $data;
	}
}
