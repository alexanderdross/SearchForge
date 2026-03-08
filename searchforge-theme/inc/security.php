<?php
/**
 * Security hardening for the theme.
 *
 * @package SearchForge_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add security headers.
 */
function sf_theme_security_headers(): void {
	if ( is_admin() ) {
		return;
	}

	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
}
add_action( 'send_headers', 'sf_theme_security_headers' );

/**
 * Remove WordPress version from RSS feeds.
 */
add_filter( 'the_generator', '__return_empty_string' );

/**
 * Disable XML-RPC.
 */
add_filter( 'xmlrpc_enabled', '__return_false' );
