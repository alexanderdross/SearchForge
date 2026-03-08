<?php
/**
 * Performance optimizations.
 *
 * @package SearchForge_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Remove jQuery from frontend (theme does not use it).
 * Uses wp_dequeue_script to avoid breaking plugins that declare jQuery as a dependency.
 */
function sf_theme_dequeue_jquery(): void {
	if ( ! is_admin() ) {
		wp_dequeue_script( 'jquery' );
	}
}
add_action( 'wp_enqueue_scripts', 'sf_theme_dequeue_jquery' );

/**
 * Disable WordPress block library CSS on frontend (not used in this theme).
 */
function sf_theme_dequeue_block_styles(): void {
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'global-styles' );
}
add_action( 'wp_enqueue_scripts', 'sf_theme_dequeue_block_styles', 100 );
