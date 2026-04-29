<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
defined( 'ABSPATH' ) || exit;

$per_page    = 50;
$paged       = max( 1, absint( wp_unslash( $_GET['paged'] ?? 1 ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$search      = sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$offset      = ( $paged - 1 ) * $per_page;
$property_id = SearchForge\Models\Property::get_active_property_id();

$keywords = SearchForge\Admin\Dashboard::get_top_keywords( $per_page, '', $offset, $search, $property_id );
$total    = SearchForge\Admin\Dashboard::count_keywords( $search, $property_id );
$is_pro   = SearchForge\Admin\Settings::is_pro();

$total_pages = ceil( $total / $per_page );
$base_url    = admin_url( 'admin.php?page=searchforge-keywords' );
?>

<div class="wrap searchforge-wrap">
	<h1><?php esc_html_e( 'SearchForge — Keywords', 'searchforge-wordpress-plugin' ); ?>
		<span class="title-count">(<?php echo esc_html( number_format( $total ) ); ?>)</span>
	</h1>

	<?php include SEARCHFORGE_PATH . 'templates/partials/property-selector.php'; ?>

	<?php if ( ! $is_pro && $total >= 100 ) : ?>
		<div class="notice notice-info">
			<p>
				<?php esc_html_e( 'Free tier shows up to 100 keywords. Upgrade to Pro to see all keywords and unlock clustering.', 'searchforge-wordpress-plugin' ); ?>
			</p>
		</div>
	<?php endif; ?>

	<!-- Search -->
	<form method="get" class="sf-search-form">
		<input type="hidden" name="page" value="searchforge-keywords" />
		<p class="search-box">
			<label class="screen-reader-text" for="sf-search-input">
				<?php esc_html_e( 'Search keywords:', 'searchforge-wordpress-plugin' ); ?>
			</label>
			<input type="search" id="sf-search-input" name="s"
				value="<?php echo esc_attr( $search ); ?>"
				placeholder="<?php esc_attr_e( 'Search keywords or pages...', 'searchforge-wordpress-plugin' ); ?>" />
			<input type="submit" class="button" value="<?php esc_attr_e( 'Search', 'searchforge-wordpress-plugin' ); ?>" />
			<?php if ( $search ) : ?>
				<a href="<?php echo esc_url( $base_url ); ?>" class="button">
					<?php esc_html_e( 'Clear', 'searchforge-wordpress-plugin' ); ?>
				</a>
			<?php endif; ?>
		</p>
	</form>

	<?php if ( empty( $keywords ) ) : ?>
		<p><?php esc_html_e( 'No keyword data available. Run a GSC sync first.', 'searchforge-wordpress-plugin' ); ?></p>
	<?php else : ?>
		<table class="widefat sf-table">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Keyword', 'searchforge-wordpress-plugin' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Page', 'searchforge-wordpress-plugin' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Clicks', 'searchforge-wordpress-plugin' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Impressions', 'searchforge-wordpress-plugin' ); ?></th>
					<th scope="col"><?php esc_html_e( 'CTR', 'searchforge-wordpress-plugin' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Position', 'searchforge-wordpress-plugin' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $keywords as $kw ) : ?>
					<tr>
						<td><?php echo esc_html( $kw['query'] ); ?></td>
						<td><code><?php echo esc_html( $kw['page_path'] ); ?></code></td>
						<td><?php echo esc_html( number_format( (int) $kw['clicks'] ) ); ?></td>
						<td><?php echo esc_html( number_format( (int) $kw['impressions'] ) ); ?></td>
						<td><?php echo esc_html( round( (float) $kw['ctr'] * 100, 1 ) ); ?>%</td>
						<td><?php echo esc_html( round( (float) $kw['position'], 1 ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<!-- Pagination -->
		<?php if ( $total_pages > 1 ) : ?>
			<div class="tablenav bottom">
				<div class="tablenav-pages">
					<span class="displaying-num">
						<?php echo esc_html( sprintf(
							/* translators: %s: total items */
							__( '%s items', 'searchforge-wordpress-plugin' ),
							number_format( $total )
						) ); ?>
					</span>
					<span class="pagination-links">
						<?php if ( $paged > 1 ) : ?>
							<a class="prev-page button" href="<?php echo esc_url( add_query_arg( [ 'paged' => $paged - 1, 's' => $search ], $base_url ) ); ?>" aria-label="<?php esc_attr_e( 'Previous page', 'searchforge-wordpress-plugin' ); ?>">
								&lsaquo;
							</a>
						<?php endif; ?>
						<span class="paging-input">
							<?php echo esc_html( $paged ); ?> / <?php echo esc_html( $total_pages ); ?>
						</span>
						<?php if ( $paged < $total_pages ) : ?>
							<a class="next-page button" href="<?php echo esc_url( add_query_arg( [ 'paged' => $paged + 1, 's' => $search ], $base_url ) ); ?>" aria-label="<?php esc_attr_e( 'Next page', 'searchforge-wordpress-plugin' ); ?>">
								&rsaquo;
							</a>
						<?php endif; ?>
					</span>
				</div>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>
<?php // phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
