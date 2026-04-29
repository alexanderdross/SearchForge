<?php

namespace SearchForge\Analysis;

use SearchForge\Admin\Dashboard;
use SearchForge\Admin\PageDetail;
use SearchForge\Models\Property;
use SearchForge\Monitoring\PerformanceTrend;
use SearchForge\Scoring\Score;

defined( 'ABSPATH' ) || exit;

class MergerAnalysis {

	private array $property_ids;
	private array $properties = [];
	private array $nav_uploads = [];

	public function __construct( array $property_ids, array $nav_uploads = [] ) {
		$this->property_ids = array_map( 'intval', $property_ids );
		$this->nav_uploads  = $nav_uploads;
		foreach ( $this->property_ids as $pid ) {
			$prop = Property::get( $pid );
			if ( $prop ) {
				$this->properties[ $pid ] = $prop;
			}
		}
	}

	public function generate_markdown(): string {
		$url_patterns  = $this->detect_url_patterns();
		$nav_analysis  = $this->analyze_navigation();
		$ia_analysis   = $this->analyze_information_architecture();
		$funnel        = $this->analyze_user_funnels();
		$cannibal      = $this->detect_cross_property_cannibalization();
		$summaries     = PropertyComparison::compare_summaries( $this->property_ids );

		$md = "# CMS Backend Merger Analysis\n\n";
		$md .= "Generated: " . wp_date( 'Y-m-d H:i' ) . "\n\n";
		$md .= "Properties analyzed: " . count( $this->properties ) . "\n\n";

		foreach ( $this->properties as $pid => $prop ) {
			$s = $summaries[ $pid ] ?? [];
			$md .= "- **{$prop['label']}** ({$prop['domain']}): ";
			$md .= number_format( $s['total_clicks'] ?? 0 ) . " clicks, ";
			$md .= number_format( $s['total_impressions'] ?? 0 ) . " impressions, ";
			$md .= ( $s['avg_position'] ?? '—' ) . " avg pos\n";
		}

		$md .= "\n---\n\n";
		$md .= $this->render_executive_summary( $summaries, $nav_analysis, $cannibal );
		if ( ! empty( $this->nav_uploads ) ) {
			$md .= $this->render_current_navigation();
		}
		$md .= $this->render_url_patterns( $url_patterns );
		$md .= $this->render_navigation_recommendations( $nav_analysis );
		$md .= $this->render_ia_restructuring( $ia_analysis );
		$md .= $this->render_funnel_analysis( $funnel );
		$md .= $this->render_keyword_strategy( $cannibal );

		return $md;
	}

	private function detect_url_patterns(): array {
		global $wpdb;

		$patterns = [];

		foreach ( $this->property_ids as $pid ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$rows = $wpdb->get_results( $wpdb->prepare(
				"SELECT DISTINCT page_path FROM {$wpdb->prefix}sf_snapshots
				WHERE property_id = %d AND source = 'gsc' AND device = 'all'
				ORDER BY page_path",
				$pid
			), ARRAY_A );

			$paths = array_column( $rows, 'page_path' );
			$prefixes = [];

			foreach ( $paths as $path ) {
				$parts = array_filter( explode( '/', trim( $path, '/' ) ) );
				if ( count( $parts ) >= 1 ) {
					$prefix = '/' . $parts[0] . '/';
					if ( ! isset( $prefixes[ $prefix ] ) ) {
						$prefixes[ $prefix ] = 0;
					}
					$prefixes[ $prefix ]++;
				}
			}

			arsort( $prefixes );

			$patterns[ $pid ] = [
				'total_pages' => count( $paths ),
				'prefixes'    => $prefixes,
				'max_depth'   => max( array_map( fn( $p ) => count( array_filter( explode( '/', trim( $p, '/' ) ) ) ), $paths ?: [ '/' ] ) ),
			];
		}

		return $patterns;
	}

	private function analyze_navigation(): array {
		global $wpdb;

		$all_pages = [];

		foreach ( $this->property_ids as $pid ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$latest = $wpdb->get_var( $wpdb->prepare(
				"SELECT MAX(snapshot_date) FROM {$wpdb->prefix}sf_snapshots
				WHERE property_id = %d AND source = 'gsc' AND device = 'all'",
				$pid
			) );
			if ( ! $latest ) {
				continue;
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$pages = $wpdb->get_results( $wpdb->prepare(
				"SELECT s.page_path, s.clicks, s.impressions, s.position, s.ctr
				FROM {$wpdb->prefix}sf_snapshots s
				WHERE s.property_id = %d AND s.source = 'gsc' AND s.device = 'all' AND s.snapshot_date = %s
				ORDER BY s.clicks DESC",
				$pid,
				$latest
			), ARRAY_A );

			$ga4_data = $this->get_ga4_lookup( $pid, $latest );

			foreach ( $pages as $page ) {
				$path = $page['page_path'];
				$ga4  = $ga4_data[ $path ] ?? null;

				$sessions    = $ga4 ? (int) $ga4['sessions'] : 0;
				$bounce      = $ga4 ? (float) $ga4['bounce_rate'] : 50.0;
				$conversions = $ga4 ? (int) $ga4['conversions'] : 0;

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$kw_count = (int) $wpdb->get_var( $wpdb->prepare(
					"SELECT COUNT(DISTINCT query) FROM {$wpdb->prefix}sf_keywords
					WHERE property_id = %d AND page_path = %s AND source = 'gsc'
					AND snapshot_date = %s",
					$pid,
					$path,
					$latest
				) );

				if ( ! isset( $all_pages[ $path ] ) ) {
					$all_pages[ $path ] = [
						'page_path'       => $path,
						'combined_clicks' => 0,
						'combined_impr'   => 0,
						'combined_sess'   => 0,
						'best_position'   => 100.0,
						'total_conv'      => 0,
						'avg_bounce'      => [],
						'kw_breadth'      => 0,
						'property_ids'    => [],
					];
				}

				$all_pages[ $path ]['combined_clicks'] += (int) $page['clicks'];
				$all_pages[ $path ]['combined_impr']   += (int) $page['impressions'];
				$all_pages[ $path ]['combined_sess']   += $sessions;
				$all_pages[ $path ]['best_position']    = min( $all_pages[ $path ]['best_position'], (float) $page['position'] );
				$all_pages[ $path ]['total_conv']      += $conversions;
				$all_pages[ $path ]['avg_bounce'][]     = $bounce;
				$all_pages[ $path ]['kw_breadth']      += $kw_count;
				$all_pages[ $path ]['property_ids'][]   = $pid;
			}
		}

		foreach ( $all_pages as &$page ) {
			$bounces = $page['avg_bounce'];
			$page['avg_bounce'] = count( $bounces ) > 0 ? array_sum( $bounces ) / count( $bounces ) : 50.0;
		}
		unset( $page );

		$scored = $this->score_navigation_items( $all_pages );

		usort( $scored, fn( $a, $b ) => $b['nav_score'] <=> $a['nav_score'] );

		$header = array_slice( $scored, 0, 10 );
		$footer = array_slice( $scored, 10, 15 );

		$consolidate = [];
		$retire      = [];
		$path_groups = [];
		foreach ( $scored as $item ) {
			$prefix = '/' . ( explode( '/', trim( $item['page_path'], '/' ) )[0] ?? '' ) . '/';
			$path_groups[ $prefix ][] = $item;
		}
		foreach ( $path_groups as $prefix => $items ) {
			if ( count( $items ) > 3 ) {
				$low_traffic = array_filter( $items, fn( $i ) => $i['combined_clicks'] < 10 );
				foreach ( $low_traffic as $item ) {
					$retire[] = $item;
				}
				$mid_traffic = array_filter( $items, fn( $i ) => $i['combined_clicks'] >= 10 && $i['nav_score'] < 30 );
				foreach ( array_slice( $mid_traffic, 0, 5 ) as $item ) {
					$consolidate[] = $item;
				}
			}
		}

		return [
			'header'      => $header,
			'footer'      => $footer,
			'consolidate' => array_slice( $consolidate, 0, 10 ),
			'retire'      => array_slice( $retire, 0, 10 ),
			'all_scored'  => $scored,
		];
	}

	private function score_navigation_items( array $pages ): array {
		if ( empty( $pages ) ) {
			return [];
		}

		$max_clicks = max( array_column( $pages, 'combined_clicks' ) ) ?: 1;
		$max_sess   = max( array_column( $pages, 'combined_sess' ) ) ?: 1;
		$max_kw     = max( array_column( $pages, 'kw_breadth' ) ) ?: 1;
		$max_conv   = max( array_column( $pages, 'total_conv' ) ) ?: 1;

		$scored = [];
		foreach ( $pages as $page ) {
			$click_norm  = $page['combined_clicks'] / $max_clicks;
			$sess_norm   = $page['combined_sess'] / $max_sess;
			$engage_norm = 1.0 - ( $page['avg_bounce'] / 100.0 );
			$kw_norm     = $page['kw_breadth'] / $max_kw;
			$conv_norm   = $page['total_conv'] / $max_conv;

			$score = (
				$click_norm  * 0.30 +
				$sess_norm   * 0.20 +
				$engage_norm * 0.20 +
				$kw_norm     * 0.15 +
				$conv_norm   * 0.15
			) * 100;

			$page['nav_score'] = round( $score, 1 );
			$scored[] = $page;
		}

		return $scored;
	}

	private function analyze_information_architecture(): array {
		global $wpdb;

		$all_keywords = [];
		foreach ( $this->property_ids as $pid ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$latest = $wpdb->get_var( $wpdb->prepare(
				"SELECT MAX(snapshot_date) FROM {$wpdb->prefix}sf_keywords
				WHERE property_id = %d AND source = 'gsc'",
				$pid
			) );
			if ( ! $latest ) {
				continue;
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$rows = $wpdb->get_results( $wpdb->prepare(
				"SELECT page_path, query, clicks, impressions, position
				FROM {$wpdb->prefix}sf_keywords
				WHERE property_id = %d AND source = 'gsc' AND snapshot_date = %s
				ORDER BY clicks DESC
				LIMIT 2000",
				$pid,
				$latest
			), ARRAY_A );

			foreach ( $rows as $row ) {
				$all_keywords[] = [
					'property_id' => $pid,
					'page_path'   => $row['page_path'],
					'query'       => $row['query'],
					'clicks'      => (int) $row['clicks'],
					'impressions' => (int) $row['impressions'],
					'position'    => (float) $row['position'],
				];
			}
		}

		$page_topics = [];
		foreach ( $all_keywords as $kw ) {
			$path = $kw['page_path'];
			if ( ! isset( $page_topics[ $path ] ) ) {
				$page_topics[ $path ] = [];
			}
			$page_topics[ $path ][] = $kw['query'];
		}

		$silos = [];
		$prefix_pages = [];
		foreach ( array_keys( $page_topics ) as $path ) {
			$parts = array_filter( explode( '/', trim( $path, '/' ) ) );
			$prefix = count( $parts ) >= 1 ? '/' . $parts[0] . '/' : '/';
			$prefix_pages[ $prefix ][] = $path;
		}

		foreach ( $prefix_pages as $prefix => $paths ) {
			$kw_set = [];
			$total_clicks = 0;
			foreach ( $paths as $path ) {
				foreach ( $all_keywords as $kw ) {
					if ( $kw['page_path'] === $path ) {
						$kw_set[ $kw['query'] ] = ( $kw_set[ $kw['query'] ] ?? 0 ) + $kw['clicks'];
						$total_clicks += $kw['clicks'];
					}
				}
			}
			arsort( $kw_set );

			$silos[] = [
				'prefix'        => $prefix,
				'pages'         => count( $paths ),
				'total_clicks'  => $total_clicks,
				'top_keywords'  => array_slice( array_keys( $kw_set ), 0, 5 ),
				'sample_pages'  => array_slice( $paths, 0, 5 ),
			];
		}

		usort( $silos, fn( $a, $b ) => $b['total_clicks'] <=> $a['total_clicks'] );

		$redirects = $this->build_redirect_map( $all_keywords );

		$all_paths_with_traffic = [];
		foreach ( $all_keywords as $kw ) {
			$all_paths_with_traffic[ $kw['page_path'] ] = ( $all_paths_with_traffic[ $kw['page_path'] ] ?? 0 ) + $kw['clicks'];
		}
		$orphaned = array_filter( $all_paths_with_traffic, fn( $clicks ) => $clicks === 0 );

		return [
			'silos'     => array_slice( $silos, 0, 15 ),
			'redirects' => array_slice( $redirects, 0, 20 ),
			'orphaned'  => array_slice( array_keys( $orphaned ), 0, 10 ),
		];
	}

	private function build_redirect_map( array $all_keywords ): array {
		$page_traffic = [];
		foreach ( $all_keywords as $kw ) {
			$path = $kw['page_path'];
			$page_traffic[ $path ] = ( $page_traffic[ $path ] ?? 0 ) + $kw['clicks'];
		}

		$page_queries = [];
		foreach ( $all_keywords as $kw ) {
			$page_queries[ $kw['page_path'] ][] = $kw['query'];
		}

		$redirects = [];
		$seen = [];

		foreach ( $page_queries as $path_a => $queries_a ) {
			foreach ( $page_queries as $path_b => $queries_b ) {
				if ( $path_a === $path_b ) {
					continue;
				}
				$key = $path_a < $path_b ? "{$path_a}|{$path_b}" : "{$path_b}|{$path_a}";
				if ( isset( $seen[ $key ] ) ) {
					continue;
				}
				$seen[ $key ] = true;

				$overlap = array_intersect( $queries_a, $queries_b );
				$overlap_pct = count( $overlap ) / max( count( $queries_a ), count( $queries_b ), 1 );

				if ( $overlap_pct >= 0.5 ) {
					$traffic_a = $page_traffic[ $path_a ] ?? 0;
					$traffic_b = $page_traffic[ $path_b ] ?? 0;

					if ( $traffic_a >= $traffic_b ) {
						$redirects[] = [
							'from'        => $path_b,
							'to'          => $path_a,
							'overlap_pct' => round( $overlap_pct * 100, 1 ),
							'shared_kws'  => count( $overlap ),
						];
					} else {
						$redirects[] = [
							'from'        => $path_a,
							'to'          => $path_b,
							'overlap_pct' => round( $overlap_pct * 100, 1 ),
							'shared_kws'  => count( $overlap ),
						];
					}
				}
			}
		}

		usort( $redirects, fn( $a, $b ) => $b['shared_kws'] <=> $a['shared_kws'] );

		return $redirects;
	}

	private function analyze_user_funnels(): array {
		global $wpdb;

		$entry_points   = [];
		$dropoff_points = [];
		$conversion_corridors = [];
		$engagement_paths     = [];

		foreach ( $this->property_ids as $pid ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$latest = $wpdb->get_var( $wpdb->prepare(
				"SELECT MAX(snapshot_date) FROM {$wpdb->prefix}sf_ga4_metrics
				WHERE property_id = %d",
				$pid
			) );
			if ( ! $latest ) {
				continue;
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$rows = $wpdb->get_results( $wpdb->prepare(
				"SELECT page_path, sessions, bounce_rate, avg_session_dur,
					conversions, organic_sessions, organic_bounce, organic_conversions,
					pageviews, engaged_sessions
				FROM {$wpdb->prefix}sf_ga4_metrics
				WHERE property_id = %d AND snapshot_date = %s
				ORDER BY sessions DESC",
				$pid,
				$latest
			), ARRAY_A );

			foreach ( $rows as $row ) {
				$path = $row['page_path'];
				$organic = (int) $row['organic_sessions'];
				$bounce  = (float) $row['bounce_rate'];
				$dur     = (float) $row['avg_session_dur'];
				$conv    = (int) $row['conversions'];
				$sess    = (int) $row['sessions'];

				if ( $organic > 20 ) {
					$entry_points[] = [
						'page_path'        => $path,
						'property_id'      => $pid,
						'organic_sessions' => $organic,
						'bounce_rate'      => $bounce,
						'conversions'      => $conv,
					];
				}

				if ( $sess > 10 && $bounce > 70 ) {
					$dropoff_points[] = [
						'page_path'    => $path,
						'property_id'  => $pid,
						'sessions'     => $sess,
						'bounce_rate'  => $bounce,
					];
				}

				if ( $conv > 0 ) {
					$conversion_corridors[] = [
						'page_path'   => $path,
						'property_id' => $pid,
						'conversions' => $conv,
						'sessions'    => $sess,
						'conv_rate'   => $sess > 0 ? round( $conv / $sess * 100, 2 ) : 0,
					];
				}

				if ( $bounce < 40 && $dur > 60 ) {
					$engagement_paths[] = [
						'page_path'   => $path,
						'property_id' => $pid,
						'sessions'    => $sess,
						'bounce_rate' => $bounce,
						'avg_dur'     => round( $dur ),
					];
				}
			}
		}

		usort( $entry_points, fn( $a, $b ) => $b['organic_sessions'] <=> $a['organic_sessions'] );
		usort( $dropoff_points, fn( $a, $b ) => $b['bounce_rate'] <=> $a['bounce_rate'] );
		usort( $conversion_corridors, fn( $a, $b ) => $b['conversions'] <=> $a['conversions'] );
		usort( $engagement_paths, fn( $a, $b ) => $b['sessions'] <=> $a['sessions'] );

		return [
			'entry_points'         => array_slice( $entry_points, 0, 15 ),
			'dropoff_points'       => array_slice( $dropoff_points, 0, 10 ),
			'conversion_corridors' => array_slice( $conversion_corridors, 0, 10 ),
			'engagement_paths'     => array_slice( $engagement_paths, 0, 10 ),
		];
	}

	private function detect_cross_property_cannibalization(): array {
		global $wpdb;

		$kw_map = [];

		foreach ( $this->property_ids as $pid ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$latest = $wpdb->get_var( $wpdb->prepare(
				"SELECT MAX(snapshot_date) FROM {$wpdb->prefix}sf_keywords
				WHERE property_id = %d AND source = 'gsc'",
				$pid
			) );
			if ( ! $latest ) {
				continue;
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$rows = $wpdb->get_results( $wpdb->prepare(
				"SELECT query, page_path, clicks, impressions, position
				FROM {$wpdb->prefix}sf_keywords
				WHERE property_id = %d AND source = 'gsc' AND snapshot_date = %s
				ORDER BY clicks DESC
				LIMIT 1000",
				$pid,
				$latest
			), ARRAY_A );

			foreach ( $rows as $row ) {
				$q = mb_strtolower( $row['query'] );
				if ( ! isset( $kw_map[ $q ] ) ) {
					$kw_map[ $q ] = [];
				}
				$kw_map[ $q ][] = [
					'property_id' => $pid,
					'page_path'   => $row['page_path'],
					'clicks'      => (int) $row['clicks'],
					'impressions' => (int) $row['impressions'],
					'position'    => (float) $row['position'],
				];
			}
		}

		$conflicts = [];
		foreach ( $kw_map as $query => $entries ) {
			$pids = array_unique( array_column( $entries, 'property_id' ) );
			if ( count( $pids ) < 2 ) {
				continue;
			}

			$total_clicks = array_sum( array_column( $entries, 'clicks' ) );
			$total_impr   = array_sum( array_column( $entries, 'impressions' ) );

			$conflicts[] = [
				'query'             => $query,
				'property_count'    => count( $pids ),
				'entries'           => $entries,
				'total_clicks'      => $total_clicks,
				'total_impressions' => $total_impr,
			];
		}

		usort( $conflicts, fn( $a, $b ) => $b['total_impressions'] <=> $a['total_impressions'] );

		return array_slice( $conflicts, 0, 30 );
	}

	private function get_ga4_lookup( int $pid, string $date ): array {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT page_path, sessions, bounce_rate, avg_session_dur, conversions,
				organic_sessions, organic_bounce, organic_conversions
			FROM {$wpdb->prefix}sf_ga4_metrics
			WHERE property_id = %d AND snapshot_date = %s",
			$pid,
			$date
		), ARRAY_A );

		$lookup = [];
		foreach ( $rows as $row ) {
			$lookup[ $row['page_path'] ] = $row;
		}
		return $lookup;
	}

	private function render_executive_summary( array $summaries, array $nav, array $cannibal ): string {
		$total_clicks = array_sum( array_column( $summaries, 'total_clicks' ) );
		$total_impr   = array_sum( array_column( $summaries, 'total_impressions' ) );
		$header_count = count( $nav['header'] );
		$cannibal_count = count( $cannibal );

		$md  = "## Executive Summary\n\n";
		$md .= "- **Combined traffic:** " . number_format( $total_clicks ) . " clicks, " . number_format( $total_impr ) . " impressions across " . count( $this->properties ) . " properties\n";
		$md .= "- **Recommended header items:** {$header_count} (traffic-weighted, max 10)\n";
		$md .= "- **Cross-property keyword conflicts:** {$cannibal_count} keywords compete across properties\n";
		$md .= "- **Pages to consolidate:** " . count( $nav['consolidate'] ) . " | **Pages to retire:** " . count( $nav['retire'] ) . "\n";
		$md .= "\n";

		return $md;
	}

	private function render_url_patterns( array $patterns ): string {
		$md = "## URL Pattern Analysis\n\n";

		foreach ( $patterns as $pid => $data ) {
			$label = $this->properties[ $pid ]['label'] ?? "Property {$pid}";
			$md .= "### {$label}\n\n";
			$md .= "- Total pages: {$data['total_pages']}\n";
			$md .= "- Max URL depth: {$data['max_depth']}\n";
			$md .= "- Top subfolder prefixes:\n\n";

			$md .= "| Prefix | Pages |\n|--------|-------|\n";
			foreach ( array_slice( $data['prefixes'], 0, 10 ) as $prefix => $count ) {
				$md .= "| `{$prefix}` | {$count} |\n";
			}
			$md .= "\n";
		}

		return $md;
	}

	private function render_navigation_recommendations( array $nav ): string {
		$md = "## Navigation Merger Recommendations\n\n";

		$md .= "### Recommended Primary Navigation (Header)\n\n";
		$md .= "Traffic-weighted ranking for limited header space (max 8-10 items).\n\n";
		$md .= "| # | Page | Score | Clicks | Sessions | Bounce | Keywords | Properties |\n";
		$md .= "|---|------|-------|--------|----------|--------|----------|------------|\n";

		foreach ( $nav['header'] as $i => $item ) {
			$n    = $i + 1;
			$path = $item['page_path'];
			$sc   = $item['nav_score'];
			$cl   = number_format( $item['combined_clicks'] );
			$se   = number_format( $item['combined_sess'] );
			$bo   = round( $item['avg_bounce'], 1 ) . '%';
			$kw   = $item['kw_breadth'];
			$pids = implode( ', ', $item['property_ids'] );
			$md  .= "| {$n} | `{$path}` | {$sc} | {$cl} | {$se} | {$bo} | {$kw} | {$pids} |\n";
		}

		$md .= "\n### Recommended Footer Navigation\n\n";
		$md .= "| Page | Score | Clicks | Keywords |\n";
		$md .= "|------|-------|--------|----------|\n";

		foreach ( $nav['footer'] as $item ) {
			$path = $item['page_path'];
			$sc   = $item['nav_score'];
			$cl   = number_format( $item['combined_clicks'] );
			$kw   = $item['kw_breadth'];
			$md  .= "| `{$path}` | {$sc} | {$cl} | {$kw} |\n";
		}

		if ( ! empty( $nav['consolidate'] ) ) {
			$md .= "\n### Navigation Items to Consolidate\n\n";
			$md .= "These pages have moderate traffic but low navigation priority. Consider merging into parent pages.\n\n";
			foreach ( $nav['consolidate'] as $item ) {
				$md .= "- `{$item['page_path']}` (score: {$item['nav_score']}, clicks: " . number_format( $item['combined_clicks'] ) . ")\n";
			}
		}

		if ( ! empty( $nav['retire'] ) ) {
			$md .= "\n### Navigation Items to Retire\n\n";
			$md .= "Low-traffic pages that can be removed from navigation or redirected.\n\n";
			foreach ( $nav['retire'] as $item ) {
				$md .= "- `{$item['page_path']}` (clicks: " . number_format( $item['combined_clicks'] ) . ")\n";
			}
		}

		$md .= "\n";
		return $md;
	}

	private function render_ia_restructuring( array $ia ): string {
		$md = "## Information Architecture Restructuring\n\n";

		$md .= "### Proposed Content Silos\n\n";
		$md .= "Silos derived from URL hierarchy and keyword clustering.\n\n";

		foreach ( $ia['silos'] as $silo ) {
			$md .= "**`{$silo['prefix']}`** - {$silo['pages']} pages, " . number_format( $silo['total_clicks'] ) . " clicks\n";
			if ( ! empty( $silo['top_keywords'] ) ) {
				$md .= "- Top keywords: " . implode( ', ', $silo['top_keywords'] ) . "\n";
			}
			if ( ! empty( $silo['sample_pages'] ) ) {
				$md .= "- Sample pages: " . implode( ', ', array_map( fn( $p ) => "`{$p}`", array_slice( $silo['sample_pages'], 0, 3 ) ) ) . "\n";
			}
			$md .= "\n";
		}

		if ( ! empty( $ia['redirects'] ) ) {
			$md .= "### Redirect Map (301s for Consolidation)\n\n";
			$md .= "Pages with high keyword overlap that should be consolidated.\n\n";
			$md .= "| From | To | Overlap | Shared Keywords |\n";
			$md .= "|------|----|---------|-----------------|\n";
			foreach ( $ia['redirects'] as $r ) {
				$md .= "| `{$r['from']}` | `{$r['to']}` | {$r['overlap_pct']}% | {$r['shared_kws']} |\n";
			}
			$md .= "\n";
		}

		if ( ! empty( $ia['orphaned'] ) ) {
			$md .= "### Orphaned Content\n\n";
			$md .= "Pages with zero keyword clicks. Review for removal or improvement.\n\n";
			foreach ( $ia['orphaned'] as $path ) {
				$md .= "- `{$path}`\n";
			}
			$md .= "\n";
		}

		return $md;
	}

	private function render_funnel_analysis( array $funnel ): string {
		$md = "## User Funnel Optimization\n\n";

		if ( ! empty( $funnel['entry_points'] ) ) {
			$md .= "### Entry Points (Keep + Optimize)\n\n";
			$md .= "High organic traffic pages. Protect these during migration.\n\n";
			$md .= "| Page | Organic Sessions | Bounce Rate | Conversions | Property |\n";
			$md .= "|------|-----------------|-------------|-------------|----------|\n";
			foreach ( $funnel['entry_points'] as $ep ) {
				$label = $this->properties[ $ep['property_id'] ]['label'] ?? $ep['property_id'];
				$md .= "| `{$ep['page_path']}` | " . number_format( $ep['organic_sessions'] ) . " | {$ep['bounce_rate']}% | {$ep['conversions']} | {$label} |\n";
			}
			$md .= "\n";
		}

		if ( ! empty( $funnel['dropoff_points'] ) ) {
			$md .= "### Drop-off Points (Fix or Redirect)\n\n";
			$md .= "High-traffic pages with excessive bounce rates.\n\n";
			$md .= "| Page | Sessions | Bounce Rate | Property |\n";
			$md .= "|------|----------|-------------|----------|\n";
			foreach ( $funnel['dropoff_points'] as $dp ) {
				$label = $this->properties[ $dp['property_id'] ]['label'] ?? $dp['property_id'];
				$md .= "| `{$dp['page_path']}` | " . number_format( $dp['sessions'] ) . " | {$dp['bounce_rate']}% | {$label} |\n";
			}
			$md .= "\n";
		}

		if ( ! empty( $funnel['conversion_corridors'] ) ) {
			$md .= "### Conversion Corridors (Protect During Migration)\n\n";
			$md .= "Pages generating conversions. These must survive the migration intact.\n\n";
			$md .= "| Page | Conversions | Conv Rate | Sessions | Property |\n";
			$md .= "|------|-------------|-----------|----------|----------|\n";
			foreach ( $funnel['conversion_corridors'] as $cc ) {
				$label = $this->properties[ $cc['property_id'] ]['label'] ?? $cc['property_id'];
				$md .= "| `{$cc['page_path']}` | {$cc['conversions']} | {$cc['conv_rate']}% | " . number_format( $cc['sessions'] ) . " | {$label} |\n";
			}
			$md .= "\n";
		}

		if ( ! empty( $funnel['engagement_paths'] ) ) {
			$md .= "### Recommended User Flows (Internal Linking)\n\n";
			$md .= "High-engagement pages (low bounce, long duration). Build internal links through these.\n\n";
			foreach ( $funnel['engagement_paths'] as $ep ) {
				$label = $this->properties[ $ep['property_id'] ]['label'] ?? $ep['property_id'];
				$md .= "- `{$ep['page_path']}` - {$ep['sessions']} sessions, {$ep['bounce_rate']}% bounce, {$ep['avg_dur']}s avg duration ({$label})\n";
			}
			$md .= "\n";
		}

		if ( empty( $funnel['entry_points'] ) && empty( $funnel['dropoff_points'] ) && empty( $funnel['conversion_corridors'] ) ) {
			$md .= "*No GA4 data available. Connect GA4 for user funnel analysis.*\n\n";
		}

		return $md;
	}

	private function render_current_navigation(): string {
		$md = "## Current Navigation Inventory\n\n";
		$md .= "Navigation items uploaded from existing sites. Compare these against the traffic-weighted recommendations below.\n\n";

		$total_header = 0;
		$total_footer = 0;

		foreach ( $this->nav_uploads as $upload ) {
			$domain = $upload['domain'] ?? 'Unknown';
			$items  = $upload['items'] ?? [];

			$header_items = array_filter( $items, fn( $i ) => strtolower( $i['location'] ) === 'header' );
			$footer_items = array_filter( $items, fn( $i ) => strtolower( $i['location'] ) !== 'header' );
			$total_header += count( $header_items );
			$total_footer += count( $footer_items );

			$md .= "### {$domain}\n\n";

			if ( ! empty( $header_items ) ) {
				$md .= "**Header Navigation** (" . count( $header_items ) . " items)\n\n";
				$md .= "| Label | URL |\n|-------|-----|\n";
				foreach ( $header_items as $item ) {
					$md .= "| {$item['label']} | `{$item['url']}` |\n";
				}
				$md .= "\n";
			}

			if ( ! empty( $footer_items ) ) {
				$md .= "**Footer Navigation** (" . count( $footer_items ) . " items)\n\n";
				$md .= "| Label | URL |\n|-------|-----|\n";
				foreach ( $footer_items as $item ) {
					$md .= "| {$item['label']} | `{$item['url']}` |\n";
				}
				$md .= "\n";
			}
		}

		$md .= "**Totals:** {$total_header} header items, {$total_footer} footer items across " . count( $this->nav_uploads ) . " domains/subfolders.\n\n";

		$all_urls = [];
		foreach ( $this->nav_uploads as $upload ) {
			foreach ( $upload['items'] ?? [] as $item ) {
				$url = $item['url'] ?? '';
				$path = wp_parse_url( $url, PHP_URL_PATH ) ?: $url;
				$all_urls[ $path ] = ( $all_urls[ $path ] ?? 0 ) + 1;
			}
		}
		$duplicates = array_filter( $all_urls, fn( $count ) => $count > 1 );
		if ( ! empty( $duplicates ) ) {
			arsort( $duplicates );
			$md .= "**Shared across multiple domains:**\n\n";
			foreach ( $duplicates as $path => $count ) {
				$md .= "- `{$path}` (appears in {$count} domains)\n";
			}
			$md .= "\n";
		}

		return $md;
	}

	private function render_keyword_strategy( array $cannibal ): string {
		$md = "## Keyword Strategy\n\n";

		if ( ! empty( $cannibal ) ) {
			$md .= "### Cross-Property Cannibalization\n\n";
			$md .= "Keywords where multiple properties compete. Consolidate to one page post-merger.\n\n";
			$md .= "| Keyword | Properties | Total Clicks | Total Impressions |\n";
			$md .= "|---------|------------|-------------|-------------------|\n";
			foreach ( array_slice( $cannibal, 0, 20 ) as $item ) {
				$pids = implode( ', ', array_unique( array_column( $item['entries'], 'property_id' ) ) );
				$md .= "| {$item['query']} | {$pids} | " . number_format( $item['total_clicks'] ) . " | " . number_format( $item['total_impressions'] ) . " |\n";
			}
			$md .= "\n";

			$md .= "### Keyword Consolidation Opportunities\n\n";
			$consolidation = [];
			foreach ( $cannibal as $item ) {
				$best = null;
				$best_clicks = -1;
				foreach ( $item['entries'] as $entry ) {
					if ( $entry['clicks'] > $best_clicks ) {
						$best_clicks = $entry['clicks'];
						$best = $entry;
					}
				}
				if ( $best ) {
					$others = array_filter( $item['entries'], fn( $e ) => $e['page_path'] !== $best['page_path'] || $e['property_id'] !== $best['property_id'] );
					if ( ! empty( $others ) ) {
						$consolidation[] = [
							'query'   => $item['query'],
							'winner'  => $best['page_path'],
							'winner_pid' => $best['property_id'],
							'losers'  => array_map( fn( $e ) => $e['page_path'] . " (prop {$e['property_id']})", $others ),
						];
					}
				}
			}

			foreach ( array_slice( $consolidation, 0, 10 ) as $c ) {
				$md .= "- **\"{$c['query']}\"**: Keep `{$c['winner']}` (prop {$c['winner_pid']}), redirect: " . implode( ', ', array_map( fn( $l ) => "`{$l}`", $c['losers'] ) ) . "\n";
			}
			$md .= "\n";
		}

		$md .= "### Keyword Gaps Post-Merger\n\n";
		$md .= "After consolidation, review these areas:\n\n";
		$md .= "1. Keywords unique to each property that must be preserved with redirects\n";
		$md .= "2. Long-tail variations that can be consolidated into comprehensive pillar pages\n";
		$md .= "3. Branded terms that need updating to reflect the merged entity\n";
		$md .= "\n";

		return $md;
	}
}
