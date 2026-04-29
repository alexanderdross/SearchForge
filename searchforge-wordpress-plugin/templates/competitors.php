<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
defined( 'ABSPATH' ) || exit;

$is_pro      = SearchForge\Admin\Settings::is_pro();
$property_id = SearchForge\Models\Property::get_active_property_id();
$competitors = SearchForge\Analysis\Competitors::get_all( $property_id );
$visibility  = $is_pro ? SearchForge\Analysis\Competitors::get_visibility_comparison( $property_id ) : null;

$tier  = SearchForge\Admin\Settings::get( 'license_tier' );
$limit = match ( $tier ) {
	'enterprise', 'agency' => 999,
	'pro'                  => 3,
	default                => 0,
};
$can_add = count( $competitors ) < $limit;
?>

<div class="wrap searchforge-wrap">
	<h1><?php esc_html_e( 'SearchForge — Competitors', 'searchforge-wordpress-plugin' ); ?>
		<?php if ( ! $is_pro ) : ?>
			<span class="sf-pro-badge">Pro</span>
		<?php endif; ?>
	</h1>

	<?php include SEARCHFORGE_PATH . 'templates/partials/property-selector.php'; ?>

	<?php if ( ! $is_pro ) : ?>
		<div class="notice notice-info">
			<p><?php esc_html_e( 'Competitor tracking requires a Pro license. Upgrade to compare your keyword rankings against competitors.', 'searchforge-wordpress-plugin' ); ?></p>
		</div>
	<?php else : ?>

		<!-- Add Competitor -->
		<?php if ( $can_add ) : ?>
			<div class="sf-add-competitor">
				<h2><?php esc_html_e( 'Add Competitor', 'searchforge-wordpress-plugin' ); ?></h2>
				<div class="sf-add-competitor-form">
					<input type="text" id="sf-competitor-domain" class="regular-text"
						placeholder="<?php esc_attr_e( 'example.com', 'searchforge-wordpress-plugin' ); ?>" />
					<input type="text" id="sf-competitor-label" class="regular-text"
						placeholder="<?php esc_attr_e( 'Label (optional)', 'searchforge-wordpress-plugin' ); ?>" />
					<button type="button" class="button button-primary" id="sf-add-competitor">
						<?php esc_html_e( 'Add', 'searchforge-wordpress-plugin' ); ?>
					</button>
				</div>
				<p class="description">
					<?php
					/* translators: %1$d: number of competitor slots used, %2$d: total competitor slots available */
					echo esc_html( sprintf(
						__( '%1$d of %2$d competitor slots used.', 'searchforge-wordpress-plugin' ),
						count( $competitors ),
						$limit
					) ); ?>
				</p>
			</div>
		<?php endif; ?>

		<!-- Registered Competitors -->
		<?php if ( ! empty( $competitors ) ) : ?>
			<h2><?php esc_html_e( 'Tracked Competitors', 'searchforge-wordpress-plugin' ); ?></h2>
			<table class="widefat sf-table">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Domain', 'searchforge-wordpress-plugin' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Label', 'searchforge-wordpress-plugin' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Added', 'searchforge-wordpress-plugin' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Actions', 'searchforge-wordpress-plugin' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $competitors as $comp ) : ?>
						<tr>
							<td><code><?php echo esc_html( $comp['domain'] ); ?></code></td>
							<td><?php echo esc_html( $comp['label'] ); ?></td>
							<td><?php echo esc_html( wp_date( get_option( 'date_format' ), strtotime( $comp['added_at'] ) ) ); ?></td>
							<td>
								<button class="button button-small sf-sync-competitor"
									data-id="<?php echo esc_attr( $comp['id'] ); ?>">
									<?php esc_html_e( 'Sync Keywords', 'searchforge-wordpress-plugin' ); ?>
								</button>
								<button class="button button-small sf-remove-competitor"
									data-id="<?php echo esc_attr( $comp['id'] ); ?>">
									<?php esc_html_e( 'Remove', 'searchforge-wordpress-plugin' ); ?>
								</button>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<!-- Tabs -->
		<?php if ( ! empty( $competitors ) ) : ?>
			<nav class="nav-tab-wrapper" role="tablist" style="margin-top: 24px;">
				<a href="#sf-tab-visibility" class="nav-tab nav-tab-active" data-tab="sf-tab-visibility"
					role="tab" aria-selected="true" aria-controls="sf-tab-visibility" id="sf-tab-visibility-tab">
					<?php esc_html_e( 'Visibility', 'searchforge-wordpress-plugin' ); ?>
				</a>
				<a href="#sf-tab-overlap" class="nav-tab" data-tab="sf-tab-overlap"
					role="tab" aria-selected="false" aria-controls="sf-tab-overlap" id="sf-tab-overlap-tab">
					<?php esc_html_e( 'Keyword Overlap', 'searchforge-wordpress-plugin' ); ?>
				</a>
				<a href="#sf-tab-gaps" class="nav-tab" data-tab="sf-tab-gaps"
					role="tab" aria-selected="false" aria-controls="sf-tab-gaps" id="sf-tab-gaps-tab">
					<?php esc_html_e( 'Content Gaps', 'searchforge-wordpress-plugin' ); ?>
				</a>
			</nav>

			<!-- Tab: Visibility -->
			<div id="sf-tab-visibility" class="sf-tab-panel sf-tab-active" role="tabpanel" aria-labelledby="sf-tab-visibility-tab" tabindex="0">
				<?php if ( $visibility ) : ?>
					<div class="sf-visibility-grid">
						<div class="sf-visibility-card sf-visibility-you">
							<h3><?php esc_html_e( 'Your Site', 'searchforge-wordpress-plugin' ); ?></h3>
							<div class="sf-visibility-score"><?php echo esc_html( $visibility['your_site']['visibility'] ); ?></div>
							<div class="sf-visibility-label"><?php esc_html_e( 'Visibility Score', 'searchforge-wordpress-plugin' ); ?></div>
							<div class="sf-visibility-kw">
								<?php echo esc_html( number_format( $visibility['your_site']['keywords'] ) ); ?>
								<?php esc_html_e( 'keywords', 'searchforge-wordpress-plugin' ); ?>
							</div>
						</div>
						<?php foreach ( $visibility['competitors'] as $comp ) : ?>
							<div class="sf-visibility-card">
								<h3><?php echo esc_html( $comp['label'] ); ?></h3>
								<div class="sf-visibility-score"><?php echo esc_html( $comp['visibility'] ); ?></div>
								<div class="sf-visibility-label"><?php esc_html_e( 'Visibility Score', 'searchforge-wordpress-plugin' ); ?></div>
								<div class="sf-visibility-kw">
									<?php echo esc_html( number_format( $comp['keywords'] ) ); ?>
									<?php esc_html_e( 'keywords', 'searchforge-wordpress-plugin' ); ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
					<p class="description">
						<?php esc_html_e( 'Visibility Score = sum of 1/position for all keywords ranking in top 100. Higher is better.', 'searchforge-wordpress-plugin' ); ?>
					</p>
				<?php endif; ?>
			</div>

			<!-- Tab: Keyword Overlap -->
			<div id="sf-tab-overlap" class="sf-tab-panel" role="tabpanel" aria-labelledby="sf-tab-overlap-tab" tabindex="0">
				<?php
				$overlap = SearchForge\Analysis\Competitors::get_keyword_overlap( 50, $property_id );
				if ( ! empty( $overlap ) ) : ?>
					<p class="description">
						<?php esc_html_e( 'Keywords where both you and a competitor rank. Win these to capture competitor traffic.', 'searchforge-wordpress-plugin' ); ?>
					</p>
					<table class="widefat sf-table">
						<thead>
							<tr>
								<th scope="col"><?php esc_html_e( 'Keyword', 'searchforge-wordpress-plugin' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Your Position', 'searchforge-wordpress-plugin' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Your Clicks', 'searchforge-wordpress-plugin' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Competitor', 'searchforge-wordpress-plugin' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Their Position', 'searchforge-wordpress-plugin' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Status', 'searchforge-wordpress-plugin' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $overlap as $item ) :
								$your_pos = (float) $item['your_position'];
								$their_pos = $item['competitor_position'] ? (float) $item['competitor_position'] : null;
								if ( $their_pos && $your_pos < $their_pos ) {
									$status_class = 'sf-comp-winning';
									$status_label = __( 'Winning', 'searchforge-wordpress-plugin' );
								} elseif ( $their_pos && $your_pos > $their_pos ) {
									$status_class = 'sf-comp-losing';
									$status_label = __( 'Losing', 'searchforge-wordpress-plugin' );
								} else {
									$status_class = 'sf-comp-tied';
									$status_label = __( 'Tied', 'searchforge-wordpress-plugin' );
								}
							?>
								<tr>
									<td><?php echo esc_html( $item['query'] ); ?></td>
									<td><?php echo esc_html( round( $your_pos, 1 ) ); ?></td>
									<td><?php echo esc_html( number_format( (int) $item['your_clicks'] ) ); ?></td>
									<td><code><?php echo esc_html( $item['competitor_domain'] ); ?></code></td>
									<td><?php echo $their_pos ? esc_html( round( $their_pos, 1 ) ) : '—'; ?></td>
									<td><span class="sf-comp-badge <?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $status_label ); ?></span></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<p><?php esc_html_e( 'No keyword overlap data yet. Sync competitor keywords first.', 'searchforge-wordpress-plugin' ); ?></p>
				<?php endif; ?>
			</div>

			<!-- Tab: Content Gaps -->
			<div id="sf-tab-gaps" class="sf-tab-panel" role="tabpanel" aria-labelledby="sf-tab-gaps-tab" tabindex="0">
				<?php
				$gaps = SearchForge\Analysis\Competitors::get_competitor_only_keywords( 50, $property_id );
				if ( ! empty( $gaps ) ) : ?>
					<p class="description">
						<?php esc_html_e( 'Keywords where competitors rank but you don\'t. These are content opportunities.', 'searchforge-wordpress-plugin' ); ?>
					</p>
					<table class="widefat sf-table">
						<thead>
							<tr>
								<th scope="col"><?php esc_html_e( 'Keyword', 'searchforge-wordpress-plugin' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Competitor', 'searchforge-wordpress-plugin' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Their Position', 'searchforge-wordpress-plugin' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $gaps as $gap ) : ?>
								<tr>
									<td><?php echo esc_html( $gap['query'] ); ?></td>
									<td><code><?php echo esc_html( $gap['competitor_domain'] ); ?></code></td>
									<td><?php echo $gap['competitor_position'] ? esc_html( round( (float) $gap['competitor_position'], 1 ) ) : '—'; ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<p><?php esc_html_e( 'No content gap data yet. Sync competitor keywords first.', 'searchforge-wordpress-plugin' ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

	<?php endif; ?>
</div>
<?php // phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
