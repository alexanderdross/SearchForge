<?php

namespace SearchForge\Admin;

defined( 'ABSPATH' ) || exit;

class Onboarding {

	public function __construct() {
		add_action( 'admin_notices', [ $this, 'show_notices' ] );
		add_action( 'wp_ajax_searchforge_dismiss_onboarding', [ $this, 'dismiss' ] );
	}

	public function show_notices(): void {
		$screen = get_current_screen();
		if ( ! $screen || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Only show on SearchForge pages and WP Dashboard.
		$sf_pages = [ 'dashboard', 'toplevel_page_searchforge' ];
		$is_sf_page = str_starts_with( $screen->id, 'searchforge' ) || in_array( $screen->id, $sf_pages, true );
		if ( ! $is_sf_page ) {
			return;
		}

		$dismissed = get_user_meta( get_current_user_id(), 'searchforge_onboarding_dismissed', true );
		if ( $dismissed ) {
			return;
		}

		$settings  = Settings::get_all();
		$connected = ! empty( $settings['gsc_access_token'] );

		if ( $connected ) {
			// Check if first sync has happened.
			global $wpdb;
			$has_data = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}sf_snapshots LIMIT 1" );
			if ( $has_data > 0 ) {
				// User has data — dismiss onboarding.
				update_user_meta( get_current_user_id(), 'searchforge_onboarding_dismissed', 1 );
				return;
			}
		}

		$steps = self::get_steps( $settings, $connected );
		$completed = count( array_filter( $steps, fn( $s ) => $s['done'] ) );
		$total     = count( $steps );
		?>
		<div class="notice notice-info sf-onboarding-notice" id="sf-onboarding">
			<p>
				<strong><?php esc_html_e( 'SearchForge Setup', 'searchforge' ); ?></strong>
				(<?php echo esc_html( "{$completed}/{$total}" ); ?>)
				<button type="button" class="notice-dismiss sf-dismiss-onboarding"
					title="<?php esc_attr_e( 'Dismiss', 'searchforge' ); ?>"></button>
			</p>
			<ol class="sf-setup-steps">
				<?php foreach ( $steps as $step ) : ?>
					<li class="<?php echo $step['done'] ? 'sf-step-done' : ''; ?>">
						<?php if ( $step['done'] ) : ?>
							<span class="dashicons dashicons-yes-alt" style="color:#00a32a"></span>
						<?php else : ?>
							<span class="dashicons dashicons-marker" style="color:#c3c4c7"></span>
						<?php endif; ?>
						<?php echo wp_kses( $step['text'], [ 'a' => [ 'href' => [] ] ] ); ?>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
		<script>
		jQuery(function($) {
			$('.sf-dismiss-onboarding').on('click', function() {
				$('#sf-onboarding').fadeOut();
				$.post(ajaxurl, { action: 'searchforge_dismiss_onboarding', nonce: '<?php echo esc_js( wp_create_nonce( 'searchforge_nonce' ) ); ?>' });
			});
		});
		</script>
		<?php
	}

	private static function get_steps( array $settings, bool $connected ): array {
		$steps = [];

		$steps[] = [
			'text' => $connected
				? __( 'Google Search Console connected', 'searchforge' )
				: sprintf(
					__( 'Connect Google Search Console in <a href="%s">Settings</a>', 'searchforge' ),
					admin_url( 'admin.php?page=searchforge-settings' )
				),
			'done' => $connected,
		];

		$steps[] = [
			'text' => ! empty( $settings['gsc_property'] )
				? sprintf( __( 'Property selected: %s', 'searchforge' ), $settings['gsc_property'] )
				: sprintf(
					__( 'Select a GSC property in <a href="%s">Settings</a>', 'searchforge' ),
					admin_url( 'admin.php?page=searchforge-settings' )
				),
			'done' => ! empty( $settings['gsc_property'] ),
		];

		global $wpdb;
		$has_data = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}sf_snapshots LIMIT 1" );
		$steps[] = [
			'text' => $has_data > 0
				? __( 'First sync completed', 'searchforge' )
				: __( 'Run your first sync from the Dashboard', 'searchforge' ),
			'done' => $has_data > 0,
		];

		return $steps;
	}

	public function dismiss(): void {
		check_ajax_referer( 'searchforge_nonce', 'nonce' );
		if ( current_user_can( 'manage_options' ) ) {
			update_user_meta( get_current_user_id(), 'searchforge_onboarding_dismissed', 1 );
		}
		wp_send_json_success();
	}
}
