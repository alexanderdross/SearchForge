<?php
/**
 * SearchForge Theme — functions and definitions.
 *
 * @package SearchForge_Theme
 */

defined( 'ABSPATH' ) || exit;

define( 'SF_THEME_VERSION', '1.2.0' );
define( 'SF_THEME_DIR', get_template_directory() );
define( 'SF_THEME_URI', get_template_directory_uri() );

/**
 * Theme setup.
 */
function sf_theme_setup(): void {
	add_theme_support( 'title-tag' );
	add_theme_support( 'custom-logo', [
		'height'      => 40,
		'width'       => 180,
		'flex-height' => true,
		'flex-width'  => true,
	] );
	add_theme_support( 'html5', [ 'search-form', 'gallery', 'caption', 'style', 'script' ] );

	register_nav_menus( [
		'primary' => __( 'Primary Navigation', 'searchforge-theme' ),
		'footer'  => __( 'Footer Navigation', 'searchforge-theme' ),
	] );
}
add_action( 'after_setup_theme', 'sf_theme_setup' );

/**
 * Enqueue styles and scripts.
 */
function sf_theme_enqueue_assets(): void {
	// Fonts — self-hosted for GDPR compliance (no Google CDN requests).
	wp_enqueue_style(
		'sf-fonts',
		SF_THEME_URI . '/assets/fonts/fonts.css',
		[],
		SF_THEME_VERSION
	);

	// Stylesheets — all depend on variables only (allows parallel loading).
	$css_files = [ 'variables', 'base', 'components', 'sections', 'responsive' ];
	foreach ( $css_files as $file ) {
		wp_enqueue_style(
			"sf-{$file}",
			SF_THEME_URI . "/assets/css/{$file}.css",
			$file === 'variables' ? [ 'sf-fonts' ] : [ 'sf-variables' ],
			SF_THEME_VERSION
		);
	}

	// Scripts — conditionally load only what the current page needs.
	wp_enqueue_script( 'sf-navigation', SF_THEME_URI . '/assets/js/navigation.js', [], SF_THEME_VERSION, [ 'strategy' => 'defer', 'in_footer' => true ] );
	wp_enqueue_script( 'sf-animations', SF_THEME_URI . '/assets/js/animations.js', [], SF_THEME_VERSION, [ 'strategy' => 'defer', 'in_footer' => true ] );

	if ( is_front_page() ) {
		wp_enqueue_script( 'sf-faq', SF_THEME_URI . '/assets/js/faq.js', [], SF_THEME_VERSION, [ 'strategy' => 'defer', 'in_footer' => true ] );
	}

	if ( is_page_template( array_map( fn( $t ) => "page-templates/page-docs-{$t}.php", [ 'getting-started', 'data-sources', 'features', 'export-output', 'developer', 'integrations' ] ) ) ) {
		wp_enqueue_script( 'sf-doc-nav', SF_THEME_URI . '/assets/js/doc-nav.js', [], SF_THEME_VERSION, [ 'strategy' => 'defer', 'in_footer' => true ] );
	}
}
add_action( 'wp_enqueue_scripts', 'sf_theme_enqueue_assets' );

/**
 * Remove unnecessary WordPress head output.
 */
function sf_theme_cleanup_head(): void {
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
}
add_action( 'after_setup_theme', 'sf_theme_cleanup_head' );

/**
 * Redirect legal/contact pages to dross.net.
 */
function sf_theme_legal_redirects(): void {
	$redirects = [
		'/imprint/'  => 'https://dross.net/imprint/',
		'/privacy/'  => 'https://dross.net/privacy-policy/',
		'/contact/'  => 'https://dross.net/contact/?topic=searchforge',
	];

	$raw_uri    = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	$parsed_path = wp_parse_url( $raw_uri, PHP_URL_PATH );
	if ( ! is_string( $parsed_path ) ) {
		return;
	}
	$path = trailingslashit( $parsed_path );

	if ( isset( $redirects[ $path ] ) ) {
		wp_redirect( $redirects[ $path ], 301 );
		exit;
	}
}
add_action( 'template_redirect', 'sf_theme_legal_redirects' );

/**
 * Build breadcrumb trail for the current page.
 *
 * @return array<int, array{label: string, url?: string, external?: bool}>
 */
function sf_get_breadcrumbs(): array {
	if ( is_front_page() ) {
		return [];
	}

	$crumbs = [
		[
			'label'    => 'Dross:Media',
			'url'      => 'https://dross.net/media/',
			'external' => true,
		],
		[
			'label' => 'SearchForge',
			'url'   => home_url( '/' ),
		],
	];

	if ( is_page() ) {
		$post      = get_queried_object();
		$ancestors = array_reverse( get_post_ancestors( $post ) );

		foreach ( $ancestors as $ancestor_id ) {
			$crumbs[] = [
				'label' => get_the_title( $ancestor_id ),
				'url'   => get_permalink( $ancestor_id ),
			];
		}

		$crumbs[] = [ 'label' => get_the_title( $post ) ];
	} elseif ( is_404() ) {
		$crumbs[] = [ 'label' => '404' ];
	} else {
		$crumbs[] = [ 'label' => get_the_title() ];
	}

	return $crumbs;
}

/**
 * Render doc sidebar navigation from section definitions.
 *
 * @param array<int, array{id: string, label: string}> $sections Section ID and label pairs.
 */
function sf_doc_sidebar( array $sections ): void {
	echo '<aside class="sf-doc-sidebar" aria-label="' . esc_attr__( 'On this page', 'searchforge-theme' ) . '"><p class="sf-doc-sidebar__title">On this page</p><ul class="sf-doc-nav">';
	foreach ( $sections as $section ) {
		$title_attr = '';
		if ( ! empty( $section['title'] ) ) {
			$title_attr = sprintf( ' title="%s"', esc_attr( $section['title'] ) );
		}
		printf(
			'<li><a class="sf-doc-nav__link" href="#%s"%s>%s</a></li>',
			esc_attr( $section['id'] ),
			$title_attr,
			esc_html( $section['label'] )
		);
	}
	echo '</ul></aside>';
}

/**
 * Default navigation fallback when no menu is assigned.
 */
if ( ! function_exists( 'sf_default_nav' ) ) {
	function sf_default_nav(): void {
		echo '<ul class="sf-nav-list">';
		echo '<li><a href="' . esc_url( home_url( '/#features' ) ) . '" title="SearchForge Features — SEO Score, AI Briefs, Keyword Clustering &amp; More">Features</a></li>';
		echo '<li><a href="' . esc_url( home_url( '/pricing/' ) ) . '" title="SearchForge Pricing — Free, Pro &amp; Agency Plans">Pricing</a></li>';
		echo '<li><a href="' . esc_url( home_url( '/docs/' ) ) . '" title="SearchForge Documentation — Setup, Configuration &amp; API Reference">Docs</a></li>';
		echo '<li><a href="' . esc_url( home_url( '/changelog/' ) ) . '" title="SearchForge Changelog — Version History &amp; Release Notes">Changelog</a></li>';
		echo '<li><a href="' . esc_url( home_url( '/enterprise/' ) ) . '" title="SearchForge Enterprise — Multi-Site, White-Label &amp; Priority Support">Enterprise</a></li>';
		echo '</ul>';
	}
}

// Load includes.
require_once SF_THEME_DIR . '/inc/seo-meta.php';
require_once SF_THEME_DIR . '/inc/schema.php';
require_once SF_THEME_DIR . '/inc/security.php';
require_once SF_THEME_DIR . '/inc/performance.php';
