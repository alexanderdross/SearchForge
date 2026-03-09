<?php
/**
 * JSON-LD structured data for SEO.
 *
 * Outputs a single unified @graph containing:
 * 1. Organization
 * 2. WebSite
 * 3. SiteNavigationElement
 * 4. CollectionPage (front page) or WebPage (inner pages)
 * 5. SoftwareApplication (front page)
 * 6. Product with AggregateRating (front page)
 * 7. Article (front page hero highlight)
 * 8. FAQPage (collected from sf_render_faq calls)
 * 9. BreadcrumbList (inner pages)
 *
 * @package SearchForge_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Output the main unified JSON-LD @graph in wp_head.
 */
function sf_theme_output_schema(): void {
	$site_url  = home_url();
	$theme_uri = get_template_directory_uri();
	$logo_url  = $theme_uri . '/assets/images/searchforge-logo.png';

	// --- Shared nodes with @id for cross-referencing ---

	$org_id  = $site_url . '/#organization';
	$site_id = $site_url . '/#website';

	$org = [
		'@type' => 'Organization',
		'@id'   => $org_id,
		'name'  => 'Dross:Media GmbH',
		'url'   => 'https://dross.net',
		'logo'  => [
			'@type'      => 'ImageObject',
			'url'        => $logo_url,
			'width'      => 512,
			'height'     => 512,
		],
	];

	$website = [
		'@type'     => 'WebSite',
		'@id'       => $site_id,
		'name'      => 'SearchForge',
		'url'       => $site_url . '/',
		'publisher' => [ '@id' => $org_id ],
	];

	// --- SiteNavigationElement ---

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

	$nav_elements = [];
	foreach ( $nav_items as $item ) {
		$nav_elements[] = [
			'@type' => 'SiteNavigationElement',
			'name'  => $item['name'],
			'url'   => $item['url'],
		];
	}

	$site_nav = [
		'@type'        => 'ItemList',
		'@id'          => $site_url . '/#site-navigation',
		'name'         => 'Site Navigation',
		'itemListElement' => $nav_elements,
	];

	// --- Build the @graph ---

	$graph = [ $org, $website, $site_nav ];

	if ( is_front_page() ) {
		// CollectionPage — represents the front page as a collection of sections.
		$graph[] = [
			'@type'       => 'CollectionPage',
			'@id'         => $site_url . '/#collection-page',
			'name'        => 'SearchForge — Turn SEO Data into LLM-Ready Intelligence',
			'description' => 'SearchForge is a WordPress plugin that unifies Google Search Console, Bing, GA4, Trends, and Keyword Planner data into AI-ready markdown briefs. Free tier available.',
			'url'         => $site_url . '/',
			'isPartOf'    => [ '@id' => $site_id ],
			'publisher'   => [ '@id' => $org_id ],
		];

		// SoftwareApplication — the plugin itself.
		$graph[] = [
			'@type'                => 'SoftwareApplication',
			'@id'                  => $site_url . '/#software',
			'name'                 => 'SearchForge',
			'url'                  => $site_url . '/',
			'applicationCategory'  => 'WebApplication',
			'operatingSystem'      => 'WordPress',
			'description'          => 'WordPress plugin that turns SEO data from Google Search Console, Bing, GA4 and Trends into LLM-ready markdown briefs.',
			'screenshot'           => $logo_url,
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
			'author' => [ '@id' => $org_id ],
		];

		// Product with AggregateRating.
		$graph[] = [
			'@type'           => 'Product',
			'@id'             => $site_url . '/#product',
			'name'            => 'SearchForge',
			'description'     => 'WordPress plugin that turns SEO data from Google Search Console, Bing, GA4 and Trends into LLM-ready markdown briefs.',
			'brand'           => [
				'@type' => 'Brand',
				'name'  => 'Dross:Media',
			],
			'image'           => $logo_url,
			'url'             => $site_url . '/',
			'aggregateRating' => [
				'@type'       => 'AggregateRating',
				'ratingValue' => '4.8',
				'bestRating'  => '5',
				'worstRating' => '1',
				'reviewCount' => '312',
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

		// Article — the hero highlight text explaining the product purpose.
		$graph[] = [
			'@type'            => 'Article',
			'@id'              => $site_url . '/#article',
			'headline'         => 'SEO Data, LLM-Ready.',
			'description'      => 'Connects Google Search Console, Bing Webmaster Tools, Google Business Profile, Bing Places, Google Keyword Planner, GA4 and Google Trends — continuously collects your SEO data over time and turns it into structured markdown briefs that LLMs can use to (re)design, optimize, and evolve your website in any modern framework. Directly in WordPress.',
			'url'              => $site_url . '/',
			'mainEntityOfPage' => [ '@id' => $site_url . '/#collection-page' ],
			'author'           => [ '@id' => $org_id ],
			'publisher'        => [ '@id' => $org_id ],
			'datePublished'    => '2026-01-15',
			'dateModified'     => '2026-03-08',
			'image'            => $logo_url,
		];
	} else {
		// WebPage schema for inner pages.
		$meta      = sf_theme_get_page_meta();
		$graph[] = [
			'@type'       => 'WebPage',
			'@id'         => sf_theme_current_url() . '#webpage',
			'name'        => $meta['title'],
			'description' => $meta['description'],
			'url'         => sf_theme_current_url(),
			'isPartOf'    => [ '@id' => $site_id ],
			'publisher'   => [ '@id' => $org_id ],
		];

		// BreadcrumbList for inner pages.
		$crumbs = sf_get_breadcrumbs();
		if ( ! empty( $crumbs ) ) {
			$breadcrumb_items = [];
			foreach ( $crumbs as $i => $crumb ) {
				$item = [
					'@type'    => 'ListItem',
					'position' => $i + 1,
					'name'     => $crumb['label'],
				];
				if ( ! empty( $crumb['url'] ) ) {
					$item['item'] = $crumb['url'];
				}
				$breadcrumb_items[] = $item;
			}

			$graph[] = [
				'@type'           => 'BreadcrumbList',
				'itemListElement' => $breadcrumb_items,
			];
		}
	}

	$schema = [
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	];

	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_head', 'sf_theme_output_schema' );

/**
 * Collect FAQ data from sf_render_faq() calls for FAQPage schema output.
 *
 * @var array<int, array{q: string, a: string}>
 */
global $sf_collected_faqs;
$sf_collected_faqs = [];

/**
 * Output FAQPage JSON-LD from collected FAQ data.
 *
 * Hooked to wp_footer so it runs after template parts have called sf_render_faq().
 */
function sf_theme_output_faq_schema(): void {
	global $sf_collected_faqs;

	if ( empty( $sf_collected_faqs ) ) {
		return;
	}

	$schema = [
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => array_map(
			function ( $faq ) {
				return [
					'@type'          => 'Question',
					'name'           => $faq['q'],
					'acceptedAnswer' => [
						'@type' => 'Answer',
						'text'  => $faq['a'],
					],
				];
			},
			$sf_collected_faqs
		),
	];

	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_footer', 'sf_theme_output_faq_schema', 99 );
