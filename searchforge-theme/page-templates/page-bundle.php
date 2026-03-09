<?php
/**
 * Template Name: Bundle
 *
 * @package SearchForge_Theme
 */

get_header();
?>

<section class="sf-section sf-section--dark sf-hero sf-page-hero">
	<div class="sf-container">
		<h1><span class="sf-gradient-text">SearchForge + CacheWarmer Bundle</span></h1>
		<p class="sf-text--inverse-muted sf-page-hero__subtitle sf-page-hero__subtitle--narrow">
			SEO intelligence meets cache warming. Two plugins, one price, 15% off.
		</p>
		<div class="sf-hero__actions sf-page-hero__actions">
			<a href="<?php echo esc_url( home_url( '/checkout/?tier=bundle' ) ); ?>" class="sf-btn sf-btn--primary sf-btn--lg" rel="noopener" title="SearchForge + CacheWarmer Bundle — &euro;169/yr, Save 15%">Get the Bundle &euro;169/yr</a>
			<a href="#how-it-works" class="sf-btn sf-btn--outline sf-btn--lg" title="How the SearchForge + CacheWarmer Bundle Works Together">How It Works</a>
		</div>
	</div>
</section>

<!-- How it works -->
<section class="sf-section" id="how-it-works">
	<div class="sf-container">
		<div class="sf-section__header">
			<h2>Better Together</h2>
			<p class="sf-text--muted">SearchForge finds what needs fixing. CacheWarmer makes the fix go live instantly.</p>
		</div>

		<div class="sf-steps">
			<div class="sf-step">
				<span class="sf-step__number">1</span>
				<h3 class="sf-step__title">Detect</h3>
				<p class="sf-step__desc">SearchForge monitors rankings, content decay, and AI visibility across all your data sources.</p>
			</div>
			<div class="sf-step">
				<span class="sf-step__number">2</span>
				<h3 class="sf-step__title">Update</h3>
				<p class="sf-step__desc">Act on AI content briefs and keyword clusters. Update pages that are losing traffic or rankings.</p>
			</div>
			<div class="sf-step">
				<span class="sf-step__number">3</span>
				<h3 class="sf-step__title">Warm</h3>
				<p class="sf-step__desc">CacheWarmer pushes updated pages to CDNs, search engines, and social platforms automatically.</p>
			</div>
		</div>
	</div>
</section>

<!-- What's included -->
<section class="sf-section sf-section--light">
	<div class="sf-container">
		<div class="sf-section__header">
			<h2>What&rsquo;s Included</h2>
		</div>

		<div class="sf-grid sf-grid--2">
			<div class="sf-card sf-card--bordered">
				<h3 class="sf-card__title">SearchForge Pro</h3>
				<p class="sf-card__desc">Full SEO intelligence for one WordPress site.</p>
				<ul class="sf-bundle-features">
					<li>&#10003; Google Search Console &amp; Bing Webmaster</li>
					<li>&#10003; Google Analytics 4 &amp; Keyword Planner</li>
					<li>&#10003; Google Trends &amp; Business Profile</li>
					<li>&#10003; AI Visibility Monitor (20 queries/mo)</li>
					<li>&#10003; AI Content Briefs &amp; Keyword Clustering</li>
					<li>&#10003; Content Decay Alerts</li>
					<li>&#10003; Markdown Export &amp; llms.txt</li>
					<li>&#10003; 12-month data retention</li>
				</ul>
				<p class="sf-text--muted sf-section__fine-print">Standalone: &euro;99/yr</p>
			</div>
			<div class="sf-card sf-card--bordered">
				<h3 class="sf-card__title">CacheWarmer Premium</h3>
				<p class="sf-card__desc">Automated cache warming for one WordPress site.</p>
				<ul class="sf-bundle-features">
					<li>&#10003; All 11 warming targets</li>
					<li>&#10003; CDN warming (Cloudflare, Akamai, Imperva)</li>
					<li>&#10003; Search engines (Google, Bing, IndexNow)</li>
					<li>&#10003; Social platforms (Facebook, LinkedIn, X)</li>
					<li>&#10003; Up to 10,000 URLs per job</li>
					<li>&#10003; 50 warming jobs daily</li>
					<li>&#10003; Automatic scheduler</li>
					<li>&#10003; Smart warming (diff-detection)</li>
				</ul>
				<p class="sf-text--muted sf-section__fine-print">Standalone: &euro;99/yr</p>
			</div>
		</div>
	</div>
</section>

<!-- Pricing -->
<section class="sf-section" id="pricing">
	<div class="sf-container sf-container--narrow sf-section--centered">
		<h2>Bundle Pricing</h2>
		<p class="sf-text--muted sf-section__subtitle">Both plugins, one license key, one renewal date.</p>

		<div class="sf-card sf-card--bordered sf-bundle-pricing-box">
			<div class="sf-comparison-table-wrapper">
				<table class="sf-comparison-table">
					<tbody>
						<tr><td>SearchForge Pro</td><td class="sf-text--right">&euro;99/yr</td></tr>
						<tr><td>CacheWarmer Premium</td><td class="sf-text--right">&euro;99/yr</td></tr>
						<tr class="sf-comparison-table__separator"><td>Separate total</td><td class="sf-text--right sf-text--muted sf-text--line-through">&euro;198/yr</td></tr>
						<tr><td><strong>Bundle price</strong></td><td class="sf-text--right"><strong>&euro;169/yr</strong> <span class="sf-badge sf-badge--accent">Save 15%</span></td></tr>
					</tbody>
				</table>
			</div>

			<a href="<?php echo esc_url( home_url( '/checkout/?tier=bundle' ) ); ?>" class="sf-btn sf-btn--primary sf-btn--lg sf-btn--block" rel="noopener" title="SearchForge + CacheWarmer Bundle — &euro;169/yr, Save 15%">Get the Bundle &euro;169/yr</a>
			<p class="sf-text--muted sf-section__fine-print">
				30-day money-back guarantee. Cancel anytime.
			</p>
		</div>
	</div>
</section>

<!-- CacheWarmer Integration -->
<section class="sf-section sf-section--light">
	<div class="sf-container">
		<div class="sf-section__header">
			<h2>Built-In Integration</h2>
			<p class="sf-text--muted">When both plugins are active, they work together automatically.</p>
		</div>

		<div class="sf-grid sf-grid--3">
			<div class="sf-card sf-card--accent">
				<h3 class="sf-card__title">Auto-Trigger</h3>
				<p class="sf-card__desc">When SearchForge detects a content update, CacheWarmer automatically warms the affected URLs across all targets.</p>
			</div>
			<div class="sf-card sf-card--accent">
				<h3 class="sf-card__title">Shared Dashboard</h3>
				<p class="sf-card__desc">See warming status alongside SEO metrics. Know exactly when search engines and CDNs have your latest content.</p>
			</div>
			<div class="sf-card sf-card--accent">
				<h3 class="sf-card__title">Single License</h3>
				<p class="sf-card__desc">One license key activates both plugins. One renewal date. One place to manage everything.</p>
			</div>
		</div>
	</div>
</section>

<!-- FAQ -->
<section class="sf-section" id="faq">
	<div class="sf-container sf-container--narrow">
		<div class="sf-section__header">
			<h2>Bundle FAQ</h2>
		</div>

		<?php
		$bundle_faqs = [
			[ 'q' => 'Can I upgrade to the bundle from an existing license?', 'a' => 'Yes. If you already have SearchForge Pro or CacheWarmer Premium, contact us at support@drossmedia.de and we\'ll credit your remaining subscription toward the bundle price.' ],
			[ 'q' => 'Does the bundle work on multisite?',                    'a' => 'The bundle covers one WordPress installation. For multisite or multiple domains, consider SearchForge Agency + CacheWarmer Enterprise.' ],
			[ 'q' => 'What if I only need one of the plugins?',               'a' => 'Each plugin is available separately at €99/yr. The bundle is purely a cost-saving option if you want both.' ],
			[ 'q' => 'Is there a lifetime bundle option?',                    'a' => 'Not currently. Lifetime licenses are available for each plugin separately: SearchForge Lifetime Pro (€249) and CacheWarmer Lifetime Premium (€249).' ],
		];
		sf_render_faq( $bundle_faqs, 'bundle-faq' );
		?>
	</div>
</section>

<!-- Final CTA -->
<section class="sf-section sf-section--dark sf-final-cta">
	<div class="sf-container sf-section--centered">
		<h2>Ready to Optimize Your Entire Workflow?</h2>
		<p class="sf-text--inverse-muted">SEO intelligence + cache warming. Start with a 14-day free trial of both plugins.</p>
		<div class="sf-hero__actions sf-section__actions">
			<a href="<?php echo esc_url( home_url( '/checkout/?tier=bundle' ) ); ?>" class="sf-btn sf-btn--primary sf-btn--lg" rel="noopener" title="SearchForge + CacheWarmer Bundle — Start Your 14-Day Free Trial">Get the Bundle</a>
			<a href="https://dross.net/contact/?topic=searchforge-bundle" class="sf-btn sf-btn--outline sf-btn--lg" target="_blank" rel="noopener" title="Contact Dross:Media — Questions About the SearchForge + CacheWarmer Bundle">Questions? Contact Us</a>
		</div>
	</div>
</section>

<?php
get_footer();
