# SearchForge — Static WordPress Theme Plan

## Overview

A lightweight, static WordPress theme for the SearchForge marketing site (`searchforge.drossmedia.de`). Follows the established Dross:Media product site pattern used by [cachewarmer.drossmedia.de](https://cachewarmer.drossmedia.de) and [pdfviewer.drossmedia.de](https://pdfviewer.drossmedia.de).

**Theme Name:** SearchForge
**Text Domain:** `searchforge-theme`
**Requires WordPress:** 6.0+
**Requires PHP:** 8.2+
**License:** Proprietary (Dross:Media GmbH)

---

## Design System

### Brand Identity

SearchForge transforms raw SEO data into LLM-ready intelligence. The brand communicates **precision**, **data synthesis**, and **AI-readiness**.

**Tagline:** "SEO Data, LLM-Ready."
**Subline:** "Turn Search Console, Bing, GA4 & Trends into actionable AI briefs — directly in WordPress."

### Color Palette

| Token | Hex | Usage |
|-------|-----|-------|
| `--sf-primary` | `#0f766e` | Primary brand (teal-700) — buttons, links, accents |
| `--sf-primary-dark` | `#0d5f59` | Hover states, active elements |
| `--sf-primary-light` | `#14b8a6` | Highlights, badges, gradient endpoint |
| `--sf-accent` | `#f59e0b` | CTAs, pricing highlights, attention (amber-500) |
| `--sf-accent-dark` | `#d97706` | Accent hover states |
| `--sf-bg-dark` | `#0f172a` | Dark sections (hero, footer) — slate-900 |
| `--sf-bg-light` | `#f8fafc` | Light sections — slate-50 |
| `--sf-bg-card` | `#ffffff` | Card backgrounds |
| `--sf-text` | `#1e293b` | Primary text — slate-800 |
| `--sf-text-muted` | `#64748b` | Secondary text — slate-500 |
| `--sf-text-inverse` | `#f1f5f9` | Text on dark backgrounds — slate-100 |
| `--sf-border` | `#e2e8f0` | Borders, dividers — slate-200 |
| `--sf-success` | `#10b981` | Positive metrics, checks |
| `--sf-warning` | `#f59e0b` | Alerts, caution |
| `--sf-error` | `#ef4444` | Errors, critical |

**Gradient (hero/headings):** `linear-gradient(135deg, #0f766e 0%, #14b8a6 50%, #f59e0b 100%)`

### Typography

| Element | Font | Weight | Size |
|---------|------|--------|------|
| Headings | Outfit | 700 | 2.5rem–3.75rem (clamp) |
| Subheadings | Outfit | 600 | 1.5rem–2rem |
| Body | Inter | 400 | 1rem (16px) |
| Body emphasis | Inter | 500 | 1rem |
| Small/Caption | Inter | 400 | 0.875rem |
| Monospace | JetBrains Mono | 400 | 0.875rem |

**Loading:** Google Fonts via `<link>` with `display=swap`. Fallback: system sans-serif.

### Spacing Scale

```
--space-xs:   0.25rem   (4px)
--space-sm:   0.5rem    (8px)
--space-md:   1rem      (16px)
--space-lg:   1.5rem    (24px)
--space-xl:   2rem      (32px)
--space-2xl:  3rem      (48px)
--space-3xl:  4rem      (64px)
--space-4xl:  6rem      (96px)
```

### Border Radius

```
--radius-sm:  0.25rem
--radius-md:  0.5rem
--radius-lg:  0.75rem
--radius-xl:  1rem
--radius-pill: 9999px
```

---

## Page Structure

### Global: Navigation (Sticky Header)

```
┌──────────────────────────────────────────────────────────────┐
│  [SF Logo]  Features  Pricing  Docs  Changelog  Enterprise  │
│                                            [Get Pro →]       │
└──────────────────────────────────────────────────────────────┘
```

- **Desktop:** Horizontal bar, max-width 1280px centered
- **Mobile (<1024px):** Hamburger menu, slide-in drawer
- **Style:** `backdrop-filter: blur(12px)`, semi-transparent background, subtle bottom border
- **Logo:** SVG mark + "Search**Forge**" wordmark ("Search" in teal, "Forge" in default)
- **CTA button:** "Get Pro" — filled teal, pill shape

### Section 1: Hero

```
┌──────────────────────────────────────────────────────────────┐
│                                                              │
│            SEO Data, LLM-Ready.                              │
│                                                              │
│   Turn Search Console, Bing, GA4 & Trends into              │
│   actionable AI briefs — directly in WordPress.              │
│                                                              │
│   [Get Started Free]  [View Pricing]                         │
│                                                              │
│   ┌────────────────────────────────────────────┐             │
│   │  # AI Content Brief: /germany/             │             │
│   │  **SearchForge Score:** 72/100             │             │
│   │  **Clicks (28d):** 1,247 ↑ +18%           │             │
│   │  **Top Query:** "aip germany" (Pos 3.2)   │             │
│   │  **AI Citation:** Google AI ✓ ChatGPT ✓   │             │
│   │  **Action:** Expand FAQ section for AEO    │             │
│   └────────────────────────────────────────────┘             │
│                                                              │
└──────────────────────────────────────────────────────────────┘
```

- **Background:** Dark gradient (slate-900 → slate-800)
- **Headline:** Gradient text effect (`background-clip: text`)
- **Code block:** Styled markdown preview with syntax highlighting, subtle glow border
- **CTAs:** Primary (teal filled) + Secondary (outline)
- **Padding:** 6rem top/bottom

### Section 2: Social Proof / Stats Bar

```
┌──────────────────────────────────────────────────────────────┐
│   8 Data Sources  ·  30+ Export Formats  ·  AI-Ready Briefs │
│   ·  From €0/year  ·  WordPress Native                      │
└──────────────────────────────────────────────────────────────┘
```

- **Style:** Light background, centered text, icon + number pairs
- **Separator:** Subtle dot or pipe

### Section 3: The Problem (Pain Points)

**Heading:** "Your SEO Data Is Trapped in Silos"

```
┌────────────┐  ┌────────────┐  ┌────────────┐
│ 🔒 Locked  │  │ 📊 Manual  │  │ 🤖 AI-Blind│
│ in Consoles│  │ Copy-Paste │  │ No LLM     │
│            │  │            │  │ Context    │
│ GSC, Bing, │  │ Hours spent│  │ ChatGPT &  │
│ GA4 each   │  │ exporting  │  │ Perplexity │
│ live in    │  │ CSVs and   │  │ can't see  │
│ separate   │  │ building   │  │ your SEO   │
│ dashboards │  │ spreadsheet│  │ performance│
└────────────┘  └────────────┘  └────────────┘

┌────────────┐  ┌────────────┐  ┌────────────┐
│ 📈 No      │  │ ⏰ Outdated │  │ 💰 $1,400/yr│
│ Combined   │  │ by the Time│  │ for Basic  │
│ View       │  │ You Export │  │ Competitor │
│            │  │            │  │ Data       │
│ Google vs  │  │ Weekly CSV │  │ Semrush &  │
│ Bing vs GA │  │ exports    │  │ Ahrefs are │
│ never      │  │ are stale  │  │ overkill   │
│ correlated │  │ on arrival │  │ for most   │
└────────────┘  └────────────┘  └────────────┘
```

- **Layout:** 3×2 grid, icon cards with subtle border
- **Style:** Light background section

### Section 4: The Solution (Benefits)

**Heading:** "One Plugin. All Your SEO Data. AI-Ready."

```
┌────────────┐  ┌────────────┐  ┌────────────┐
│ ✓ Unified  │  │ ✓ Auto-Sync│  │ ✓ LLM      │
│ Dashboard  │  │ Daily      │  │ Markdown   │
│            │  │            │  │ Export     │
│ GSC + Bing │  │ Background │  │ Per-page   │
│ + GA4 +    │  │ sync keeps │  │ briefs     │
│ Trends in  │  │ data fresh │  │ ready for  │
│ one place  │  │ always     │  │ Claude/GPT │
└────────────┘  └────────────┘  └────────────┘
```

- **Layout:** Mirror of problem section, but with teal accent borders/icons
- **Background:** White

### Section 5: Data Sources Showcase

**Heading:** "8 Data Sources. One Unified Brief."

```
┌──────────────────────────────────────────────────────────────┐
│                                                              │
│  [GSC]  [Bing]  [GA4]  [Keyword Planner]                    │
│  [Trends]  [GBP]  [Bing Places]  [SerpApi]                  │
│                                                              │
│  ┌──────────────────────────────────────────────┐            │
│  │  Combined Master Brief: /germany/            │            │
│  │                                               │            │
│  │  ## Search Performance (GSC + Bing)           │            │
│  │  | Query           | Google | Bing | Volume | │            │
│  │  |-----------------|--------|------|--------| │            │
│  │  | aip germany     | 3.2    | 2.8  | 720    | │            │
│  │  | german aip      | 5.1    | 4.3  | 390    | │            │
│  │                                               │            │
│  │  ## On-Page Behavior (GA4)                    │            │
│  │  Bounce: 68% · Engagement: 1:12 · Conv: 3    │            │
│  │                                               │            │
│  │  ## AI Visibility (AEO)                       │            │
│  │  Google AI: ✓ Cited · ChatGPT: ✓ Source #1   │            │
│  └──────────────────────────────────────────────┘            │
│                                                              │
└──────────────────────────────────────────────────────────────┘
```

- **Background:** Slate-50
- **Source badges:** Clickable pills that highlight corresponding brief section
- **Brief preview:** Dark code block with syntax-highlighted markdown

### Section 6: Key Features Grid

**Heading:** "Everything You Need to Win in Search + AI"

| Feature | Description |
|---------|-------------|
| SearchForge Score | Proprietary 0-100 SEO score with actionable breakdown |
| AI Visibility Monitor | Track citations in ChatGPT, Perplexity, Google AI Overviews |
| Competitor Intelligence | See who outranks you and why — without $1,400/yr tools |
| Content Briefs | AI-generated per-page briefs with specific recommendations |
| `llms.txt` Generation | Auto-generate `/llms.txt` for AI crawler discoverability |
| Keyword Clustering | Group keywords into topic clusters with pillar page suggestions |
| Content Decay Alerts | Get notified when rankings start declining |
| Historical Trends | 12-month rolling snapshots with YoY comparison |
| Cannibalization Detection | Find pages competing for the same keywords |
| Multi-Source Export | Combined markdown briefs from all 8 data sources |

- **Layout:** 2×5 grid on desktop, single column on mobile
- **Cards:** Icon (teal circle) + title + 2-line description
- **Hover:** Subtle lift + shadow

### Section 7: 3-Step Setup

**Heading:** "Up and Running in 3 Minutes"

```
   ①                    ②                    ③
Install Plugin    Connect Google      Export AI Briefs
                  Search Console

Upload the ZIP    One-click OAuth     Per-page markdown
or install from   — GSC, GA4, and    briefs ready for
wordpress.org     Bing connect in    Claude, ChatGPT,
                  seconds            or any LLM
```

- **Layout:** 3 columns with numbered circles
- **Style:** Clean, minimal, with connecting line between steps
- **Background:** White

### Section 8: Comparison Table

**Heading:** "SearchForge vs. The Old Way"

| Capability | Manual / Spreadsheets | SearchForge |
|-----------|----------------------|-------------|
| Data collection | Hours per week | Automatic daily sync |
| Sources combined | Copy-paste between tabs | 8 sources in one dashboard |
| LLM-ready export | Reformat manually | One-click markdown brief |
| AI visibility | Not tracked | Monitored weekly |
| Competitor data | $1,400/yr (Semrush) | Included in Pro (€99/yr) |
| Historical trends | Lost when CSVs pile up | 12-month rolling snapshots |
| Content recommendations | Guess | AI-generated per page |

- **Style:** Alternating row colors, checkmarks vs. crosses
- **Background:** Slate-50

### Section 9: Pricing

**Heading:** "Simple, Transparent Pricing"

```
┌────────────┐  ┌────────────────┐  ┌────────────┐
│   FREE     │  │     PRO        │  │   AGENCY   │
│   €0/yr    │  │   €99/yr       │  │  €249/yr   │
│            │  │  ★ Most Popular │  │            │
│ GSC (10pg) │  │ All 8 sources  │  │ Everything │
│ Basic score│  │ Full score     │  │ 10 sites   │
│ llms.txt   │  │ AI briefs      │  │ White-label│
│ 1 site     │  │ AEO monitor    │  │ Client     │
│            │  │ Competitors    │  │ portal     │
│ [Start     │  │ 12mo history   │  │ REST API   │
│  Free]     │  │ Alerts         │  │ Slack/     │
│            │  │ WP-CLI         │  │ Webhooks   │
│            │  │                │  │            │
│            │  │ [Get Pro →]    │  │ [Contact]  │
└────────────┘  └────────────────┘  └────────────┘
```

- **Layout:** 3 columns (Enterprise as a full-width row below, or linked)
- **Pro column:** Elevated with teal border + "Most Popular" badge
- **Background:** White
- **Lifetime deal:** Subtle note below pricing cards
- **Toggle:** Monthly / Annual switch (annual default, show savings)

### Section 10: Yoast / Rank Math Compatibility

**Heading:** "Works With Your Existing SEO Plugin"

```
┌──────────────────────────────────────────────────────────────┐
│                                                              │
│   [Yoast Logo]   [Rank Math Logo]   [AIOSEO Logo]           │
│                                                              │
│   SearchForge doesn't replace your SEO plugin — it          │
│   supercharges it. Import focus keywords, cross-reference   │
│   with real GSC data, and see both scores side by side.     │
│                                                              │
└──────────────────────────────────────────────────────────────┘
```

- **Style:** Centered logos, brief explanation
- **Background:** Slate-50

### Section 11: CacheWarmer Integration

**Heading:** "Even Better With CacheWarmer"

```
┌──────────────────────────────────────────────────────────────┐
│                                                              │
│   SearchForge detects content decay → You update the page   │
│   → CacheWarmer warms all caches automatically              │
│                                                              │
│   Bundle: SearchForge Pro + CacheWarmer Premium = €169/yr   │
│                        (Save 15%)                            │
│                                                              │
│   [Get the Bundle]                                           │
│                                                              │
└──────────────────────────────────────────────────────────────┘
```

- **Style:** Two-tone card with both product logos
- **Background:** Gradient or dark section

### Section 12: FAQ

**Heading:** "Frequently Asked Questions"

Accordion-style, 8-10 questions:
1. What data sources does SearchForge support?
2. Do I need API keys for all sources?
3. How does the free tier work?
4. What makes SearchForge different from Yoast/Rank Math?
5. How does AI Visibility Monitoring work?
6. Can I use SearchForge with Claude Code / ChatGPT?
7. What is `llms.txt` and why do I need it?
8. How does the SearchForge Score work?
9. Is my data stored on your servers?
10. Can I cancel my Pro subscription anytime?

- **Style:** Click-to-expand, smooth animation, chevron icon
- **Background:** White
- **Schema:** FAQPage JSON-LD for SEO

### Section 13: Final CTA

```
┌──────────────────────────────────────────────────────────────┐
│                                                              │
│        Ready to make your SEO data work harder?              │
│                                                              │
│        [Get Started Free]    [Compare Plans]                 │
│                                                              │
└──────────────────────────────────────────────────────────────┘
```

- **Background:** Dark (slate-900), same as hero
- **Style:** Large heading, two CTAs

### Global: Footer

```
┌──────────────────────────────────────────────────────────────┐
│                                                              │
│  [SF Logo]               Documentation    Data Sources       │
│  SEO Data, LLM-Ready.   Getting Started   Google Search      │
│                          Configuration     Console            │
│  Resources               API Reference    Bing Webmaster     │
│  Features                WP-CLI           Google Analytics   │
│  Pricing                 REST API         Keyword Planner    │
│  Changelog                                Google Trends      │
│  Enterprise              Integrations     Business Profile   │
│  Blog                    Yoast/Rank Math  Bing Places        │
│                          CacheWarmer                         │
│                          GitHub/Notion                       │
│                                                              │
│  ─────────────────────────────────────────────────────────   │
│  © 2026 Dross:Media GmbH · Made with ♥ in Stuttgart         │
│  Imprint · Privacy Policy · Contact                          │
│                                                              │
└──────────────────────────────────────────────────────────────┘
```

- **Background:** Slate-900 (dark)
- **Columns:** 4 on desktop, stacked on mobile
- **Logo:** SVG, teal accent
- **Legal links:** Bottom row

---

## Technical Architecture

### Theme Type

**Static theme** — no dynamic WordPress features (no blog, no comments, no sidebar). All content is hardcoded in PHP templates. The site serves as a marketing landing page.

### File Structure

```
searchforge-theme/
├── style.css                    # Theme metadata + compiled CSS
├── functions.php                # Theme setup, enqueues, menus
├── index.php                    # Fallback template
├── front-page.php               # Homepage (landing page)
├── page.php                     # Generic page template
├── header.php                   # Sticky nav + mobile menu
├── footer.php                   # Footer + legal links
├── 404.php                      # 404 error page
├── screenshot.png               # Theme screenshot (1200×900)
│
├── assets/
│   ├── css/
│   │   ├── variables.css        # CSS custom properties (design tokens)
│   │   ├── base.css             # Reset, typography, global styles
│   │   ├── components.css       # Buttons, cards, badges, forms
│   │   ├── sections.css         # Hero, features, pricing, FAQ
│   │   └── responsive.css       # Media queries
│   ├── js/
│   │   ├── navigation.js        # Mobile menu toggle
│   │   ├── faq.js               # Accordion functionality
│   │   ├── pricing.js           # Monthly/annual toggle
│   │   └── animations.js        # Intersection Observer scroll fx
│   ├── images/
│   │   ├── logo.svg             # Full logo (mark + wordmark)
│   │   ├── logo-mark.svg        # Icon only (for favicon, small)
│   │   ├── logo-white.svg       # White variant for dark backgrounds
│   │   ├── og-image.png         # OpenGraph image (1200×630)
│   │   └── icons/               # Feature icons (SVG)
│   │       ├── gsc.svg
│   │       ├── bing.svg
│   │       ├── ga4.svg
│   │       ├── trends.svg
│   │       ├── kwp.svg
│   │       ├── gbp.svg
│   │       ├── aeo.svg
│   │       ├── competitors.svg
│   │       ├── score.svg
│   │       ├── briefs.svg
│   │       ├── llms-txt.svg
│   │       ├── clustering.svg
│   │       ├── decay.svg
│   │       └── history.svg
│   └── fonts/                   # Self-hosted fallback (optional)
│
├── inc/
│   ├── schema.php               # JSON-LD structured data
│   ├── security.php             # Security headers, cleanup
│   └── performance.php          # Preload, defer, minify hints
│
└── template-parts/
    ├── hero.php
    ├── stats-bar.php
    ├── problems.php
    ├── solutions.php
    ├── data-sources.php
    ├── features.php
    ├── setup-steps.php
    ├── comparison.php
    ├── pricing.php
    ├── compatibility.php
    ├── cachewarmer-bundle.php
    ├── faq.php
    └── final-cta.php
```

### WordPress Integration

**functions.php:**
```php
// Theme supports
add_theme_support('title-tag');
add_theme_support('custom-logo');
add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);

// Enqueue assets
wp_enqueue_style('searchforge-variables', .../variables.css);
wp_enqueue_style('searchforge-base', .../base.css);
wp_enqueue_style('searchforge-components', .../components.css);
wp_enqueue_style('searchforge-sections', .../sections.css);
wp_enqueue_style('searchforge-responsive', .../responsive.css);

wp_enqueue_script('searchforge-navigation', .../navigation.js, [], null, true);
wp_enqueue_script('searchforge-faq', .../faq.js, [], null, true);
wp_enqueue_script('searchforge-pricing', .../pricing.js, [], null, true);
wp_enqueue_script('searchforge-animations', .../animations.js, [], null, true);

// Register nav menus
register_nav_menus(['primary' => 'Primary Navigation']);

// Remove unnecessary WordPress head bloat
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wp_shortlink_wp_head');
```

### SEO & Performance

- **JSON-LD:** Product, Organization, FAQPage, WebSite, SiteNavigationElement schemas
- **Open Graph:** Full og:title, og:description, og:image, og:type
- **Twitter Cards:** summary_large_image
- **Canonical URL:** Self-referencing
- **Preload:** Fonts, critical CSS
- **Defer:** All JS loaded with `defer`
- **No jQuery:** Zero jQuery dependency
- **Target:** <100KB total page weight (excluding fonts), Lighthouse 95+

### Accessibility (WCAG 2.1 AA)

- Skip-to-content link
- Semantic HTML5 landmarks (`<header>`, `<main>`, `<nav>`, `<footer>`, `<section>`)
- ARIA attributes on interactive elements (accordion, mobile menu, pricing toggle)
- Focus-visible styles for keyboard navigation
- Color contrast: All text meets AA ratio (4.5:1 normal, 3:1 large)
- `prefers-reduced-motion` respected for animations
- `prefers-color-scheme` dark mode support (optional, phase 2)

### Responsive Breakpoints

| Breakpoint | Width | Layout |
|-----------|-------|--------|
| Mobile | <640px | Single column, stacked cards |
| Tablet | 640–1024px | 2-column grids |
| Desktop | >1024px | Full layout, 3-4 column grids |
| Wide | >1280px | Max-width container centered |

---

## Content Strategy

### SEO Targets

- **Primary:** "wordpress seo data plugin", "seo data to llm", "search console wordpress plugin"
- **Secondary:** "llms.txt generator", "ai seo monitoring", "seo markdown export"
- **Long-tail:** "export google search console data as markdown", "ai visibility monitoring wordpress"

### Conversion Funnel

1. **Organic search / referral** → Landing page
2. **Hero** → Understand value proposition
3. **Problems** → Relate to pain points
4. **Solutions + Features** → See how SearchForge solves them
5. **Pricing** → Choose tier
6. **CTA** → Install free or purchase Pro
7. **Post-install** → Dashboard upsell (in-plugin)

### Pages (WordPress)

| Page | Template | URL |
|------|----------|-----|
| Home | `front-page.php` | `/` |
| Features | `page.php` | `/features/` |
| Pricing | `page.php` | `/pricing/` |
| Documentation | `page.php` (links to external docs) | `/docs/` |
| Changelog | `page.php` | `/changelog/` |
| Enterprise | `page.php` | `/enterprise/` |
| Imprint | `page.php` | `/imprint/` |
| Privacy | `page.php` | `/privacy/` |
| Contact | `page.php` | `/contact/` |

---

## Logo Concept

### Mark (Icon)

A stylized **anvil + magnifying glass** hybrid — representing "forge" (crafting/building) merged with "search" (SEO/data). The anvil shape contains a subtle search lens circle.

**Specifications:**
- Viewbox: 32×32
- Primary color: `#0f766e` (teal-700)
- Style: Geometric, minimal line weight, solid fills
- Must work at 16px (favicon), 32px (nav), and 64px+ (hero)

### Wordmark

"Search**Forge**" — "Search" in `--sf-primary` teal, "Forge" in `--sf-text` dark or `--sf-text-inverse` on dark backgrounds. Outfit font, weight 700.

### Variants

| Variant | Usage |
|---------|-------|
| Mark + Wordmark (horizontal) | Navigation, documentation headers |
| Mark only | Favicon, app icon, small spaces |
| White mark + white wordmark | Dark backgrounds (footer, hero) |
| Mark + Wordmark (stacked) | Loading screens, about page |

---

## Development Phases

| Phase | Scope | Effort |
|-------|-------|--------|
| v0.1 | Theme scaffold, design tokens, header/footer, hero | ~8h |
| v0.2 | Problem/solution sections, features grid, setup steps | ~6h |
| v0.3 | Pricing section with toggle, comparison table | ~4h |
| v0.4 | FAQ accordion, final CTA, stats bar | ~3h |
| v0.5 | Compatibility section, CacheWarmer bundle | ~2h |
| v0.6 | Data sources showcase with interactive brief preview | ~4h |
| v0.7 | SEO: JSON-LD, OG tags, performance optimization | ~3h |
| v0.8 | Responsive polish, accessibility audit, animations | ~4h |
| v0.9 | Inner pages (features, pricing, enterprise, legal) | ~6h |
| v1.0 | Final QA, Lighthouse optimization, deployment | ~4h |
| **Total** | | **~44h** |

---

## Deployment

1. Theme ZIP uploaded to WordPress on `searchforge.drossmedia.de`
2. Static pages created in WP admin
3. Primary navigation menu configured
4. Custom logo uploaded
5. SEO plugin (Yoast/Rank Math) configured for meta descriptions
6. Cloudflare caching enabled
7. CacheWarmer configured to warm the marketing site itself
