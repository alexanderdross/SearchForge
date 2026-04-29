<?php
defined( 'ABSPATH' ) || exit;

$is_pro      = SearchForge\Admin\Settings::is_pro();
$properties  = SearchForge\Models\Property::get_all();
$property_id = SearchForge\Models\Property::get_active_property_id();
?>

<div class="wrap searchforge-wrap">
	<h1><?php esc_html_e( 'SearchForge — Merger Analysis', 'searchforge' ); ?>
		<?php if ( ! $is_pro ) : ?>
			<span class="sf-pro-badge">Pro</span>
		<?php endif; ?>
	</h1>

	<?php include SEARCHFORGE_PATH . 'templates/partials/property-selector.php'; ?>

	<?php if ( ! $is_pro ) : ?>
		<div class="notice notice-info">
			<p><?php esc_html_e( 'Merger analysis requires a Pro license. Upgrade to generate combined SEO briefs for domain mergers and migrations.', 'searchforge' ); ?></p>
		</div>
	<?php elseif ( count( $properties ) < 2 ) : ?>
		<div class="notice notice-info">
			<p>
				<?php esc_html_e( 'Add at least 2 properties in Settings to use merger analysis.', 'searchforge' ); ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=searchforge-settings' ) ); ?>">
					<?php esc_html_e( 'Go to Settings', 'searchforge' ); ?>
				</a>
			</p>
		</div>
	<?php else : ?>

		<p class="description">
			<?php esc_html_e( 'Generate a combined SEO analysis brief for selected properties. Useful for domain mergers, migrations, and portfolio analysis.', 'searchforge' ); ?>
		</p>

		<div class="sf-merger-form" style="margin-top: 16px;">
			<h2><?php esc_html_e( 'Select Properties to Analyze', 'searchforge' ); ?></h2>
			<fieldset>
				<?php foreach ( $properties as $prop ) : ?>
					<label style="display: block; margin-bottom: 8px;">
						<input type="checkbox" class="sf-merger-property" value="<?php echo esc_attr( $prop['id'] ); ?>" />
						<?php echo esc_html( $prop['label'] ); ?> (<code><?php echo esc_html( $prop['domain'] ); ?></code>)
					</label>
				<?php endforeach; ?>
			</fieldset>
			<p style="margin-top: 12px;">
				<button type="button" class="button button-primary" id="sf-generate-merger-brief" disabled>
					<?php esc_html_e( 'Generate Merger Brief', 'searchforge' ); ?>
				</button>
				<span id="sf-merger-status" style="margin-left: 8px;"></span>
			</p>
		</div>

		<div id="sf-merger-output" style="display: none; margin-top: 24px;">
			<h2><?php esc_html_e( 'Merger Analysis Brief', 'searchforge' ); ?></h2>
			<pre id="sf-merger-content" style="background: #f6f7f7; border: 1px solid #dcdcde; padding: 16px; max-height: 600px; overflow: auto; white-space: pre-wrap;"></pre>
			<p>
				<button type="button" class="button button-primary" id="sf-merger-download">
					<?php esc_html_e( 'Download .md', 'searchforge' ); ?>
				</button>
			</p>
		</div>

	<?php endif; ?>
</div>
