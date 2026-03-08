<?php

namespace SearchForge\Admin;

defined( 'ABSPATH' ) || exit;

class DashboardWidget {

	public function __construct() {
		add_action( 'wp_dashboard_setup', [ $this, 'register_widget' ] );
	}

	public function register_widget(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wp_add_dashboard_widget(
			'searchforge_dashboard_widget',
			__( 'SearchForge Overview', 'searchforge' ),
			[ $this, 'render' ]
		);
	}

	public function render(): void {
		$settings  = Settings::get_all();
		$connected = ! empty( $settings['gsc_access_token'] );

		if ( ! $connected ) {
			echo '<p>';
			printf(
				/* translators: %s: settings page URL */
				wp_kses(
					__( 'Google Search Console is not connected. <a href="%s">Connect now</a>.', 'searchforge' ),
					[ 'a' => [ 'href' => [] ] ]
				),
				esc_url( admin_url( 'admin.php?page=searchforge-settings' ) )
			);
			echo '</p>';
			return;
		}

		$summary = Dashboard::get_summary();

		echo '<div class="sf-widget-grid">';

		$metrics = [
			'total_clicks'      => __( 'Clicks', 'searchforge' ),
			'total_impressions' => __( 'Impressions', 'searchforge' ),
			'avg_ctr'           => __( 'Avg CTR', 'searchforge' ),
			'avg_position'      => __( 'Avg Position', 'searchforge' ),
			'total_pages'       => __( 'Pages', 'searchforge' ),
			'total_keywords'    => __( 'Keywords', 'searchforge' ),
		];

		foreach ( $metrics as $key => $label ) {
			$value = $summary[ $key ] ?? 0;

			if ( in_array( $key, [ 'total_clicks', 'total_impressions', 'total_pages', 'total_keywords' ], true ) ) {
				$display = number_format( (int) $value );
			} elseif ( $key === 'avg_ctr' ) {
				$display = $value . '%';
			} else {
				$display = $value;
			}

			printf(
				'<div class="sf-widget-metric"><span class="sf-widget-label">%s</span><span class="sf-widget-value">%s</span></div>',
				esc_html( $label ),
				esc_html( $display )
			);
		}

		echo '</div>';

		// Score.
		$score = \SearchForge\Scoring\Score::calculate_site_score();
		if ( $score ) {
			$class = $score['total'] >= 70 ? 'good' : ( $score['total'] >= 40 ? 'ok' : 'low' );
			printf(
				'<p class="sf-widget-score"><strong>%s:</strong> <span class="sf-score-%s">%d/100</span></p>',
				esc_html__( 'SearchForge Score', 'searchforge' ),
				esc_attr( $class ),
				(int) $score['total']
			);
		}

		// Last sync.
		if ( $summary['last_sync'] ) {
			printf(
				'<p class="sf-widget-sync">%s: %s</p>',
				esc_html__( 'Last sync', 'searchforge' ),
				esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $summary['last_sync'] ) ) )
			);
		}

		// Unread alerts count.
		global $wpdb;
		$alert_count = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}sf_alerts WHERE is_read = 0"
		);

		if ( $alert_count > 0 ) {
			printf(
				'<p class="sf-widget-alerts"><a href="%s">%s</a></p>',
				esc_url( admin_url( 'admin.php?page=searchforge' ) ),
				esc_html( sprintf(
					/* translators: %d: number of unread alerts */
					__( '%d unread alert(s)', 'searchforge' ),
					$alert_count
				) )
			);
		}

		printf(
			'<p><a href="%s" class="button button-small">%s</a></p>',
			esc_url( admin_url( 'admin.php?page=searchforge' ) ),
			esc_html__( 'View Dashboard', 'searchforge' )
		);
	}
}
