<?php

namespace SearchForge\Sitemap;

defined( 'ABSPATH' ) || exit;

class Discovery {

	/**
	 * Auto-discover sitemap URLs for the current site.
	 *
	 * Checks robots.txt, well-known locations, and common sitemap paths.
	 *
	 * @return array List of discovered sitemap URLs.
	 */
	public static function discover(): array {
		$sitemaps = [];
		$site_url = untrailingslashit( home_url() );

		// 1. Check robots.txt for Sitemap directives.
		$robots_sitemaps = self::from_robots_txt( $site_url );
		$sitemaps = array_merge( $sitemaps, $robots_sitemaps );

		// 2. Check common sitemap locations.
		$common_paths = [
			'/sitemap.xml',
			'/sitemap_index.xml',
			'/wp-sitemap.xml',
			'/sitemap.xml.gz',
		];

		foreach ( $common_paths as $path ) {
			$url = $site_url . $path;
			if ( ! in_array( $url, $sitemaps, true ) && self::url_exists( $url ) ) {
				$sitemaps[] = $url;
			}
		}

		return array_unique( $sitemaps );
	}

	/**
	 * Parse robots.txt for Sitemap directives.
	 */
	private static function from_robots_txt( string $site_url ): array {
		$robots_url = $site_url . '/robots.txt';
		$response   = wp_remote_get( $robots_url, [
			'timeout'    => 10,
			'user-agent' => 'SearchForge/' . SEARCHFORGE_VERSION,
			'sslverify'  => false,
		] );

		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return [];
		}

		$body    = wp_remote_retrieve_body( $response );
		$sitemaps = [];

		if ( preg_match_all( '/^Sitemap:\s*(.+)$/mi', $body, $matches ) ) {
			foreach ( $matches[1] as $url ) {
				$url = trim( $url );
				if ( filter_var( $url, FILTER_VALIDATE_URL ) ) {
					$sitemaps[] = $url;
				}
			}
		}

		return $sitemaps;
	}

	/**
	 * Parse a sitemap XML and extract page URLs.
	 *
	 * Handles both <urlset> and <sitemapindex> (recursive).
	 *
	 * @param string $sitemap_url URL to the sitemap.
	 * @param int    $max_depth   Max recursion depth for sitemap indexes.
	 * @return array List of page URLs with optional lastmod.
	 */
	public static function parse( string $sitemap_url, int $max_depth = 3 ): array {
		if ( $max_depth <= 0 ) {
			return [];
		}

		$response = wp_remote_get( $sitemap_url, [
			'timeout'    => 20,
			'user-agent' => 'SearchForge/' . SEARCHFORGE_VERSION,
			'sslverify'  => false,
		] );

		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return [];
		}

		$body = wp_remote_retrieve_body( $response );

		// Handle gzip.
		if ( substr( $sitemap_url, -3 ) === '.gz' ) {
			$body = @gzdecode( $body );
			if ( ! $body ) {
				return [];
			}
		}

		libxml_use_internal_errors( true );
		$xml = simplexml_load_string( $body );
		if ( ! $xml ) {
			return [];
		}

		$urls = [];

		// Sitemap index — recurse into child sitemaps.
		if ( isset( $xml->sitemap ) ) {
			foreach ( $xml->sitemap as $sitemap ) {
				$loc = (string) $sitemap->loc;
				if ( $loc ) {
					$child_urls = self::parse( $loc, $max_depth - 1 );
					$urls = array_merge( $urls, $child_urls );
				}
			}
			return $urls;
		}

		// URL set — extract URLs.
		if ( isset( $xml->url ) ) {
			foreach ( $xml->url as $url_entry ) {
				$loc     = (string) $url_entry->loc;
				$lastmod = isset( $url_entry->lastmod ) ? (string) $url_entry->lastmod : null;

				if ( $loc ) {
					$parsed = wp_parse_url( $loc );
					$path   = $parsed['path'] ?? '/';

					$urls[] = [
						'url'      => $loc,
						'path'     => $path,
						'lastmod'  => $lastmod,
					];
				}
			}
		}

		return $urls;
	}

	/**
	 * Check if a URL returns a valid response.
	 */
	private static function url_exists( string $url ): bool {
		$response = wp_remote_head( $url, [
			'timeout'     => 5,
			'redirection' => 2,
			'user-agent'  => 'SearchForge/' . SEARCHFORGE_VERSION,
			'sslverify'   => false,
		] );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$status = wp_remote_retrieve_response_code( $response );
		return $status >= 200 && $status < 400;
	}

	/**
	 * Get page count from a sitemap URL.
	 */
	public static function count_urls( string $sitemap_url ): int {
		$urls = self::parse( $sitemap_url );
		return count( $urls );
	}
}
