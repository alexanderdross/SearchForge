<?php
/**
 * Performance optimizations.
 *
 * @package SearchForge_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Inline @font-face declarations and preload critical fonts.
 *
 * Eliminates the fonts.css → woff2 request chain by inlining @font-face
 * rules directly and preloading LCP-critical fonts so the browser can
 * start fetching them immediately without waiting for a CSS file.
 */
function sf_theme_inline_fonts(): void {
	$font_dir = esc_url( SF_THEME_URI . '/assets/fonts' );

	// Preload LCP-critical fonts (body text + primary heading weight).
	echo '<link rel="preload" href="' . $font_dir . '/inter-v20-latin-regular.woff2" as="font" type="font/woff2" crossorigin>' . "\n";
	echo '<link rel="preload" href="' . $font_dir . '/outfit-v15-latin-700.woff2" as="font" type="font/woff2" crossorigin>' . "\n";

	// Inline all @font-face declarations to avoid a chained CSS request.
	echo '<style id="sf-fonts-inline">';
	echo "@font-face{font-family:'Inter';font-style:normal;font-weight:400;font-display:swap;src:url('{$font_dir}/inter-v20-latin-regular.woff2') format('woff2')}";
	echo "@font-face{font-family:'Inter';font-style:normal;font-weight:500;font-display:swap;src:url('{$font_dir}/inter-v20-latin-500.woff2') format('woff2')}";
	echo "@font-face{font-family:'Inter';font-style:normal;font-weight:600;font-display:swap;src:url('{$font_dir}/inter-v20-latin-600.woff2') format('woff2')}";
	echo "@font-face{font-family:'Outfit';font-style:normal;font-weight:600;font-display:swap;src:url('{$font_dir}/outfit-v15-latin-600.woff2') format('woff2')}";
	echo "@font-face{font-family:'Outfit';font-style:normal;font-weight:700;font-display:swap;src:url('{$font_dir}/outfit-v15-latin-700.woff2') format('woff2')}";
	echo "@font-face{font-family:'JetBrains Mono';font-style:normal;font-weight:400;font-display:swap;src:url('{$font_dir}/jetbrains-mono-v24-latin-regular.woff2') format('woff2')}";
	echo '</style>' . "\n";
}
add_action( 'wp_head', 'sf_theme_inline_fonts', 1 );

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
