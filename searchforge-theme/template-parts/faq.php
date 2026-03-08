<?php
/**
 * Template part: FAQ accordion.
 *
 * @package SearchForge_Theme
 */

$faqs = [
	[
		'q' => 'What data sources does SearchForge support?',
		'a' => 'SearchForge connects to 8 data sources: Google Search Console, Bing Webmaster Tools, Google Analytics 4, Google Keyword Planner, Google Trends, Google Business Profile, Bing Places for Business, and SerpApi (for AI visibility and competitor monitoring). The free tier includes GSC with a 10-page limit; Pro unlocks all sources.',
	],
	[
		'q' => 'Do I need API keys for all sources?',
		'a' => 'Google sources (GSC, GA4, Keyword Planner, Business Profile) use one-click OAuth — no API keys needed. Bing uses OAuth or an API key from Bing Webmaster Tools. Google Trends and competitor monitoring use SerpApi, which requires a separate API key (you provide your own).',
	],
	[
		'q' => 'How does the free tier work?',
		'a' => 'The free tier gives you Google Search Console data for up to 10 pages and 100 keywords, a basic SearchForge Score (overall number), and simple llms.txt generation. It\'s genuinely useful for small sites. No credit card required, no time limit.',
	],
	[
		'q' => 'What makes SearchForge different from Yoast or Rank Math?',
		'a' => 'Yoast and Rank Math focus on on-page SEO optimization (meta tags, readability, schema). SearchForge focuses on data synthesis — pulling real performance data from multiple sources and turning it into actionable, AI-ready markdown briefs. They complement each other perfectly.',
	],
	[
		'q' => 'How does AI Visibility Monitoring work?',
		'a' => 'SearchForge periodically queries your target keywords via SerpApi and checks if AI engines (Google AI Overviews, ChatGPT, Perplexity, Bing Copilot) cite your pages. It tracks citation rates over time and identifies gaps where you rank well but aren\'t cited by AI.',
	],
	[
		'q' => 'Can I use SearchForge with Claude Code or ChatGPT?',
		'a' => 'Yes — this is a core use case. Export per-page markdown briefs, then paste them into Claude Code or ChatGPT for AI-assisted content optimization. The briefs include all the context an LLM needs: rankings, traffic, competitor data, and specific recommendations.',
	],
	[
		'q' => 'What is llms.txt and why do I need it?',
		'a' => 'llms.txt is a standard file (like robots.txt) that helps AI crawlers understand your site. SearchForge auto-generates and maintains /llms.txt and /llms-full.txt, making your content more discoverable by AI engines like ChatGPT and Perplexity.',
	],
	[
		'q' => 'Is my data stored on your servers?',
		'a' => 'No. All data is stored in your WordPress database. SearchForge connects directly to Google, Bing, and other APIs from your server. We never see or store your SEO data. The only external call is license validation.',
	],
	[
		'q' => 'Can I cancel my Pro subscription anytime?',
		'a' => 'Yes. Cancel anytime from your account dashboard. Your Pro features remain active until the end of your billing period. After that, your site reverts to the free tier — no data is deleted.',
	],
];
?>

<section class="sf-section sf-section--light" id="faq">
	<div class="sf-container sf-container--narrow">
		<div class="sf-section__header">
			<h2>Frequently Asked Questions</h2>
		</div>

		<div class="sf-faq" role="list">
			<?php foreach ( $faqs as $i => $faq ) :
				$slug = sanitize_title( $faq['q'] );
			?>
				<div class="sf-faq__item" id="<?php echo esc_attr( $slug ); ?>" role="listitem">
					<button class="sf-faq__question" aria-expanded="false" aria-controls="faq-answer-<?php echo esc_attr( $i ); ?>" title="<?php echo esc_attr( $faq['q'] ); ?>">
						<span><?php echo esc_html( $faq['q'] ); ?></span>
						<span class="sf-faq__chevron" aria-hidden="true"></span>
					</button>
					<div class="sf-faq__answer" id="faq-answer-<?php echo esc_attr( $i ); ?>" hidden>
						<p><?php echo esc_html( $faq['a'] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<script type="application/ld+json">
<?php
echo wp_json_encode(
	[
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => array_map(
			function ( $faq ) {
				return [
					'@type' => 'Question',
					'name'  => $faq['q'],
					'acceptedAnswer' => [
						'@type' => 'Answer',
						'text'  => $faq['a'],
					],
				];
			},
			$faqs
		),
	],
	JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
);
?>
</script>
