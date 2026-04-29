<?php

namespace SearchForge\Database;

defined( 'ABSPATH' ) || exit;

class Encryption {

	public static function encrypt( string $value ): string|false {
		if ( empty( $value ) ) {
			return $value;
		}

		$key = self::get_key();
		$iv  = openssl_random_pseudo_bytes( 16 );

		$encrypted = openssl_encrypt( $value, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv );
		if ( false === $encrypted ) {
			return false;
		}

		return 'enc:' . base64_encode( $iv . $encrypted );
	}

	public static function decrypt( string $value ): string|false {
		if ( ! str_starts_with( $value, 'enc:' ) ) {
			return $value;
		}

		$key  = self::get_key();
		$data = base64_decode( substr( $value, 4 ), true );

		if ( false === $data || strlen( $data ) < 17 ) {
			return false;
		}

		$iv        = substr( $data, 0, 16 );
		$encrypted = substr( $data, 16 );

		return openssl_decrypt( $encrypted, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv );
	}

	private static function get_key(): string {
		return hash( 'sha256', wp_salt( 'auth' ) . 'searchforge_encrypt', true );
	}
}
