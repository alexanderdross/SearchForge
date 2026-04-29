# Changelog

All notable changes to the SearchForge WordPress plugin will be documented in this file.

## [3.1.0] - 2026-04-29

### Added
- Adobe Analytics integration via Analytics 2.0 API (visits, page views, bounce rate, time on page, conversions, revenue).
- OAuth Server-to-Server authentication with Adobe IMS (Identity Management System) per property.
- Adobe Analytics settings section in property configuration: Organization ID, Client ID, Client Secret, Report Suite ID.
- Adobe data synced into behavior metrics table alongside GA4 for unified analysis.
- CSV navigation upload in Merger Analysis — upload one CSV per domain or subfolder with current header/footer items.
- Flexible CSV column mapping accepts multiple header formats (label/text/name, url/link/href, location/position/type).
- "Current Navigation Inventory" section in merger brief shows existing navigation alongside traffic-weighted recommendations.
- Dynamic CSV upload UI with add/remove rows for multiple domains and subfolders.
- WP-CLI: `wp searchforge sync --source=adobe` for Adobe Analytics sync.
- REST API: merger-analysis endpoint accepts optional `nav_data` parameter.

### Changed
- Data source count increased from 8 to 9 with Adobe Analytics addition.
- Merger analysis description updated to clarify CMS-agnostic support (WordPress, Drupal, custom backends).

## [3.0.0] - 2026-04-29

### Added
- Multi-domain property management: manage multiple GSC properties, Bing sites, and GA4 streams from one WordPress installation.
- New `sf_properties` database table with per-property credentials (AES-256-CBC encrypted).
- `property_id` column added to all 7 existing data tables with migration from single-property data.
- Property selector dropdown on all admin pages for switching between properties.
- Property Comparison page: side-by-side metrics (clicks, impressions, CTR, position, pages, keywords, SearchForge Score) across all properties.
- CMS Backend Merger Analysis: generates comprehensive markdown briefs for domain mergers, migrations, and portfolio consolidation.
- Merger brief includes: executive summary, URL pattern analysis, traffic-weighted navigation recommendations (header/footer), information architecture restructuring with redirect map, user funnel optimization (requires GA4), and cross-property keyword cannibalization detection.
- Navigation scoring formula: clicks (30%) + sessions (20%) + engagement (20%) + keyword breadth (15%) + conversions (15%).
- Instructions admin page with comprehensive documentation for all plugin features.
- REST API: `GET/POST /properties`, `GET/PUT/DELETE /properties/{id}`, `GET /comparison`, `GET /merger-analysis`.
- REST API: all existing endpoints accept optional `property_id` parameter.
- WP-CLI: `wp searchforge properties` (list all), `wp searchforge merger --properties=1,2,3 --file=out.md`.
- WP-CLI: `--property=<id>` flag on `sync`, `export` commands.
- AJAX handlers: `switch_property`, `add_property`, `remove_property`, `sync_property`, `generate_merger_brief`.
- Multi-property daily sync loop processes all properties automatically.

### Changed
- OAuth tokens and API keys stored in `sf_properties` table (per-property) instead of `wp_options`.
- Encryption logic extracted to shared `Database\Encryption` helper.
- All ~90 database queries across 16 files now filter by `property_id`.
- Transient cache keys include `property_id` for per-property isolation.
- Settings page reorganized with Properties management section.
- Version bump to 3.0.0 for major multi-property architecture change.

### Fixed
- Page Detail view: `Engine::get_page_trend()` and `Engine::get_yoy_comparison()` now receive correct `source` argument.

## [2.0.0] - 2026-03-08

### Added
- Comprehensive SEO metadata across the marketing site (meta tags, Open Graph, Twitter Cards).
- Product schema markup with AggregateRating for rich search results.
- SiteNavigationElement structured data for all site pages.
- WebPage schema on every inner page with breadcrumb support.
- Canonical URLs and robots meta on all pages.
- URL normalization for deduplication in CacheWarmer bundle integration.
- FAQ schema auto-generated from all FAQ accordion sections.

### Changed
- Version bump from 1.9.0 to 2.0.0 marking feature completeness.
- Unified JSON-LD output via single @graph array for all schema types.
- Improved data source sync reliability with retry logic.

### Fixed
- Organization schema name corrected to "Dross:Media".
- Schema URL references now point to correct production domain.

## [1.9.0] - 2026-03-06

### Added
- Competitor domain tracking with auto-detection from shared keywords.
- SERP snapshot capture for top keywords (positions 1-10).
- Content gap analysis vs. competitors.
- SERP feature tracking (featured snippets, PAA, video packs).
- Competitor keyword overlap and visibility comparison.
- Competitor markdown export for LLM context.
- REST API endpoints: `/competitors`, `/competitors/overlap`, `/competitors/gaps`, `/competitors/visibility`.

### Fixed
- GA4 column name mismatch (`avg_session_dur` vs. `avg_session_duration`) in page detail view.
- Weekly digest email array path alignment.

## [1.8.0] - 2026-02-28

### Added
- Bulk page selection for batch brief export.
- Weekly digest email with key metric changes (clicks, impressions, position shifts).
- Dashboard chart for clicks/impressions over time (Chart.js).
- ZIP bulk export containing all page briefs in one download.
- Webhook support for Slack-formatted notifications.

### Improved
- Export ZIP now includes all brief formats (Markdown, CSV, JSON).
- Dashboard KPI cards show percentage change vs. previous period.

## [1.7.0] - 2026-02-21

### Added
- Detailed per-page view with Chart.js visualizations.
- Keyword table with sorting and filtering per page.
- Position tracking chart per keyword over time.
- GA4 behavior metrics integration (sessions, bounce rate, conversions) on page detail.
- SearchForge Score breakdown (Technical, Content, Authority, Momentum).
- REST API endpoint: `/page-detail`.

## [1.6.0] - 2026-02-14

### Added
- API key authentication for external access via `Authorization: Bearer` or `X-SearchForge-Key` headers.
- Pagination and search on pages/keywords admin lists.
- 3-step onboarding wizard for first-time setup (license → data source → first sync).
- Response caching for dashboard performance.

### Security
- API keys accepted via headers only — query parameter authentication removed to prevent credential exposure in server logs.

## [1.5.0] - 2026-02-07

### Added
- Ranking drop alerts with configurable threshold (default: 3+ positions).
- Content decay detection with 7-day, 30-day, and 90-day trend analysis.
- New keyword detection alerts (keywords entering the top 100).
- Monitoring dashboard with alert history and severity levels.
- Traffic anomaly detection (significant drops or spikes).
- SSL certificate monitoring with expiry warnings.
- Broken link scanning (Pro only).

## [1.4.0] - 2026-01-31

### Added
- Google Trends integration via SerpAPI (interest over time, related queries, rising topics).
- AI content brief generation using OpenAI or Anthropic APIs.
- Heuristic content briefs (no API key required) based on keyword clustering and performance data.
- Content brief caching with configurable expiration.
- REST API endpoint: `/content-brief`.
- REST API endpoint: `/trends`.

## [1.3.0] - 2026-01-28

### Added
- N-gram Jaccard similarity keyword clustering.
- Keyword cannibalization detection (multiple pages ranking for same keyword).
- Cluster visualization in admin dashboard.
- REST API endpoints: `/clusters`, `/cannibalization`.

## [1.2.0] - 2026-01-24

### Added
- Google Analytics 4 integration (sessions, bounce rate, avg. session duration, conversions).
- Google Keyword Planner integration (search volume, competition level, CPC data).
- GA4 metrics table (`sf_ga4_metrics`) for behavior data storage.
- Keyword volume enrichment in keyword tables and exports.
- Combined data source sync with `wp searchforge sync --source=all`.

## [1.1.0] - 2026-01-20

### Added
- Bing Webmaster Tools integration (API key authentication).
- CSV export for pages, keywords, and alerts.
- Data retention enforcement with configurable retention period.
- WP-CLI commands: `wp searchforge sync`, `wp searchforge status`, `wp searchforge export`.
- WP-Cron scheduler for automated daily/weekly syncs.

## [1.0.0] - 2026-01-15

### Added
- Google Search Console integration with OAuth 2.0 (clicks, impressions, position, CTR).
- Per-page markdown brief export with keyword data, performance metrics, and recommendations.
- Combined site-wide markdown master brief export.
- Admin dashboard with top pages, top keywords, and KPI summary cards.
- SearchForge Score (0–100) with 4 components: Technical, Content, Authority, Momentum.
- Auto-generated recommendations based on score analysis.
- `llms.txt` and `llms-full.txt` auto-generation for AI crawler discovery.
- Sitemap discovery via `robots.txt` parsing.
- 10 database tables for snapshots, keywords, sync logs, briefs cache, alerts, and settings.
- Free tier with 10-page limit, 30-day data retention.
- Pro tier with unlimited pages, 365-day retention, full scoring, and REST API access.
- WordPress dashboard widget with quick SEO summary.
- PSR-4 autoloading and WordPress coding standards compliance.

### REST API
- `GET /searchforge/v1/status` — Plugin health and version.
- `GET /searchforge/v1/pages` — Top pages with metrics.
- `GET /searchforge/v1/keywords` — Top keywords.
- `GET /searchforge/v1/export/page` — Single page markdown brief.
- `GET /searchforge/v1/export/site` — Full site export.
- `POST /searchforge/v1/sync` — Manual sync trigger.
