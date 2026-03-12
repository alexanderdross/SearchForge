</main>

<footer class="sf-footer" role="contentinfo">
	<!-- Accent bar -->
	<div class="sf-footer__accent"></div>

	<!-- Main footer -->
	<div class="sf-footer__main">
		<div class="sf-container sf-footer__grid">
			<!-- Brand column -->
			<div class="sf-footer__brand">
				<div class="sf-footer__logo">
					<img class="sf-footer__logo-img" src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/searchforge-logo.png" alt="SearchForge Logo - WordPress SEO Data Aggregation Plugin" width="36" height="36">
					<span class="sf-footer__logo-text">
						<strong>SearchForge</strong>
						<small>for WordPress</small>
					</span>
				</div>
				<p class="sf-footer__desc">Transform raw SEO data from 8 sources into LLM-ready intelligence. Unify Search Console, Analytics, and more into actionable briefs.</p>
				<a href="<?php echo esc_url( home_url( '/docs/' ) ); ?>" class="sf-footer__doc-btn" title="SearchForge Documentation - Setup, Configuration &amp; API Reference">
					<svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="1.5" width="10" height="13" rx="1"/><line x1="5.5" y1="5" x2="10.5" y2="5"/><line x1="5.5" y1="7.5" x2="10.5" y2="7.5"/><line x1="5.5" y1="10" x2="8.5" y2="10"/></svg>
					Documentation
				</a>
			</div>

			<!-- Data Sources column -->
			<div class="sf-footer__col">
				<p class="sf-footer__heading">Data Sources</p>
				<ul class="sf-footer__links">
					<li><a href="<?php echo esc_url( home_url( '/docs/data-sources/#google-search-console' ) ); ?>" title="Google Search Console Integration - Connect &amp; Sync Search Performance Data">Google Search Console</a></li>
					<li><a href="<?php echo esc_url( home_url( '/docs/data-sources/#bing-webmaster-tools' ) ); ?>" title="Bing Webmaster Tools Integration - Import Bing Search Data">Bing Webmaster Tools</a></li>
					<li><a href="<?php echo esc_url( home_url( '/docs/data-sources/#google-analytics-4' ) ); ?>" title="Google Analytics 4 Integration - Sessions, Bounce Rate &amp; Conversions">Google Analytics 4</a></li>
					<li><a href="<?php echo esc_url( home_url( '/docs/data-sources/#google-keyword-planner' ) ); ?>" title="Google Keyword Planner Integration - Search Volume &amp; Competition Data">Keyword Planner</a></li>
					<li><a href="<?php echo esc_url( home_url( '/docs/data-sources/#google-trends' ) ); ?>" title="Google Trends Integration - Interest Over Time &amp; Related Queries">Google Trends</a></li>
					<li><a href="<?php echo esc_url( home_url( '/docs/data-sources/' ) ); ?>" title="SerpAPI Integration - SERP Data &amp; Search Engine Results">SerpAPI</a></li>
				</ul>
			</div>

			<!-- Resources column -->
			<div class="sf-footer__col">
				<p class="sf-footer__heading">Resources</p>
				<ul class="sf-footer__links">
					<li><a href="<?php echo esc_url( home_url( '/features/' ) ); ?>" title="SearchForge Features - SEO Score, AI Briefs, Competitor Analysis &amp; More">Features</a></li>
					<li><a href="<?php echo esc_url( home_url( '/docs/' ) ); ?>" title="SearchForge Documentation - Setup, Configuration &amp; API Reference">Documentation</a></li>
					<li><a href="<?php echo esc_url( home_url( '/docs/developer/' ) ); ?>" title="SearchForge Developer Docs - REST API, WP-CLI &amp; API Key Setup">API Keys Setup</a></li>
					<li><a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>" title="SearchForge Pricing - Compare Free, Pro &amp; Agency Plans">Pricing</a></li>
					<li><a href="<?php echo esc_url( home_url( '/enterprise/' ) ); ?>" title="SearchForge Enterprise - Multi-Site, White-Label &amp; Priority Support">Enterprise</a></li>
					<li><a href="<?php echo esc_url( home_url( '/changelog/' ) ); ?>" title="SearchForge Changelog - Version History &amp; Release Notes">Changelog</a></li>
				</ul>
			</div>

			<!-- Export & Analysis column -->
			<div class="sf-footer__col">
				<p class="sf-footer__heading">Export &amp; Analysis</p>
				<ul class="sf-footer__links">
					<li><a href="<?php echo esc_url( home_url( '/docs/export-output/#markdown-briefs' ) ); ?>" title="Markdown Brief Export - Per-Page LLM-Ready SEO Briefs">Markdown Briefs</a></li>
					<li><a href="<?php echo esc_url( home_url( '/docs/export-output/#zip-bulk-export' ) ); ?>" title="CSV Export - Bulk Data Export for Spreadsheets">CSV Export</a></li>
					<li><a href="<?php echo esc_url( home_url( '/docs/export-output/#llms-txt-generation' ) ); ?>" title="llms.txt Generation - AI Crawler Discovery Endpoints">llms.txt</a></li>
					<li><a href="<?php echo esc_url( home_url( '/docs/features/#searchforge-score' ) ); ?>" title="SearchForge SEO Score - Proprietary 0-100 Performance Rating">SEO Score</a></li>
					<li><a href="<?php echo esc_url( home_url( '/docs/features/#ai-content-briefs' ) ); ?>" title="AI Content Briefs - Heuristic &amp; AI-Generated SEO Recommendations">Content Briefs</a></li>
					<li><a href="<?php echo esc_url( home_url( '/docs/features/#keyword-clustering' ) ); ?>" title="Keyword Clustering - Automatic Topic Grouping &amp; N-Gram Analysis">Keyword Clusters</a></li>
					<li><a href="<?php echo esc_url( home_url( '/docs/features/#competitor-intelligence' ) ); ?>" title="Competitor Analysis - SERP Visibility, Keyword Overlap &amp; Content Gaps">Competitor Analysis</a></li>
				</ul>
			</div>
		</div>
	</div>

	<!-- Bottom bar -->
	<div class="sf-footer__bottom">
		<div class="sf-container sf-footer__bottom-inner">
			<p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> SearchForge. All rights reserved.</p>
			<ul class="sf-footer__legal">
				<li><a href="<?php echo esc_url( 'https://dross.net/imprint/?ref=searchforge' ); ?>" target="_blank" rel="noopener" title="Dross:Media GmbH - Legal Imprint &amp; Company Information">Imprint</a></li>
				<li><a href="<?php echo esc_url( 'https://dross.net/privacy-policy/?ref=searchforge' ); ?>" target="_blank" rel="noopener" title="Dross:Media GmbH - Privacy Policy &amp; Data Protection">Privacy Policy</a></li>
				<li><a href="<?php echo esc_url( 'https://dross.net/contact/?topic=searchforge' ); ?>" target="_blank" rel="noopener" title="Contact Dross:Media GmbH - SearchForge Support &amp; Inquiries">Contact</a></li>
			</ul>
			<p>Made with &hearts; by <a href="<?php echo esc_url( 'https://dross.net/?ref=searchforge' ); ?>" target="_blank" rel="noopener" title="Dross:Media GmbH - WordPress Plugins &amp; Digital Solutions">Dross:Media</a></p>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
