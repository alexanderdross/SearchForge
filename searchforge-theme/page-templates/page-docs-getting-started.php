<?php
/**
 * Template Name: Docs - Getting Started
 *
 * @package SearchForge_Theme
 */

get_header();

$sections = [
	[ 'id' => 'installation',                    'label' => 'Installation',                    'title' => 'How to install SearchForge on WordPress' ],
	[ 'id' => 'license-activation',              'label' => 'License Activation',              'title' => 'Activate your SearchForge license key' ],
	[ 'id' => 'connecting-google-search-console', 'label' => 'Connecting Google Search Console', 'title' => 'Connect Google Search Console to SearchForge' ],
	[ 'id' => 'connecting-additional-sources',    'label' => 'Additional Data Sources',         'title' => 'Connect additional SEO data sources to SearchForge' ],
	[ 'id' => 'your-first-data-sync',            'label' => 'Your First Data Sync',            'title' => 'Run your first SEO data sync with SearchForge' ],
	[ 'id' => 'exporting-your-first-brief',      'label' => 'Exporting Your First Brief',      'title' => 'Export your first AI-ready SEO brief' ],
];
?>

<section class="sf-section sf-section--dark sf-hero" style="padding: var(--space-3xl) 0;">
	<div class="sf-container" style="text-align: center;">
		<h1><span class="sf-gradient-text">Getting Started</span></h1>
		<p class="sf-text--inverse-muted" style="font-size: 1.25rem; max-width: 640px; margin: var(--space-md) auto 0;">
			Install SearchForge, activate your license, connect Google Search Console, and export your first SEO brief in under 10 minutes.
		</p>
	</div>
</section>

<section class="sf-section">
	<div class="sf-container">
		<div class="sf-doc-layout">
			<?php sf_doc_sidebar( $sections ); ?>
			<div class="sf-doc-content">

		<article class="sf-doc-section" id="installation">
			<h2>Installation</h2>
			<p>SearchForge installs like any WordPress plugin. Download the ZIP from your account dashboard or install directly from the WordPress plugin directory.</p>
			<ol class="sf-content">
				<li>Go to <strong>Plugins &rarr; Add New</strong> in your WordPress admin.</li>
				<li>Click <strong>Upload Plugin</strong> and select the <code>searchforge.zip</code> file.</li>
				<li>Click <strong>Install Now</strong>, then <strong>Activate</strong>.</li>
				<li>The SearchForge menu appears in your admin sidebar.</li>
			</ol>
			<p>Requirements: WordPress 6.0+, PHP 8.2+, and a valid SSL certificate for OAuth connections.</p>
		</article>

		<hr style="border: none; border-top: 1px solid var(--sf-border); margin: var(--space-2xl) 0;">

		<article class="sf-doc-section" id="license-activation">
			<h2>License Activation</h2>
			<p>Free tier works without a license key. For Pro, Agency, or Enterprise, enter your key in <strong>SearchForge &rarr; Settings &rarr; License</strong>.</p>
			<ol class="sf-content">
				<li>Copy your license key from the purchase confirmation email or your account at <code>searchforge.drossmedia.de/account/</code>.</li>
				<li>Navigate to <strong>SearchForge &rarr; Settings &rarr; License</strong>.</li>
				<li>Paste the key and click <strong>Activate</strong>.</li>
				<li>Your tier and expiry date appear immediately. Features unlock within seconds.</li>
			</ol>
			<p>Each license is valid for one production domain. Development domains (<code>localhost</code>, <code>*.local</code>, <code>*.dev</code>, <code>*.test</code>) are free and unlimited.</p>
		</article>

		<hr style="border: none; border-top: 1px solid var(--sf-border); margin: var(--space-2xl) 0;">

		<article class="sf-doc-section" id="connecting-google-search-console">
			<h2>Connecting Google Search Console</h2>
			<p>Google Search Console is the primary data source and works on every tier, including Free. You need to create OAuth 2.0 credentials in Google Cloud Console first.</p>

			<h3>Step 1: Create a Google Cloud Project</h3>
			<ol class="sf-content">
				<li>Go to <a href="https://console.cloud.google.com/" target="_blank" rel="noopener">Google Cloud Console</a>.</li>
				<li>Click the project dropdown at the top and select <strong>New Project</strong>.</li>
				<li>Name it (e.g., &ldquo;SearchForge&rdquo;) and click <strong>Create</strong>.</li>
			</ol>

			<h3>Step 2: Enable the Search Console API</h3>
			<ol class="sf-content">
				<li>In your project, go to <a href="https://console.cloud.google.com/apis/library" target="_blank" rel="noopener">APIs &amp; Services &rarr; Library</a>.</li>
				<li>Search for &ldquo;Google Search Console API&rdquo; and click <strong>Enable</strong>.</li>
			</ol>

			<h3>Step 3: Create OAuth 2.0 Credentials</h3>
			<ol class="sf-content">
				<li>Go to <a href="https://console.cloud.google.com/apis/credentials" target="_blank" rel="noopener">APIs &amp; Services &rarr; Credentials</a>.</li>
				<li>Click <strong>Create Credentials &rarr; OAuth client ID</strong>.</li>
				<li>If prompted, configure the <a href="https://console.cloud.google.com/apis/credentials/consent" target="_blank" rel="noopener">OAuth Consent Screen</a> first: select &ldquo;External&rdquo;, fill in your app name and email, then save.</li>
				<li>For Application Type, select <strong>Web application</strong>.</li>
				<li>Under <strong>Authorized redirect URIs</strong>, add the callback URL shown in SearchForge &rarr; Settings under the GSC section (displayed below the Client Secret field with a Copy button).</li>
				<li>Click <strong>Create</strong> and copy the <strong>Client ID</strong> and <strong>Client Secret</strong>.</li>
			</ol>

			<h3>Step 4: Connect in SearchForge</h3>
			<ol class="sf-content">
				<li>Go to <strong>SearchForge &rarr; Settings &rarr; Google Search Console</strong>.</li>
				<li>Paste the Client ID and Client Secret.</li>
				<li>Click <strong>Authorize with Google</strong> and sign in with the Google account that has access to your GSC property.</li>
				<li>Grant the requested permissions (read-only access).</li>
				<li>Select your GSC property from the dropdown and click <strong>Save</strong>.</li>
			</ol>
			<p>SearchForge uses OAuth 2.0 &mdash; your Google password is never stored. Only the access and refresh tokens are saved securely in your WordPress database.</p>
		</article>

		<hr style="border: none; border-top: 1px solid var(--sf-border); margin: var(--space-2xl) 0;">

		<article class="sf-doc-section" id="connecting-additional-sources">
			<h2>Connecting Additional Data Sources</h2>
			<p>Pro and above tiers can connect up to 9 data sources. After GSC, consider connecting these for richer briefs:</p>
			<ul class="sf-content">
				<li><strong>Bing Webmaster Tools</strong> &mdash; Get your API key from <a href="https://www.bing.com/webmasters/" target="_blank" rel="noopener">Bing Webmaster Tools</a> &rarr; Settings &rarr; API Access &rarr; API Key.</li>
				<li><strong>Google Analytics 4</strong> &mdash; Uses the same Google Cloud project as GSC. Enable the &ldquo;Google Analytics Data API&rdquo; in <a href="https://console.cloud.google.com/apis/library" target="_blank" rel="noopener">APIs &amp; Services &rarr; Library</a>, then enter your GA4 Property ID (found in <a href="https://analytics.google.com/analytics/web/" target="_blank" rel="noopener">Google Analytics</a> &rarr; Admin &rarr; Property Settings).</li>
				<li><strong>Google Keyword Planner</strong> &mdash; Requires a <a href="https://ads.google.com/" target="_blank" rel="noopener">Google Ads</a> account (no active campaigns needed). Get a developer token from Tools &amp; Settings &rarr; Setup &rarr; API Center.</li>
				<li><strong>Google Trends</strong> &mdash; Requires a <a href="https://serpapi.com/" target="_blank" rel="noopener">SerpAPI</a> key. Copy it from your <a href="https://serpapi.com/dashboard" target="_blank" rel="noopener">SerpAPI Dashboard</a>.</li>
				<li><strong>Google Business Profile</strong> &mdash; Local SEO data for physical locations. Connect via OAuth.</li>
				<li><strong>Bing Places</strong> &mdash; Bing local search data. Connect with your Microsoft account.</li>
				<li><strong>Adobe Analytics</strong> &mdash; Create an OAuth Server-to-Server credential in the <a href="https://developer.adobe.com/console/" target="_blank" rel="noopener">Adobe Developer Console</a>. You&rsquo;ll need the Organization ID, Client ID, Client Secret, and Report Suite ID.</li>
			</ul>
			<p>Configure all sources at <strong>SearchForge &rarr; Settings</strong>. See the <a href="<?php echo esc_url( home_url( '/docs/data-sources/' ) ); ?>">Data Sources</a> documentation for detailed step-by-step guides for each integration.</p>
		</article>

		<hr style="border: none; border-top: 1px solid var(--sf-border); margin: var(--space-2xl) 0;">

		<article class="sf-doc-section" id="your-first-data-sync">
			<h2>Your First Data Sync</h2>
			<p>After connecting GSC, trigger your first sync to pull ranking data into SearchForge.</p>
			<ol class="sf-content">
				<li>Go to <strong>SearchForge &rarr; Dashboard</strong>.</li>
				<li>Click <strong>Sync Now</strong> or wait for the automatic daily sync.</li>
				<li>SearchForge pulls clicks, impressions, CTR, and position data for each page and keyword.</li>
				<li>Free tier: up to 10 pages and 100 keywords. Pro: unlimited.</li>
			</ol>
			<p>The first sync usually takes 30-60 seconds depending on your site size. Subsequent syncs are incremental and faster.</p>
		</article>

		<hr style="border: none; border-top: 1px solid var(--sf-border); margin: var(--space-2xl) 0;">

		<article class="sf-doc-section" id="exporting-your-first-brief">
			<h2>Exporting Your First Brief</h2>
			<p>The core output of SearchForge is the per-page markdown brief  - a structured document ready for LLMs.</p>
			<ol class="sf-content">
				<li>Navigate to <strong>SearchForge &rarr; Pages</strong> and click any page.</li>
				<li>Review the data summary: rankings, top keywords, trends, and SearchForge Score.</li>
				<li>Click <strong>Export Markdown Brief</strong>.</li>
				<li>Copy the brief and paste it into Claude Code, ChatGPT, or save it as a <code>.md</code> file.</li>
			</ol>
			<p>The brief includes all the context an LLM needs to give you actionable SEO recommendations for that specific page.</p>
		</article>

			</div>
		</div>
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
