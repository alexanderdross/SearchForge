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
					<img class="sf-footer__logo-img" src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/searchforge-logo.png" alt="" aria-hidden="true" width="36" height="36">
					<span class="sf-footer__logo-text">
						<strong>SearchForge</strong>
						<small>for WordPress</small>
					</span>
				</div>
				<p class="sf-footer__desc">Transform raw SEO data from 8 sources into LLM-ready intelligence. Unify Search Console, Analytics, and more into actionable briefs.</p>
				<a href="<?php echo esc_url( home_url( '/docs/' ) ); ?>" class="sf-footer__doc-btn">
					<svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="1.5" width="10" height="13" rx="1"/><line x1="5.5" y1="5" x2="10.5" y2="5"/><line x1="5.5" y1="7.5" x2="10.5" y2="7.5"/><line x1="5.5" y1="10" x2="8.5" y2="10"/></svg>
					Documentation
				</a>
			</div>

			<!-- Data Sources column -->
			<div class="sf-footer__col">
				<h4 class="sf-footer__heading">Data Sources</h4>
				<ul class="sf-footer__links">
					<li><a href="<?php echo esc_url( home_url( '/docs/data-sources/' ) ); ?>">Google Search Console</a></li>
					<li><a href="<?php echo esc_url( home_url( '/docs/data-sources/' ) ); ?>">Bing Webmaster Tools</a></li>
					<li><a href="<?php echo esc_url( home_url( '/docs/data-sources/' ) ); ?>">Google Analytics 4</a></li>
					<li><a href="<?php echo esc_url( home_url( '/docs/data-sources/' ) ); ?>">Keyword Planner</a></li>
					<li><a href="<?php echo esc_url( home_url( '/docs/data-sources/' ) ); ?>">Google Trends</a></li>
					<li><a href="<?php echo esc_url( home_url( '/docs/data-sources/' ) ); ?>">SerpAPI</a></li>
				</ul>
			</div>

			<!-- Resources column -->
			<div class="sf-footer__col">
				<h4 class="sf-footer__heading">Resources</h4>
				<ul class="sf-footer__links">
					<li><a href="<?php echo esc_url( home_url( '/features/' ) ); ?>">Features</a></li>
					<li><a href="<?php echo esc_url( home_url( '/docs/' ) ); ?>">Documentation</a></li>
					<li><a href="<?php echo esc_url( home_url( '/docs/developer/' ) ); ?>">API Keys Setup</a></li>
					<li><a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>">Pricing</a></li>
					<li><a href="<?php echo esc_url( home_url( '/enterprise/' ) ); ?>">Enterprise</a></li>
					<li><a href="<?php echo esc_url( home_url( '/changelog/' ) ); ?>">Changelog</a></li>
				</ul>
			</div>

			<!-- Export & Analysis column -->
			<div class="sf-footer__col">
				<h4 class="sf-footer__heading">Export &amp; Analysis</h4>
				<ul class="sf-footer__links">
					<li><a href="<?php echo esc_url( home_url( '/docs/export-output/' ) ); ?>">Markdown Briefs</a></li>
					<li><a href="<?php echo esc_url( home_url( '/docs/export-output/' ) ); ?>">CSV Export</a></li>
					<li><a href="<?php echo esc_url( home_url( '/docs/export-output/' ) ); ?>">llms.txt</a></li>
					<li><a href="<?php echo esc_url( home_url( '/docs/features/' ) ); ?>">SEO Score</a></li>
					<li><a href="<?php echo esc_url( home_url( '/docs/features/' ) ); ?>">Content Briefs</a></li>
					<li><a href="<?php echo esc_url( home_url( '/docs/features/' ) ); ?>">Keyword Clusters</a></li>
					<li><a href="<?php echo esc_url( home_url( '/docs/features/' ) ); ?>">Competitor Analysis</a></li>
				</ul>
			</div>
		</div>
	</div>

	<!-- Bottom bar -->
	<div class="sf-footer__bottom">
		<div class="sf-container sf-footer__bottom-inner">
			<p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> SearchForge. All rights reserved.</p>
			<ul class="sf-footer__legal">
				<li><a href="https://dross.net/imprint/?ref=searchforge" target="_blank" rel="noopener">Imprint</a></li>
				<li><a href="https://dross.net/privacy-policy/?ref=searchforge" target="_blank" rel="noopener">Privacy Policy</a></li>
				<li><a href="https://dross.net/contact/?topic=searchforge" target="_blank" rel="noopener">Contact</a></li>
			</ul>
			<p>Made with &hearts; by <a href="https://dross.net/?ref=searchforge" target="_blank" rel="noopener">Dross:Media</a></p>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
