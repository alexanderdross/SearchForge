<?php
/**
 * Template Name: Docs - Data Sources
 *
 * @package SearchForge_Theme
 */

get_header();

$sections = [
	[ 'id' => 'google-search-console',   'label' => 'Google Search Console',   'title' => 'Configure Google Search Console integration in SearchForge' ],
	[ 'id' => 'bing-webmaster-tools',     'label' => 'Bing Webmaster Tools',   'title' => 'Configure Bing Webmaster Tools integration in SearchForge' ],
	[ 'id' => 'google-analytics-4',       'label' => 'Google Analytics 4',     'title' => 'Configure Google Analytics 4 integration in SearchForge' ],
	[ 'id' => 'google-keyword-planner',   'label' => 'Google Keyword Planner', 'title' => 'Configure Google Keyword Planner integration in SearchForge' ],
	[ 'id' => 'google-trends',            'label' => 'Google Trends',          'title' => 'Configure Google Trends integration in SearchForge' ],
	[ 'id' => 'google-business-profile',  'label' => 'Google Business Profile','title' => 'Configure Google Business Profile integration in SearchForge' ],
	[ 'id' => 'bing-places-for-business', 'label' => 'Bing Places for Business','title' => 'Configure Bing Places for Business integration in SearchForge' ],
	[ 'id' => 'adobe-analytics',          'label' => 'Adobe Analytics',         'title' => 'Configure Adobe Analytics integration in SearchForge' ],
];
?>

<section class="sf-section sf-section--dark sf-hero" style="padding: var(--space-3xl) 0;">
	<div class="sf-container" style="text-align: center;">
		<h1><span class="sf-gradient-text">Data Sources</span></h1>
		<p class="sf-text--inverse-muted" style="font-size: 1.25rem; max-width: 640px; margin: var(--space-md) auto 0;">
			SearchForge connects to 9 SEO data sources. Learn how to configure each integration.
		</p>
	</div>
</section>

<section class="sf-section">
	<div class="sf-container">
		<div class="sf-doc-layout">
			<?php sf_doc_sidebar( $sections ); ?>
			<div class="sf-doc-content">

		<article class="sf-doc-section" id="google-search-console">
			<h2>Google Search Console</h2>
			<p>The primary data source for SearchForge. Provides clicks, impressions, CTR, and average position data per page and per keyword.</p>
			<h3>Setup</h3>
			<p>GSC requires OAuth 2.0 credentials from a Google Cloud project. Follow these steps:</p>
			<ol class="sf-content">
				<li><strong>Create a Google Cloud project</strong> &mdash; Go to <a href="https://console.cloud.google.com/" target="_blank" rel="noopener">Google Cloud Console</a>, click the project dropdown at the top, and select <strong>New Project</strong>. Name it (e.g., &ldquo;SearchForge&rdquo;) and click <strong>Create</strong>.</li>
				<li><strong>Enable the Search Console API</strong> &mdash; In your project, go to <a href="https://console.cloud.google.com/apis/library" target="_blank" rel="noopener">APIs &amp; Services &rarr; Library</a>. Search for &ldquo;Google Search Console API&rdquo; and click <strong>Enable</strong>.</li>
				<li><strong>Configure the OAuth Consent Screen</strong> &mdash; Go to <a href="https://console.cloud.google.com/apis/credentials/consent" target="_blank" rel="noopener">APIs &amp; Services &rarr; OAuth consent screen</a>. Select &ldquo;External&rdquo; user type, enter your app name and email, then save.</li>
				<li><strong>Create OAuth 2.0 credentials</strong> &mdash; Go to <a href="https://console.cloud.google.com/apis/credentials" target="_blank" rel="noopener">APIs &amp; Services &rarr; Credentials</a>. Click <strong>Create Credentials &rarr; OAuth client ID</strong>. Select &ldquo;Web application&rdquo; as the type.</li>
				<li><strong>Add the redirect URI</strong> &mdash; Under &ldquo;Authorized redirect URIs&rdquo;, add the callback URL shown in SearchForge &rarr; Settings &rarr; Google Search Console (below the Client Secret field). Click <strong>Create</strong>.</li>
				<li><strong>Copy credentials</strong> &mdash; Copy the <strong>Client ID</strong> and <strong>Client Secret</strong> shown in the dialog.</li>
				<li><strong>Connect in SearchForge</strong> &mdash; Go to <strong>SearchForge &rarr; Settings &rarr; Google Search Console</strong>, paste the Client ID and Client Secret, click <strong>Authorize with Google</strong>, sign in, select your property, and save.</li>
			</ol>
			<h3>Data pulled</h3>
			<ul class="sf-content">
				<li>Per-page: clicks, impressions, CTR, average position</li>
				<li>Per-keyword: same metrics plus device segmentation (desktop, mobile, tablet)</li>
				<li>Date ranges: 7d, 28d, 3m, 6m, 12m</li>
			</ul>
			<h3>Limits</h3>
			<p>Free tier: 10 pages, 100 keywords. Pro and above: unlimited.</p>
		</article>

		<hr style="border: none; border-top: 1px solid var(--sf-border); margin: var(--space-2xl) 0;">

		<article class="sf-doc-section" id="bing-webmaster-tools">
			<h2>Bing Webmaster Tools</h2>
			<p>Bing-specific search data often reveals keywords that Google doesn't surface. Side-by-side comparison with GSC data in your briefs.</p>
			<h3>Setup</h3>
			<ol class="sf-content">
				<li>Sign in to <a href="https://www.bing.com/webmasters/" target="_blank" rel="noopener">Bing Webmaster Tools</a> with your Microsoft account.</li>
				<li>Add and verify your site if you haven&rsquo;t already.</li>
				<li>Click the gear icon (<strong>Settings</strong>) &rarr; <strong>API Access</strong> &rarr; <strong>API Key</strong>.</li>
				<li>Copy the API key.</li>
				<li>In <strong>SearchForge &rarr; Settings &rarr; Bing Webmaster Tools</strong>, paste the API key and your site URL, enable, and save.</li>
			</ol>
			<h3>Data pulled</h3>
			<ul class="sf-content">
				<li>Clicks, impressions, CTR, average position</li>
				<li>Keyword-level data with Bing-specific search intent signals</li>
			</ul>
		</article>

		<hr style="border: none; border-top: 1px solid var(--sf-border); margin: var(--space-2xl) 0;">

		<article class="sf-doc-section" id="google-analytics-4">
			<h2>Google Analytics 4</h2>
			<p>Behavioral data that complements search rankings. Understand what users do after they click.</p>
			<h3>Setup</h3>
			<p>GA4 uses the same Google Cloud project as GSC. You only need to enable one additional API:</p>
			<ol class="sf-content">
				<li>In your Google Cloud project, go to <a href="https://console.cloud.google.com/apis/library" target="_blank" rel="noopener">APIs &amp; Services &rarr; Library</a>.</li>
				<li>Search for &ldquo;Google Analytics Data API&rdquo; and click <strong>Enable</strong>.</li>
				<li>Find your GA4 Property ID in <a href="https://analytics.google.com/analytics/web/" target="_blank" rel="noopener">Google Analytics</a> &rarr; Admin &rarr; Property Settings (numeric ID, e.g., &ldquo;123456789&rdquo;).</li>
				<li>In <strong>SearchForge &rarr; Settings &rarr; Google Analytics 4</strong>, enter the Property ID, enable, and save.</li>
			</ol>
			<h3>Data pulled</h3>
			<ul class="sf-content">
				<li>Sessions, bounce rate, engagement time per page</li>
				<li>Scroll depth and conversion events</li>
				<li>Traffic source attribution</li>
			</ul>
		</article>

		<hr style="border: none; border-top: 1px solid var(--sf-border); margin: var(--space-2xl) 0;">

		<article class="sf-doc-section" id="google-keyword-planner">
			<h2>Google Keyword Planner</h2>
			<p>Absolute search volume and competition data to enrich your GSC keywords with market context.</p>
			<h3>Setup</h3>
			<ol class="sf-content">
				<li>Sign in to <a href="https://ads.google.com/" target="_blank" rel="noopener">Google Ads</a> (create an account if needed &mdash; no active campaigns are required).</li>
				<li>Go to <strong>Tools &amp; Settings &rarr; Setup &rarr; API Center</strong>. Apply for a developer token if you don&rsquo;t have one.</li>
				<li>Find your Customer ID (10-digit number, formatted as XXX-XXX-XXXX) at the top right of the Google Ads dashboard.</li>
				<li>In <strong>SearchForge &rarr; Settings &rarr; Keyword Planner</strong>, enter the Developer Token and Customer ID, then save.</li>
			</ol>
			<p>A basic access developer token is sufficient. You do not need to run paid ads.</p>
			<h3>Data pulled</h3>
			<ul class="sf-content">
				<li>Monthly search volume (exact and range)</li>
				<li>Competition level (low/medium/high)</li>
				<li>Suggested bid / CPC data</li>
				<li>Seasonal trends (12-month histogram)</li>
			</ul>
		</article>

		<hr style="border: none; border-top: 1px solid var(--sf-border); margin: var(--space-2xl) 0;">

		<article class="sf-doc-section" id="google-trends">
			<h2>Google Trends</h2>
			<p>Relative interest over time and geographic breakdown for your keywords. Ideal for content calendar planning.</p>
			<h3>Setup</h3>
			<ol class="sf-content">
				<li>Create an account at <a href="https://serpapi.com/" target="_blank" rel="noopener">SerpAPI</a>.</li>
				<li>Go to your <a href="https://serpapi.com/dashboard" target="_blank" rel="noopener">SerpAPI Dashboard</a> and copy your API key.</li>
				<li>In <strong>SearchForge &rarr; Settings &rarr; Google Trends</strong>, paste the SerpAPI key, enable, and save.</li>
			</ol>
			<p>SerpAPI offers a free tier with 100 searches/month. Paid plans are available for higher volume.</p>
			<h3>Data pulled</h3>
			<ul class="sf-content">
				<li>Interest over time (relative 0-100 scale)</li>
				<li>Related queries and rising queries</li>
				<li>Geographic breakdown by region</li>
			</ul>
		</article>

		<hr style="border: none; border-top: 1px solid var(--sf-border); margin: var(--space-2xl) 0;">

		<article class="sf-doc-section" id="google-business-profile">
			<h2>Google Business Profile</h2>
			<p>Local SEO data for brick-and-mortar businesses. Track how customers find your business listing.</p>
			<h3>Setup</h3>
			<p>Connect via OAuth at <strong>Data Sources &rarr; Google Business Profile</strong>. Select your business location(s).</p>
			<h3>Data pulled</h3>
			<ul class="sf-content">
				<li>Direct vs. discovery queries</li>
				<li>Maps vs. Search impressions</li>
				<li>Customer actions (calls, directions, website clicks)</li>
				<li>Review count and average rating</li>
			</ul>
			<h3>Limits</h3>
			<p>Pro: 1 location. Agency: 10 locations. Enterprise: unlimited.</p>
		</article>

		<hr style="border: none; border-top: 1px solid var(--sf-border); margin: var(--space-2xl) 0;">

		<article class="sf-doc-section" id="bing-places-for-business">
			<h2>Bing Places for Business</h2>
			<p>Bing local search data. Cross-platform comparison with Google Business Profile to identify Bing-only discovery keywords.</p>
			<h3>Setup</h3>
			<p>Authenticate at <strong>Data Sources &rarr; Bing Places</strong> using your Bing Places account credentials.</p>
			<h3>Data pulled</h3>
			<ul class="sf-content">
				<li>Local search impressions and clicks</li>
				<li>Customer actions on Bing</li>
				<li>Cross-platform comparison metrics</li>
			</ul>
		</article>

		<hr style="border: none; border-top: 1px solid var(--sf-border); margin: var(--space-2xl) 0;">

		<article class="sf-doc-section" id="adobe-analytics">
			<h2>Adobe Analytics</h2>
			<p>Enterprise-grade behavior analytics for organizations using the Adobe Experience Cloud. Provides similar metrics to GA4 but with deeper segmentation and revenue attribution.</p>
			<h3>Setup</h3>
			<ol class="sf-content">
				<li>Create an OAuth Server-to-Server credential in the <a href="https://developer.adobe.com/console/" rel="noopener" title="Adobe Developer Console - Create API Credentials">Adobe Developer Console</a>.</li>
				<li>Add the <strong>Adobe Analytics</strong> API to your project and assign the correct product profile.</li>
				<li>In SearchForge, go to <strong>Data Sources - Adobe Analytics</strong>.</li>
				<li>Enter your Organization ID, Client ID, and Client Secret from the Adobe Developer Console.</li>
				<li>Enter your Report Suite ID (e.g. <code>mycompanyprod</code>).</li>
				<li>Click <strong>Test Connection</strong> to verify access, then <strong>Save</strong>.</li>
			</ol>
			<h3>Data pulled</h3>
			<ul class="sf-content">
				<li>Visits (sessions) and unique visitors per page</li>
				<li>Page views and average time on page</li>
				<li>Bounce rate and exit rate</li>
				<li>Conversion events and revenue attribution</li>
				<li>Traffic source breakdown (organic search, direct, referral)</li>
			</ul>
			<h3>Authentication</h3>
			<p>SearchForge uses Adobe IMS OAuth Server-to-Server credentials. This is the recommended method by Adobe and does not require user interaction after initial setup. Tokens are refreshed automatically.</p>
			<h3>Limits</h3>
			<p>Available on Pro tier and above. API rate limits depend on your Adobe Analytics contract. SearchForge batches requests to stay within typical limits.</p>
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
