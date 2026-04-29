<?php
/**
 * Template Name: Changelog
 *
 * @package SearchForge_Theme
 */

get_header();
?>

<section class="sf-section sf-section--dark sf-hero" style="padding: var(--space-3xl) 0;">
	<div class="sf-container" style="text-align: center;">
		<h1><span class="sf-gradient-text">Changelog</span></h1>
		<p class="sf-text--inverse-muted" style="font-size: 1.25rem;">
			What's new in SearchForge. Every release, documented.
		</p>
	</div>
</section>

<section class="sf-section">
	<div class="sf-container sf-container--narrow">

		<article class="sf-changelog-entry">
			<div class="sf-changelog-entry__header">
				<h2>v3.1.0 <span class="sf-badge sf-badge--accent">Latest</span></h2>
				<time class="sf-text--muted" datetime="2026-04-29">April 29, 2026</time>
			</div>
			<h3>Adobe Analytics &amp; CSV Navigation Upload</h3>
			<p class="sf-text--muted">SearchForge now connects to 9 data sources. Adobe Analytics joins the stack with enterprise-grade behavior metrics, and the Merger Analysis gains CSV upload for existing navigation inventories.</p>
			<h4>Added</h4>
			<ul>
				<li>Adobe Analytics integration via Analytics 2.0 API (visits, page views, bounce rate, time on page, conversions, revenue)</li>
				<li>OAuth Server-to-Server authentication with Adobe IMS (Identity Management System) per property</li>
				<li>Adobe Analytics settings section with Report Suite ID, Organization ID, and Client ID/Secret fields</li>
				<li>Adobe behavior data synced into behavior metrics table alongside GA4 for unified analysis</li>
				<li>CSV navigation upload in Merger Analysis &mdash; upload one CSV per domain or subfolder with current header/footer navigation items</li>
				<li>Flexible CSV column mapping accepts multiple header formats (label/text/name, url/link/href, location/position/type)</li>
				<li>&ldquo;Current Navigation Inventory&rdquo; section in merger brief shows existing navigation alongside traffic-weighted recommendations</li>
				<li>Dynamic CSV upload UI with add/remove rows for multiple domains and subfolders</li>
				<li>WP-CLI: <code>wp searchforge sync --source=adobe</code></li>
				<li>REST API: merger-analysis endpoint accepts optional <code>nav_data</code> parameter</li>
			</ul>
			<h4>Changed</h4>
			<ul>
				<li>Data source count increased from 8 to 9 with Adobe Analytics addition</li>
				<li>Merger analysis clarified as CMS-agnostic &mdash; works with analytics data from WordPress, Drupal, and custom backends</li>
			</ul>
		</article>

		<article class="sf-changelog-entry">
			<div class="sf-changelog-entry__header">
				<h2>v3.0.0</h2>
				<time class="sf-text--muted" datetime="2026-04-29">April 29, 2026</time>
			</div>
			<h3>Multi-Property Management &amp; Merger Intelligence</h3>
			<p class="sf-text--muted">The biggest SearchForge release yet. Manage multiple domains from one WordPress installation, compare metrics across properties, and generate data-driven merger briefs for domain consolidations.</p>
			<h4>Added</h4>
			<ul>
				<li>Multi-domain property management &mdash; manage multiple GSC properties, Bing sites, and GA4 streams per WordPress installation</li>
				<li>Property selector dropdown on all admin pages for switching between properties</li>
				<li>Property Comparison page &mdash; side-by-side metrics across all properties (clicks, impressions, CTR, position, pages, keywords, SearchForge Score)</li>
				<li>CMS Backend Merger Analysis &mdash; comprehensive markdown briefs for domain mergers, migrations, and portfolio consolidation</li>
				<li>Merger brief includes: executive summary, URL pattern analysis, traffic-weighted navigation recommendations, information architecture restructuring with 301 redirect map, user funnel optimization, and cross-property keyword cannibalization</li>
				<li>Instructions page in the plugin admin with documentation for all features</li>
				<li>New <a href="<?php echo esc_url( home_url( '/docs/multi-property/' ) ); ?>">Multi-Property &amp; Merger documentation</a></li>
				<li>REST API: <code>GET/POST /properties</code>, <code>GET /comparison</code>, <code>GET /merger-analysis</code></li>
				<li>WP-CLI: <code>wp searchforge properties</code>, <code>wp searchforge merger --properties=1,2,3</code></li>
			</ul>
			<h4>Changed</h4>
			<ul>
				<li>All database queries now filter by <code>property_id</code> for complete data isolation</li>
				<li>OAuth tokens stored per-property with AES-256-CBC encryption</li>
				<li>Settings page reorganized with Properties management section</li>
			</ul>
			<h4>Fixed</h4>
			<ul>
				<li>Page Detail view engine call argument order corrected</li>
			</ul>
		</article>

		<article class="sf-changelog-entry">
			<div class="sf-changelog-entry__header">
				<h2>v2.0.0</h2>
				<time class="sf-text--muted" datetime="2026-03-08">March 8, 2026</time>
			</div>
			<h3>Version 2.0  - Feature Complete</h3>
			<p class="sf-text--muted">SearchForge reaches feature completeness. Full SEO metadata, structured data, and marketing site polish make this the definitive release for production use.</p>
			<h4>Added</h4>
			<ul>
				<li>Comprehensive SEO metadata across all pages (meta description, Open Graph, Twitter Cards)</li>
				<li>Product schema markup with AggregateRating for rich search results</li>
				<li>SiteNavigationElement structured data for all site pages</li>
				<li>WebPage schema on every inner page with breadcrumb support</li>
				<li>Canonical URLs and robots meta on all pages</li>
				<li>URL normalization for deduplication in CacheWarmer bundle integration</li>
				<li>FAQ schema auto-generated from all FAQ accordion sections</li>
			</ul>
			<h4>Changed</h4>
			<ul>
				<li>Version bump from 1.9.0 to 2.0.0 marking feature completeness</li>
				<li>Unified JSON-LD output via single <code>@graph</code> array for all schema types</li>
				<li>Improved data source sync reliability with retry logic</li>
			</ul>
			<h4>Fixed</h4>
			<ul>
				<li>Organization schema name corrected to "Dross:Media"</li>
				<li>Schema URL references now point to correct production domain</li>
			</ul>
		</article>

		<article class="sf-changelog-entry">
			<div class="sf-changelog-entry__header">
				<h2>v1.9.0</h2>
				<time class="sf-text--muted" datetime="2026-03-06">March 6, 2026</time>
			</div>
			<h3>Competitor Tracking &amp; SERP Intelligence</h3>
			<p class="sf-text--muted">Full competitor analysis suite. Track what your competitors rank for, find content gaps, and monitor SERP feature appearances.</p>
			<h4>Added</h4>
			<ul>
				<li>Competitor domain tracking with auto-detection from shared keywords</li>
				<li>SERP snapshot capture for top keywords (positions 1-10)</li>
				<li>Content gap analysis vs. competitors</li>
				<li>SERP feature tracking (featured snippets, PAA, video packs)</li>
				<li>Competitor keyword overlap and visibility comparison</li>
				<li>Competitor markdown export for LLM context</li>
				<li>REST API endpoints: <code>/competitors</code>, <code>/competitors/overlap</code>, <code>/competitors/gaps</code>, <code>/competitors/visibility</code></li>
			</ul>
			<h4>Fixed</h4>
			<ul>
				<li>GA4 column name mismatch (<code>avg_session_dur</code> vs. <code>avg_session_duration</code>) in page detail view</li>
				<li>Weekly digest email array path alignment</li>
			</ul>
		</article>

		<article class="sf-changelog-entry">
			<div class="sf-changelog-entry__header">
				<h2>v1.8.0</h2>
				<time class="sf-text--muted" datetime="2026-02-28">February 28, 2026</time>
			</div>
			<h3>Bulk Actions &amp; Weekly Digest</h3>
			<p class="sf-text--muted">Export at scale and stay informed with automated weekly email summaries of your SEO performance.</p>
			<h4>Added</h4>
			<ul>
				<li>Bulk page selection for batch brief export</li>
				<li>Weekly digest email with key metric changes (clicks, impressions, position shifts)</li>
				<li>Dashboard chart for clicks/impressions over time (Chart.js)</li>
				<li>ZIP bulk export containing all page briefs in one download</li>
				<li>Webhook support for Slack-formatted notifications</li>
			</ul>
			<h4>Improved</h4>
			<ul>
				<li>Export ZIP now includes all brief formats (Markdown, CSV, JSON)</li>
				<li>Dashboard KPI cards show percentage change vs. previous period</li>
			</ul>
		</article>

		<article class="sf-changelog-entry">
			<div class="sf-changelog-entry__header">
				<h2>v1.7.0</h2>
				<time class="sf-text--muted" datetime="2026-02-21">February 21, 2026</time>
			</div>
			<h3>Page Detail View &amp; Charts</h3>
			<p class="sf-text--muted">Deep-dive into per-page analytics with interactive charts, keyword breakdowns, and GA4 behavior metrics.</p>
			<h4>Added</h4>
			<ul>
				<li>Detailed per-page view with Chart.js visualizations</li>
				<li>Keyword table with sorting and filtering per page</li>
				<li>Position tracking chart per keyword over time</li>
				<li>GA4 behavior metrics integration (sessions, bounce rate, conversions) on page detail</li>
				<li>SearchForge Score breakdown (Technical, Content, Authority, Momentum)</li>
				<li>REST API endpoint: <code>/page-detail</code></li>
			</ul>
		</article>

		<article class="sf-changelog-entry">
			<div class="sf-changelog-entry__header">
				<h2>v1.6.0</h2>
				<time class="sf-text--muted" datetime="2026-02-14">February 14, 2026</time>
			</div>
			<h3>API Keys, Pagination &amp; Onboarding</h3>
			<p class="sf-text--muted">External API access, a guided setup wizard, and smoother data browsing for larger sites.</p>
			<h4>Added</h4>
			<ul>
				<li>API key authentication for external access via <code>Authorization: Bearer</code> or <code>X-SearchForge-Key</code> headers</li>
				<li>Pagination and search on pages/keywords admin lists</li>
				<li>3-step onboarding wizard for first-time setup (license &rarr; data source &rarr; first sync)</li>
				<li>Response caching for dashboard performance</li>
			</ul>
			<h4>Security</h4>
			<ul>
				<li>API keys accepted via headers only  - query parameter authentication removed to prevent credential exposure in server logs</li>
			</ul>
		</article>

		<article class="sf-changelog-entry">
			<div class="sf-changelog-entry__header">
				<h2>v1.5.0</h2>
				<time class="sf-text--muted" datetime="2026-02-07">February 7, 2026</time>
			</div>
			<h3>Alert System &amp; Content Decay</h3>
			<p class="sf-text--muted">Proactive monitoring for ranking drops, traffic anomalies, and content that needs refreshing.</p>
			<h4>Added</h4>
			<ul>
				<li>Ranking drop alerts with configurable threshold (default: 3+ positions)</li>
				<li>Content decay detection with 7-day, 30-day, and 90-day trend analysis</li>
				<li>New keyword detection alerts (keywords entering the top 100)</li>
				<li>Monitoring dashboard with alert history and severity levels</li>
				<li>Traffic anomaly detection (significant drops or spikes)</li>
				<li>SSL certificate monitoring with expiry warnings</li>
				<li>Broken link scanning (Pro only)</li>
			</ul>
		</article>

		<article class="sf-changelog-entry">
			<div class="sf-changelog-entry__header">
				<h2>v1.4.0</h2>
				<time class="sf-text--muted" datetime="2026-01-31">January 31, 2026</time>
			</div>
			<h3>Google Trends &amp; AI Content Briefs</h3>
			<p class="sf-text--muted">Trend data enrichment and AI-powered content recommendations join the SearchForge intelligence stack.</p>
			<h4>Added</h4>
			<ul>
				<li>Google Trends integration via SerpAPI (interest over time, related queries, rising topics)</li>
				<li>AI content brief generation using OpenAI or Anthropic APIs</li>
				<li>Heuristic content briefs (no API key required) based on keyword clustering and performance data</li>
				<li>Content brief caching with configurable expiration</li>
				<li>REST API endpoint: <code>/content-brief</code></li>
				<li>REST API endpoint: <code>/trends</code></li>
			</ul>
		</article>

		<article class="sf-changelog-entry">
			<div class="sf-changelog-entry__header">
				<h2>v1.3.0</h2>
				<time class="sf-text--muted" datetime="2026-01-28">January 28, 2026</time>
			</div>
			<h3>Keyword Clustering &amp; Cannibalization</h3>
			<p class="sf-text--muted">Automatic topic grouping and keyword overlap detection help you identify optimization opportunities and avoid internal competition.</p>
			<h4>Added</h4>
			<ul>
				<li>N-gram Jaccard similarity keyword clustering</li>
				<li>Keyword cannibalization detection (multiple pages ranking for same keyword)</li>
				<li>Cluster visualization in admin dashboard</li>
				<li>REST API endpoints: <code>/clusters</code>, <code>/cannibalization</code></li>
			</ul>
		</article>

		<article class="sf-changelog-entry">
			<div class="sf-changelog-entry__header">
				<h2>v1.2.0</h2>
				<time class="sf-text--muted" datetime="2026-01-24">January 24, 2026</time>
			</div>
			<h3>Google Analytics 4 &amp; Keyword Planner</h3>
			<p class="sf-text--muted">Two new data sources bring behavior metrics and search volume data into your SEO intelligence stack.</p>
			<h4>Added</h4>
			<ul>
				<li>Google Analytics 4 integration (sessions, bounce rate, avg. session duration, conversions)</li>
				<li>Google Keyword Planner integration (search volume, competition level, CPC data)</li>
				<li>GA4 metrics table (<code>sf_ga4_metrics</code>) for behavior data storage</li>
				<li>Keyword volume enrichment in keyword tables and exports</li>
				<li>Combined data source sync with <code>wp searchforge sync --source=all</code></li>
			</ul>
		</article>

		<article class="sf-changelog-entry">
			<div class="sf-changelog-entry__header">
				<h2>v1.1.0</h2>
				<time class="sf-text--muted" datetime="2026-01-20">January 20, 2026</time>
			</div>
			<h3>Bing Webmaster Tools &amp; CSV Export</h3>
			<p class="sf-text--muted">Second search engine integration and tabular export format for spreadsheet workflows.</p>
			<h4>Added</h4>
			<ul>
				<li>Bing Webmaster Tools integration (API key authentication)</li>
				<li>CSV export for pages, keywords, and alerts</li>
				<li>Data retention enforcement with configurable retention period</li>
				<li>WP-CLI commands: <code>wp searchforge sync</code>, <code>wp searchforge status</code>, <code>wp searchforge export</code></li>
				<li>WP-Cron scheduler for automated daily/weekly syncs</li>
			</ul>
		</article>

		<article class="sf-changelog-entry">
			<div class="sf-changelog-entry__header">
				<h2>v1.0.0</h2>
				<time class="sf-text--muted" datetime="2026-01-15">January 15, 2026</time>
			</div>
			<h3>Initial Release</h3>
			<p class="sf-text--muted">The foundation of SearchForge. Google Search Console data, markdown brief export, and the proprietary SEO scoring system that started it all.</p>
			<h4>Added</h4>
			<ul>
				<li>Google Search Console integration with OAuth 2.0 (clicks, impressions, position, CTR)</li>
				<li>Per-page markdown brief export with keyword data, performance metrics, and recommendations</li>
				<li>Combined site-wide markdown master brief export</li>
				<li>Admin dashboard with top pages, top keywords, and KPI summary cards</li>
				<li>SearchForge Score (0-100) with 4 components: Technical, Content, Authority, Momentum</li>
				<li>Auto-generated recommendations based on score analysis</li>
				<li><code>llms.txt</code> and <code>llms-full.txt</code> auto-generation for AI crawler discovery</li>
				<li>Sitemap discovery via <code>robots.txt</code> parsing</li>
				<li>10 database tables for snapshots, keywords, sync logs, briefs cache, alerts, and settings</li>
				<li>Free tier with 10-page limit, 30-day data retention</li>
				<li>Pro tier with unlimited pages, 365-day retention, full scoring, and REST API access</li>
				<li>WordPress dashboard widget with quick SEO summary</li>
				<li>PSR-4 autoloading and WordPress coding standards compliance</li>
			</ul>
			<h4>REST API</h4>
			<ul>
				<li><code>GET /searchforge/v1/status</code>  - Plugin health and version</li>
				<li><code>GET /searchforge/v1/pages</code>  - Top pages with metrics</li>
				<li><code>GET /searchforge/v1/keywords</code>  - Top keywords</li>
				<li><code>GET /searchforge/v1/export/page</code>  - Single page markdown brief</li>
				<li><code>GET /searchforge/v1/export/site</code>  - Full site export</li>
				<li><code>POST /searchforge/v1/sync</code>  - Manual sync trigger</li>
			</ul>
		</article>

	</div>
</section>

<section class="sf-section sf-section--light" style="text-align: center;">
	<div class="sf-container sf-container--narrow">
		<p class="sf-text--muted">
			<a href="<?php echo esc_url( home_url( '/docs/' ) ); ?>" title="SearchForge Documentation - Setup, Configuration &amp; API Reference">&larr; Back to Documentation</a>
		</p>
	</div>
</section>

<?php
get_footer();
