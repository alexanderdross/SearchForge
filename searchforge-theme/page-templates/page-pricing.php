<?php
/**
 * Template Name: Pricing
 *
 * @package SearchForge_Theme
 */

get_header();
?>

<section class="sf-section sf-section--dark sf-hero" style="padding: var(--space-3xl) 0;">
	<div class="sf-container" style="text-align: center;">
		<h1><span class="sf-gradient-text">Simple, Transparent Pricing</span></h1>
		<p class="sf-text--inverse-muted" style="font-size: 1.25rem;">
			Start free. Upgrade when you need more power.
		</p>
	</div>
</section>

<?php get_template_part( 'template-parts/pricing' ); ?>

<!-- Detailed Feature Comparison -->
<section class="sf-section" id="compare">
	<div class="sf-container">
		<div class="sf-section__header">
			<h2>Full Feature Comparison</h2>
		</div>

		<div class="sf-comparison-table-wrapper">
			<table class="sf-comparison-table">
				<caption class="screen-reader-text"><?php esc_html_e( 'Feature comparison across SearchForge pricing tiers', 'searchforge-theme' ); ?></caption>
				<thead>
					<tr>
						<th scope="col">Feature</th>
						<th scope="col">Free</th>
						<th scope="col">Pro &euro;99/yr</th>
						<th scope="col">Agency &euro;249/yr</th>
						<th scope="col">Enterprise &euro;599/yr</th>
					</tr>
				</thead>
				<tbody>
					<tr><th colspan="5" scope="colgroup" style="background: var(--sf-bg-light); font-weight: 600;">Data Sources</th></tr>
					<tr><th scope="row">Google Search Console</th><td>10 pages</td><td>Unlimited</td><td>Unlimited</td><td>Unlimited</td></tr>
					<tr><th scope="row">Bing Webmaster Tools</th><td>&mdash;</td><td>Unlimited</td><td>Unlimited</td><td>Unlimited</td></tr>
					<tr><th scope="row">Google Analytics 4</th><td>&mdash;</td><td>Unlimited</td><td>Unlimited</td><td>Unlimited</td></tr>
					<tr><th scope="row">Keyword Planner</th><td>&mdash;</td><td>Unlimited</td><td>Unlimited</td><td>Unlimited</td></tr>
					<tr><th scope="row">Google Trends</th><td>&mdash;</td><td>Unlimited</td><td>Unlimited</td><td>Unlimited</td></tr>
					<tr><th scope="row">Google Business Profile</th><td>&mdash;</td><td>1 location</td><td>10 locations</td><td>Unlimited</td></tr>
					<tr><th scope="row">Bing Places</th><td>&mdash;</td><td>1 location</td><td>10 locations</td><td>Unlimited</td></tr>
					<tr><th scope="row">AI Visibility Monitor</th><td>&mdash;</td><td>20 queries/mo</td><td>200 queries/mo</td><td>Unlimited</td></tr>
					<tr><th scope="row">Competitor Intelligence</th><td>&mdash;</td><td>10 keywords/mo</td><td>100 keywords/mo</td><td>Unlimited</td></tr>

					<tr><th colspan="5" scope="colgroup" style="background: var(--sf-bg-light); font-weight: 600;">Analysis &amp; Intelligence</th></tr>
					<tr><th scope="row">SearchForge Score</th><td>Overall only</td><td>Full breakdown</td><td>Full breakdown</td><td>Full breakdown</td></tr>
					<tr><th scope="row">Content Gap Analysis</th><td>Top 3</td><td>Unlimited</td><td>Unlimited</td><td>Unlimited</td></tr>
					<tr><th scope="row">AI Content Briefs</th><td>&mdash;</td><td>10/mo</td><td>50/mo</td><td>Unlimited</td></tr>
					<tr><th scope="row">Keyword Clustering</th><td>&mdash;</td><td>&#10003;</td><td>&#10003;</td><td>&#10003;</td></tr>
					<tr><th scope="row">Cannibalization Detection</th><td>&mdash;</td><td>&#10003;</td><td>&#10003;</td><td>&#10003;</td></tr>
					<tr><th scope="row">Content Decay Alerts</th><td>&mdash;</td><td>Email</td><td>Email + Slack</td><td>All channels</td></tr>

					<tr><th colspan="5" scope="colgroup" style="background: var(--sf-bg-light); font-weight: 600;">Export &amp; Output</th></tr>
					<tr><th scope="row">Markdown Export</th><td>GSC only</td><td>All sources</td><td>All sources</td><td>All sources</td></tr>
					<tr><th scope="row">Combined Master Brief</th><td>&mdash;</td><td>Per page</td><td>Per page</td><td>Per page</td></tr>
					<tr><th scope="row">llms.txt</th><td>Basic</td><td>Advanced</td><td>Advanced</td><td>Advanced</td></tr>
					<tr><th scope="row">WP-CLI</th><td>&mdash;</td><td>&#10003;</td><td>Multi-site</td><td>Multi-site</td></tr>
					<tr><th scope="row">REST API</th><td>&mdash;</td><td>Read-only</td><td>Full CRUD</td><td>Full CRUD</td></tr>
					<tr><th scope="row">GitHub / GitLab Push</th><td>&mdash;</td><td>&mdash;</td><td>Auto-push</td><td>Auto-push</td></tr>
					<tr><th scope="row">Scheduled Exports</th><td>&mdash;</td><td>&mdash;</td><td>Email, cloud</td><td>Email, cloud</td></tr>
					<tr><th scope="row">White-label Reports</th><td>&mdash;</td><td>&mdash;</td><td>PDF/HTML</td><td>PDF/HTML</td></tr>

					<tr><th colspan="5" scope="colgroup" style="background: var(--sf-bg-light); font-weight: 600;">History &amp; Monitoring</th></tr>
					<tr><th scope="row">Data Retention</th><td>30 days</td><td>12 months</td><td>24 months</td><td>24 months</td></tr>
					<tr><th scope="row">Historical Snapshots</th><td>&mdash;</td><td>Weekly</td><td>Daily</td><td>Daily</td></tr>
					<tr><th scope="row">YoY Comparison</th><td>&mdash;</td><td>&#10003;</td><td>&#10003;</td><td>&#10003;</td></tr>
					<tr><th scope="row">Weekly Digest Email</th><td>&mdash;</td><td>Single site</td><td>All sites</td><td>All sites</td></tr>
					<tr><th scope="row">Slack / Discord Alerts</th><td>&mdash;</td><td>&mdash;</td><td>&#10003;</td><td>&#10003;</td></tr>

					<tr><th colspan="5" scope="colgroup" style="background: var(--sf-bg-light); font-weight: 600;">Scale &amp; Collaboration</th></tr>
					<tr><th scope="row">Sites</th><td>1</td><td>1</td><td>10</td><td>Unlimited</td></tr>
					<tr><th scope="row">Team Members</th><td>1</td><td>3</td><td>Unlimited</td><td>Unlimited</td></tr>
					<tr><td>Client Portal</td><td>&mdash;</td><td>&mdash;</td><td>&#10003;</td><td>&#10003;</td></tr>
					<tr><td>CacheWarmer Integration</td><td>&mdash;</td><td>Manual</td><td>Auto-trigger</td><td>Auto-trigger</td></tr>
					<tr><td>Audit Log</td><td>&mdash;</td><td>&mdash;</td><td>&mdash;</td><td>&#10003;</td></tr>
					<tr><td>Priority Support</td><td>&mdash;</td><td>&mdash;</td><td>&mdash;</td><td>&#10003;</td></tr>
				</tbody>
			</table>
		</div>
	</div>
</section>

<!-- Lifetime Deals -->
<section class="sf-section sf-section--light">
	<div class="sf-container sf-container--narrow" style="text-align: center;">
		<h2>Lifetime Deals Available</h2>
		<p class="sf-text--muted" style="margin-bottom: var(--space-xl);">Pay once, use forever. No recurring fees.</p>
		<div class="sf-grid sf-grid--2">
			<div class="sf-card sf-card--bordered" style="text-align: center;">
				<h3>Lifetime Pro</h3>
				<p style="font-size: 2.5rem; font-family: 'Outfit', sans-serif; font-weight: 700; margin: var(--space-md) 0;">&euro;249</p>
				<p class="sf-text--muted">One-time payment. All Pro features forever for 1 site.</p>
				<a href="<?php echo esc_url( home_url( '/checkout/?tier=lifetime-pro' ) ); ?>" class="sf-btn sf-btn--primary" style="margin-top: var(--space-md);" rel="noopener">Get Lifetime Pro</a>
			</div>
			<div class="sf-card sf-card--bordered" style="text-align: center;">
				<h3>Lifetime Agency</h3>
				<p style="font-size: 2.5rem; font-family: 'Outfit', sans-serif; font-weight: 700; margin: var(--space-md) 0;">&euro;599</p>
				<p class="sf-text--muted">One-time payment. All Agency features forever for 10 sites.</p>
				<a href="<?php echo esc_url( home_url( '/checkout/?tier=lifetime-agency' ) ); ?>" class="sf-btn sf-btn--primary" style="margin-top: var(--space-md);" rel="noopener">Get Lifetime Agency</a>
			</div>
		</div>
	</div>
</section>

<!-- Bundle -->
<?php get_template_part( 'template-parts/cachewarmer-bundle' ); ?>

<!-- FAQ -->
<section class="sf-section" id="faq">
	<div class="sf-container sf-container--narrow">
		<div class="sf-section__header">
			<h2>Pricing FAQ</h2>
		</div>

		<div class="sf-faq" role="list">
			<?php
			$pricing_faqs = [
				[ 'q' => 'Can I try Pro before buying?',                  'a' => 'Yes. Every new installation gets a 14-day Pro trial with all features unlocked. No credit card required.' ],
				[ 'q' => 'What payment methods do you accept?',           'a' => 'We accept all major credit cards (Visa, Mastercard, Amex) and SEPA direct debit via Stripe. All transactions are processed securely by Stripe.' ],
				[ 'q' => 'Can I upgrade or downgrade at any time?',       'a' => 'Yes. Upgrade immediately and get pro-rated credit. Downgrade at the end of your billing period. No lock-in.' ],
				[ 'q' => 'What happens when my subscription expires?',    'a' => 'Your site reverts to the free tier. All your data is preserved, but Pro-only features become read-only. You can re-subscribe anytime to regain full access.' ],
				[ 'q' => 'Do you offer refunds?',                         'a' => 'Yes. 30-day money-back guarantee, no questions asked. If SearchForge isn\'t right for you, we\'ll refund your payment in full.' ],
				[ 'q' => 'Is there a development/staging license?',       'a' => 'Yes. Development licenses are free and include Enterprise features, restricted to localhost, *.local, *.dev, and *.test domains.' ],
				[ 'q' => 'Do you offer discounts for non-profits?',       'a' => 'Yes. Contact us at support@drossmedia.de with proof of non-profit status for a 50% discount on any tier.' ],
			];
			foreach ( $pricing_faqs as $i => $faq ) :
				$slug = sanitize_title( $faq['q'] );
			?>
				<div class="sf-faq__item" id="<?php echo esc_attr( $slug ); ?>" role="listitem">
					<button class="sf-faq__question" aria-expanded="false" aria-controls="pricing-faq-<?php echo esc_attr( $i ); ?>" title="<?php echo esc_attr( $faq['q'] ); ?>">
						<span><?php echo esc_html( $faq['q'] ); ?></span>
						<span class="sf-faq__chevron" aria-hidden="true"></span>
					</button>
					<div class="sf-faq__answer" id="pricing-faq-<?php echo esc_attr( $i ); ?>" hidden>
						<p><?php echo esc_html( $faq['a'] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<noscript><style>.sf-faq__answer[hidden] { display: block !important; }</style></noscript>
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
			$pricing_faqs
		),
	],
	JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
);
?>
</script>

<?php
get_footer();
