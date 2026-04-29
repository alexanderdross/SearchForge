<?php
defined( 'ABSPATH' ) || exit;

$is_pro      = SearchForge\Admin\Settings::is_pro();
$properties  = SearchForge\Models\Property::get_all();
$property_id = SearchForge\Models\Property::get_active_property_id();
?>

<div class="wrap searchforge-wrap">
	<h1><?php esc_html_e( 'SearchForge — Property Comparison', 'searchforge' ); ?>
		<?php if ( ! $is_pro ) : ?>
			<span class="sf-pro-badge">Pro</span>
		<?php endif; ?>
	</h1>

	<?php include SEARCHFORGE_PATH . 'templates/partials/property-selector.php'; ?>

	<?php if ( ! $is_pro ) : ?>
		<div class="notice notice-info">
			<p><?php esc_html_e( 'Property comparison requires a Pro license. Upgrade to compare metrics across multiple properties.', 'searchforge' ); ?></p>
		</div>
	<?php elseif ( count( $properties ) < 2 ) : ?>
		<div class="notice notice-info">
			<p>
				<?php esc_html_e( 'Add at least 2 properties in Settings to use the comparison view.', 'searchforge' ); ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=searchforge-settings' ) ); ?>">
					<?php esc_html_e( 'Go to Settings', 'searchforge' ); ?>
				</a>
			</p>
		</div>
	<?php else : ?>

		<p class="description"><?php esc_html_e( 'Compare key SEO metrics side-by-side across your properties.', 'searchforge' ); ?></p>

		<table class="widefat sf-table" style="margin-top: 16px;">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Metric', 'searchforge' ); ?></th>
					<?php foreach ( $properties as $prop ) : ?>
						<th scope="col"><?php echo esc_html( $prop['label'] ); ?></th>
					<?php endforeach; ?>
				</tr>
			</thead>
			<tbody>
				<?php
				$summaries = [];
				$scores    = [];
				foreach ( $properties as $prop ) {
					$summaries[ $prop['id'] ] = SearchForge\Admin\Dashboard::get_summary( $prop['id'] );
					$scores[ $prop['id'] ]    = SearchForge\Scoring\Score::calculate_site_score( $prop['id'] );
				}
				?>
				<tr>
					<td><strong><?php esc_html_e( 'Total Clicks', 'searchforge' ); ?></strong></td>
					<?php foreach ( $properties as $prop ) : ?>
						<td><?php echo esc_html( number_format( $summaries[ $prop['id'] ]['total_clicks'] ) ); ?></td>
					<?php endforeach; ?>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Total Impressions', 'searchforge' ); ?></strong></td>
					<?php foreach ( $properties as $prop ) : ?>
						<td><?php echo esc_html( number_format( $summaries[ $prop['id'] ]['total_impressions'] ) ); ?></td>
					<?php endforeach; ?>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Avg CTR', 'searchforge' ); ?></strong></td>
					<?php foreach ( $properties as $prop ) : ?>
						<td><?php echo esc_html( $summaries[ $prop['id'] ]['avg_ctr'] ); ?>%</td>
					<?php endforeach; ?>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Avg Position', 'searchforge' ); ?></strong></td>
					<?php foreach ( $properties as $prop ) : ?>
						<td><?php echo esc_html( $summaries[ $prop['id'] ]['avg_position'] ); ?></td>
					<?php endforeach; ?>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Pages', 'searchforge' ); ?></strong></td>
					<?php foreach ( $properties as $prop ) : ?>
						<td><?php echo esc_html( number_format( $summaries[ $prop['id'] ]['total_pages'] ) ); ?></td>
					<?php endforeach; ?>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Keywords', 'searchforge' ); ?></strong></td>
					<?php foreach ( $properties as $prop ) : ?>
						<td><?php echo esc_html( number_format( $summaries[ $prop['id'] ]['total_keywords'] ) ); ?></td>
					<?php endforeach; ?>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'SearchForge Score', 'searchforge' ); ?></strong></td>
					<?php foreach ( $properties as $prop ) : ?>
						<td>
							<?php if ( $scores[ $prop['id'] ] ) : ?>
								<span class="sf-score-<?php echo $scores[ $prop['id'] ]['total'] >= 70 ? 'good' : ( $scores[ $prop['id'] ]['total'] >= 40 ? 'ok' : 'low' ); ?>">
									<?php echo esc_html( $scores[ $prop['id'] ]['total'] ); ?>/100
								</span>
							<?php else : ?>
								&mdash;
							<?php endif; ?>
						</td>
					<?php endforeach; ?>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'GSC Connected', 'searchforge' ); ?></strong></td>
					<?php foreach ( $properties as $prop ) : ?>
						<td><?php echo ! empty( $prop['gsc_access_token'] ) ? '<span class="sf-status sf-status-connected">Yes</span>' : '<span class="sf-status sf-status-disconnected">No</span>'; ?></td>
					<?php endforeach; ?>
				</tr>
			</tbody>
		</table>

	<?php endif; ?>
</div>
