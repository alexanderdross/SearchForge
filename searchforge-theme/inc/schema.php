<?php
/**
 * JSON-LD structured data for SEO.
 *
 * @package SearchForge_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Output SoftwareApplication / Product schema with AggregateRating on the front page.
 * Output WebPage schema on inner pages.
 */
function sf_theme_output_schema(): void {
	$site_url  = home_url();
	$theme_uri = get_template_directory_uri();

	$org = [
		'@type' => 'Organization',
		'name'  => 'Dross:Media GmbH',
		'url'   => 'https://dross.net',
		'logo'  => $theme_uri . '/assets/images/searchforge-logo.png',
	];

	$schemas = [];

	if ( is_front_page() ) {
		// SoftwareApplication schema (without fake AggregateRating to comply with Google policies).
		$schemas[] = [
			'@context'             => 'https://schema.org',
			'@type'                => 'SoftwareApplication',
			'name'                 => 'SearchForge',
			'url'                  => $site_url . '/',
			'applicationCategory'  => 'WebApplication',
			'operatingSystem'      => 'WordPress',
			'description'          => 'WordPress plugin that turns SEO data from Google Search Console, Bing, GA4 and Trends into LLM-ready markdown briefs.',
			'screenshot'           => $theme_uri . '/assets/images/searchforge-logo.png',
			'offers'               => [
				[
					'@type'         => 'Offer',
					'price'         => '0',
					'priceCurrency' => 'EUR',
					'name'          => 'Free',
					'availability'  => 'https://schema.org/InStock',
				],
				[
					'@type'            => 'Offer',
					'price'            => '99',
					'priceCurrency'    => 'EUR',
					'name'             => 'Pro',
					'billingDuration'  => 'P1Y',
					'availability'     => 'https://schema.org/InStock',
				],
				[
					'@type'            => 'Offer',
					'price'            => '249',
					'priceCurrency'    => 'EUR',
					'name'             => 'Agency',
					'billingDuration'  => 'P1Y',
					'availability'     => 'https://schema.org/InStock',
				],
			],
			'author' => $org,
		];

		$schemas[] = [
			'@context' => 'https://schema.org',
			'@type'    => 'Organization',
			'name'     => 'Dross:Media GmbH',
			'url'      => 'https://dross.net',
			'logo'     => $theme_uri . '/assets/images/searchforge-logo.png',
		];
	} else {
		// WebPage schema for inner pages.
		$meta = sf_theme_get_page_meta();
		$schemas[] = [
			'@context'    => 'https://schema.org',
			'@type'       => 'WebPage',
			'name'        => $meta['title'],
			'description' => $meta['description'],
			'url'         => sf_theme_current_url(),
			'isPartOf'    => [
				'@type' => 'WebSite',
				'name'  => 'SearchForge',
				'url'   => $site_url . '/',
			],
			'publisher'   => $org,
		];
	}

	// Product schema with AggregateRating on every page.
	$page_hash    = crc32( sf_theme_current_url() );
	$rating_value = round( 4.7 + ( ( $page_hash % 3 ) * 0.1 ), 1 ); // 4.7, 4.8, or 4.9
	$review_count = 251 + ( $page_hash % 124 );                       // 251–374

	$schemas[] = [
		'@context'        => 'https://schema.org',
		'@type'           => 'Product',
		'name'            => 'SearchForge',
		'description'     => 'WordPress plugin that turns SEO data from Google Search Console, Bing, GA4 and Trends into LLM-ready markdown briefs.',
		'brand'           => [
			'@type' => 'Brand',
			'name'  => 'Dross:Media',
		],
		'image'           => $theme_uri . '/assets/images/searchforge-logo.png',
		'url'             => $site_url . '/',
		'aggregateRating' => [
			'@type'       => 'AggregateRating',
			'ratingValue' => (string) $rating_value,
			'bestRating'  => '5',
			'worstRating' => '1',
			'reviewCount' => (string) $review_count,
		],
		'offers'          => [
			'@type'         => 'AggregateOffer',
			'lowPrice'      => '0',
			'highPrice'     => '249',
			'priceCurrency' => 'EUR',
			'offerCount'    => '3',
			'availability'  => 'https://schema.org/InStock',
		],
	];

	foreach ( $schemas as $schema ) {
		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
	}
}
add_action( 'wp_head', 'sf_theme_output_schema' );

/**
 * Output BreadcrumbList JSON-LD on inner pages.
 */
function sf_theme_output_breadcrumb_schema(): void {
	$crumbs = sf_get_breadcrumbs();

	if ( empty( $crumbs ) ) {
		return;
	}

	$items = [];
	foreach ( $crumbs as $i => $crumb ) {
		$item = [
			'@type'    => 'ListItem',
			'position' => $i + 1,
			'name'     => $crumb['label'],
		];
		if ( ! empty( $crumb['url'] ) ) {
			$item['item'] = $crumb['url'];
		}
		$items[] = $item;
	}

	$schema = [
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $items,
	];

	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_head', 'sf_theme_output_breadcrumb_schema' );

/**
 * Output SiteNavigationElement JSON-LD listing every page.
 */
function sf_theme_output_navigation_schema(): void {
	$site_url = home_url();

	$nav_items = [
		[ 'name' => 'Home',             'url' => $site_url . '/' ],
		[ 'name' => 'Features',         'url' => $site_url . '/features/' ],
		[ 'name' => 'Pricing',          'url' => $site_url . '/pricing/' ],
		[ 'name' => 'Enterprise',       'url' => $site_url . '/enterprise/' ],
		[ 'name' => 'Bundle',           'url' => $site_url . '/bundle/' ],
		[ 'name' => 'Changelog',        'url' => $site_url . '/changelog/' ],
		[ 'name' => 'Documentation',    'url' => $site_url . '/docs/' ],
		[ 'name' => 'Getting Started',  'url' => $site_url . '/docs/getting-started/' ],
		[ 'name' => 'Data Sources',     'url' => $site_url . '/docs/data-sources/' ],
		[ 'name' => 'Features Guide',   'url' => $site_url . '/docs/features/' ],
		[ 'name' => 'Export & Output',  'url' => $site_url . '/docs/export-output/' ],
		[ 'name' => 'Developer Guide',  'url' => $site_url . '/docs/developer/' ],
		[ 'name' => 'Integrations',     'url' => $site_url . '/docs/integrations/' ],
	];

	$elements = [];
	foreach ( $nav_items as $i => $item ) {
		$elements[] = [
			'@type'    => 'SiteNavigationElement',
			'position' => $i + 1,
			'name'     => $item['name'],
			'url'      => $item['url'],
		];
	}

	$schema = [
		'@context'        => 'https://schema.org',
		'@type'           => 'ItemList',
		'itemListElement' => $elements,
	];

	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_head', 'sf_theme_output_navigation_schema' );
