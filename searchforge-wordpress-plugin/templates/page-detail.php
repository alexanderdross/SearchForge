<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
defined( 'ABSPATH' ) || exit;

$page_path = sanitize_text_field( wp_unslash( $_GET['path'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
if ( empty( $page_path ) ) {
	echo '<div class="wrap"><div class="notice notice-error"><p>' . esc_html__( 'No page path specified.', 'searchforge-wordpress-plugin' ) . '</p></div></div>';
	return;
}

$property_id = SearchForge\Models\Property::get_active_property_id();

$page_data   = SearchForge\Admin\PageDetail::get_page_data( $page_path, $property_id );
if ( ! $page_data ) {
	echo '<div class="wrap"><div class="notice notice-warning"><p>' . esc_html__( 'No data found for this page. Run a GSC sync first.', 'searchforge-wordpress-plugin' ) . '</p></div></div>';
	return;
}

$keywords     = SearchForge\Admin\PageDetail::get_page_keywords( $page_path, 0, $property_id );
$devices      = SearchForge\Admin\PageDetail::get_device_breakdown( $page_path, $property_id );
$daily_trend  = SearchForge\Admin\PageDetail::get_daily_trend( $page_path, 30, $property_id );
$bing_data    = SearchForge\Admin\PageDetail::get_bing_data( $page_path, $property_id );
$ga4_data     = SearchForge\Admin\PageDetail::get_ga4_data( $page_path, $property_id );
$pos_dist     = SearchForge\Admin\PageDetail::get_position_distribution( $page_path, $property_id );
$is_pro       = SearchForge\Admin\Settings::is_pro();
$score        = SearchForge\Scoring\Score::calculate_page_score( $page_path, $property_id );
$trend        = $is_pro ? SearchForge\Trends\Engine::get_page_trend( $page_path, 'gsc', $property_id ) : null;
$yoy          = $is_pro ? SearchForge\Trends\Engine::get_yoy_comparison( $page_path, 'gsc', $property_id ) : null;
$cannibal     = $is_pro ? SearchForge\Admin\PageDetail::get_page_cannibalization( $page_path, $property_id ) : [];
?>

<div class="wrap searchforge-wrap sf-page-detail">
	<h1>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=searchforge-pages' ) ); ?>" class="sf-back-link">
			&larr; <?php esc_html_e( 'Pages', 'searchforge-wordpress-plugin' ); ?>
		</a>
		<?php echo esc_html( $page_path ); ?>
		<a href="<?php echo esc_url( home_url( $page_path ) ); ?>" target="_blank" class="sf-external-link" aria-label="<?php esc_attr_e( 'View page in new tab', 'searchforge-wordpress-plugin' ); ?>">&#8599;</a>
	</h1>

	<?php include SEARCHFORGE_PATH . 'templates/partials/property-selector.php'; ?>

	<p class="sf-page-meta">
		<?php
		echo esc_html( sprintf(
			/* translators: %s: snapshot date */
			__( 'Data from %s', 'searchforge-wordpress-plugin' ),
			wp_date( get_option( 'date_format' ), strtotime( $page_data['snapshot_date'] ) )
		) ); ?>
	</p>

	<!-- Metric Cards -->
	<div class="sf-cards">
		<div class="sf-card">
			<h3><?php esc_html_e( 'Clicks', 'searchforge-wordpress-plugin' ); ?></h3>
			<span class="sf-card-value"><?php echo esc_html( number_format( $page_data['clicks'] ) ); ?></span>
		</div>
		<div class="sf-card">
			<h3><?php esc_html_e( 'Impressions', 'searchforge-wordpress-plugin' ); ?></h3>
			<span class="sf-card-value"><?php echo esc_html( number_format( $page_data['impressions'] ) ); ?></span>
		</div>
		<div class="sf-card">
			<h3><?php esc_html_e( 'CTR', 'searchforge-wordpress-plugin' ); ?></h3>
			<span class="sf-card-value"><?php echo esc_html( round( $page_data['ctr'] * 100, 1 ) ); ?>%</span>
		</div>
		<div class="sf-card">
			<h3><?php esc_html_e( 'Position', 'searchforge-wordpress-plugin' ); ?></h3>
			<span class="sf-card-value"><?php echo esc_html( round( $page_data['position'], 1 ) ); ?></span>
		</div>
		<div class="sf-card">
			<h3><?php esc_html_e( 'Keywords', 'searchforge-wordpress-plugin' ); ?></h3>
			<span class="sf-card-value"><?php echo esc_html( count( $keywords ) ); ?></span>
		</div>
		<?php if ( $score ) : ?>
			<div class="sf-card sf-card-score">
				<h3><?php esc_html_e( 'Score', 'searchforge-wordpress-plugin' ); ?></h3>
				<span class="sf-card-value sf-score-<?php echo $score['total'] >= 70 ? 'good' : ( $score['total'] >= 40 ? 'ok' : 'low' ); ?>">
					<?php echo esc_html( $score['total'] ); ?>/100
				</span>
			</div>
		<?php endif; ?>
	</div>

	<!-- Action Buttons -->
	<div class="sf-detail-actions">
		<button class="button sf-export-btn" data-page="<?php echo esc_attr( $page_path ); ?>">
			<?php esc_html_e( 'Export Brief', 'searchforge-wordpress-plugin' ); ?>
		</button>
		<?php if ( $is_pro ) : ?>
			<button class="button sf-ai-brief-btn" data-page="<?php echo esc_attr( $page_path ); ?>">
				<?php esc_html_e( 'AI Content Brief', 'searchforge-wordpress-plugin' ); ?>
			</button>
		<?php endif; ?>
	</div>

	<!-- Tabs -->
	<nav class="nav-tab-wrapper" role="tablist">
		<a href="#sf-tab-overview" class="nav-tab nav-tab-active" data-tab="sf-tab-overview"
			role="tab" aria-selected="true" aria-controls="sf-tab-overview" id="sf-tab-overview-tab">
			<?php esc_html_e( 'Overview', 'searchforge-wordpress-plugin' ); ?>
		</a>
		<a href="#sf-tab-keywords" class="nav-tab" data-tab="sf-tab-keywords"
			role="tab" aria-selected="false" aria-controls="sf-tab-keywords" id="sf-tab-keywords-tab">
			<?php esc_html_e( 'Keywords', 'searchforge-wordpress-plugin' ); ?>
			<span class="sf-tab-count">(<?php echo esc_html( count( $keywords ) ); ?>)</span>
		</a>
		<a href="#sf-tab-trends" class="nav-tab" data-tab="sf-tab-trends"
			role="tab" aria-selected="false" aria-controls="sf-tab-trends" id="sf-tab-trends-tab">
			<?php esc_html_e( 'Trends', 'searchforge-wordpress-plugin' ); ?>
		</a>
		<?php if ( $score && $is_pro ) : ?>
			<a href="#sf-tab-score" class="nav-tab" data-tab="sf-tab-score"
				role="tab" aria-selected="false" aria-controls="sf-tab-score" id="sf-tab-score-tab">
				<?php esc_html_e( 'Score', 'searchforge-wordpress-plugin' ); ?>
			</a>
		<?php endif; ?>
	</nav>

	<!-- Tab: Overview -->
	<div id="sf-tab-overview" class="sf-tab-panel sf-tab-active" role="tabpanel" aria-labelledby="sf-tab-overview-tab" tabindex="0">

		<!-- Trend Chart -->
		<?php if ( ! empty( $daily_trend ) ) : ?>
			<div class="sf-chart-container" aria-label="<?php esc_attr_e( '30-day trend chart showing clicks and impressions over time', 'searchforge-wordpress-plugin' ); ?>">
				<h2><?php esc_html_e( '30-Day Trend', 'searchforge-wordpress-plugin' ); ?></h2>
				<canvas id="sf-trend-chart" height="280"></canvas>
				<span class="screen-reader-text">
					<?php
					echo esc_html( sprintf(
						/* translators: %1$s: page path, %2$s: current clicks, %3$s: current impressions, %4$s: current position */
						__( 'Line chart displaying the 30-day trend for page %1$s. Current clicks: %2$s, impressions: %3$s, position: %4$s.', 'searchforge-wordpress-plugin' ),
						$page_path,
						number_format( $page_data['clicks'] ),
						number_format( $page_data['impressions'] ),
						round( $page_data['position'], 1 )
					) ); ?>
				</span>
			</div>
		<?php endif; ?>

		<!-- Device Breakdown -->
		<?php if ( ! empty( $devices ) ) : ?>
			<div class="sf-section">
				<h2><?php esc_html_e( 'Device Breakdown', 'searchforge-wordpress-plugin' ); ?></h2>
				<div class="sf-cards sf-device-cards">
					<?php foreach ( $devices as $dev ) : ?>
						<div class="sf-card">
							<h3><?php echo esc_html( ucfirst( $dev['device'] ) ); ?></h3>
							<span class="sf-card-value"><?php echo esc_html( number_format( (int) $dev['clicks'] ) ); ?></span>
							<span class="sf-card-sub"><?php echo esc_html( number_format( (int) $dev['impressions'] ) ); ?> impr</span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<!-- Cross-Engine Comparison -->
		<?php if ( $bing_data ) : ?>
			<div class="sf-section">
				<h2><?php esc_html_e( 'Google vs Bing', 'searchforge-wordpress-plugin' ); ?></h2>
				<table class="widefat sf-table">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Engine', 'searchforge-wordpress-plugin' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Clicks', 'searchforge-wordpress-plugin' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Impressions', 'searchforge-wordpress-plugin' ); ?></th>
							<th scope="col"><?php esc_html_e( 'CTR', 'searchforge-wordpress-plugin' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Position', 'searchforge-wordpress-plugin' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><strong>Google</strong></td>
							<td><?php echo esc_html( number_format( $page_data['clicks'] ) ); ?></td>
							<td><?php echo esc_html( number_format( $page_data['impressions'] ) ); ?></td>
							<td><?php echo esc_html( round( $page_data['ctr'] * 100, 1 ) ); ?>%</td>
							<td><?php echo esc_html( round( $page_data['position'], 1 ) ); ?></td>
						</tr>
						<tr>
							<td><strong>Bing</strong></td>
							<td><?php echo esc_html( number_format( (int) $bing_data['clicks'] ) ); ?></td>
							<td><?php echo esc_html( number_format( (int) $bing_data['impressions'] ) ); ?></td>
							<td><?php echo esc_html( round( (float) $bing_data['ctr'] * 100, 1 ) ); ?>%</td>
							<td><?php echo esc_html( round( (float) $bing_data['position'], 1 ) ); ?></td>
						</tr>
					</tbody>
				</table>
			</div>
		<?php endif; ?>

		<!-- GA4 Behavior -->
		<?php if ( $ga4_data ) : ?>
			<div class="sf-section">
				<h2><?php esc_html_e( 'On-Page Behavior (GA4)', 'searchforge-wordpress-plugin' ); ?></h2>
				<div class="sf-cards">
					<div class="sf-card">
						<h3><?php esc_html_e( 'Sessions', 'searchforge-wordpress-plugin' ); ?></h3>
						<span class="sf-card-value"><?php echo esc_html( number_format( (int) $ga4_data['sessions'] ) ); ?></span>
					</div>
					<div class="sf-card">
						<h3><?php esc_html_e( 'Bounce Rate', 'searchforge-wordpress-plugin' ); ?></h3>
						<span class="sf-card-value"><?php echo esc_html( round( (float) $ga4_data['bounce_rate'], 1 ) ); ?>%</span>
					</div>
					<div class="sf-card">
						<h3><?php esc_html_e( 'Avg Duration', 'searchforge-wordpress-plugin' ); ?></h3>
						<span class="sf-card-value"><?php echo esc_html( gmdate( 'i:s', (int) $ga4_data['avg_session_dur'] ) ); ?></span>
					</div>
					<div class="sf-card">
						<h3><?php esc_html_e( 'Conversions', 'searchforge-wordpress-plugin' ); ?></h3>
						<span class="sf-card-value"><?php echo esc_html( number_format( (int) $ga4_data['conversions'] ) ); ?></span>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<!-- Position Distribution Chart -->
		<?php if ( ! empty( $pos_dist ) && array_sum( $pos_dist ) > 0 ) : ?>
			<div class="sf-chart-container" aria-label="<?php esc_attr_e( 'Keyword position distribution chart', 'searchforge-wordpress-plugin' ); ?>">
				<h2><?php esc_html_e( 'Keyword Position Distribution', 'searchforge-wordpress-plugin' ); ?></h2>
				<canvas id="sf-position-chart" height="200"></canvas>
				<span class="screen-reader-text">
					<?php
					echo esc_html( sprintf(
						/* translators: %d: total number of keywords */
						__( 'Bar chart showing keyword position distribution: %d keywords total across position ranges.', 'searchforge-wordpress-plugin' ),
						array_sum( $pos_dist )
					) ); ?>
				</span>
			</div>
		<?php endif; ?>

		<!-- YoY Comparison -->
		<?php if ( $yoy ) : ?>
			<div class="sf-section">
				<h2><?php esc_html_e( 'Year-over-Year Comparison', 'searchforge-wordpress-plugin' ); ?></h2>
				<table class="widefat sf-table">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Metric', 'searchforge-wordpress-plugin' ); ?></th>
							<th scope="col"><?php esc_html_e( 'This Year', 'searchforge-wordpress-plugin' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Last Year', 'searchforge-wordpress-plugin' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Change', 'searchforge-wordpress-plugin' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><?php esc_html_e( 'Clicks', 'searchforge-wordpress-plugin' ); ?></td>
							<td><?php echo esc_html( number_format( (int) $yoy['current']['clicks'] ) ); ?></td>
							<td><?php echo esc_html( number_format( (int) $yoy['previous']['clicks'] ) ); ?></td>
							<td class="<?php echo $yoy['changes']['clicks'] >= 0 ? 'sf-change-up' : 'sf-change-down'; ?>">
								<?php echo esc_html( ( $yoy['changes']['clicks'] >= 0 ? '+' : '' ) . $yoy['changes']['clicks'] ); ?>%
							</td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Impressions', 'searchforge-wordpress-plugin' ); ?></td>
							<td><?php echo esc_html( number_format( (int) $yoy['current']['impressions'] ) ); ?></td>
							<td><?php echo esc_html( number_format( (int) $yoy['previous']['impressions'] ) ); ?></td>
							<td class="<?php echo $yoy['changes']['impressions'] >= 0 ? 'sf-change-up' : 'sf-change-down'; ?>">
								<?php echo esc_html( ( $yoy['changes']['impressions'] >= 0 ? '+' : '' ) . $yoy['changes']['impressions'] ); ?>%
							</td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Position', 'searchforge-wordpress-plugin' ); ?></td>
							<td><?php echo esc_html( round( (float) $yoy['current']['position'], 1 ) ); ?></td>
							<td><?php echo esc_html( round( (float) $yoy['previous']['position'], 1 ) ); ?></td>
							<td class="<?php echo $yoy['changes']['position'] >= 0 ? 'sf-change-up' : 'sf-change-down'; ?>">
								<?php echo esc_html( ( $yoy['changes']['position'] >= 0 ? '+' : '' ) . $yoy['changes']['position'] ); ?> pos
							</td>
						</tr>
					</tbody>
				</table>
				<p class="description"><?php echo esc_html( $yoy['period'] ); ?></p>
			</div>
		<?php endif; ?>
	</div>

	<!-- Tab: Keywords -->
	<div id="sf-tab-keywords" class="sf-tab-panel" role="tabpanel" aria-labelledby="sf-tab-keywords-tab" tabindex="0">
		<?php if ( empty( $keywords ) ) : ?>
			<p><?php esc_html_e( 'No keyword data for this page.', 'searchforge-wordpress-plugin' ); ?></p>
		<?php else : ?>
			<table class="widefat sf-table">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Keyword', 'searchforge-wordpress-plugin' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Clicks', 'searchforge-wordpress-plugin' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Impressions', 'searchforge-wordpress-plugin' ); ?></th>
						<th scope="col"><?php esc_html_e( 'CTR', 'searchforge-wordpress-plugin' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Position', 'searchforge-wordpress-plugin' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Status', 'searchforge-wordpress-plugin' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $keywords as $kw ) :
						$pos = (float) $kw['position'];
						if ( $pos <= 3 ) {
							$status_class = 'sf-pos-top3';
							$status_label = __( 'Top 3', 'searchforge-wordpress-plugin' );
						} elseif ( $pos <= 10 ) {
							$status_class = 'sf-pos-page1';
							$status_label = __( 'Page 1', 'searchforge-wordpress-plugin' );
						} elseif ( $pos <= 20 ) {
							$status_class = 'sf-pos-page2';
							$status_label = __( 'Page 2', 'searchforge-wordpress-plugin' );
						} else {
							$status_class = 'sf-pos-deep';
							$status_label = __( 'Deep', 'searchforge-wordpress-plugin' );
						}
					?>
						<tr>
							<td><?php echo esc_html( $kw['query'] ); ?></td>
							<td><?php echo esc_html( number_format( (int) $kw['clicks'] ) ); ?></td>
							<td><?php echo esc_html( number_format( (int) $kw['impressions'] ) ); ?></td>
							<td><?php echo esc_html( round( (float) $kw['ctr'] * 100, 1 ) ); ?>%</td>
							<td><?php echo esc_html( round( $pos, 1 ) ); ?></td>
							<td><span class="sf-pos-badge <?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $status_label ); ?></span></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<!-- Cannibalization Issues -->
		<?php if ( ! empty( $cannibal ) ) : ?>
			<div class="sf-section" style="margin-top: 20px;">
				<h2><?php esc_html_e( 'Keyword Cannibalization', 'searchforge-wordpress-plugin' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Keywords where this page competes with other pages on your site.', 'searchforge-wordpress-plugin' ); ?></p>
				<table class="widefat sf-table">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Keyword', 'searchforge-wordpress-plugin' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Your Pos.', 'searchforge-wordpress-plugin' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Your Clicks', 'searchforge-wordpress-plugin' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Competing Page', 'searchforge-wordpress-plugin' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Their Pos.', 'searchforge-wordpress-plugin' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Their Clicks', 'searchforge-wordpress-plugin' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $cannibal as $item ) : ?>
							<tr>
								<td><?php echo esc_html( $item['query'] ); ?></td>
								<td><?php echo esc_html( round( (float) $item['my_position'], 1 ) ); ?></td>
								<td><?php echo esc_html( number_format( (int) $item['my_clicks'] ) ); ?></td>
								<td>
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=searchforge-page-detail&path=' . urlencode( $item['competing_page'] ) ) ); ?>">
										<code><?php echo esc_html( $item['competing_page'] ); ?></code>
									</a>
								</td>
								<td><?php echo esc_html( round( (float) $item['their_position'], 1 ) ); ?></td>
								<td><?php echo esc_html( number_format( (int) $item['their_clicks'] ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</div>

	<!-- Tab: Trends -->
	<div id="sf-tab-trends" class="sf-tab-panel" role="tabpanel" aria-labelledby="sf-tab-trends-tab" tabindex="0">
		<?php if ( ! $is_pro ) : ?>
			<div class="notice notice-info">
				<p><?php esc_html_e( 'Trend analysis requires a Pro license.', 'searchforge-wordpress-plugin' ); ?></p>
			</div>
		<?php elseif ( $trend && ! empty( $trend['snapshots'] ) ) : ?>
			<div class="sf-chart-container" aria-label="<?php esc_attr_e( 'Weekly click trend chart', 'searchforge-wordpress-plugin' ); ?>">
				<h2><?php esc_html_e( 'Weekly Click Trend', 'searchforge-wordpress-plugin' ); ?></h2>
				<canvas id="sf-weekly-trend-chart" height="280"></canvas>
				<span class="screen-reader-text">
					<?php esc_html_e( 'Line chart displaying weekly click trends over time for this page.', 'searchforge-wordpress-plugin' ); ?>
				</span>
			</div>

			<?php if ( $trend['decay_detected'] ) : ?>
				<div class="notice notice-warning sf-decay-notice">
					<p>
						<strong><?php esc_html_e( 'Content Decay Detected', 'searchforge-wordpress-plugin' ); ?></strong> -
						<?php
						echo esc_html( sprintf(
							/* translators: %1$s: percentage of click decline, %2$d: number of days in decay period */
							__( 'Clicks declined %1$s%% over the last %2$d days.', 'searchforge-wordpress-plugin' ),
							$trend['decay_percentage'],
							$trend['decay_period_days']
						) ); ?>
					</p>
				</div>
			<?php endif; ?>

			<table class="widefat sf-table" style="margin-top: 16px;">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Week', 'searchforge-wordpress-plugin' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Clicks', 'searchforge-wordpress-plugin' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Change', 'searchforge-wordpress-plugin' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Impressions', 'searchforge-wordpress-plugin' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Position', 'searchforge-wordpress-plugin' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( array_reverse( $trend['snapshots'] ) as $snap ) : ?>
						<tr>
							<td><?php echo esc_html( wp_date( 'M j', strtotime( $snap['date'] ) ) ); ?></td>
							<td><?php echo esc_html( number_format( $snap['clicks'] ) ); ?></td>
							<td>
								<?php if ( isset( $snap['clicks_change'] ) ) : ?>
									<span class="<?php echo $snap['clicks_change'] >= 0 ? 'sf-change-up' : 'sf-change-down'; ?>">
										<?php echo esc_html( ( $snap['clicks_change'] >= 0 ? '+' : '' ) . $snap['clicks_change'] ); ?>%
									</span>
								<?php else : ?>
									-
								<?php endif; ?>
							</td>
							<td><?php echo esc_html( number_format( $snap['impressions'] ) ); ?></td>
							<td><?php echo esc_html( $snap['position'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php else : ?>
			<p><?php esc_html_e( 'Not enough historical data yet. Trend data requires at least 2 weeks of snapshots.', 'searchforge-wordpress-plugin' ); ?></p>
		<?php endif; ?>
	</div>

	<!-- Tab: Score Breakdown -->
	<?php if ( $score && $is_pro ) : ?>
		<div id="sf-tab-score" class="sf-tab-panel" role="tabpanel" aria-labelledby="sf-tab-score-tab" tabindex="0">
			<div class="sf-score-overview">
				<div class="sf-score-big sf-score-<?php echo $score['total'] >= 70 ? 'good' : ( $score['total'] >= 40 ? 'ok' : 'low' ); ?>">
					<?php echo esc_html( $score['total'] ); ?><span class="sf-score-max">/100</span>
				</div>
			</div>

			<div class="sf-score-components">
				<?php foreach ( $score['components'] as $name => $comp ) :
					$bar_class = $comp['score'] >= 70 ? 'sf-bar-good' : ( $comp['score'] >= 40 ? 'sf-bar-ok' : 'sf-bar-low' );
				?>
					<div class="sf-score-component">
						<div class="sf-score-component-header">
							<span class="sf-score-component-name"><?php echo esc_html( ucfirst( $name ) ); ?></span>
							<span class="sf-score-component-value"><?php echo esc_html( $comp['score'] ); ?>/100</span>
						</div>
						<div class="sf-score-bar">
							<div class="sf-score-bar-fill <?php echo esc_attr( $bar_class ); ?>" style="width: <?php echo esc_attr( $comp['score'] ); ?>%"></div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<?php if ( ! empty( $score['recommendations'] ) ) : ?>
				<div class="sf-section" style="margin-top: 20px;">
					<h2><?php esc_html_e( 'Recommendations', 'searchforge-wordpress-plugin' ); ?></h2>
					<ul class="sf-recommendations">
						<?php foreach ( $score['recommendations'] as $rec ) : ?>
							<li><?php echo esc_html( $rec ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>

<!-- Export Modal (reuse from admin.js) -->
<div id="sf-export-modal" class="sf-modal" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="sf-modal-title">
	<div class="sf-modal-content">
		<span class="sf-modal-close" role="button" tabindex="0" aria-label="<?php esc_attr_e( 'Close modal', 'searchforge-wordpress-plugin' ); ?>">&times;</span>
		<h2 id="sf-modal-title"></h2>
		<pre id="sf-modal-body"></pre>
		<button class="button button-primary" id="sf-modal-download">
			<?php esc_html_e( 'Download', 'searchforge-wordpress-plugin' ); ?>
		</button>
	</div>
</div>

<?php
// Pass chart data to JS.
$chart_data = [
	'daily_trend' => $daily_trend,
	'pos_dist'    => $pos_dist,
	'weekly_trend' => ( $trend && ! empty( $trend['snapshots'] ) ) ? $trend['snapshots'] : [],
];
?>
<script>
	var sfChartData = <?php echo wp_json_encode( $chart_data ); ?>;
</script>
<?php // phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
