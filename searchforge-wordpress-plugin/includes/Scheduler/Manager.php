<?php

namespace SearchForge\Scheduler;

use SearchForge\Admin\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Scheduling manager for configurable sync intervals.
 *
 * Manages WP-Cron schedule based on user settings.
 */
class Manager {

	private const HOOK = 'searchforge_daily_sync';

	public function __construct() {
		add_filter( 'cron_schedules', [ $this, 'add_custom_schedules' ] );
		add_action( 'admin_init', [ $this, 'maybe_reschedule' ] );
	}

	/**
	 * Add custom cron intervals.
	 */
	public function add_custom_schedules( array $schedules ): array {
		$schedules['every_six_hours'] = [
			'interval' => 6 * HOUR_IN_SECONDS,
			'display'  => __( 'Every 6 Hours', 'searchforge' ),
		];

		$schedules['every_four_hours'] = [
			'interval' => 4 * HOUR_IN_SECONDS,
			'display'  => __( 'Every 4 Hours', 'searchforge' ),
		];

		return $schedules;
	}

	/**
	 * Reschedule if the frequency setting has changed.
	 */
	public function maybe_reschedule(): void {
		$desired   = Settings::get( 'sync_frequency', 'daily' );
		$scheduled = wp_get_schedule( self::HOOK );

		if ( $scheduled === $desired ) {
			return;
		}

		// Clear existing and reschedule.
		wp_clear_scheduled_hook( self::HOOK );

		if ( ! wp_next_scheduled( self::HOOK ) ) {
			wp_schedule_event( time(), $desired, self::HOOK );
		}
	}

	/**
	 * Get available schedule options.
	 */
	public static function get_schedule_options(): array {
		$options = [
			'daily'      => __( 'Daily', 'searchforge' ),
			'twicedaily' => __( 'Twice Daily', 'searchforge' ),
			'weekly'     => __( 'Weekly', 'searchforge' ),
		];

		if ( Settings::is_pro() ) {
			$options = [
				'every_four_hours' => __( 'Every 4 Hours', 'searchforge' ),
				'every_six_hours'  => __( 'Every 6 Hours', 'searchforge' ),
			] + $options;
		}

		return $options;
	}

	/**
	 * Get info about the next scheduled run.
	 */
	public static function get_next_run(): ?string {
		$timestamp = wp_next_scheduled( self::HOOK );
		if ( ! $timestamp ) {
			return null;
		}

		return wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp );
	}

	/**
	 * Get the current schedule recurrence.
	 */
	public static function get_current_schedule(): string {
		return wp_get_schedule( self::HOOK ) ?: 'daily';
	}
}
