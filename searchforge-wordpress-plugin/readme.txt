=== SearchForge ===
Contributors: drossmedia
Tags: seo, search console, analytics, content optimization, llm
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 8.0
Stable tag: 3.1.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Unifies search data sources (GSC, Bing, GA4, Adobe Analytics, Keyword Planner, Trends) into LLM-ready markdown briefs with AI content analysis.

== Description ==

SearchForge aggregates SEO data from multiple sources into a single WordPress dashboard and generates LLM-ready markdown briefs for every page on your site.

**Supported Data Sources:**

* Google Search Console (OAuth 2.0)
* Bing Webmaster Tools
* Google Analytics 4
* Adobe Analytics
* Google Keyword Planner
* Google Trends (via SerpAPI)
* Google Business Profile
* Bing Places
* Sitemap discovery

**Key Features:**

* Proprietary SearchForge Score (0-100) combining technical, content, authority, and momentum signals
* AI-powered and heuristic content briefs with actionable recommendations
* Keyword clustering via n-gram Jaccard similarity
* Keyword cannibalization detection
* Competitor tracking and SERP analysis
* Multi-domain property management with per-property credentials
* CMS Backend Merger Analysis for domain migrations and consolidation
* Automated alerts for ranking drops, traffic anomalies, and content decay
* CSV and Markdown export, plus `/llms.txt` and `/llms-full.txt` endpoints
* Full REST API (21 endpoints) with API key authentication
* WP-CLI commands for sync, export, and status
* Weekly digest emails and webhook/Slack notifications

== Installation ==

1. Upload the `searchforge` folder to the `/wp-content/plugins/` directory, or install directly through the WordPress plugin screen.
2. Activate the plugin through the "Plugins" screen in WordPress.
3. Navigate to **SearchForge > Settings** and complete the onboarding wizard to connect your first data source.
4. Run your first sync from the dashboard or via WP-CLI: `wp searchforge sync`.

== Frequently Asked Questions ==

= Which data sources can I connect? =

SearchForge supports Google Search Console, Bing Webmaster Tools, Google Analytics 4, Adobe Analytics, Google Keyword Planner, Google Trends (via SerpAPI), Google Business Profile, and Bing Places. The free tier includes GSC (limited to 10 pages). Pro and Agency tiers unlock all sources.

= What is the difference between Free, Pro, and Agency? =

The free tier provides basic GSC integration for up to 10 pages with 30-day data retention. Pro (EUR 99/year) adds unlimited pages, all data sources, content briefs, competitor tracking, the REST API, and 365-day retention. Agency (EUR 249/year) adds multi-site support (up to 10 sites) and white-labeling.

= Does SearchForge have a REST API? =

Yes. Pro and Agency tiers expose 21 REST endpoints under the `searchforge/v1` namespace. Authenticate with an API key via the `Authorization: Bearer` or `X-SearchForge-Key` header.

= What are LLM-ready briefs? =

SearchForge generates structured markdown documents for each page combining search performance data, keyword analysis, scoring insights, and actionable recommendations. These briefs are designed to be fed directly into large language models for content optimization workflows.

== Changelog ==

= 3.1.0 =
* Added Adobe Analytics integration via Analytics 2.0 API (visits, page views, bounce rate, time on page, conversions, revenue).
* Added OAuth Server-to-Server authentication with Adobe IMS.
* Added CSV navigation upload in Merger Analysis for current header/footer inventory.
* Added flexible CSV column mapping with multiple header format support.
* Added WP-CLI `wp searchforge sync --source=adobe` command.
* Added REST API merger-analysis `nav_data` parameter.

= 3.0.0 =
* Added multi-domain property management with per-property encrypted credentials.
* Added Property Comparison page for side-by-side metrics across properties.
* Added CMS Backend Merger Analysis for domain migrations and portfolio consolidation.
* Added Instructions admin page with comprehensive feature documentation.
* Added REST API endpoints for properties and merger analysis.
* Added WP-CLI commands for property management and merger brief generation.
* Changed OAuth tokens and API keys to per-property storage with AES-256-CBC encryption.
* Fixed Page Detail view argument handling for trend and year-over-year data.

== Upgrade Notice ==

= 3.1.0 =
Adds Adobe Analytics as a ninth data source and CSV navigation upload for merger analysis. No database migration required.
