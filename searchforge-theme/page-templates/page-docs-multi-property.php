<?php
/**
 * Template Name: Docs - Multi-Property & Merger
 *
 * @package SearchForge_Theme
 */

get_header();

$sections = [
	[ 'id' => 'multi-property-overview',  'label' => 'Overview',                'title' => 'Multi-property management overview' ],
	[ 'id' => 'adding-properties',        'label' => 'Adding Properties',       'title' => 'How to add and configure multiple properties' ],
	[ 'id' => 'switching-properties',     'label' => 'Switching Properties',    'title' => 'Switch between properties in the admin UI' ],
	[ 'id' => 'property-comparison',      'label' => 'Property Comparison',     'title' => 'Compare SEO metrics across properties' ],
	[ 'id' => 'merger-analysis',          'label' => 'Merger Analysis',         'title' => 'Generate CMS backend merger intelligence briefs' ],
	[ 'id' => 'merger-brief-contents',    'label' => 'Brief Contents',          'title' => 'What the merger analysis brief includes' ],
	[ 'id' => 'api-cli-access',           'label' => 'API & CLI',              'title' => 'Multi-property REST API and WP-CLI access' ],
];
?>

<section class="sf-section sf-section--dark sf-hero" style="padding: var(--space-3xl) 0;">
	<div class="sf-container" style="text-align: center;">
		<h1><span class="sf-gradient-text">Multi-Property &amp; Merger Analysis</span></h1>
		<p class="sf-text--inverse-muted" style="font-size: 1.25rem; max-width: 640px; margin: var(--space-md) auto 0;">
			Manage multiple domains from one WordPress installation. Compare metrics across properties and generate data-driven merger briefs.
		</p>
	</div>
</section>

<section class="sf-section">
	<div class="sf-container">
		<div class="sf-doc-layout">
			<?php sf_doc_sidebar( $sections ); ?>
			<div class="sf-doc-content">

		<article class="sf-doc-section" id="multi-property-overview">
			<h2>Overview</h2>
			<p>SearchForge Pro supports managing multiple GSC properties, Bing sites, and GA4 streams from a single WordPress installation. Each property has isolated data, credentials, and sync schedules.</p>
			<h3>Use Cases</h3>
			<ul class="sf-content">
				<li><strong>Multi-domain portfolios</strong> &mdash; Track SEO health across all your domains in one place.</li>
				<li><strong>Country subfolders</strong> &mdash; Monitor <code>/de/</code>, <code>/fr/</code>, <code>/es/</code> as separate properties with independent metrics.</li>
				<li><strong>Domain mergers</strong> &mdash; Compare data across the domains you plan to consolidate, then generate a merger brief with navigation and IA recommendations.</li>
				<li><strong>Client management</strong> &mdash; Agency tier users can manage multiple client domains and export per-property reports.</li>
			</ul>
			<p>Requires a Pro, Agency, or Enterprise license. Free tier is limited to a single property.</p>
		</article>

		<hr style="border: none; border-top: 1px solid var(--sf-border); margin: var(--space-2xl) 0;">

		<article class="sf-doc-section" id="adding-properties">
			<h2>Adding Properties</h2>
			<p>Properties are managed in <strong>SearchForge &rarr; Settings &rarr; Properties</strong>.</p>
			<ol class="sf-content">
				<li>Enter a <strong>label</strong> (e.g., "Main Site" or "DE Subfolder") and the <strong>domain</strong> (e.g., <code>example.com</code> or <code>example.com/de</code>).</li>
				<li>Click <strong>Add Property</strong>. The property appears in the properties table.</li>
				<li>Connect data sources for the new property:
					<ul>
						<li><strong>GSC:</strong> Enter OAuth Client ID and Client Secret, then click "Authorize with Google". Select the GSC property and save.</li>
						<li><strong>Bing:</strong> Enter the Bing Webmaster API key and site URL.</li>
						<li><strong>GA4:</strong> Enter the GA4 property ID (uses the same Google OAuth as GSC).</li>
					</ul>
				</li>
				<li>Click <strong>Sync Now</strong> on the Dashboard to pull initial data for the new property.</li>
			</ol>
			<p>Each property stores its own API credentials securely using AES-256-CBC encryption. Deleting a property removes all its associated data (snapshots, keywords, GA4 metrics, alerts, sync logs).</p>
		</article>

		<hr style="border: none; border-top: 1px solid var(--sf-border); margin: var(--space-2xl) 0;">

		<article class="sf-doc-section" id="switching-properties">
			<h2>Switching Properties</h2>
			<p>When 2 or more properties exist, a <strong>Property dropdown</strong> appears at the top of every SearchForge admin page.</p>
			<ul class="sf-content">
				<li>Select a property from the dropdown to view its data.</li>
				<li>Your selection is stored per WordPress user, so each admin sees the property they last selected.</li>
				<li>All pages &mdash; Dashboard, Pages, Keywords, Analysis, Competitors, Monitoring, Export &mdash; automatically filter to the active property.</li>
			</ul>
			<p>The daily automatic sync processes all properties regardless of which one is currently active.</p>
		</article>

		<hr style="border: none; border-top: 1px solid var(--sf-border); margin: var(--space-2xl) 0;">

		<article class="sf-doc-section" id="property-comparison">
			<h2>Property Comparison</h2>
			<p>Available at <strong>SearchForge &rarr; Comparison</strong> when you have 2 or more properties. Displays a side-by-side metrics table covering:</p>
			<ul class="sf-content">
				<li><strong>Total Clicks</strong> and <strong>Total Impressions</strong> &mdash; aggregate search performance per property.</li>
				<li><strong>Average CTR</strong> and <strong>Average Position</strong> &mdash; overall search visibility.</li>
				<li><strong>Pages</strong> and <strong>Keywords</strong> &mdash; index coverage breadth.</li>
				<li><strong>SearchForge Score</strong> &mdash; the proprietary 0-100 SEO health metric per property.</li>
				<li><strong>GSC Connected</strong> &mdash; connection status for quick troubleshooting.</li>
			</ul>
			<p>Use this view to identify which properties need attention, which are growing, and where to focus optimization efforts.</p>
		</article>

		<hr style="border: none; border-top: 1px solid var(--sf-border); margin: var(--space-2xl) 0;">

		<article class="sf-doc-section" id="merger-analysis">
			<h2>Merger Analysis</h2>
			<p>Generate a comprehensive markdown brief for domain mergers, CMS backend consolidations, and portfolio restructuring. Available at <strong>SearchForge &rarr; Merger Analysis</strong>.</p>
			<h3>How to Generate a Brief</h3>
			<ol class="sf-content">
				<li>Ensure at least 2 properties are configured with synced GSC data. GA4 data is optional but enriches the funnel analysis.</li>
				<li>Navigate to <strong>SearchForge &rarr; Merger Analysis</strong>.</li>
				<li>Select 2 or more properties using the checkboxes.</li>
				<li>Click <strong>Generate Merger Brief</strong>.</li>
				<li>Review the rendered brief in the preview area.</li>
				<li>Click <strong>Download .md</strong> to save the brief as a markdown file.</li>
			</ol>
			<p>The brief is cached in the database. Regenerate it anytime by clicking the button again with the same properties selected.</p>
		</article>

		<hr style="border: none; border-top: 1px solid var(--sf-border); margin: var(--space-2xl) 0;">

		<article class="sf-doc-section" id="merger-brief-contents">
			<h2>What the Brief Includes</h2>
			<p>The merger analysis engine cross-references GSC snapshots, GSC keywords, GA4 metrics, keyword clustering, and cannibalization data to produce:</p>
			<ul class="sf-content">
				<li><strong>Executive Summary</strong> &mdash; Aggregate traffic, keyword counts, SearchForge Scores, and high-level consolidation recommendations per property.</li>
				<li><strong>URL Pattern Analysis</strong> &mdash; Detects subfolder structures (e.g., <code>/products/</code>, <code>/blog/</code>) across properties with page counts and traffic per pattern.</li>
				<li><strong>Navigation Merger Recommendations</strong> &mdash; Traffic-weighted scoring model (clicks 30%, sessions 20%, engagement 20%, keyword breadth 15%, conversions 15%) produces:
					<ul>
						<li>Recommended primary navigation (header) &mdash; top 8-10 items ranked by combined traffic value.</li>
						<li>Recommended footer navigation &mdash; items ranked 11-25 by the same scoring.</li>
						<li>Items to consolidate &mdash; pages covering the same topic across properties.</li>
						<li>Items to retire &mdash; low-traffic duplicate pages.</li>
					</ul>
				</li>
				<li><strong>Information Architecture Restructuring</strong> &mdash; Proposed content silos based on keyword cluster overlap and URL hierarchy. Includes a 301 redirect map for pages with high keyword overlap (Jaccard similarity) and a list of orphaned content (pages with zero clicks).</li>
				<li><strong>User Funnel Optimization</strong> (requires GA4) &mdash; Entry points (high organic sessions), drop-off pages (high bounce rate), conversion corridors to protect during migration, and internal linking recommendations.</li>
				<li><strong>Cross-Property Cannibalization</strong> &mdash; Keywords where multiple properties compete against each other, ranked by severity. Identifies which property should own each contested keyword.</li>
			</ul>
		</article>

		<hr style="border: none; border-top: 1px solid var(--sf-border); margin: var(--space-2xl) 0;">

		<article class="sf-doc-section" id="api-cli-access">
			<h2>API &amp; CLI Access</h2>
			<p>Multi-property features are available via both the REST API and WP-CLI.</p>
			<h3>REST API</h3>
			<ul class="sf-content">
				<li><code>GET /wp-json/searchforge/v1/properties</code> &mdash; List all properties.</li>
				<li><code>POST /wp-json/searchforge/v1/properties</code> &mdash; Create a new property.</li>
				<li><code>GET /wp-json/searchforge/v1/comparison</code> &mdash; Cross-property metric comparison.</li>
				<li><code>GET /wp-json/searchforge/v1/merger-analysis?property_ids[]=1&amp;property_ids[]=2</code> &mdash; Generate a merger brief.</li>
				<li>All existing endpoints accept an optional <code>property_id</code> query parameter (defaults to the default property).</li>
			</ul>
			<h3>WP-CLI</h3>
			<ul class="sf-content">
				<li><code>wp searchforge properties</code> &mdash; List all properties with connection status.</li>
				<li><code>wp searchforge sync --property=2</code> &mdash; Sync a specific property.</li>
				<li><code>wp searchforge export pages --property=2 --format=csv</code> &mdash; Export data for a specific property.</li>
				<li><code>wp searchforge merger --properties=1,2,3 --file=merger.md</code> &mdash; Generate and save a merger brief.</li>
			</ul>
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
