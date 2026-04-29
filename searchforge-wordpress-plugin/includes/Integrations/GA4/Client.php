<?php

namespace SearchForge\Integrations\GA4;

use SearchForge\Admin\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Google Analytics 4 Data API client.
 *
 * Uses the GA Data API v1 to fetch on-page behavior metrics
 * (bounce rate, engagement time, conversions) per page.
 */
class Client {

	private const API_BASE = 'https://analyticsdata.googleapis.com/v1beta';

	/**
	 * Get page-level engagement metrics.
	 *
	 * @param int        $days     Number of days to look back.
	 * @param int        $limit    Max rows.
	 * @param array|null $property Optional property config with ga4_property_id and OAuth tokens.
	 * @return array|\WP_Error  [ '/path' => [ 'sessions' => ..., 'bounce_rate' => ..., ... ], ... ]
	 */
	public static function get_page_metrics( int $days = 28, int $limit = 100, ?array $property = null ): array|\WP_Error {
		$property_id = self::resolve_ga4_property_id( $property );
		if ( empty( $property_id ) ) {
			return new \WP_Error( 'no_ga4', __( 'GA4 property ID not configured.', 'searchforge-wordpress-plugin' ) );
		}

		$body = [
			'dateRanges'      => [
				[ 'startDate' => "{$days}daysAgo", 'endDate' => 'today' ],
			],
			'dimensions'      => [
				[ 'name' => 'pagePath' ],
			],
			'metrics'         => [
				[ 'name' => 'sessions' ],
				[ 'name' => 'bounceRate' ],
				[ 'name' => 'averageSessionDuration' ],
				[ 'name' => 'engagedSessions' ],
				[ 'name' => 'conversions' ],
				[ 'name' => 'screenPageViews' ],
			],
			'orderBys'        => [
				[ 'metric' => [ 'metricName' => 'sessions' ], 'desc' => true ],
			],
			'limit'           => $limit,
		];

		$result = self::api_request( "properties/{$property_id}:runReport", $body, $property );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$pages = [];
		foreach ( $result['rows'] ?? [] as $row ) {
			$path = $row['dimensionValues'][0]['value'] ?? '';
			$pages[ $path ] = [
				'sessions'          => (int) ( $row['metricValues'][0]['value'] ?? 0 ),
				'bounce_rate'       => round( (float) ( $row['metricValues'][1]['value'] ?? 0 ) * 100, 1 ),
				'avg_session_dur'   => round( (float) ( $row['metricValues'][2]['value'] ?? 0 ), 1 ),
				'engaged_sessions'  => (int) ( $row['metricValues'][3]['value'] ?? 0 ),
				'conversions'       => (int) ( $row['metricValues'][4]['value'] ?? 0 ),
				'pageviews'         => (int) ( $row['metricValues'][5]['value'] ?? 0 ),
			];
		}

		return $pages;
	}

	/**
	 * Get landing page performance (search-attributed sessions).
	 *
	 * @param int        $days     Number of days to look back.
	 * @param int        $limit    Max rows.
	 * @param array|null $property Optional property config with ga4_property_id and OAuth tokens.
	 * @return array|\WP_Error
	 */
	public static function get_landing_pages( int $days = 28, int $limit = 50, ?array $property = null ): array|\WP_Error {
		$property_id = self::resolve_ga4_property_id( $property );
		if ( empty( $property_id ) ) {
			return new \WP_Error( 'no_ga4', __( 'GA4 property ID not configured.', 'searchforge-wordpress-plugin' ) );
		}

		$body = [
			'dateRanges'       => [
				[ 'startDate' => "{$days}daysAgo", 'endDate' => 'today' ],
			],
			'dimensions'       => [
				[ 'name' => 'landingPage' ],
				[ 'name' => 'sessionDefaultChannelGroup' ],
			],
			'metrics'          => [
				[ 'name' => 'sessions' ],
				[ 'name' => 'bounceRate' ],
				[ 'name' => 'averageSessionDuration' ],
				[ 'name' => 'conversions' ],
			],
			'dimensionFilter'  => [
				'filter' => [
					'fieldName'    => 'sessionDefaultChannelGroup',
					'stringFilter' => [
						'matchType' => 'EXACT',
						'value'     => 'Organic Search',
					],
				],
			],
			'orderBys'         => [
				[ 'metric' => [ 'metricName' => 'sessions' ], 'desc' => true ],
			],
			'limit'            => $limit,
		];

		$result = self::api_request( "properties/{$property_id}:runReport", $body, $property );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$pages = [];
		foreach ( $result['rows'] ?? [] as $row ) {
			$path = $row['dimensionValues'][0]['value'] ?? '';
			$pages[ $path ] = [
				'organic_sessions' => (int) ( $row['metricValues'][0]['value'] ?? 0 ),
				'bounce_rate'      => round( (float) ( $row['metricValues'][1]['value'] ?? 0 ) * 100, 1 ),
				'avg_session_dur'  => round( (float) ( $row['metricValues'][2]['value'] ?? 0 ), 1 ),
				'conversions'      => (int) ( $row['metricValues'][3]['value'] ?? 0 ),
			];
		}

		return $pages;
	}

	/**
	 * Resolve GA4 property ID from property config or global settings.
	 *
	 * @param array|null $property Optional property config array.
	 */
	private static function resolve_ga4_property_id( ?array $property = null ): string {
		if ( $property && ! empty( $property['ga4_property_id'] ) ) {
			return (string) $property['ga4_property_id'];
		}
		return Settings::get( 'ga4_property_id', '' );
	}

	/**
	 * Make a request to the GA4 Data API.
	 *
	 * @param string     $endpoint API endpoint.
	 * @param array      $body     Request body.
	 * @param array|null $property Optional property config for per-property tokens.
	 */
	private static function api_request( string $endpoint, array $body, ?array $property = null ): array|\WP_Error {
		$token = self::get_access_token( $property );
		if ( is_wp_error( $token ) ) {
			return $token;
		}

		$url = self::API_BASE . '/' . $endpoint;

		$response = wp_remote_post( $url, [
			'timeout' => 30,
			'headers' => [
				'Authorization' => "Bearer {$token}",
				'Content-Type'  => 'application/json',
			],
			'body'    => wp_json_encode( $body ),
		] );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $code >= 400 ) {
			$message = $data['error']['message'] ?? "GA4 API error (HTTP {$code})";
			return new \WP_Error( 'ga4_api', $message );
		}

		return $data;
	}

	/**
	 * Get a valid OAuth access token (reuses GSC OAuth tokens).
	 *
	 * @param array|null $property Optional property config for per-property tokens.
	 */
	private static function get_access_token( ?array $property = null ): string|\WP_Error {
		// GA4 uses the same Google OAuth tokens as GSC.
		$token = \SearchForge\Integrations\GSC\OAuth::get_access_token( $property );

		if ( is_wp_error( $token ) ) {
			return new \WP_Error( 'ga4_auth', __( 'GA4 requires a connected Google account. Connect via GSC settings.', 'searchforge-wordpress-plugin' ) );
		}

		return $token;
	}
}
