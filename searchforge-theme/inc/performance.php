<?php
/**
 * Performance optimizations.
 *
 * @package SearchForge_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Preload critical fonts.
 */
function sf_theme_preload_fonts(): void {
	echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
	echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}
add_action( 'wp_head', 'sf_theme_preload_fonts', 1 );

/**
 * Remove jQuery from frontend (not needed).
 */
function sf_theme_dequeue_jquery(): void {
	if ( ! is_admin() ) {
		wp_deregister_script( 'jquery' );
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
