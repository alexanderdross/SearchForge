<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
defined( 'ABSPATH' ) || exit;

$is_pro      = SearchForge\Admin\Settings::is_pro();
$tab         = sanitize_text_field( wp_unslash( $_GET['tab'] ?? 'cannibalization' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$property_id = SearchForge\Models\Property::get_active_property_id();

$cannibalization = [];
$clusters        = [];

if ( $is_pro ) {
	if ( $tab === 'cannibalization' ) {
		$cannibalization = SearchForge\Analysis\Cannibalization::detect( 50, $property_id );
	} elseif ( $tab === 'clusters' ) {
		$clusters = SearchForge\Analysis\Clustering::cluster_keywords( 0.3, 500, $property_id );
	}
}
?>

<div class="wrap searchforge-wrap">
	<h1><?php esc_html_e( 'SearchForge — Analysis', 'searchforge-wordpress-plugin' ); ?>
		<?php if ( ! $is_pro ) : ?>
			<span class="sf-pro-badge">Pro</span>
		<?php endif; ?>
	</h1>

	<?php include SEARCHFORGE_PATH . 'templates/partials/property-selector.php'; ?>

	<?php if ( ! $is_pro ) : ?>
		<div class="notice notice-info">
			<p><?php esc_html_e( 'Analysis features require a Pro license. Upgrade to unlock cannibalization detection, keyword clustering, and AI content briefs.', 'searchforge-wordpress-plugin' ); ?></p>
		</div>
	<?php else : ?>

		<nav class="nav-tab-wrapper" role="tablist">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=searchforge-analysis&tab=cannibalization' ) ); ?>"
				class="nav-tab <?php echo $tab === 'cannibalization' ? 'nav-tab-active' : ''; ?>"
				role="tab" aria-selected="<?php echo $tab === 'cannibalization' ? 'true' : 'false'; ?>">
				<?php esc_html_e( 'Cannibalization', 'searchforge-wordpress-plugin' ); ?>
			</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=searchforge-analysis&tab=clusters' ) ); ?>"
				class="nav-tab <?php echo $tab === 'clusters' ? 'nav-tab-active' : ''; ?>"
				role="tab" aria-selected="<?php echo $tab === 'clusters' ? 'true' : 'false'; ?>">
				<?php esc_html_e( 'Keyword Clusters', 'searchforge-wordpress-plugin' ); ?>
			</a>
		</nav>

		<?php if ( $tab === 'cannibalization' ) : ?>
			<div class="sf-analysis-section">
				<p class="description">
					<?php esc_html_e( 'Keywords where multiple pages from your site compete for the same query, potentially splitting ranking signals.', 'searchforge-wordpress-plugin' ); ?>
				</p>

				<?php if ( empty( $cannibalization ) ) : ?>
					<p><?php esc_html_e( 'No cannibalization detected. This is good! Each keyword maps to a single page.', 'searchforge-wordpress-plugin' ); ?></p>
				<?php else : ?>
					<?php foreach ( $cannibalization as $item ) : ?>
						<div class="sf-cannibal-item sf-cannibal-<?php echo esc_attr( $item['severity'] ); ?>">
							<div class="sf-cannibal-header">
								<strong class="sf-cannibal-query"><?php echo esc_html( $item['query'] ); ?></strong>
								<span class="sf-severity-badge sf-severity-<?php echo esc_attr( $item['severity'] ); ?>">
									<?php echo esc_html( ucfirst( $item['severity'] ) ); ?>
								</span>
								<span class="sf-cannibal-meta">
									<?php
									echo esc_html( sprintf(
										/* translators: %1$d: number of pages, %2$s: total clicks, %3$s: total impressions, %4$s: position spread */
										__( '%1$d pages | %2$s clicks | %3$s impressions | spread: %4$s pos', 'searchforge-wordpress-plugin' ),
										$item['page_count'],
										number_format( $item['total_clicks'] ),
										number_format( $item['total_impressions'] ),
										$item['position_spread']
									) ); ?>
								</span>
							</div>
							<table class="widefat sf-table sf-cannibal-table">
								<thead>
									<tr>
										<th scope="col"><?php esc_html_e( 'Page', 'searchforge-wordpress-plugin' ); ?></th>
										<th scope="col"><?php esc_html_e( 'Position', 'searchforge-wordpress-plugin' ); ?></th>
										<th scope="col"><?php esc_html_e( 'Clicks', 'searchforge-wordpress-plugin' ); ?></th>
										<th scope="col"><?php esc_html_e( 'Impressions', 'searchforge-wordpress-plugin' ); ?></th>
										<th scope="col"><?php esc_html_e( 'CTR', 'searchforge-wordpress-plugin' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( $item['pages'] as $page ) : ?>
										<tr>
											<td><code><?php echo esc_html( $page['page_path'] ); ?></code></td>
											<td><?php echo esc_html( round( (float) $page['position'], 1 ) ); ?></td>
											<td><?php echo esc_html( number_format( (int) $page['clicks'] ) ); ?></td>
											<td><?php echo esc_html( number_format( (int) $page['impressions'] ) ); ?></td>
											<td><?php echo esc_html( round( (float) $page['ctr'] * 100, 1 ) ); ?>%</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>

		<?php elseif ( $tab === 'clusters' ) : ?>
			<div class="sf-analysis-section">
				<p class="description">
					<?php esc_html_e( 'Keywords grouped by topical similarity. Use clusters to identify content themes and optimize internal linking.', 'searchforge-wordpress-plugin' ); ?>
				</p>

				<?php if ( empty( $clusters ) ) : ?>
					<p><?php esc_html_e( 'Not enough keyword data to form clusters. Sync more data first.', 'searchforge-wordpress-plugin' ); ?></p>
				<?php else : ?>
					<?php foreach ( $clusters as $i => $cluster ) : ?>
						<div class="sf-cluster-item">
							<div class="sf-cluster-header">
								<strong><?php echo esc_html( $cluster['name'] ); ?></strong>
								<span class="sf-cluster-meta">
									<?php
									echo esc_html( sprintf(
										/* translators: %1$d: number of keywords, %2$s: total clicks, %3$s: total impressions */
										__( '%1$d keywords | %2$s clicks | %3$s impressions', 'searchforge-wordpress-plugin' ),
										count( $cluster['keywords'] ),
										number_format( $cluster['total_clicks'] ),
										number_format( $cluster['total_impressions'] )
									) ); ?>
								</span>
							</div>
							<table class="widefat sf-table sf-cluster-table">
								<thead>
									<tr>
										<th scope="col"><?php esc_html_e( 'Keyword', 'searchforge-wordpress-plugin' ); ?></th>
										<th scope="col"><?php esc_html_e( 'Clicks', 'searchforge-wordpress-plugin' ); ?></th>
										<th scope="col"><?php esc_html_e( 'Impressions', 'searchforge-wordpress-plugin' ); ?></th>
										<th scope="col"><?php esc_html_e( 'Avg Position', 'searchforge-wordpress-plugin' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( $cluster['keywords'] as $kw ) : ?>
										<tr>
											<td><?php echo esc_html( $kw['query'] ); ?></td>
											<td><?php echo esc_html( number_format( (int) $kw['total_clicks'] ) ); ?></td>
											<td><?php echo esc_html( number_format( (int) $kw['total_impressions'] ) ); ?></td>
											<td><?php echo esc_html( round( (float) $kw['avg_position'], 1 ) ); ?></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		<?php endif; ?>

	<?php endif; ?>
</div>
<?php // phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
