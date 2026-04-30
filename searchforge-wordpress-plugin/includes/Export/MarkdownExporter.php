<?php

namespace SearchForge\Export;

use SearchForge\Admin\Settings;
use SearchForge\Models\Property;

defined( 'ABSPATH' ) || exit;

class MarkdownExporter {

	/**
	 * Generate a markdown brief for a single page (combined GSC + Bing).
	 *
	 * @return string|\WP_Error
	 */
	public function generate_page_brief( string $page_path, int $property_id = 0 ): string|\WP_Error {
		$property_id = $property_id ?: Property::get_active_property_id();

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$latest_date = $wpdb->get_var( $wpdb->prepare(
			"SELECT MAX(snapshot_date) FROM {$wpdb->prefix}sf_snapshots
			WHERE page_path = %s AND source = 'gsc' AND property_id = %d",
			$page_path,
			$property_id
		) );

		if ( ! $latest_date ) {
			return new \WP_Error( 'no_data', __( 'No data found for this page.', 'searchforge-wordpress-plugin' ) );
		}

		$site_url = home_url();
		$full_url = $site_url . $page_path;

		$sources = [ 'gsc' ];
		if ( Settings::is_pro() && Settings::get( 'bing_enabled' ) ) {
			$sources[] = 'bing';
		}

		$md  = "# SEO Brief: {$page_path}\n";
		$md .= "**Source:** SearchForge (" . implode( ' + ', array_map( 'strtoupper', $sources ) ) . ")\n";
		$md .= "**URL:** {$full_url}\n";
		$md .= "**Data Period:** 28 days ending {$latest_date}\n";
		$md .= "**Generated:** " . gmdate( 'Y-m-d H:i' ) . " UTC\n\n";
		$md .= "---\n\n";

		// Cross-engine page performance comparison.
		if ( count( $sources ) > 1 ) {
			$md .= $this->render_cross_engine_page( $page_path, $latest_date, $sources, $property_id );
		} else {
			$md .= $this->render_single_source_page( $page_path, $latest_date, 'gsc', $property_id );
		}

		// Keywords per source.
		foreach ( $sources as $source ) {
			$md .= $this->render_keywords( $page_path, $latest_date, $source, $property_id );
		}

		// Cross-engine keyword comparison (if multiple sources).
		if ( count( $sources ) > 1 ) {
			$md .= $this->render_keyword_comparison( $page_path, $latest_date, $property_id );
		}

		// Historical trend (if data exists).
		$md .= $this->render_trend_section( $page_path, $property_id );

		// GA4 behavior data (if available).
		$md .= $this->render_ga4_section( $page_path );

		// Insights.
		$all_keywords = $this->get_keywords( $page_path, $latest_date, 'gsc', 50, $property_id );
		$page_data    = $this->get_page_data( $page_path, $latest_date, 'gsc', $property_id );
		if ( $page_data && ! empty( $all_keywords ) ) {
			$md .= "## Quick Insights\n";
			$md .= $this->generate_insights( $page_data, $all_keywords );
		}

		// SearchForge Score.
		$score = \SearchForge\Scoring\Score::calculate_page_score( $page_path, $property_id );
		if ( $score ) {
			$md .= $this->render_score_section( $score );
		}

		return $md;
	}

	/**
	 * Generate a master brief for the entire site (combined sources).
	 *
	 * @return string|\WP_Error
	 */
	public function generate_site_brief( int $property_id = 0 ): string|\WP_Error {
		$property_id = $property_id ?: Property::get_active_property_id();

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$latest_date = $wpdb->get_var( $wpdb->prepare(
			"SELECT MAX(snapshot_date) FROM {$wpdb->prefix}sf_snapshots WHERE source = 'gsc' AND property_id = %d",
			$property_id
		) );

		if ( ! $latest_date ) {
			return new \WP_Error( 'no_data', __( 'No GSC data available. Run a sync first.', 'searchforge-wordpress-plugin' ) );
		}

		$page_limit   = Settings::get_page_limit();
		$limit_clause = $page_limit > 0 ? $wpdb->prepare( 'LIMIT %d', $page_limit ) : '';

		$site_url = home_url();
		$sources  = [ 'gsc' ];
		if ( Settings::is_pro() && Settings::get( 'bing_enabled' ) ) {
			$sources[] = 'bing';
		}

		$md  = "# SearchForge Site Brief: {$site_url}\n";
		$md .= "**Sources:** " . implode( ', ', array_map( 'strtoupper', $sources ) ) . "\n";
		$md .= "**Data Period:** 28 days ending {$latest_date}\n";
		$md .= "**Generated:** " . gmdate( 'Y-m-d H:i' ) . " UTC\n\n";
		$md .= "---\n\n";

		// Per-source site overview.
		foreach ( $sources as $source ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$totals = $wpdb->get_row( $wpdb->prepare(
				"SELECT COUNT(DISTINCT page_path) as pages, SUM(clicks) as clicks,
					SUM(impressions) as impressions, AVG(ctr) as ctr, AVG(position) as position
				FROM {$wpdb->prefix}sf_snapshots
				WHERE source = %s AND snapshot_date = %s AND device = 'all' AND property_id = %d",
				$source,
				$latest_date,
				$property_id
			), ARRAY_A );

			$label = strtoupper( $source );
			$md .= "## {$label} Overview\n";
			$md .= "| Metric | Value |\n";
			$md .= "|--------|-------|\n";
			$md .= "| Pages Tracked | " . number_format( (int) $totals['pages'] ) . " |\n";
			$md .= "| Total Clicks | " . number_format( (int) $totals['clicks'] ) . " |\n";
			$md .= "| Total Impressions | " . number_format( (int) $totals['impressions'] ) . " |\n";
			$md .= "| Avg CTR | " . round( (float) $totals['ctr'] * 100, 1 ) . "% |\n";
			$md .= "| Avg Position | " . round( (float) $totals['position'], 1 ) . " |\n\n";
		}

		// Top pages (GSC primary, Bing secondary).
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$pages = $wpdb->get_results( $wpdb->prepare(
			"SELECT page_path, clicks, impressions, ctr, position
			FROM {$wpdb->prefix}sf_snapshots
			WHERE source = 'gsc' AND snapshot_date = %s AND device = 'all' AND property_id = %d
			ORDER BY clicks DESC
			{$limit_clause}",
			$latest_date,
			$property_id
		), ARRAY_A );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter

		$md .= "## Top Pages\n";
		$md .= "| Page | Clicks | Impressions | CTR | Position |\n";
		$md .= "|------|--------|-------------|-----|----------|\n";

		foreach ( $pages as $page ) {
			$md .= sprintf(
				"| %s | %s | %s | %s%% | %s |\n",
				$this->escape_md( $page['page_path'] ),
				number_format( (int) $page['clicks'] ),
				number_format( (int) $page['impressions'] ),
				round( (float) $page['ctr'] * 100, 1 ),
				round( (float) $page['position'], 1 )
			);
		}

		$md .= "\n";

		// Site-level SearchForge Score.
		$score = \SearchForge\Scoring\Score::calculate_site_score( $property_id );
		if ( $score ) {
			$md .= $this->render_score_section( $score );
		}

		return $md;
	}

	/**
	 * Render cross-engine page performance comparison.
	 */
	private function render_cross_engine_page( string $page_path, string $date, array $sources, int $property_id = 0 ): string {
		$md  = "## Page Performance (Cross-Engine)\n";
		$md .= "| Metric | " . implode( ' | ', array_map( 'strtoupper', $sources ) ) . " |\n";
		$md .= "|--------" . str_repeat( '|-------', count( $sources ) ) . "|\n";

		$data = [];
		foreach ( $sources as $source ) {
			$data[ $source ] = $this->get_page_data( $page_path, $date, $source, $property_id );
		}

		$metrics = [
			'clicks'      => 'Clicks',
			'impressions' => 'Impressions',
			'ctr'         => 'CTR',
			'position'    => 'Avg Position',
		];

		foreach ( $metrics as $key => $label ) {
			$md .= "| {$label}";
			foreach ( $sources as $source ) {
				$val = $data[ $source ][ $key ] ?? 0;
				if ( $key === 'ctr' ) {
					$md .= ' | ' . round( (float) $val * 100, 1 ) . '%';
				} elseif ( $key === 'position' ) {
					$md .= ' | ' . round( (float) $val, 1 );
				} else {
					$md .= ' | ' . number_format( (int) $val );
				}
			}
			$md .= " |\n";
		}

		$md .= "\n";
		return $md;
	}

	/**
	 * Render single-source page metrics.
	 */
	private function render_single_source_page( string $page_path, string $date, string $source, int $property_id = 0 ): string {
		$data = $this->get_page_data( $page_path, $date, $source, $property_id );
		if ( ! $data ) {
			return '';
		}

		$md  = "## Page Performance\n";
		$md .= "| Metric | Value |\n";
		$md .= "|--------|-------|\n";
		$md .= "| Clicks | " . number_format( (int) $data['clicks'] ) . " |\n";
		$md .= "| Impressions | " . number_format( (int) $data['impressions'] ) . " |\n";
		$md .= "| CTR | " . round( (float) $data['ctr'] * 100, 1 ) . "% |\n";
		$md .= "| Avg Position | " . round( (float) $data['position'], 1 ) . " |\n\n";

		return $md;
	}

	/**
	 * Render keyword table for a specific source.
	 */
	private function render_keywords( string $page_path, string $date, string $source, int $property_id = 0 ): string {
		$keywords = $this->get_keywords( $page_path, $date, $source, 50, $property_id );
		if ( empty( $keywords ) ) {
			return '';
		}

		$label = strtoupper( $source );
		$md  = "## {$label} Keywords (" . count( $keywords ) . " shown)\n";
		$md .= "| Keyword | Clicks | Impressions | CTR | Position |\n";
		$md .= "|---------|--------|-------------|-----|----------|\n";

		foreach ( $keywords as $kw ) {
			$md .= sprintf(
				"| %s | %s | %s | %s%% | %s |\n",
				$this->escape_md( $kw['query'] ),
				number_format( (int) $kw['clicks'] ),
				number_format( (int) $kw['impressions'] ),
				round( (float) $kw['ctr'] * 100, 1 ),
				round( (float) $kw['position'], 1 )
			);
		}

		$md .= "\n";
		return $md;
	}

	/**
	 * Render side-by-side keyword comparison (Google vs Bing).
	 */
	private function render_keyword_comparison( string $page_path, string $date, int $property_id = 0 ): string {
		$property_id = $property_id ?: Property::get_active_property_id();

		global $wpdb;

		// Keywords that appear in both engines.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$shared = $wpdb->get_results( $wpdb->prepare(
			"SELECT
				g.query,
				g.clicks AS gsc_clicks, g.impressions AS gsc_impressions, g.position AS gsc_position,
				b.clicks AS bing_clicks, b.impressions AS bing_impressions, b.position AS bing_position
			FROM {$wpdb->prefix}sf_keywords g
			INNER JOIN {$wpdb->prefix}sf_keywords b
				ON g.query = b.query AND b.source = 'bing' AND b.snapshot_date = %s AND b.page_path = %s AND b.property_id = %d
			WHERE g.source = 'gsc' AND g.snapshot_date = %s AND g.page_path = %s AND g.property_id = %d
			ORDER BY (g.clicks + b.clicks) DESC
			LIMIT 20",
			$date,
			$page_path,
			$property_id,
			$date,
			$page_path,
			$property_id
		), ARRAY_A );

		if ( empty( $shared ) ) {
			return '';
		}

		$md  = "## Google vs Bing Keyword Comparison\n";
		$md .= "| Keyword | GSC Clicks | Bing Clicks | GSC Pos | Bing Pos | Delta |\n";
		$md .= "|---------|-----------|------------|---------|---------|-------|\n";

		foreach ( $shared as $row ) {
			$delta = round( (float) $row['gsc_position'] - (float) $row['bing_position'], 1 );
			$delta_str = $delta > 0 ? "+{$delta}" : (string) $delta;

			$md .= sprintf(
				"| %s | %s | %s | %s | %s | %s |\n",
				$this->escape_md( $row['query'] ),
				number_format( (int) $row['gsc_clicks'] ),
				number_format( (int) $row['bing_clicks'] ),
				round( (float) $row['gsc_position'], 1 ),
				round( (float) $row['bing_position'], 1 ),
				$delta_str
			);
		}

		// Bing-only keywords.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$bing_only = $wpdb->get_results( $wpdb->prepare(
			"SELECT b.query, b.clicks, b.impressions, b.position
			FROM {$wpdb->prefix}sf_keywords b
			LEFT JOIN {$wpdb->prefix}sf_keywords g
				ON b.query = g.query AND g.source = 'gsc' AND g.snapshot_date = %s AND g.page_path = %s AND g.property_id = %d
			WHERE b.source = 'bing' AND b.snapshot_date = %s AND b.page_path = %s AND b.property_id = %d
				AND g.id IS NULL
			ORDER BY b.clicks DESC
			LIMIT 10",
			$date,
			$page_path,
			$property_id,
			$date,
			$page_path,
			$property_id
		), ARRAY_A );

		if ( ! empty( $bing_only ) ) {
			$md .= "\n### Bing-Only Keywords\n";
			$md .= "Keywords where users find you on Bing but not Google:\n\n";
			foreach ( $bing_only as $kw ) {
				$md .= sprintf(
					"- **%s** — %s clicks, position %s\n",
					$kw['query'],
					number_format( (int) $kw['clicks'] ),
					round( (float) $kw['position'], 1 )
				);
			}
		}

		$md .= "\n";
		return $md;
	}

	/**
	 * Render historical trend section for a page.
	 */
	private function render_trend_section( string $page_path, int $property_id = 0 ): string {
		$trend = \SearchForge\Trends\Engine::get_page_trend( $page_path, 'gsc', $property_id );
		if ( ! $trend || empty( $trend['snapshots'] ) ) {
			return '';
		}

		$md  = "## Historical Trend\n";
		$md .= "| Period | Clicks | Impressions | Position | Change |\n";
		$md .= "|--------|--------|-------------|----------|--------|\n";

		foreach ( $trend['snapshots'] as $snap ) {
			$change = '';
			if ( isset( $snap['clicks_change'] ) ) {
				$pct = $snap['clicks_change'];
				$change = $pct >= 0 ? "+{$pct}%" : "{$pct}%";
			}

			$md .= sprintf(
				"| %s | %s | %s | %s | %s |\n",
				$snap['date'],
				number_format( (int) $snap['clicks'] ),
				number_format( (int) $snap['impressions'] ),
				round( (float) $snap['position'], 1 ),
				$change
			);
		}

		if ( ! empty( $trend['decay_detected'] ) ) {
			$md .= "\n> **Content Decay Detected:** This page has lost "
				. abs( $trend['decay_percentage'] ) . "% clicks over the last "
				. $trend['decay_period_days'] . " days.\n";
		}

		$md .= "\n";
		return $md;
	}

	/**
	 * Render GA4 on-page behavior section.
	 */
	private function render_ga4_section( string $page_path ): string {
		if ( ! Settings::is_pro() || ! Settings::get( 'ga4_enabled' ) ) {
			return '';
		}

		$ga4 = \SearchForge\Integrations\GA4\Syncer::get_page_behavior( $page_path );
		if ( ! $ga4 ) {
			return '';
		}

		$md  = "## On-Page Behavior (GA4)\n";
		$md .= "| Metric | Value | Signal |\n";
		$md .= "|--------|-------|--------|\n";

		$bounce = (float) $ga4['bounce_rate'];
		$bounce_signal = $bounce > 60 ? 'High — content mismatch' : ( $bounce > 40 ? 'Normal' : 'Good' );
		$md .= "| Bounce Rate | {$bounce}% | {$bounce_signal} |\n";

		$dur = (float) $ga4['avg_session_dur'];
		$dur_signal = $dur < 60 ? 'Low — users leave quickly' : ( $dur < 180 ? 'Average' : 'Good engagement' );
		$md .= "| Avg Session Duration | " . gmdate( 'i:s', (int) $dur ) . " | {$dur_signal} |\n";

		$md .= "| Sessions | " . number_format( (int) $ga4['sessions'] ) . " | — |\n";
		$md .= "| Organic Sessions | " . number_format( (int) $ga4['organic_sessions'] ) . " | — |\n";
		$md .= "| Conversions | " . (int) $ga4['conversions'] . " | "
			. ( (int) $ga4['conversions'] > 0 ? 'Converting' : 'No conversions' ) . " |\n";

		$md .= "\n";
		return $md;
	}

	/**
	 * Render SearchForge Score section.
	 */
	private function render_score_section( array $score ): string {
		$md  = "## SearchForge Score\n";
		$md .= "**Overall: {$score['total']}/100**\n\n";

		if ( Settings::is_pro() && ! empty( $score['components'] ) ) {
			$md .= "| Component | Score | Weight |\n";
			$md .= "|-----------|-------|--------|\n";

			foreach ( $score['components'] as $name => $data ) {
				$md .= sprintf(
					"| %s | %d/100 | %d%% |\n",
					ucfirst( $name ),
					$data['score'],
					$data['weight']
				);
			}

			if ( ! empty( $score['recommendations'] ) ) {
				$md .= "\n### Recommendations\n";
				foreach ( $score['recommendations'] as $rec ) {
					$md .= "- {$rec}\n";
				}
			}
		} else {
			$md .= "*Upgrade to Pro for full score breakdown and recommendations.*\n";
		}

		$md .= "\n";
		return $md;
	}

	/**
	 * Generate heuristic insights.
	 */
	private function generate_insights( array $page_data, array $keywords ): string {
		$md = '';

		// High impressions, low CTR.
		$low_ctr = array_filter( $keywords, function ( $kw ) {
			return (int) $kw['impressions'] > 50 && (float) $kw['ctr'] < 0.02;
		} );

		if ( ! empty( $low_ctr ) ) {
			$md .= "\n### Low CTR Opportunities\n";
			$md .= "These keywords have high impressions but low CTR — consider optimizing title/description:\n\n";
			foreach ( array_slice( $low_ctr, 0, 5 ) as $kw ) {
				$md .= sprintf(
					"- **%s** — %s impressions, %s%% CTR, position %s\n",
					$kw['query'],
					number_format( (int) $kw['impressions'] ),
					round( (float) $kw['ctr'] * 100, 1 ),
					round( (float) $kw['position'], 1 )
				);
			}
			$md .= "\n";
		}

		// Close to page 1.
		$almost_page1 = array_filter( $keywords, function ( $kw ) {
			$pos = (float) $kw['position'];
			return $pos > 10 && $pos <= 20 && (int) $kw['impressions'] > 20;
		} );

		if ( ! empty( $almost_page1 ) ) {
			$md .= "\n### Almost Page 1 (positions 11-20)\n";
			$md .= "Keywords close to page 1 with existing impressions:\n\n";
			foreach ( array_slice( $almost_page1, 0, 5 ) as $kw ) {
				$md .= sprintf(
					"- **%s** — position %s, %s impressions\n",
					$kw['query'],
					round( (float) $kw['position'], 1 ),
					number_format( (int) $kw['impressions'] )
				);
			}
			$md .= "\n";
		}

		if ( empty( $md ) ) {
			$md = "No specific actionable insights detected for this page.\n";
		}

		return $md;
	}

	/**
	 * Get page snapshot data for a source.
	 */
	private function get_page_data( string $page_path, string $date, string $source, int $property_id = 0 ): ?array {
		$property_id = $property_id ?: Property::get_active_property_id();

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT clicks, impressions, ctr, position
			FROM {$wpdb->prefix}sf_snapshots
			WHERE page_path = %s AND snapshot_date = %s AND source = %s AND device = 'all' AND property_id = %d",
			$page_path,
			$date,
			$source,
			$property_id
		), ARRAY_A );
	}

	/**
	 * Get keywords for a page/source.
	 */
	private function get_keywords( string $page_path, string $date, string $source, int $limit = 50, int $property_id = 0 ): array {
		$property_id = $property_id ?: Property::get_active_property_id();

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT query, clicks, impressions, ctr, position
			FROM {$wpdb->prefix}sf_keywords
			WHERE page_path = %s AND snapshot_date = %s AND source = %s AND property_id = %d
			ORDER BY clicks DESC
			LIMIT %d",
			$page_path,
			$date,
			$source,
			$property_id,
			$limit
		), ARRAY_A );
	}

	private function escape_md( string $text ): string {
		return str_replace( '|', '\\|', $text );
	}
}
