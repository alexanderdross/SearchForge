<?php
/**
 * JSON-LD structured data for SEO.
 *
 * Outputs a single unified @graph containing (on every page):
 * 1. Organization
 * 2. WebSite
 * 3. SiteNavigationElement (all site links)
 * 4. Product with AggregateRating
 *
 * Front page additionally:
 * 5. CollectionPage
 * 6. SoftwareApplication
 * 7. Article (hero highlight)
 *
 * Inner pages additionally:
 * 8. WebPage
 * 9. BreadcrumbList
 *
 * FAQPage is output separately in wp_footer (collected from sf_render_faq calls).
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

	$org_id  = $site_url . '/#organization';
	$site_id = $site_url . '/#website';

	// --- Nodes present on every page ---

	$organization = [
		'@type' => 'Organization',
		'@id'   => $org_id,
		'name'  => 'Dross:Media GmbH',
		'url'   => 'https://dross.net',
		'logo'  => [
			'@type'  => 'ImageObject',
			'url'    => $logo_url,
			'width'  => 512,
			'height' => 512,
		],
	];

	$website = [
		'@type'     => 'WebSite',
		'@id'       => $site_id,
		'name'      => 'SearchForge',
		'url'       => $site_url . '/',
		'publisher' => [ '@id' => $org_id ],
	];

	$nav_items = [
		'Home'            => '/',
		'Features'        => '/features/',
		'Pricing'         => '/pricing/',
		'Enterprise'      => '/enterprise/',
		'Bundle'          => '/bundle/',
		'Changelog'       => '/changelog/',
		'Documentation'   => '/docs/',
		'Getting Started' => '/docs/getting-started/',
		'Data Sources'    => '/docs/data-sources/',
		'Features Guide'  => '/docs/features/',
		'Export & Output' => '/docs/export-output/',
		'Developer Guide' => '/docs/developer/',
		'Integrations'    => '/docs/integrations/',
	];

	$nav_elements = [];
	foreach ( $nav_items as $name => $path ) {
		$nav_elements[] = [
			'@type' => 'SiteNavigationElement',
			'name'  => $name,
			'url'   => $site_url . $path,
		];
	}

	$site_navigation = [
		'@type'           => 'ItemList',
		'@id'             => $site_url . '/#site-navigation',
		'name'            => 'Site Navigation',
		'itemListElement' => $nav_elements,
	];

	$product = [
		'@type'           => 'Product',
		'@id'             => $site_url . '/#product',
		'name'            => 'SearchForge',
		'description'     => 'WordPress plugin that unifies SEO data from Google Search Console, Bing, GA4 and Trends into LLM-ready markdown briefs.',
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

	$graph = [ $organization, $website, $site_navigation, $product ];

	// --- Front page specific nodes ---

	if ( is_front_page() ) {
		$graph[] = [
			'@type'       => 'CollectionPage',
			'@id'         => $site_url . '/#collection-page',
			'name'        => 'SearchForge — Turn SEO Data into LLM-Ready Intelligence',
			'description' => 'SearchForge is a WordPress plugin that unifies Google Search Console, Bing, GA4, Trends, and Keyword Planner data into AI-ready markdown briefs. Free tier available.',
			'url'         => $site_url . '/',
			'isPartOf'    => [ '@id' => $site_id ],
			'publisher'   => [ '@id' => $org_id ],
		];

		$graph[] = [
			'@type'               => 'SoftwareApplication',
			'@id'                 => $site_url . '/#software',
			'name'                => 'SearchForge',
			'url'                 => $site_url . '/',
			'applicationCategory' => 'WebApplication',
			'operatingSystem'     => 'WordPress',
			'description'         => 'WordPress plugin that unifies SEO data from Google Search Console, Bing, GA4 and Trends into LLM-ready markdown briefs.',
			'screenshot'          => $logo_url,
			'offers'              => [
				[
					'@type'         => 'Offer',
					'price'         => '0',
					'priceCurrency' => 'EUR',
					'name'          => 'Free',
					'availability'  => 'https://schema.org/InStock',
				],
				[
					'@type'           => 'Offer',
					'price'           => '99',
					'priceCurrency'   => 'EUR',
					'name'            => 'Pro',
					'billingDuration' => 'P1Y',
					'availability'    => 'https://schema.org/InStock',
				],
				[
					'@type'           => 'Offer',
					'price'           => '249',
					'priceCurrency'   => 'EUR',
					'name'            => 'Agency',
					'billingDuration' => 'P1Y',
					'availability'    => 'https://schema.org/InStock',
				],
			],
			'author' => [ '@id' => $org_id ],
		];

		$graph[] = [
			'@type'            => 'Article',
			'@id'              => $site_url . '/#article',
			'headline'         => 'SEO Data, LLM-Ready.',
			'description'      => 'Connects Google Search Console, Bing Webmaster Tools, Google Business Profile, Bing Places, Google Keyword Planner, GA4 and Google Trends — continuously collecting your SEO data and turning it into structured markdown briefs for LLMs to design, optimize, and evolve your website. Perfect for vibe coding: instead of letting an LLM guess your content and information architecture, this plugin feeds it real, historically collected web data — boosting your new website\'s SEO, GEO, and AEO.',
			'url'              => $site_url . '/',
			'mainEntityOfPage' => [ '@id' => $site_url . '/#collection-page' ],
			'author'           => [ '@id' => $org_id ],
			'publisher'        => [ '@id' => $org_id ],
			'datePublished'    => '2026-01-15',
			'dateModified'     => '2026-03-08',
			'image'            => $logo_url,
		];
	} else {
		// --- Inner page nodes ---

		$meta    = sf_theme_get_page_meta();
		$page_url = sf_theme_current_url();

		$graph[] = [
			'@type'       => 'WebPage',
			'@id'         => $page_url . '#webpage',
			'name'        => $meta['title'],
			'description' => $meta['description'],
			'url'         => $page_url,
			'isPartOf'    => [ '@id' => $site_id ],
			'publisher'   => [ '@id' => $org_id ],
		];

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

	echo '<script type="application/ld+json">' . "\n";
	echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
	echo "\n" . '</script>' . "\n";
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
			static function ( array $faq ): array {
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

	echo '<script type="application/ld+json">' . "\n";
	echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
	echo "\n" . '</script>' . "\n";
}
add_action( 'wp_footer', 'sf_theme_output_faq_schema', 99 );
