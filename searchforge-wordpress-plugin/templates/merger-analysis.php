<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
defined( 'ABSPATH' ) || exit;

$is_pro      = SearchForge\Admin\Settings::is_pro();
$properties  = SearchForge\Models\Property::get_all();
$property_id = SearchForge\Models\Property::get_active_property_id();
?>

<div class="wrap searchforge-wrap">
	<h1><?php esc_html_e( 'SearchForge — Merger Analysis', 'searchforge-wordpress-plugin' ); ?>
		<?php if ( ! $is_pro ) : ?>
			<span class="sf-pro-badge">Pro</span>
		<?php endif; ?>
	</h1>

	<?php include SEARCHFORGE_PATH . 'templates/partials/property-selector.php'; ?>

	<?php if ( ! $is_pro ) : ?>
		<div class="notice notice-info">
			<p><?php esc_html_e( 'Merger analysis requires a Pro license. Upgrade to generate combined SEO briefs for domain mergers and migrations.', 'searchforge-wordpress-plugin' ); ?></p>
		</div>
	<?php elseif ( count( $properties ) < 2 ) : ?>
		<div class="notice notice-info">
			<p>
				<?php esc_html_e( 'Add at least 2 properties in Settings to use merger analysis.', 'searchforge-wordpress-plugin' ); ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=searchforge-settings' ) ); ?>">
					<?php esc_html_e( 'Go to Settings', 'searchforge-wordpress-plugin' ); ?>
				</a>
			</p>
		</div>
	<?php else : ?>

		<p class="description">
			<?php esc_html_e( 'Generate a combined SEO analysis brief for selected properties. Useful for domain mergers, migrations, and portfolio analysis. The analytics data can come from any CMS backend (WordPress, Drupal, custom).', 'searchforge-wordpress-plugin' ); ?>
		</p>

		<div class="sf-merger-form" style="margin-top: 16px;">
			<h2><?php esc_html_e( 'Select Properties to Analyze', 'searchforge-wordpress-plugin' ); ?></h2>
			<fieldset>
				<?php foreach ( $properties as $prop ) : ?>
					<label style="display: block; margin-bottom: 8px;">
						<input type="checkbox" class="sf-merger-property" value="<?php echo esc_attr( $prop['id'] ); ?>" />
						<?php echo esc_html( $prop['label'] ); ?> (<code><?php echo esc_html( $prop['domain'] ); ?></code>)
					</label>
				<?php endforeach; ?>
			</fieldset>

			<hr style="border: none; border-top: 1px solid #dcdcde; margin: 20px 0;">

			<h2><?php esc_html_e( 'Upload Current Navigation (Optional)', 'searchforge-wordpress-plugin' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Upload CSV files with the current header and footer navigation items for each domain or subfolder. This enriches the brief with your existing navigation structure for comparison and merger recommendations.', 'searchforge-wordpress-plugin' ); ?>
			</p>

			<div class="sf-csv-format-info" style="background: #f0f6fc; border: 1px solid #c3d1e0; border-radius: 4px; padding: 16px; margin-top: 12px;">
				<h3 style="margin: 0 0 8px;"><?php esc_html_e( 'CSV Format', 'searchforge-wordpress-plugin' ); ?></h3>
				<p class="description" style="margin: 0 0 8px;">
					<?php esc_html_e( 'The file must be comma-separated (,) — not semicolon. Save as UTF-8 .csv from Excel or Google Sheets.', 'searchforge-wordpress-plugin' ); ?>
				</p>
				<table class="widefat" style="max-width: 640px; margin-bottom: 12px;">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Column', 'searchforge-wordpress-plugin' ); ?></th>
							<th><?php esc_html_e( 'Required', 'searchforge-wordpress-plugin' ); ?></th>
							<th><?php esc_html_e( 'Description', 'searchforge-wordpress-plugin' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><code>label</code></td>
							<td><?php esc_html_e( 'Yes', 'searchforge-wordpress-plugin' ); ?></td>
							<td><?php esc_html_e( 'Navigation item text (e.g. "Products", "About Us")', 'searchforge-wordpress-plugin' ); ?></td>
						</tr>
						<tr>
							<td><code>url</code></td>
							<td><?php esc_html_e( 'Yes', 'searchforge-wordpress-plugin' ); ?></td>
							<td><?php esc_html_e( 'Full URL including https:// (e.g. "https://example.com/products/")', 'searchforge-wordpress-plugin' ); ?></td>
						</tr>
						<tr>
							<td><code>location</code></td>
							<td><?php esc_html_e( 'No', 'searchforge-wordpress-plugin' ); ?></td>
							<td><?php esc_html_e( '"header" or "footer" — defaults to "header" if omitted', 'searchforge-wordpress-plugin' ); ?></td>
						</tr>
					</tbody>
				</table>
				<p style="margin: 0;">
					<a href="<?php echo esc_url( SEARCHFORGE_URL . 'assets/templates/navigation-template.csv' ); ?>" download="navigation-template.csv" class="button">
						<?php esc_html_e( 'Download Example CSV Template', 'searchforge-wordpress-plugin' ); ?>
					</a>
					<span class="description" style="margin-left: 8px;">
						<?php esc_html_e( 'Fill in your own URLs and labels, then upload below.', 'searchforge-wordpress-plugin' ); ?>
					</span>
				</p>
			</div>

			<div id="sf-nav-uploads" style="margin-top: 16px;">
				<div class="sf-nav-upload-row" style="display: flex; gap: 8px; align-items: center; margin-bottom: 8px;">
					<input type="text" class="sf-nav-csv-label regular-text" placeholder="<?php esc_attr_e( 'e.g. www.domain.com', 'searchforge-wordpress-plugin' ); ?>" style="max-width: 240px;" />
					<input type="file" class="sf-nav-csv-file" accept=".csv" />
					<button type="button" class="button sf-nav-upload-remove" title="<?php esc_attr_e( 'Remove', 'searchforge-wordpress-plugin' ); ?>">&times;</button>
				</div>
			</div>
			<p style="margin-top: 8px;">
				<button type="button" class="button" id="sf-add-nav-upload">
					<?php esc_html_e( '+ Add Another Domain / Subfolder', 'searchforge-wordpress-plugin' ); ?>
				</button>
			</p>

			<hr style="border: none; border-top: 1px solid #dcdcde; margin: 20px 0;">

			<p>
				<button type="button" class="button button-primary" id="sf-generate-merger-brief" disabled>
					<?php esc_html_e( 'Generate Merger Brief', 'searchforge-wordpress-plugin' ); ?>
				</button>
				<span id="sf-merger-status" style="margin-left: 8px;"></span>
			</p>
		</div>

		<div id="sf-merger-output" style="display: none; margin-top: 24px;">
			<h2><?php esc_html_e( 'Merger Analysis Brief', 'searchforge-wordpress-plugin' ); ?></h2>
			<pre id="sf-merger-content" style="background: #f6f7f7; border: 1px solid #dcdcde; padding: 16px; max-height: 600px; overflow: auto; white-space: pre-wrap;"></pre>
			<p>
				<button type="button" class="button button-primary" id="sf-merger-download">
					<?php esc_html_e( 'Download .md', 'searchforge-wordpress-plugin' ); ?>
				</button>
			</p>
		</div>

	<?php endif; ?>
</div>
<?php // phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
