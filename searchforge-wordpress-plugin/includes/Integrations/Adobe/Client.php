<?php

namespace SearchForge\Integrations\Adobe;

use SearchForge\Admin\Settings;

defined( 'ABSPATH' ) || exit;

class Client {

	private const IMS_TOKEN_URL = 'https://ims-na1.adobelogin.com/ims/token/v3';
	private const API_BASE      = 'https://analytics.adobe.io/api';

	public static function get_page_metrics( int $days = 28, int $limit = 500, ?array $property = null ): array|\WP_Error {
		$config = self::resolve_config( $property );
		if ( is_wp_error( $config ) ) {
			return $config;
		}

		$token = self::get_access_token( $config );
		if ( is_wp_error( $token ) ) {
			return $token;
		}

		$end_date   = wp_date( 'Y-m-d\TH:i:s' );
		$start_date = wp_date( 'Y-m-d\TH:i:s', strtotime( "-{$days} days" ) );

		$body = [
			'rsid'        => $config['report_suite_id'],
			'globalFilters' => [
				[
					'type'        => 'dateRange',
					'dateRange'   => "{$start_date}/{$end_date}",
				],
			],
			'metricContainer' => [
				'metrics' => [
					[ 'id' => 'metrics/visits' ],
					[ 'id' => 'metrics/pageviews' ],
					[ 'id' => 'metrics/bouncerate' ],
					[ 'id' => 'metrics/averagetimespentonpage' ],
					[ 'id' => 'metrics/orders' ],
					[ 'id' => 'metrics/revenue' ],
				],
			],
			'dimension'   => 'variables/page',
			'settings'    => [
				'limit'       => $limit,
				'page'        => 0,
				'nonesBehavior' => 'exclude-nones',
			],
		];

		$url = self::API_BASE . '/' . $config['org_id'] . '/reports';

		$response = wp_remote_post( $url, [
			'timeout' => 30,
			'headers' => [
				'Authorization'  => "Bearer {$token}",
				'x-api-key'      => $config['client_id'],
				'x-proxy-global-company-id' => $config['org_id'],
				'Content-Type'   => 'application/json',
			],
			'body'    => wp_json_encode( $body ),
		] );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $code >= 400 ) {
			$message = $data['message'] ?? $data['errorDescription'] ?? "Adobe API error (HTTP {$code})";
			return new \WP_Error( 'adobe_api', $message );
		}

		$pages = [];
		foreach ( $data['rows'] ?? [] as $row ) {
			$path = self::normalize_page_path( $row['value'] ?? '' );
			if ( empty( $path ) ) {
				continue;
			}
			$pages[ $path ] = [
				'visits'         => (int) ( $row['data'][0] ?? 0 ),
				'pageviews'      => (int) ( $row['data'][1] ?? 0 ),
				'bounce_rate'    => round( (float) ( $row['data'][2] ?? 0 ), 1 ),
				'avg_time_page'  => round( (float) ( $row['data'][3] ?? 0 ), 1 ),
				'conversions'    => (int) ( $row['data'][4] ?? 0 ),
				'revenue'        => round( (float) ( $row['data'][5] ?? 0 ), 2 ),
			];
		}

		return $pages;
	}

	private static function get_access_token( array $config ): string|\WP_Error {
		$cache_key = 'searchforge_adobe_token_' . md5( $config['client_id'] );
		$cached = get_transient( $cache_key );
		if ( $cached ) {
			return $cached;
		}

		$response = wp_remote_post( self::IMS_TOKEN_URL, [
			'timeout' => 15,
			'body'    => [
				'grant_type'    => 'client_credentials',
				'client_id'     => $config['client_id'],
				'client_secret' => $config['client_secret'],
				'scope'         => 'openid,AdobeID,read_organizations,additional_info.projectedProductContext',
			],
		] );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $code >= 400 || empty( $data['access_token'] ) ) {
			$message = $data['error_description'] ?? "Adobe IMS auth failed (HTTP {$code})";
			return new \WP_Error( 'adobe_auth', $message );
		}

		$expires = ( $data['expires_in'] ?? 3600 ) - 120;
		set_transient( $cache_key, $data['access_token'], $expires );

		return $data['access_token'];
	}

	private static function resolve_config( ?array $property = null ): array|\WP_Error {
		$org_id        = $property['adobe_org_id'] ?? '';
		$client_id     = $property['adobe_client_id'] ?? '';
		$client_secret = $property['adobe_client_secret'] ?? '';
		$rsid          = $property['adobe_report_suite_id'] ?? '';

		if ( empty( $org_id ) || empty( $client_id ) || empty( $client_secret ) || empty( $rsid ) ) {
			return new \WP_Error( 'adobe_config', __( 'Adobe Analytics credentials not configured for this property.', 'searchforge-wordpress-plugin' ) );
		}

		return [
			'org_id'          => $org_id,
			'client_id'       => $client_id,
			'client_secret'   => $client_secret,
			'report_suite_id' => $rsid,
		];
	}

	private static function normalize_page_path( string $page ): string {
		$parsed = wp_parse_url( $page );
		$path = $parsed['path'] ?? $page;
		if ( empty( $path ) || $path === '/' ) {
			return '/';
		}
		return '/' . trim( $path, '/' ) . '/';
	}
}
