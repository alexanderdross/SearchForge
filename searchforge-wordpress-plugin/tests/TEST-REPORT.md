# SearchForge WordPress Plugin — QA Test Report

**Plugin Version:** 1.9.0
**DB Version:** 1.5.0
**Audit Date:** 2026-03-06
**Auditor:** Automated Code Audit (Claude)
**Scope:** Full source code analysis of all PHP, JS, and CSS files

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Unit Test Assessment](#2-unit-test-assessment)
3. [Regression Test Assessment](#3-regression-test-assessment)
4. [Security Audit](#4-security-audit)
5. [Performance Audit](#5-performance-audit)
6. [User Acceptance Testing (UAT)](#6-user-acceptance-testing-uat)
7. [Accessibility Test](#7-accessibility-test)
8. [Bug Tracker](#8-bug-tracker)
9. [Recommendations Summary](#9-recommendations-summary)

---

## 1. Executive Summary

SearchForge is a feature-rich WordPress plugin (50+ source files) that integrates Google Search Console, Bing Webmaster Tools, Google Keyword Planner, Google Trends, and GA4 into a unified SEO dashboard with LLM-ready markdown export. The codebase demonstrates generally solid WordPress development practices with consistent use of `$wpdb->prepare()`, nonce verification, and capability checks. However, several critical and high-severity issues were identified across security, performance, and correctness domains.

### Severity Distribution

| Severity | Count |
|----------|-------|
| Critical | 4 |
| High | 9 |
| Medium | 14 |
| Low | 11 |
| Info | 8 |

### Top Risks

1. **API key accepted via query parameter** — credentials leak into server logs, browser history, and referrer headers
2. **Column name mismatch in PageDetail::get_ga4_data()** — runtime PHP errors on GA4-enabled sites
3. **WeeklyDigest email template accesses wrong array structure** — broken weekly digest emails
4. **N+1 query pattern in ContentBrief** — severe performance degradation at scale
5. **No unit test suite exists** — zero automated test coverage

---

## 2. Unit Test Assessment

### 2.1 Test Infrastructure

| Item | Status | Notes |
|------|--------|-------|
| PHPUnit configuration | **MISSING** | No `phpunit.xml` or `phpunit.xml.dist` found |
| Test directory | **MISSING** | No `tests/` directory existed prior to this audit |
| Test bootstrap | **MISSING** | No WordPress test bootstrap (`tests/bootstrap.php`) |
| Composer dev dependencies | **MISSING** | No `composer.json` found; no PHPUnit, Brain Monkey, or WP_Mock dependency |
| JavaScript test framework | **MISSING** | No Jest, Mocha, or QUnit configuration |
| CI/CD pipeline | **MISSING** | No `.github/workflows/`, `.gitlab-ci.yml`, or similar |

**Verdict: No automated tests exist. Test coverage is 0%.**

### 2.2 Recommended Unit Test Plan

The following components have the highest testability and risk, and should be prioritized:

#### Priority 1 — Pure Logic (No WordPress Dependencies)

| Component | File | Key Test Cases |
|-----------|------|----------------|
| Scoring algorithm | `includes/Scoring/Score.php` | CTR benchmark mapping, component weighting, score clamping (0-100), recommendation generation, edge cases (zero impressions, null values) |
| N-gram Jaccard similarity | `includes/Analysis/Clustering.php` | Bigram extraction, similarity calculation, cluster formation with threshold, empty input, single-keyword input |
| Cannibalization severity | `includes/Analysis/Cannibalization.php` | Severity thresholds (position spread, page 1 presence), edge case: exactly 2 pages |
| API key generation/validation | `includes/Api/ApiKeyAuth.php` | Key format (`sf_` prefix), hash comparison, header extraction (Bearer, X-SearchForge-Key, query param) |
| Markdown export formatting | `includes/Export/MarkdownExporter.php` | Section generation, number formatting, empty data handling, special characters in page paths |
| CSV export | `includes/Export/CsvExporter.php` | Column headers, data escaping, JSON output format, empty dataset |

#### Priority 2 — WordPress-Integrated (Requires WP_Mock or Brain Monkey)

| Component | File | Key Test Cases |
|-----------|------|----------------|
| Settings management | `includes/Admin/Settings.php` | Default values, sanitization callback, `is_pro()` logic, `get_page_limit()` tier mapping, OAuth token preservation |
| Database installer | `includes/Database/Installer.php` | Table creation SQL validity, `dbDelta()` call correctness, charset/collation |
| Autoloader | `includes/Autoloader.php` | Namespace-to-path mapping, case sensitivity, non-existent class |
| REST permission callbacks | `includes/Api/RestController.php` | Pro-only gating, API key auth, admin-only routes |
| Cleanup logic | `includes/Database/Cleanup.php` | Retention period calculation, table-specific cleanup, briefs cache expiration |

#### Priority 3 — Integration Tests

| Component | File | Key Test Cases |
|-----------|------|----------------|
| GSC OAuth flow | `includes/Integrations/GSC/OAuth.php` | State parameter validation, token refresh, callback redirect |
| Sitemap discovery | `includes/Sitemap/Discovery.php` | robots.txt parsing, recursive sitemap index, malformed XML handling |
| AJAX handlers | `includes/Admin/Ajax.php` | Nonce verification, capability check, input sanitization, response format |
| WP-CLI commands | `includes/Cli/Commands.php` | Argument parsing, output formatting, file write for export |

### 2.3 Estimated Coverage Gaps by Risk

| Risk Area | Files Affected | Estimated Effort |
|-----------|---------------|------------------|
| Scoring correctness | 1 | 2 days |
| Data sync integrity | 3 (GSC, Bing, GA4 Syncers) | 3 days |
| Export accuracy | 3 (CSV, Markdown, LlmsTxt) | 2 days |
| API authentication | 2 (ApiKeyAuth, RestController) | 1 day |
| Alert logic | 2 (Monitor, SslChecker) | 2 days |
| **Total estimated** | | **10 days** |

---

## 3. Regression Test Assessment

### 3.1 Identified Regression Risks

#### 3.1.1 Data Retention Setting Inconsistency
- **Severity:** Medium
- **File:** `includes/Admin/Settings.php`
- **Description:** The `DEFAULTS` array includes `'data_retention' => 90` and the settings UI allows users to modify this value. However, `get_retention_days()` returns hardcoded tier-based values (Free=30, Pro=90, Enterprise=365), ignoring the user-set value entirely.
- **Regression scenario:** A Pro user sets retention to 60 days via the UI; the system silently uses 90 days. If the tier-based logic is later "fixed" to respect the user setting, data could be unexpectedly deleted.
- **Test:** Verify `get_retention_days()` output matches user expectation and document intended behavior.

#### 3.1.2 Duplicate Weekly Digest Logic
- **Severity:** Medium
- **Files:** `includes/Alerts/Monitor.php` (lines ~200-250), `includes/Notifications/WeeklyDigest.php`
- **Description:** Both classes contain weekly digest email generation logic. `Monitor::maybe_send_weekly_digest()` and `WeeklyDigest::send()` both compose and send summary emails. The `Monitor` version is triggered by the alert check flow, while `WeeklyDigest` is registered as a standalone cron action.
- **Regression scenario:** Changes to the email template in one class are not reflected in the other, causing inconsistent digest emails. Alternatively, users may receive duplicate digests.
- **Test:** Verify only one digest pathway is active; consolidate to single implementation.

#### 3.1.3 Delete-Then-Insert Upsert Pattern
- **Severity:** High
- **Files:** `includes/Integrations/GSC/Syncer.php`, `includes/Integrations/Bing/Syncer.php`
- **Description:** Sync operations delete all existing rows for a page path before inserting new data. If the insert fails (e.g., database error, memory exhaustion, timeout), the old data is permanently lost with no rollback.
- **Regression scenario:** A partial sync failure results in pages showing zero data in the dashboard, which could trigger false content decay alerts.
- **Test:** Simulate insert failure after delete; verify data integrity. Consider wrapping in a transaction or switching to `INSERT ... ON DUPLICATE KEY UPDATE`.

#### 3.1.4 OAuth Token Preservation During Settings Save
- **Severity:** High
- **File:** `includes/Admin/Settings.php` (sanitize callback)
- **Description:** The sanitize callback explicitly preserves `gsc_access_token`, `gsc_refresh_token`, `gsc_token_expires`, and `gsc_properties` from the existing settings when the settings form is saved. If a new integration is added that stores tokens in settings, it must also be added to this preservation list.
- **Regression scenario:** Adding a new OAuth integration (e.g., GA4 separate token) without updating the sanitize callback would silently delete the new tokens on every settings save.
- **Test:** After settings save, verify all token fields are preserved. Add assertion for each known token key.

#### 3.1.5 Database Schema Evolution
- **Severity:** Medium
- **File:** `includes/Database/Installer.php`
- **Description:** The plugin relies entirely on `dbDelta()` for schema migration. `dbDelta()` can add columns and create tables but cannot rename columns, change column types, or drop columns. There is no explicit migration system (no numbered migrations, no migration tracking).
- **Regression scenario:** Renaming `avg_session_dur` to `avg_session_duration` (to fix the existing bug) via `dbDelta()` alone would create a new column while leaving the old one with stale data.
- **Test:** Verify upgrade path from each DB_VERSION to current. Test that `maybe_upgrade_db()` correctly triggers `Installer::install()`.

#### 3.1.6 Cron Schedule Changes
- **Severity:** Low
- **File:** `includes/Scheduler/Manager.php`
- **Description:** `reschedule_if_needed()` runs on `admin_init` and compares the current cron interval setting against the scheduled event. It uses `wp_unschedule_event()` + `wp_schedule_event()` which can briefly leave a gap where no event is scheduled.
- **Regression scenario:** If `admin_init` fires during a sync, the reschedule could unschedule the in-progress event's next occurrence.
- **Test:** Verify schedule integrity across settings changes.

### 3.2 Regression Test Matrix

| Feature | Trigger | Expected Behavior | Risk Level |
|---------|---------|-------------------|------------|
| GSC sync | Cron / Manual | All page data updated atomically | High |
| Settings save | Admin form submit | OAuth tokens preserved, settings validated | High |
| DB upgrade | Plugin update | All tables created/modified correctly | Medium |
| Weekly digest | Weekly cron | Single email sent with correct data | Medium |
| Data cleanup | Daily cron | Only expired data removed, active data intact | Medium |
| Pro tier gating | License change | Features lock/unlock correctly | Medium |
| Export (CSV/JSON/MD) | User action | Data matches dashboard display | Low |
| Alert generation | Post-sync | No duplicate alerts, correct severity | Low |
| Pagination | Navigation | Correct offset calculation, no data skipping | Low |

---

## 4. Security Audit

### 4.1 Authentication & Authorization

#### SEC-01: API Key Accepted via Query Parameter
- **Severity:** Critical
- **File:** `includes/Api/ApiKeyAuth.php`, line ~55
- **Description:** The `validate()` method accepts API keys from three sources: `Authorization: Bearer` header, `X-SearchForge-Key` header, and `api_key` query parameter. Query parameters are logged by web servers (access logs), cached by proxies, stored in browser history, and leaked via `Referer` headers.
- **Impact:** API key exposure in server logs, CDN logs, and browser history. Any log aggregation service would have access to valid API keys.
- **Recommendation:** Remove query parameter authentication. Accept keys only via headers.

#### SEC-02: REST API Pro-Only Gating in Permission Callback
- **Severity:** Medium
- **File:** `includes/Api/RestController.php`, `check_permissions()` method
- **Description:** `check_permissions()` returns `false` for non-Pro users, even those with valid `edit_posts` capabilities. This means the REST API is entirely inaccessible to free-tier users, but the error message does not distinguish between "not Pro" and "not authenticated."
- **Impact:** Confusing error responses for free-tier users attempting API access. The 403 response does not explain that Pro is required.
- **Recommendation:** Return a `WP_Error` with a descriptive message when the user is authenticated but lacks Pro tier.

### 4.2 SQL Injection

#### SEC-03: Unprepared SQL in Cleanup::run()
- **Severity:** Low
- **File:** `includes/Database/Cleanup.php`
- **Description:** The briefs cache cleanup query uses `"DELETE FROM {$briefs_table} WHERE expires_at < NOW()"` without `$wpdb->prepare()`. While `$briefs_table` is constructed from `$wpdb->prefix` (trusted) and a hardcoded suffix, it deviates from best practices.
- **Impact:** Minimal direct risk since the table name is not user-controlled. However, this pattern could be copy-pasted into contexts where the table name is dynamic.
- **Recommendation:** Use `$wpdb->prepare()` or `$wpdb->query($wpdb->prepare(...))` for consistency.

#### SEC-04: Direct Integer Interpolation in Dashboard Queries
- **Severity:** Low
- **File:** `includes/Admin/Dashboard.php`, `get_top_pages()` and `get_top_keywords()`
- **Description:** `$query_limit` and `$offset` are interpolated directly into SQL strings. Both are sanitized via `absint()` before interpolation, making injection unlikely.
- **Impact:** Safe in current implementation, but fragile if `absint()` calls are removed during refactoring.
- **Recommendation:** Use `$wpdb->prepare()` with `%d` placeholders for all numeric SQL parameters.

#### SEC-05: SHOW TABLES Query in PageDetail
- **Severity:** Low
- **File:** `includes/Admin/PageDetail.php`, `get_ga4_data()`
- **Description:** Uses `$wpdb->get_var("SHOW TABLES LIKE '{$table}'")` where `$table` is constructed from `$wpdb->prefix . 'sf_ga4_metrics'`. Not user-controlled.
- **Impact:** No direct risk, but deviates from prepared statement best practices.
- **Recommendation:** Use `$wpdb->prepare("SHOW TABLES LIKE %s", $table)`.

### 4.3 Cross-Site Scripting (XSS)

#### SEC-06: Template Output Escaping — PASS
- **Severity:** Info
- **All template files**
- **Description:** All 9 template files consistently use `esc_html()`, `esc_attr()`, `esc_url()`, and the `esc_html_e()`/`esc_attr_e()` translation functions for output. No instances of unescaped `echo` with user-controlled data were found.
- **Status:** PASS

#### SEC-07: JavaScript DOM Insertion
- **Severity:** Low
- **File:** `assets/js/admin.js`
- **Description:** AJAX response data is inserted into the DOM using jQuery methods like `.html()` and `.text()`. The `.text()` usage is safe. The `.html()` usage for modal content (`$('#sf-modal-body').text(data.markdown)`) correctly uses `.text()` for the markdown preview body. Export download uses `Blob` construction which is safe.
- **Status:** PASS (with minor note: verify all `.html()` calls use server-escaped data)

### 4.4 Cross-Site Request Forgery (CSRF)

#### SEC-08: Nonce Verification — PASS
- **Severity:** Info
- **Files:** `includes/Admin/Ajax.php`, all AJAX handlers
- **Description:** All 13 AJAX handlers verify nonces via `check_ajax_referer('searchforge_nonce', 'nonce')`. The nonce is localized via `wp_localize_script()` in `Assets.php`.
- **Status:** PASS

#### SEC-09: REST API Nonce
- **Severity:** Info
- **File:** `includes/Api/RestController.php`
- **Description:** REST API routes use `permission_callback` functions that check either API key auth or `current_user_can()`. WordPress REST API automatically verifies nonces for cookie-authenticated requests.
- **Status:** PASS

### 4.5 Server-Side Request Forgery (SSRF)

#### SEC-10: Broken Link Scanner SSRF Risk
- **Severity:** High
- **File:** `includes/Monitoring/BrokenLinks.php`
- **Description:** The broken link scanner extracts URLs from page content using regex and makes HTTP HEAD requests to each URL. While the scanner processes outbound links found in published content, it does not validate that target URLs are external. An attacker who can inject links into post content could cause the server to make requests to internal network addresses (e.g., `http://169.254.169.254/` for cloud metadata, `http://localhost:6379/` for Redis).
- **Impact:** Internal network reconnaissance, cloud metadata endpoint access, potential interaction with internal services.
- **Recommendation:** Validate URLs against a blocklist of private IP ranges (RFC 1918, link-local, loopback) before making requests. Use `wp_safe_remote_head()` instead of `wp_remote_head()`.

#### SEC-11: Sitemap Discovery SSRF Risk
- **Severity:** Medium
- **File:** `includes/Sitemap/Discovery.php`
- **Description:** `discover()` fetches user-provided domain URLs to find robots.txt and sitemaps. While the input comes from admin users (`manage_options` capability), the fetched content could redirect to internal URLs.
- **Impact:** Limited by admin-only access, but redirects could reach internal endpoints.
- **Recommendation:** Validate resolved IPs of fetched URLs. Use `wp_safe_remote_get()`.

### 4.6 Data Exposure

#### SEC-12: OAuth Tokens in wp_options
- **Severity:** Medium
- **File:** `includes/Integrations/GSC/OAuth.php`
- **Description:** GSC access tokens, refresh tokens, and expiry timestamps are stored in `wp_options` as part of the `searchforge_settings` option (serialized array). Any code with access to `get_option('searchforge_settings')` can read these tokens. The audit log redacts sensitive values, but the tokens are stored in plaintext.
- **Impact:** Any WordPress plugin or theme with database access can read OAuth tokens. Site export/migration could inadvertently include tokens.
- **Recommendation:** Encrypt tokens at rest using `wp_salt()` as an encryption key, or store in a separate option with restricted access.

#### SEC-13: Audit Log IP Logging
- **Severity:** Low
- **File:** `includes/Monitoring/AuditLog.php`
- **Description:** Logs the user's IP address via `$_SERVER['REMOTE_ADDR']`. This is PII under GDPR. The log retention is not configurable separately from data retention.
- **Impact:** GDPR compliance concern for EU-based sites.
- **Recommendation:** Add a setting to disable IP logging or anonymize IPs (truncate last octet).

### 4.7 Input Validation

#### SEC-14: Competitor Domain Validation
- **Severity:** Medium
- **File:** `includes/Analysis/Competitors.php`
- **Description:** Competitor domains are added via AJAX with `sanitize_text_field()` applied. However, there is no validation that the input is actually a valid domain name. Arbitrary strings could be stored and later used in API requests or display contexts.
- **Impact:** Stored invalid data; potential for crafted strings to cause issues in downstream processing.
- **Recommendation:** Validate domain format using `filter_var()` with `FILTER_VALIDATE_DOMAIN` or a regex pattern.

### 4.8 Security Summary

| ID | Issue | Severity | Status |
|----|-------|----------|--------|
| SEC-01 | API key in query parameter | Critical | Open |
| SEC-02 | Unclear Pro-only error response | Medium | Open |
| SEC-03 | Unprepared SQL in Cleanup | Low | Open |
| SEC-04 | Direct integer interpolation | Low | Open |
| SEC-05 | Unprepared SHOW TABLES | Low | Open |
| SEC-06 | Template escaping | Info | PASS |
| SEC-07 | JS DOM insertion | Low | PASS |
| SEC-08 | AJAX nonce verification | Info | PASS |
| SEC-09 | REST API nonce | Info | PASS |
| SEC-10 | Broken link scanner SSRF | High | Open |
| SEC-11 | Sitemap discovery SSRF | Medium | Open |
| SEC-12 | OAuth tokens plaintext | Medium | Open |
| SEC-13 | IP logging GDPR | Low | Open |
| SEC-14 | Competitor domain validation | Medium | Open |

---

## 5. Performance Audit

### 5.1 Database Query Performance

#### PERF-01: N+1 Query in ContentBrief::gather_context()
- **Severity:** Critical
- **File:** `includes/Analysis/ContentBrief.php`
- **Description:** `gather_context()` retrieves keywords for a page, then for each keyword calls `Cannibalization::detect()` individually. If a page has 50 keywords, this results in 50+ additional database queries, each scanning the entire keywords table.
- **Impact:** A page with 100 keywords could generate 100+ queries, each potentially scanning thousands of rows. On shared hosting, this can cause timeout errors and lock contention.
- **Recommendation:** Batch the cannibalization check. Call `Cannibalization::detect()` once for all keywords and filter results in PHP.

#### PERF-02: O(n^2) Keyword Clustering
- **Severity:** High
- **File:** `includes/Analysis/Clustering.php`
- **Description:** `cluster_keywords()` computes pairwise Jaccard similarity between all keywords. For `n` keywords, this is `O(n^2)` comparisons. With the default limit of 500 keywords, this means up to 124,750 similarity calculations.
- **Impact:** At 500 keywords, execution time may be acceptable (1-3 seconds). At 1000+ keywords (possible with Enterprise tier), this could exceed PHP's `max_execution_time`.
- **Recommendation:** Implement an inverted index approach: map each bigram to its keywords, then only compute similarity for keyword pairs sharing at least one bigram. This reduces comparisons dramatically.

#### PERF-03: Delete-Then-Insert Sync Pattern
- **Severity:** High
- **Files:** `includes/Integrations/GSC/Syncer.php`, `includes/Integrations/Bing/Syncer.php`
- **Description:** Each page path's data is synced by deleting all existing rows for that path, then inserting new rows one at a time in a loop. For a site with 500 pages averaging 20 keywords each, this is 10,000 individual INSERT statements per sync.
- **Impact:** Slow syncs (minutes on large sites), high database I/O, table lock contention on shared hosting.
- **Recommendation:**
  1. Wrap delete+insert in a transaction (`$wpdb->query('START TRANSACTION')`)
  2. Use batch INSERT with multiple value sets
  3. Consider `INSERT ... ON DUPLICATE KEY UPDATE` with a unique index on `(page_path, query, data_date, engine)`

#### PERF-04: No Index Hints in Complex Queries
- **Severity:** Medium
- **File:** `includes/Database/Installer.php`
- **Description:** The table creation SQL does not define indexes beyond primary keys. The `sf_keywords` table is queried by `page_path`, `query`, `data_date`, and `engine` in various combinations, but no composite indexes exist.
- **Impact:** Full table scans on keyword lookups for large datasets (10,000+ rows).
- **Recommendation:** Add indexes:
  ```sql
  CREATE INDEX idx_keywords_page ON {prefix}sf_keywords (page_path, data_date);
  CREATE INDEX idx_keywords_query ON {prefix}sf_keywords (query, engine);
  CREATE INDEX idx_snapshots_page ON {prefix}sf_snapshots (page_path, snapshot_date);
  CREATE INDEX idx_alerts_status ON {prefix}sf_alerts (status, created_at);
  ```

#### PERF-05: Transient Overuse for Summary Stats
- **Severity:** Low
- **File:** `includes/Admin/Dashboard.php`
- **Description:** `get_summary()` caches results in a transient with a 5-minute TTL. This is appropriate for the dashboard, but the transient is not invalidated after a sync completes, meaning the dashboard can show stale data for up to 5 minutes after a sync.
- **Impact:** Confusing UX — user triggers sync, returns to dashboard, sees old numbers.
- **Recommendation:** Delete the transient in the sync completion callback.

### 5.2 PHP Performance

#### PERF-06: Unnecessary Object Instantiation in GA4 Client
- **Severity:** Low
- **File:** `includes/Integrations/GA4/Client.php`
- **Description:** `get_access_token()` creates `new OAuth()` to retrieve the access token. The `OAuth` class constructor may perform initialization work that is unnecessary when only reading stored tokens.
- **Impact:** Minor memory/CPU overhead per GA4 API call.
- **Recommendation:** Use `Settings::get('gsc_access_token')` directly or add a static accessor.

#### PERF-07: Regex-Based Link Extraction
- **Severity:** Medium
- **File:** `includes/Monitoring/BrokenLinks.php`
- **Description:** Uses regex to extract `href` attributes from HTML content. This is fragile (may miss edge cases or match false positives) and slower than DOM parsing for large pages.
- **Impact:** Inaccurate link extraction; performance issues on pages with very large HTML content.
- **Recommendation:** Use `DOMDocument` with `loadHTML()` and `getElementsByTagName('a')` for reliable link extraction.

### 5.3 JavaScript Performance

#### PERF-08: Chart.js CDN Loading
- **Severity:** Low
- **File:** `includes/Admin/Assets.php`
- **Description:** Chart.js (v4.4.6) is loaded from jsdelivr CDN on all SearchForge admin pages, even those without charts (e.g., Settings, Export, Keywords).
- **Impact:** Unnecessary 200KB+ download on non-chart pages.
- **Recommendation:** Conditionally enqueue Chart.js only on dashboard and page-detail pages.

#### PERF-09: No Debounce on Search Input
- **Severity:** Low
- **Files:** Templates `keywords.php`, `pages.php`
- **Description:** Search forms submit on button click (standard form submit), which is appropriate. However, there is no client-side filtering or debounced AJAX search, causing full page reloads for each search.
- **Impact:** Acceptable for current implementation, but degrades UX for rapid search iterations.
- **Recommendation:** Consider adding AJAX-based search with debounce for a smoother experience (enhancement, not bug).

### 5.4 Performance Summary

| ID | Issue | Severity | Impact Area |
|----|-------|----------|-------------|
| PERF-01 | N+1 query in ContentBrief | Critical | DB load, timeouts |
| PERF-02 | O(n^2) clustering | High | CPU, timeouts |
| PERF-03 | Delete-then-insert sync | High | DB I/O, data integrity |
| PERF-04 | Missing database indexes | Medium | Query latency |
| PERF-05 | Stale transient after sync | Low | UX |
| PERF-06 | Unnecessary OAuth instantiation | Low | Memory |
| PERF-07 | Regex link extraction | Medium | Accuracy, CPU |
| PERF-08 | Chart.js on all pages | Low | Page load |
| PERF-09 | No debounced search | Low | UX |

---

## 6. User Acceptance Testing (UAT)

### 6.1 Functional Correctness

#### UAT-01: GA4 Column Name Mismatch — FAIL
- **Severity:** Critical
- **File:** `includes/Admin/PageDetail.php`, `get_ga4_data()`
- **Description:** The code references columns `avg_session_duration` and `page_views`, but the database schema in `Installer.php` defines the columns as `avg_session_dur` and `pageviews` (no underscore, abbreviated name).
- **Impact:** `get_ga4_data()` returns null/empty for these fields. The page detail template displays "N/A" or "0" for session duration and page views even when GA4 data exists. This is a silent data loss bug — no PHP error is thrown because `$wpdb->get_results()` simply returns rows without the requested columns.
- **Reproduction:** Enable GA4 integration, sync data, view any page detail. Session duration and page views will show as empty/zero.
- **Fix:** Change column references to match schema: `avg_session_dur` and `pageviews`.

#### UAT-02: Weekly Digest Email Data Structure Mismatch — FAIL
- **Severity:** Critical
- **File:** `includes/Notifications/WeeklyDigest.php`
- **Description:** The email template accesses `$comparison['clicks']['change_pct']`, expecting an associative array with a `change_pct` key. However, `PerformanceTrend::get_period_comparison()` returns `$comparison['changes']['clicks']` as a flat numeric value (the percentage change directly, not wrapped in an array).
- **Impact:** PHP `Trying to access array offset on value of type int/float` notice. The weekly digest email either shows incorrect data or fails to render the comparison section entirely.
- **Reproduction:** Wait for or trigger the weekly digest cron. Check the email content for the comparison section.
- **Fix:** Update the template to use the correct array path, e.g., `$comparison['changes']['clicks']` directly as the percentage.

#### UAT-03: Competitor Sync Generates Simulated Data — INFO
- **Severity:** Info
- **File:** `includes/Analysis/Competitors.php`, `sync_from_gsc()`
- **Description:** The competitor keyword sync method generates simulated data (random click counts, fabricated impressions) rather than fetching real competitor SERP data. The method name `sync_from_gsc` suggests it uses Google Search Console data, but GSC does not provide competitor data.
- **Impact:** Users may believe competitor analysis is based on real data. The UI does not clearly indicate that competitor metrics are estimates/simulations.
- **Recommendation:** Clearly label competitor data as "estimated" in the UI. Consider renaming the method to clarify its behavior.

#### UAT-04: Free Tier Keyword Limit Messaging
- **Severity:** Low
- **File:** `templates/keywords.php`
- **Description:** The notice "Free tier shows up to 100 keywords" appears only when `$total >= 100`. A user with exactly 99 keywords sees no warning, but may have more keywords that are being truncated.
- **Impact:** Minor UX confusion. The limit check should match the actual query limit applied during sync.
- **Recommendation:** Show the notice whenever `!$is_pro` and keywords exist, or base the check on whether sync was actually limited.

### 6.2 User Workflow Testing

#### UAT-05: Onboarding Flow
- **Status:** PASS (code review)
- **File:** `includes/Admin/Onboarding.php`
- **Description:** 3-step onboarding (Connect GSC, Select Property, First Sync) with AJAX dismiss and auto-dismiss when data exists. Nonce-protected. Steps correctly advance based on state.

#### UAT-06: Export Workflow
- **Status:** PASS (code review)
- **Files:** `templates/export.php`, `includes/Admin/Ajax.php`, `includes/Export/`
- **Description:** Site brief export, per-page brief export, and data export (CSV/JSON) all correctly gate on Pro status. Modal preview with download button works via Blob URL construction. File naming includes date stamp.

#### UAT-07: Pagination
- **Status:** PASS (code review)
- **Files:** `templates/pages.php`, `templates/keywords.php`
- **Description:** Pagination correctly calculates offset, preserves search query across pages, and shows correct total counts. Navigation arrows are conditionally rendered.

#### UAT-08: Settings Save Flow
- **Status:** PASS with caveat
- **File:** `includes/Admin/Settings.php`
- **Description:** Settings save via `register_setting()` with sanitization callback. OAuth tokens are preserved. However, the audit log entry for settings changes may not capture all changed fields accurately.
- **Caveat:** The `data_retention` setting is modifiable but ignored (see Regression 3.1.1).

#### UAT-09: Alert Dismissal
- **Status:** PASS (code review)
- **File:** `includes/Admin/Ajax.php`, `handle_dismiss_alert()`
- **Description:** Alert dismissal via AJAX with nonce verification. Status updated to "dismissed" in database.

#### UAT-10: Bulk Operations
- **Status:** PASS (code review)
- **Files:** `templates/pages.php`, `assets/js/admin.js`
- **Description:** Select-all checkbox, individual checkboxes, bulk export, and bulk AI brief generation with progress modal. Pro-only gating. Progress bar updates correctly.

### 6.3 Edge Cases

#### UAT-11: Empty State Handling
- **Status:** PASS
- **All templates**
- **Description:** All list views display appropriate empty-state messages when no data exists (e.g., "No keyword data available. Run a GSC sync first.").

#### UAT-12: Large Dataset Handling
- **Status:** Concern
- **Description:** With unlimited keywords (Enterprise tier), the clustering endpoint and content brief generation could timeout. No user-visible timeout warning or progress indicator exists for these long-running operations.
- **Recommendation:** Add a spinner/progress indicator for clustering. Consider async processing for large datasets.

### 6.4 UAT Summary

| ID | Test Case | Status | Severity |
|----|-----------|--------|----------|
| UAT-01 | GA4 data display | FAIL | Critical |
| UAT-02 | Weekly digest email | FAIL | Critical |
| UAT-03 | Competitor data labeling | INFO | Info |
| UAT-04 | Free tier limit messaging | Note | Low |
| UAT-05 | Onboarding flow | PASS | — |
| UAT-06 | Export workflow | PASS | — |
| UAT-07 | Pagination | PASS | — |
| UAT-08 | Settings save | PASS* | — |
| UAT-09 | Alert dismissal | PASS | — |
| UAT-10 | Bulk operations | PASS | — |
| UAT-11 | Empty states | PASS | — |
| UAT-12 | Large dataset handling | Concern | Medium |

---

## 7. Accessibility Test

### 7.1 WCAG 2.1 Level A Compliance

#### A11Y-01: Tab Navigation Without ARIA Roles — FAIL
- **Severity:** High
- **Files:** `templates/settings.php`, `templates/monitoring.php`, `templates/page-detail.php`
- **Description:** Tab interfaces use JavaScript-based switching (show/hide divs) without ARIA attributes. Missing:
  - `role="tablist"` on the tab container
  - `role="tab"` on each tab button/link
  - `role="tabpanel"` on each content panel
  - `aria-selected="true/false"` on tabs
  - `aria-controls` linking tabs to panels
  - `aria-labelledby` on panels
  - Keyboard navigation (Arrow keys to switch tabs, Home/End for first/last)
- **Impact:** Screen reader users cannot identify the tab interface or navigate between tabs. Keyboard-only users must Tab through all tabs rather than using Arrow keys.
- **Recommendation:** Implement the WAI-ARIA Tabs pattern: https://www.w3.org/WAI/ARIA/apg/patterns/tabs/

#### A11Y-02: Modal Dialogs Lack Focus Management — FAIL
- **Severity:** High
- **Files:** `templates/export.php`, `templates/pages.php`, `assets/js/admin.js`
- **Description:** Modal dialogs (export preview, bulk progress) are shown/hidden via CSS `display:none/block`. Missing:
  - `role="dialog"` and `aria-modal="true"`
  - `aria-labelledby` pointing to the modal title
  - Focus trap (Tab should cycle within the modal)
  - Focus return to trigger element on close
  - Escape key to close
  - Background scroll lock
- **Impact:** Screen reader users are not informed a dialog has opened. Keyboard users can Tab behind the modal to interact with the page underneath. Focus is lost when modal closes.
- **Recommendation:** Implement the WAI-ARIA Dialog pattern. Consider using the native `<dialog>` element or a tested library.

#### A11Y-03: Charts Not Accessible to Screen Readers — FAIL
- **Severity:** High
- **File:** `assets/js/charts.js`, `templates/dashboard.php`, `templates/page-detail.php`
- **Description:** Chart.js `<canvas>` elements render visual charts but provide no alternative text, data table, or ARIA description for screen reader users. The chart containers have no `role`, `aria-label`, or associated `<table>` fallback.
- **Impact:** All chart data is completely invisible to screen reader users. This includes the primary dashboard metrics chart and page detail trend charts.
- **Recommendation:**
  1. Add `aria-label` or `aria-describedby` to each chart container
  2. Provide a visually hidden `<table>` with the same data as each chart
  3. Or use Chart.js accessibility plugin to generate descriptions

#### A11Y-04: Color-Only Severity Indicators — FAIL
- **Severity:** Medium
- **Files:** `assets/css/admin.css`, `templates/analysis.php`, `templates/monitoring.php`
- **Description:** Severity indicators (cannibalization severity, alert severity, SSL status) use color as the sole differentiator. The CSS classes `.sf-severity-high`, `.sf-severity-medium`, `.sf-severity-low` use red/orange/yellow colors. While severity text labels are present in some cases, the visual badges rely primarily on color.
- **Impact:** Users with color vision deficiency may not be able to distinguish between severity levels at a glance.
- **Recommendation:** Add icons, patterns, or shape differentiators in addition to color. For example: high = red + triangle icon, medium = orange + circle icon, low = yellow + dash icon.

#### A11Y-05: Missing Skip Navigation Link
- **Severity:** Medium
- **Description:** No skip-to-content link is provided. While the plugin operates within the WordPress admin which has its own skip link, the plugin's content area has no landmark structure or skip links for its own complex layouts.
- **Impact:** Keyboard users must tab through the full WordPress admin menu and plugin navigation before reaching content on each page load.
- **Recommendation:** Add `role="main"` to the primary content area. WordPress admin generally handles this, but verify that the plugin's wrap div is within the main content landmark.

#### A11Y-06: Form Labels and Descriptions
- **Status:** PASS
- **Files:** `templates/settings.php`, `templates/keywords.php`, `templates/pages.php`
- **Description:** Search inputs have associated `<label>` elements (screen-reader-text class). Settings fields have proper labels and descriptions. The `for` attributes correctly reference input `id` values.

#### A11Y-07: Data Table Accessibility
- **Status:** Partial PASS
- **All template files with tables**
- **Description:** Tables use `<thead>` and `<tbody>` correctly. Column headers use `<th>` elements. However:
  - Tables lack `<caption>` elements describing the table content
  - No `scope="col"` on `<th>` elements
  - Sortable columns are not indicated
- **Impact:** Screen readers can navigate tables but lack context about what each table represents.
- **Recommendation:** Add `<caption>` to each table (can be visually hidden). Add `scope="col"` to all `<th>` elements.

#### A11Y-08: Button and Link Text
- **Status:** Partial PASS
- **Description:** Most buttons have descriptive text. However:
  - The external link indicator "&#8599;" (arrow character) in `pages.php` has a `title` attribute but no `aria-label` or screen-reader-only text
  - Pagination arrows use `&lsaquo;` and `&rsaquo;` without accessible labels
  - The modal close button uses `&times;` without an `aria-label`
- **Recommendation:**
  - Add `aria-label="View page (opens in new tab)"` to external links
  - Add `aria-label="Previous page"` / `aria-label="Next page"` to pagination
  - Add `aria-label="Close"` to modal close buttons

#### A11Y-09: Focus Visibility
- **Status:** PASS
- **File:** `assets/css/admin.css`
- **Description:** The CSS does not override or suppress browser default focus styles (`:focus { outline: none }` is not present). WordPress admin default focus styles apply.

#### A11Y-10: Reduced Motion
- **Status:** FAIL
- **File:** `assets/css/admin.css`
- **Description:** CSS includes transitions and animations (progress bar fill, card hover effects) without a `@media (prefers-reduced-motion: reduce)` query.
- **Impact:** Users who have enabled reduced motion in their OS settings will still see animations.
- **Recommendation:** Add:
  ```css
  @media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
      animation-duration: 0.01ms !important;
      transition-duration: 0.01ms !important;
    }
  }
  ```

### 7.2 Accessibility Summary

| ID | Issue | WCAG Criterion | Severity | Status |
|----|-------|---------------|----------|--------|
| A11Y-01 | Tabs without ARIA | 4.1.2 Name, Role, Value | High | FAIL |
| A11Y-02 | Modal focus management | 2.4.3 Focus Order | High | FAIL |
| A11Y-03 | Inaccessible charts | 1.1.1 Non-text Content | High | FAIL |
| A11Y-04 | Color-only indicators | 1.4.1 Use of Color | Medium | FAIL |
| A11Y-05 | Skip navigation | 2.4.1 Bypass Blocks | Medium | FAIL |
| A11Y-06 | Form labels | 1.3.1 Info and Relationships | — | PASS |
| A11Y-07 | Table structure | 1.3.1 Info and Relationships | Low | Partial |
| A11Y-08 | Button/link text | 2.4.4 Link Purpose | Low | Partial |
| A11Y-09 | Focus visibility | 2.4.7 Focus Visible | — | PASS |
| A11Y-10 | Reduced motion | 2.3.3 Animation from Interactions | Low | FAIL |

---

## 8. Bug Tracker

### Confirmed Bugs

| # | Title | Severity | File | Line(s) | Description |
|---|-------|----------|------|---------|-------------|
| BUG-01 | GA4 column name mismatch | Critical | `includes/Admin/PageDetail.php` | `get_ga4_data()` | References `avg_session_duration` and `page_views` but schema defines `avg_session_dur` and `pageviews` |
| BUG-02 | WeeklyDigest wrong array path | Critical | `includes/Notifications/WeeklyDigest.php` | Email template | Accesses `$comparison['clicks']['change_pct']` but data structure is `$comparison['changes']['clicks']` (flat number) |
| BUG-03 | data_retention setting ignored | Medium | `includes/Admin/Settings.php` | `get_retention_days()` | User-set retention value in settings UI is overridden by hardcoded tier-based values |
| BUG-04 | Duplicate weekly digest pathways | Medium | `includes/Alerts/Monitor.php`, `includes/Notifications/WeeklyDigest.php` | — | Two separate classes can send weekly digest emails, risking duplicate sends |
| BUG-05 | Competitor sync simulated data | Low | `includes/Analysis/Competitors.php` | `sync_from_gsc()` | Method name suggests GSC integration but generates random/simulated data |

### Potential Issues (Require Runtime Verification)

| # | Title | Severity | File | Description |
|---|-------|----------|------|-------------|
| POT-01 | Sync data loss on partial failure | High | GSC/Bing Syncer | Delete-then-insert without transaction could lose data if insert fails mid-batch |
| POT-02 | Clustering timeout on large datasets | Medium | `Analysis/Clustering.php` | O(n^2) may exceed max_execution_time at 1000+ keywords |
| POT-03 | ContentBrief timeout on keyword-rich pages | Medium | `Analysis/ContentBrief.php` | N+1 queries could cause timeouts on pages with many keywords |
| POT-04 | Stale dashboard data after sync | Low | `Admin/Dashboard.php` | 5-minute transient not cleared on sync completion |

---

## 9. Recommendations Summary

### Immediate (Pre-Release Blockers)

1. **Fix BUG-01:** Update `PageDetail::get_ga4_data()` column references to match schema (`avg_session_dur`, `pageviews`)
2. **Fix BUG-02:** Correct the array path in `WeeklyDigest` email template to match `PerformanceTrend::get_period_comparison()` return structure
3. **Fix SEC-01:** Remove query parameter API key authentication from `ApiKeyAuth::validate()`
4. **Fix SEC-10:** Add private IP range validation to `BrokenLinks` scanner; use `wp_safe_remote_head()`

### Short-Term (Next Sprint)

5. Add database indexes for `sf_keywords` and `sf_snapshots` (PERF-04)
6. Wrap sync delete+insert in database transactions (PERF-03)
7. Batch cannibalization detection in ContentBrief (PERF-01)
8. Add ARIA attributes to tab interfaces (A11Y-01)
9. Implement focus trapping in modals (A11Y-02)
10. Consolidate duplicate weekly digest logic (BUG-04)

### Medium-Term (Next Release)

11. Establish PHPUnit test suite with minimum 60% coverage on Priority 1 components
12. Add `<caption>` and `scope` attributes to all data tables (A11Y-07)
13. Add accessible alternative for charts (A11Y-03)
14. Encrypt OAuth tokens at rest (SEC-12)
15. Add `prefers-reduced-motion` CSS media query (A11Y-10)
16. Optimize clustering algorithm for large datasets (PERF-02)
17. Resolve `data_retention` setting inconsistency (BUG-03)

### Long-Term (Roadmap)

18. Add CI/CD pipeline with automated tests, linting, and accessibility scanning
19. Implement real competitor data sourcing or clearly label simulated data
20. Add GDPR-compliant IP anonymization option (SEC-13)
21. Add progressive enhancement for JavaScript-dependent features
22. Consider migration framework for database schema evolution

---

*Report generated by automated code audit. All findings are based on static code analysis and require runtime verification for confirmation. Severity ratings follow a Critical > High > Medium > Low > Info scale.*
