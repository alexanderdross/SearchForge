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
				<?php esc_html_e( 'Upload CSV files with the current header and footer navigation items for each domain or subfolder. This enriches the brief with your existing navigation structure for comparison.', 'searchforge-wordpress-plugin' ); ?>
			</p>
			<p class="description" style="margin-top: 4px;">
				<?php esc_html_e( 'CSV format: label, url, location (header/footer). First row must be a header row.', 'searchforge-wordpress-plugin' ); ?>
			</p>

			<div id="sf-nav-uploads" style="margin-top: 12px;">
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

			<details style="margin-top: 12px;">
				<summary style="cursor: pointer; color: #2271b1;"><?php esc_html_e( 'CSV Example', 'searchforge-wordpress-plugin' ); ?></summary>
				<pre style="background: #f6f7f7; border: 1px solid #dcdcde; padding: 12px; margin-top: 8px; font-size: 13px;">label,url,location
Home,https://www.domain.com/,header
Products,https://www.domain.com/products/,header
About Us,https://www.domain.com/about/,header
Contact,https://www.domain.com/contact/,header
Blog,https://www.domain.com/blog/,footer
Careers,https://www.domain.com/careers/,footer
Privacy Policy,https://www.domain.com/privacy/,footer
Terms of Service,https://www.domain.com/terms/,footer</pre>
			</details>

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
