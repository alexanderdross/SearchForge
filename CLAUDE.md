# CLAUDE.md — SearchForge Repository

## Repository Overview

SearchForge is a product ecosystem by **Dross:Media GmbH** that transforms raw SEO data into LLM-ready intelligence. This monorepo contains three components:

1. **SearchForge Theme** — WordPress marketing site theme
2. **SearchForge WordPress Plugin** — Core SEO data aggregation plugin
3. **SearchForge License Manager** — Centralized license management system

**Website:** https://searchforge.drossmedia.de
**Author:** Alexander Dross / Dross:Media GmbH
**Origin:** Migrated from the CacheWarmer repository (some CW- remnants exist)

---

## Repository Structure

```
SearchForge/
├── CLAUDE.md                              # This file
├── THEME-PLAN.md                          # Design system & theme development plan
├── searchforge-theme/                     # WordPress marketing theme (v1.1.0)
├── searchforge-theme.zip                  # Pre-built theme package
├── searchforge-wordpress-plugin/          # Core SEO plugin (v2.0.0)
├── searchforge-wordpress-plugin.zip       # Pre-built plugin package
├── searchforge-license-manager/           # License management plugin (v1.0.0)
└── searchforge-license-manager.zip        # Pre-built license manager package
```

---

## Component 1: SearchForge Theme

**Path:** `/searchforge-theme/`
**Version:** 1.1.0
**Type:** Static WordPress marketing theme
**Requirements:** WordPress 6.0+, PHP 8.2+
**License:** Proprietary (Dross:Media GmbH)

### Purpose

Product landing page and documentation hub for the SearchForge plugin at `searchforge.drossmedia.de`. Follows the Dross:Media product site pattern used by cachewarmer.drossmedia.de and pdfviewer.drossmedia.de.

### Architecture

```
searchforge-theme/
├── style.css                    # Theme metadata
├── functions.php                # Setup, assets, helpers (176 lines)
├── front-page.php               # Homepage (assembles template parts)
├── header.php                   # Sticky nav, mobile menu, breadcrumbs
├── footer.php                   # Legal links, attribution
├── page.php                     # Generic page template
├── index.php                    # Fallback template
├── 404.php                      # Error page
├── inc/
│   ├── seo-meta.php             # Meta tags, Open Graph, Twitter Cards
│   ├── schema.php               # JSON-LD structured data
│   ├── security.php             # Security headers, XML-RPC disabled
│   └── performance.php          # Font preload, jQuery removal
├── page-templates/              # 12 custom page templates
│   ├── page-features.php
│   ├── page-pricing.php
│   ├── page-enterprise.php
│   ├── page-docs.php
│   ├── page-docs-getting-started.php
│   ├── page-docs-data-sources.php
│   ├── page-docs-features.php
│   ├── page-docs-export-output.php
│   ├── page-docs-developer.php
│   ├── page-docs-integrations.php
│   ├── page-bundle.php          # SearchForge + CacheWarmer bundle
│   └── page-changelog.php
├── template-parts/              # 14+ reusable sections
│   ├── hero.php, stats-bar.php, problems.php, solutions.php
│   ├── data-sources.php, features.php, setup-steps.php
│   ├── comparison.php, pricing.php, compatibility.php
│   ├── cachewarmer-bundle.php, faq.php, final-cta.php
│   └── breadcrumb.php
├── assets/css/                  # 5 CSS files (~1,225 lines total)
│   ├── variables.css            # Design tokens
│   ├── base.css                 # Typography, reset
│   ├── components.css           # Buttons, cards, forms
│   ├── sections.css             # Page section styles
│   └── responsive.css           # Mobile breakpoints
├── assets/js/                   # 5 JS files (~169 lines total)
│   ├── navigation.js            # Mobile menu toggle
│   ├── faq.js                   # FAQ accordion
│   ├── pricing.js               # Pricing toggle
│   ├── animations.js            # Scroll animations (IntersectionObserver)
│   └── doc-nav.js               # Documentation sidebar
└── assets/images/
    ├── icons/                   # 25+ SVG feature/data-source icons
    ├── logo.svg, logo-mark.svg, logo-white.svg
    └── og-default.png           # Social sharing image (1200x630)
```

### Design System (from THEME-PLAN.md)

**Brand Colors:**
| Token | Hex | Usage |
|-------|-----|-------|
| `--sf-primary` | `#0f766e` | Primary teal — buttons, links, accents |
| `--sf-accent` | `#f59e0b` | CTAs, pricing highlights (amber) |
| `--sf-bg-dark` | `#0f172a` | Hero, footer (slate-900) |
| `--sf-bg-light` | `#f8fafc` | Light sections (slate-50) |

**Typography:**
- Headings: **Outfit** (700, clamp 2.5–3.75rem)
- Body: **Inter** (400, 1rem)
- Code: **JetBrains Mono** (400)

**Additional Colors:**
| Token | Hex | Usage |
|-------|-----|-------|
| `--sf-primary-dark` | `#0d5f59` | Hover state |
| `--sf-primary-light` | `#14b8a6` | Lighter variant |
| `--sf-accent-dark` | `#d97706` | Hover state |
| `--sf-bg-dark-alt` | `#1e293b` | Slate-800 |
| `--sf-text` | `#1e293b` | Body text |
| `--sf-text-muted` | `#64748b` | Secondary text |
| `--sf-success` | `#10b981` | Green |
| `--sf-error` | `#ef4444` | Red |

**Hero Gradient:** `linear-gradient(135deg, #0f766e 0%, #14b8a6 50%, #f59e0b 100%)`

**Spacing Scale:** `--space-xs` (0.25rem) through `--space-4xl` (6rem)
**Container:** max 1280px, narrow 800px, padding 1.5rem
**Border Radius:** sm (0.25rem), md (0.5rem), lg (0.75rem), xl (1rem), pill (9999px)
**Breakpoints:** 1024px (mobile menu), 768px (grid collapse), 640px (mobile)

**Theme Constants (functions.php):**
```php
SF_THEME_VERSION = '1.1.0'
SF_THEME_DIR     = get_template_directory()
SF_THEME_URI     = get_template_directory_uri()
```

### Key Features

- Mobile-first responsive design (768px, 640px breakpoints)
- Full SEO: meta descriptions, Open Graph, Twitter Cards, JSON-LD schema
- Security headers (X-Content-Type-Options, X-Frame-Options, Referrer-Policy)
- Performance: no jQuery, deferred JS, font preloading, block CSS removed
- Accessibility: skip links, ARIA attributes, semantic HTML, focus management
- Legal redirects: /imprint/, /privacy/, /contact/ → dross.net

### Pages Served

Home, Features, Pricing, Enterprise, Documentation (6 sub-pages), Changelog, CacheWarmer Bundle

---

## Component 2: SearchForge WordPress Plugin

**Path:** `/searchforge-wordpress-plugin/`
**Version:** 2.0.0
**Type:** WordPress plugin for SEO data aggregation
**Requirements:** WordPress 6.0+, PHP 8.0+
**License:** GPL-2.0-or-later
**Text Domain:** `searchforge`

### Purpose

Unifies search data from 8 sources (GSC, Bing, GA4, Keyword Planner, Google Trends, Google Business Profile, Bing Places, SerpAPI) into LLM-ready markdown briefs with a proprietary SEO scoring system.

### Architecture

```
searchforge-wordpress-plugin/
├── searchforge.php              # Main plugin file, hooks, activation
├── uninstall.php                # Cleanup on removal
├── CHANGELOG.md                 # Version history (v1.0.0 → v2.0.0)
├── includes/
│   ├── Autoloader.php           # PSR-4 autoloader
│   ├── Admin/
│   │   ├── Menu.php             # Admin menu (9 pages)
│   │   ├── Settings.php         # Settings + tier-based feature gating
│   │   ├── Dashboard.php        # KPI cards, charts, summary stats
│   │   ├── PageDetail.php       # Per-page analytics with Chart.js
│   │   ├── DashboardWidget.php  # WP dashboard widget
│   │   ├── Onboarding.php       # 3-step setup wizard
│   │   ├── Ajax.php             # 13 AJAX handlers
│   │   └── Assets.php           # CSS/JS enqueue
│   ├── Integrations/
│   │   ├── GSC/                 # Google Search Console (OAuth 2.0)
│   │   │   ├── OAuth.php
│   │   │   ├── Client.php
│   │   │   └── Syncer.php
│   │   ├── Bing/                # Bing Webmaster Tools (API key)
│   │   ├── GA4/                 # Google Analytics 4
│   │   │   └── Client.php
│   │   ├── KeywordPlanner/      # Search volume enrichment
│   │   └── Trends/              # Google Trends via SerpAPI
│   │       └── Engine.php
│   ├── Analysis/
│   │   ├── ContentBrief.php     # AI/heuristic brief generation (13KB)
│   │   ├── Clustering.php       # N-gram Jaccard similarity clustering
│   │   ├── Cannibalization.php  # Keyword cannibalization detection
│   │   └── Competitors.php      # Competitor tracking, SERP analysis
│   ├── Scoring/
│   │   └── Score.php            # 0-100 proprietary score (Tech+Content+Authority+Momentum)
│   ├── Export/
│   │   ├── MarkdownExporter.php # Per-page markdown briefs (16KB)
│   │   ├── CsvExporter.php      # CSV export
│   │   └── LlmsTxt.php         # /llms.txt and /llms-full.txt endpoints
│   ├── Alerts/
│   │   └── Monitor.php          # Ranking drops, traffic anomalies, decay
│   ├── Monitoring/
│   │   ├── AuditLog.php         # Activity tracking
│   │   ├── PerformanceTrend.py  # 7d/30d/90d trend detection
│   │   ├── SslChecker.php       # Certificate monitoring
│   │   ├── QuotaTracker.php     # API quota alerts
│   │   └── BrokenLinks.php      # Dead link scanning
│   ├── Notifications/
│   │   ├── WeeklyDigest.php     # Weekly summary email
│   │   └── Webhook.php          # Custom webhook support
│   ├── Api/
│   │   ├── RestController.php   # 13 REST endpoints (searchforge/v1)
│   │   └── ApiKeyAuth.php       # API key authentication
│   ├── Cli/
│   │   └── Commands.php         # WP-CLI: sync, status, export
│   ├── Database/
│   │   ├── Installer.php        # Table creation via dbDelta()
│   │   └── Cleanup.php          # Data retention enforcement
│   ├── Scheduler/
│   │   └── Manager.php          # WP-Cron management
│   └── Sitemap/                 # Sitemap discovery
├── templates/                   # Admin UI templates
└── assets/                      # Admin CSS/JS (Chart.js)
```

### Database Tables

| Table | Purpose |
|-------|---------|
| `sf_snapshots` | Daily page-level metrics (clicks, impressions, position, CTR) |
| `sf_keywords` | Keyword performance + search volume |
| `sf_sync_log` | Sync execution history |
| `sf_briefs_cache` | Generated brief content with expiration |
| `sf_alerts` | Alert history with severity and metadata |
| `sf_ga4_metrics` | GA4 behavior data (sessions, bounce, conversions) |
| `sf_settings` | Plugin configuration |

### Database Tables (Full List — 10 tables)

| Table | Purpose |
|-------|---------|
| `sf_snapshots` | Daily page-level metrics (clicks, impressions, position, CTR) |
| `sf_keywords` | Keyword performance + search volume |
| `sf_sync_log` | Sync execution history |
| `sf_briefs_cache` | Generated brief content with expiration |
| `sf_alerts` | Alert history with severity and metadata |
| `sf_ga4_metrics` | GA4 behavior data (sessions, bounce, conversions) |
| `sf_settings` | Plugin configuration |
| `sf_audit_log` | User action audit trail (Pro only) |
| `sf_competitors` | Competitor domain tracking |
| `sf_competitor_keywords` | Competitor keyword rankings |

### REST API Endpoints (namespace: `searchforge/v1` — 21 total)

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/status` | API key or Pro | Plugin health + version + tier |
| GET | `/pages` | API key or Pro | Paginated top pages |
| GET | `/keywords` | API key or Pro | Top keywords |
| GET | `/export/page` | API key or Pro | Single page markdown brief |
| GET | `/export/site` | API key or Pro | Full site export |
| POST | `/sync` | Admin only | Manual sync trigger |
| GET | `/cannibalization` | API key or Pro | Keyword overlap analysis |
| GET | `/clusters` | API key or Pro | Keyword topic clusters |
| GET | `/content-brief` | API key or Pro | AI/heuristic brief for page |
| GET | `/content-gaps` | API key or Pro | Competitor content gaps |
| GET | `/performance` | API key or Pro | Historical trends (7/30/90 days) |
| GET | `/quota` | API key or Pro | API quota usage |
| GET | `/ssl` | API key or Pro | SSL certificate status |
| GET | `/audit-log` | Admin only | Activity log (Pro) |
| GET | `/trends` | API key or Pro | Google Trends interest/related queries |
| GET | `/page-detail` | API key or Pro | Full page metrics + GA4 + score |
| GET | `/competitors` | API key or Pro | Registered competitor list |
| GET | `/competitors/overlap` | API key or Pro | Shared keywords with competitors |
| GET | `/competitors/gaps` | API key or Pro | Keywords competitors rank for |
| GET | `/competitors/visibility` | API key or Pro | Competitor SERP visibility comparison |

**API Key Auth:** via `Authorization: Bearer sf_xxx` or `X-SearchForge-Key: sf_xxx` headers

### WP-CLI Commands

```bash
wp searchforge sync [--source=gsc|bing|ga4|kwp|all]    # Trigger manual sync
wp searchforge status                                    # Show config + data summary
wp searchforge export pages|keywords|alerts|brief [--format=csv|json|md] [--file=path] [--page=/path/]
wp searchforge scan-links [--limit=20]                   # Broken link scanning (Pro)
wp searchforge quota                                     # Show API quota usage
```

### Feature Tiers

| Feature | Free | Pro (€99/yr) | Agency (€249/yr) |
|---------|------|--------------|-------------------|
| GSC Integration | 10 pages | Unlimited | Unlimited |
| Bing/GA4/KWP/Trends | — | Yes | Yes |
| SearchForge Score | Basic | Full | Full |
| Content Briefs | — | Heuristic + AI | Heuristic + AI |
| Competitor Tracking | — | 3 domains | Unlimited |
| Keyword Clustering | — | Yes | Yes |
| Data Retention | 30 days | 365 days | 365 days |
| REST API | — | Yes | Yes |
| Webhooks/Slack | — | Yes | Yes |
| Multi-site | — | — | Up to 10 sites |
| White-label | — | — | Yes |

**Bundle:** SearchForge Pro + CacheWarmer Premium = €169/yr (15% discount)

### SearchForge Score Algorithm

Proprietary 0-100 score with 4 equal components (25% each):

- **Technical (25%):** Position quality (weight 0.4), keyword breadth (weight 0.3), CTR vs. position benchmarks (weight 0.3). Expected CTR curve: Position 1 = 31.6%, Position 10 = 2.2%.
- **Content (25%):** Keyword diversity (weight 0.4), engagement/click ratio (weight 0.3), topic concentration penalty if top keyword >80% of clicks (weight 0.3).
- **Authority (25%):** Click volume on log scale (weight 0.4), impression reach on log scale (weight 0.3), position authority — Top 3 = 100%, Top 10 = 70% (weight 0.3).
- **Momentum (25%):** 14-day click trend (weight 0.6), position improvement (weight 0.4). Neutral baseline = 50 if insufficient data.

**Site-Level:** Pages indexed coverage + position quality + keyword breadth + diversity + total clicks (log) + 14d vs 28d trend.

**Auto-Generated Recommendations:** Position improvement, CTR optimization, content expansion, traffic concentration risk, authority building, momentum recovery, "almost page 1" opportunities (positions 11-15).

### Changelog Summary

| Version | Date | Highlights |
|---------|------|------------|
| 2.0.0 | 2026-03-08 | SEO metadata, schema markup, feature completeness |
| 1.9.0 | 2026-03-06 | Competitor tracking, SERP snapshots, content gaps |
| 1.8.0 | 2026-02-28 | Bulk export, weekly digest, dashboard charts |
| 1.7.0 | 2026-02-21 | Page detail view, Chart.js, GA4 metrics |
| 1.6.0 | 2026-02-14 | API auth, onboarding wizard, pagination |
| 1.5.0 | 2026-02-07 | Alerts, content decay, monitoring dashboard |
| 1.0.0 | 2026-01-15 | Initial: GSC, markdown export, llms.txt, scoring |

---

## Component 3: SearchForge License Manager

**Path:** `/searchforge-license-manager/`
**Version:** 1.0.0
**Type:** WordPress plugin for centralized license management
**Requirements:** WordPress 6.0+, PHP 8.2+
**License:** GPL-2.0-or-later
**Text Domain:** `sflm`

### Purpose

Centralized license management system for SearchForge products. Handles license key generation, activation/deactivation, Stripe payment integration, feature gating, JWT-based authentication, and multi-platform installation tracking.

### Architecture

```
searchforge-license-manager/
├── searchforge-license-manager.php    # Main plugin file, REST routes, cron
├── composer.json                       # Dependencies
├── uninstall.php                       # Cleanup
├── includes/
│   ├── class-sflm-license-manager.php  # License CRUD, key generation, lifecycle
│   ├── class-sflm-installation-tracker.php # Multi-platform install tracking
│   ├── class-sflm-jwt-handler.php      # JWT token gen/validation (HS256)
│   ├── class-sflm-feature-flags.php    # Per-tier feature matrix (22 features)
│   ├── class-sflm-rate-limiter.php     # Per-IP rate limiting (transients)
│   ├── class-sflm-audit-logger.php     # Activity tracking, IP anonymization
│   ├── class-sflm-database.php         # DB utilities, GDPR cleanup
│   ├── class-sflm-settings.php         # Config with AES-256-CBC encryption
│   ├── class-sflm-email.php            # License notification emails
│   ├── class-sflm-geoip.php            # MaxMind GeoIP2 integration
│   ├── class-sflm-activator.php        # Table creation, cron registration
│   └── class-sflm-deactivator.php      # Plugin removal cleanup
├── api/
│   ├── class-sflm-rest-controller.php  # Base controller (CORS, rate limit)
│   ├── class-sflm-health-endpoint.php  # GET /health
│   ├── class-sflm-validate-endpoint.php # POST /validate
│   ├── class-sflm-activate-endpoint.php # POST /activate
│   ├── class-sflm-check-endpoint.php   # POST /check (heartbeat)
│   ├── class-sflm-deactivate-endpoint.php # POST /deactivate
│   └── class-sflm-stripe-webhook.php   # POST /stripe/webhook
├── admin/
│   ├── class-sflm-admin.php            # Admin UI dispatch
│   ├── views/
│   │   ├── dashboard.php               # KPIs, charts, world map
│   │   ├── licenses.php                # License CRUD, search, filter
│   │   ├── installations.php           # Installation tracking
│   │   ├── products.php                # Stripe product mapping
│   │   ├── audit-log.php               # Audit trail
│   │   ├── settings.php                # Configuration
│   │   └── stripe-events.php           # Webhook event log
│   ├── sflm-admin.js                   # Form handling, dialogs
│   ├── sflm-dashboard.js               # Charts, world map SVG
│   └── sflm-admin.css                  # Admin styles
├── email-templates/
│   ├── license-created.php             # New license notification
│   └── license-expiring.php            # Expiry warning (7 days before)
└── tests/
    └── TEST-REPORT.md                  # QA audit report
```

### Database Tables

| Table | Purpose |
|-------|---------|
| `sflm_licenses` | License keys, tier, status, expiry, Stripe IDs |
| `sflm_installations` | Installation tracking by fingerprint, platform |
| `sflm_geo_data` | MaxMind geolocation per installation |
| `sflm_audit_logs` | GDPR-compliant action audit trail |
| `sflm_stripe_events` | Webhook idempotency tracking |
| `sflm_stripe_product_map` | Stripe product → tier mapping |

### REST API Endpoints (namespace: `sflm/v1`)

| Method | Endpoint | Rate Limit | Purpose |
|--------|----------|------------|---------|
| GET | `/health` | 120/min | System status check |
| POST | `/validate` | 60/min | License key format validation |
| POST | `/activate` | 10/min | Installation activation + JWT issuance |
| POST | `/check` | 30/min | Heartbeat + JWT refresh |
| POST | `/deactivate` | 10/min | Installation deactivation |
| POST | `/stripe/webhook` | None | Stripe event processing |

### License Key Format

Pattern: `{PREFIX}-{TIER}-{16_HEX_CHARS}`
- Tiers: `FREE`, `PRO`, `ENT`, `DEV`
- Example: `SF-PRO-A1B2C3D4E5F6G7H8`

### License Tiers & Feature Matrix

| Feature | Free | Pro | Enterprise | Dev |
|---------|------|-----|------------|-----|
| CDN Warming | Yes | Yes | Yes | Yes |
| Social Plugins | — | Yes | Yes | Yes |
| IndexNow | — | Yes | Yes | Yes |
| Search Console | — | Limited | Yes | Yes |
| Webhooks | — | — | Yes | Yes |
| Multi-site | — | — | Yes | Yes |
| White-label | — | — | Yes | Yes |
| Priority Support | — | — | Yes | — |
| Max Sites | 1 | 5 | Unlimited | Unlimited |
| Max URLs | 50 | 5,000 | Unlimited | Unlimited |

**Dev Tier:** Enterprise features minus priority support; restricted to dev domains (localhost, *.local, *.dev, *.test, 127.0.0.1).

### License Lifecycle

```
inactive → active → grace_period (14 days) → expired
              ↓          ↓
           revoked     revoked
```

### Stripe Integration

| Event | Action |
|-------|--------|
| `checkout.session.completed` | Create license from order |
| `invoice.payment_succeeded` | Auto-extend license |
| `invoice.payment_failed` | Log (no auto-action) |
| `customer.subscription.deleted` | Expire license |
| `charge.refunded` | Revoke license |
| `charge.dispute.created` | Revoke license |

### Security

- **JWT Auth:** HS256 tokens via `firebase/php-jwt`, 30-day expiry
- **Rate Limiting:** Per-IP, per-endpoint via WordPress transients
- **Encryption:** AES-256-CBC for stored secrets (JWT key, Stripe webhook secret)
- **IP Anonymization:** Last octet zeroed (IPv4), last 80 bits zeroed (IPv6)
- **CORS:** Configurable allowed origins
- **Stripe Webhooks:** HMAC-SHA256 signature + 5-minute replay protection

### Composer Dependencies

```json
{
  "require": {
    "php": ">=8.2",
    "geoip2/geoip2": "^3.0",
    "firebase/php-jwt": "^6.0",
    "stripe/stripe-php": "^13.0"
  },
  "require-dev": {
    "phpunit/phpunit": "^10.0",
    "wp-coding-standards/wpcs": "^3.0",
    "phpcompatibility/phpcompatibility-wp": "^2.1"
  }
}
```

### wp-config.php Constants

```php
define('SFLM_JWT_SECRET', 'min-32-char-secret');
define('SFLM_JWT_EXPIRY_DAYS', 30);
define('SFLM_CORS_ALLOWED_ORIGINS', 'https://app.searchforge.io');
define('SFLM_STRIPE_WEBHOOK_SECRET', 'whsec_...');
define('SFLM_GRACE_PERIOD_DAYS', 14);
define('SFLM_HEARTBEAT_INTERVAL_HOURS', 24);
define('SFLM_DEV_DOMAINS', 'localhost,*.local,*.dev,127.0.0.1');
define('SFLM_MAXMIND_DB_PATH', '/path/to/GeoLite2-City.mmdb');
```

---

## Known Issues

### Critical (Pre-Release Blockers)

| ID | Component | Issue |
|----|-----------|-------|
| BUG-01 | Plugin | GA4 column name mismatch (`avg_session_dur` vs `avg_session_duration`) causes silent data loss |
| BUG-02 | Plugin | WeeklyDigest email accesses wrong array structure |
| SEC-01 | Plugin | API keys accepted via query parameter (credential exposure in logs) |
| SEC-10 | Plugin | Broken link scanner lacks SSRF protection (private IP access) |
| MIGRATION-01 | License Manager | License key prefix still `CW-` instead of `SF-` |
| MIGRATION-02 | License Manager | Email templates reference "CacheWarmer" instead of "SearchForge" |
| MIGRATION-03 | License Manager | Admin menu label says "CacheWarmer LM" |

### High Severity

| ID | Component | Issue |
|----|-----------|-------|
| PERF-01 | Plugin | N+1 query pattern in ContentBrief |
| DATA-01 | Plugin | Delete-then-insert sync without transactions (data loss risk) |
| PERF-02 | Plugin | O(n²) keyword clustering algorithm (timeout at 1000+ keywords) |
| MIGRATION-04 | License Manager | Database column `cachewarmer_version` not renamed |
| MIGRATION-05 | License Manager | Settings descriptions mention "CacheWarmer-Installationen" |

### Medium Severity

- Plugin: No unit test suite (0% coverage, no PHPUnit infrastructure)
- Plugin: Missing composite database indexes
- Plugin: OAuth tokens stored plaintext in wp_options
- Plugin: Audit log IP logging without GDPR anonymization
- Plugin: Competitor analysis uses simulated data (not real SERP data)
- License Manager: Unused `sflm_rate_limits` table (rate limiting uses transients)
- License Manager: PHP version mismatch (header requires 8.2, composer requires 8.0)
- License Manager: Missing ARIA landmarks on admin pages

---

## Test Reports

### Plugin Test Coverage
- **Unit Tests:** 0% — No PHPUnit infrastructure exists
- **QA Report:** `searchforge-wordpress-plugin/tests/TEST-REPORT.md` (677 lines)
- Documents 4 critical, 9 high, 14 medium, 11 low, 8 info findings

### License Manager Test Coverage
- **Unit Tests:** 8 total (6 pass, 2 fail)
- **Regression Tests:** 3 total (1 pass, 2 fail — migration-related)
- **Security Tests:** 20 total (12 pass, 3 warnings)
- **QA Report:** `searchforge-license-manager/tests/TEST-REPORT.md` (541 lines)

---

## Integration Points

| From | To | Mechanism |
|------|-----|-----------|
| Theme → Plugin | Marketing CTAs link to plugin features | URL links |
| Theme → License Manager | CacheWarmer bundle promotion | Marketing page |
| Plugin → License Manager | Feature gating by license tier | License API check |
| Plugin → GSC/Bing/GA4 | Data sync via OAuth/API keys | REST APIs |
| License Manager → Stripe | Payment processing, subscription lifecycle | Webhooks |
| Plugin → llms.txt | AI crawler discovery | Rewrite rules |

---

## Plugin Settings Configuration

Key settings with defaults (stored in `sf_settings` table):

```php
'gsc_client_id'/'gsc_client_secret'/'gsc_access_token'/'gsc_refresh_token' => ''  // OAuth (plaintext!)
'gsc_property'               => ''           // GSC site URL
'gsc_max_pages'              => 0            // 0 = unlimited (Pro), capped at 10 (Free)
'bing_api_key'/'bing_site_url'/'bing_enabled' => ''|false
'kwp_customer_id'/'kwp_developer_token'       => ''
'kwp_language_id'            => '1000'       // English (US)
'kwp_geo_target'             => '2840'       // US
'serpapi_key'/'trends_enabled'/'ga4_property_id'/'ga4_enabled' => ''|false
'ai_api_key'                 => ''
'ai_provider'                => 'openai'     // or 'anthropic'
'webhook_url'/'webhook_format' => ''|'json'  // or 'slack'
'api_key'                    => ''           // wp_hash() of generated key
'alert_ranking_drop_threshold' => 3          // positions
'data_retention'             => 30           // days (365 for Pro)
'sync_frequency'             => 'daily'
'llms_txt_enabled'           => true
'license_key'/'license_tier' => ''|'free'
'competitors'                => []           // Domain list
```

### Admin Menu (9 pages)

Dashboard, Pages, Keywords, Analysis, Competitors, Monitoring, Export, Page Detail, Settings

### Integration Details

| Source | Auth Method | Rate Limit | Scope |
|--------|------------|------------|-------|
| Google Search Console | OAuth 2.0 refresh token | 25,000/day | `webmasters.readonly` |
| Bing Webmaster Tools | API key | 10,000/day | Page + query stats |
| Google Analytics 4 | Data API | 10,000 tokens/day | Sessions, bounce, conversions |
| Keyword Planner | Developer token + customer ID | 10,000 ops/day | Search volume, competition |
| Google Trends | SerpAPI key | 100/day | Interest over time, related queries |
| Sitemap Discovery | None | N/A | robots.txt → sitemap.xml parsing |

---

## Development Notes

### Build & Distribution

Pre-built ZIP packages are at the repository root:
- `searchforge-theme.zip` (84 KB)
- `searchforge-wordpress-plugin.zip` (138 KB)
- `searchforge-license-manager.zip` (144 KB)

### No Build Pipeline

- Theme uses vanilla CSS/JS (no bundler, no Sass, no TypeScript)
- Plugin uses vanilla PHP (no Composer dependencies)
- License Manager requires `composer install` for dependencies

### Coding Standards

- WordPress coding conventions (WPCS)
- PSR-4 autoloading (plugin), class-based autoloading (license manager)
- WordPress capability checks (`manage_options`) for admin pages
- Nonce verification on forms and AJAX handlers
- Prepared statements for database queries

### Related Documentation

- `THEME-PLAN.md` — Comprehensive design system, color palette, typography, page structure, development plan (~640 lines)
- `searchforge-wordpress-plugin/CHANGELOG.md` — Plugin version history
- `searchforge-wordpress-plugin/tests/TEST-REPORT.md` — Plugin QA audit
- `searchforge-license-manager/tests/TEST-REPORT.md` — License Manager QA audit
