<?php
defined( 'ABSPATH' ) || exit;

$is_pro = SearchForge\Admin\Settings::is_pro();
?>

<div class="wrap searchforge-wrap">
	<h1><?php esc_html_e( 'SearchForge — Instructions', 'searchforge' ); ?></h1>

	<p class="description" style="font-size: 14px; max-width: 720px;">
		<?php esc_html_e( 'Everything you need to set up, configure, and get the most out of SearchForge.', 'searchforge' ); ?>
	</p>

	<div class="sf-instructions" style="max-width: 800px; margin-top: 24px;">

		<!-- Getting Started -->
		<div class="sf-instructions-section">
			<h2><?php esc_html_e( 'Getting Started', 'searchforge' ); ?></h2>
			<ol>
				<li><?php esc_html_e( 'Go to SearchForge → Settings and enter your license key (Pro or Agency). Free tier works without a key.', 'searchforge' ); ?></li>
				<li><?php esc_html_e( 'Under Google Search Console, enter your OAuth Client ID and Client Secret, then click "Authorize with Google".', 'searchforge' ); ?></li>
				<li><?php esc_html_e( 'After authorization, select your GSC property from the dropdown and save.', 'searchforge' ); ?></li>
				<li><?php esc_html_e( 'Click "Sync Now" on the Dashboard to pull your first batch of ranking data.', 'searchforge' ); ?></li>
				<li><?php esc_html_e( 'Visit the Pages tab to see your tracked pages, then click any page for detailed metrics.', 'searchforge' ); ?></li>
			</ol>
		</div>

		<hr style="border: none; border-top: 1px solid #dcdcde; margin: 24px 0;">

		<!-- Multi-Property Management -->
		<div class="sf-instructions-section">
			<h2><?php esc_html_e( 'Multi-Property Management', 'searchforge' ); ?>
				<?php if ( ! $is_pro ) : ?>
					<span class="sf-pro-badge">Pro</span>
				<?php endif; ?>
			</h2>
			<p><?php esc_html_e( 'Manage multiple domains or GSC properties from a single WordPress installation. Each property has its own GSC, Bing, and GA4 credentials, and all data is isolated per property.', 'searchforge' ); ?></p>
			<h3><?php esc_html_e( 'Adding a Property', 'searchforge' ); ?></h3>
			<ol>
				<li><?php esc_html_e( 'Go to SearchForge → Settings → Properties.', 'searchforge' ); ?></li>
				<li><?php esc_html_e( 'Enter a label (e.g., "Main Site") and the domain (e.g., "example.com").', 'searchforge' ); ?></li>
				<li><?php esc_html_e( 'Click "Add Property". The property appears in the table above.', 'searchforge' ); ?></li>
				<li><?php esc_html_e( 'Connect GSC, Bing, and GA4 credentials for the new property in the respective settings sections.', 'searchforge' ); ?></li>
			</ol>
			<h3><?php esc_html_e( 'Switching Properties', 'searchforge' ); ?></h3>
			<p><?php esc_html_e( 'When you have 2 or more properties, a "Property" dropdown appears at the top of every admin page. Select a property to view its data. Your selection is remembered per user.', 'searchforge' ); ?></p>
			<h3><?php esc_html_e( 'Syncing', 'searchforge' ); ?></h3>
			<p><?php esc_html_e( 'The daily automatic sync processes all properties. You can also trigger a manual sync for a specific property from the Dashboard. Each property syncs independently using its own API credentials.', 'searchforge' ); ?></p>
		</div>

		<hr style="border: none; border-top: 1px solid #dcdcde; margin: 24px 0;">

		<!-- Data Sources -->
		<div class="sf-instructions-section">
			<h2><?php esc_html_e( 'Data Sources', 'searchforge' ); ?></h2>
			<table class="widefat" style="max-width: 720px;">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Source', 'searchforge' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Tier', 'searchforge' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Auth', 'searchforge' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr><td>Google Search Console</td><td><?php esc_html_e( 'Free+', 'searchforge' ); ?></td><td>OAuth 2.0</td></tr>
					<tr><td>Bing Webmaster Tools</td><td><?php esc_html_e( 'Pro+', 'searchforge' ); ?></td><td><?php esc_html_e( 'API Key', 'searchforge' ); ?></td></tr>
					<tr><td>Google Analytics 4</td><td><?php esc_html_e( 'Pro+', 'searchforge' ); ?></td><td>OAuth 2.0</td></tr>
					<tr><td>Google Keyword Planner</td><td><?php esc_html_e( 'Pro+', 'searchforge' ); ?></td><td><?php esc_html_e( 'Developer Token', 'searchforge' ); ?></td></tr>
					<tr><td>Google Trends (via SerpAPI)</td><td><?php esc_html_e( 'Pro+', 'searchforge' ); ?></td><td><?php esc_html_e( 'API Key', 'searchforge' ); ?></td></tr>
				</tbody>
			</table>
			<p style="margin-top: 12px;"><?php esc_html_e( 'Configure all sources in SearchForge → Settings. Each data source enriches your briefs with additional context.', 'searchforge' ); ?></p>
		</div>

		<hr style="border: none; border-top: 1px solid #dcdcde; margin: 24px 0;">

		<!-- Dashboard & Pages -->
		<div class="sf-instructions-section">
			<h2><?php esc_html_e( 'Dashboard & Pages', 'searchforge' ); ?></h2>
			<p><?php esc_html_e( 'The Dashboard shows aggregate KPIs — total clicks, impressions, average CTR, average position, page count, and keyword count. Click "Sync Now" to pull the latest data.', 'searchforge' ); ?></p>
			<p><?php esc_html_e( 'The Pages tab lists all tracked pages sorted by clicks. Click any row to open the Page Detail view with:', 'searchforge' ); ?></p>
			<ul>
				<li><?php esc_html_e( 'SearchForge Score breakdown (Technical, Content, Authority, Momentum)', 'searchforge' ); ?></li>
				<li><?php esc_html_e( 'Daily click/impression chart (30 days)', 'searchforge' ); ?></li>
				<li><?php esc_html_e( 'Top keywords with position, CTR, and search volume', 'searchforge' ); ?></li>
				<li><?php esc_html_e( 'Device breakdown and Bing cross-reference data', 'searchforge' ); ?></li>
				<li><?php esc_html_e( 'GA4 engagement metrics (sessions, bounce rate, conversions)', 'searchforge' ); ?></li>
			</ul>
		</div>

		<hr style="border: none; border-top: 1px solid #dcdcde; margin: 24px 0;">

		<!-- Analysis Tools -->
		<div class="sf-instructions-section">
			<h2><?php esc_html_e( 'Analysis Tools', 'searchforge' ); ?>
				<?php if ( ! $is_pro ) : ?>
					<span class="sf-pro-badge">Pro</span>
				<?php endif; ?>
			</h2>
			<p><?php esc_html_e( 'Available under SearchForge → Analysis:', 'searchforge' ); ?></p>
			<ul>
				<li><strong><?php esc_html_e( 'Content Briefs', 'searchforge' ); ?></strong> — <?php esc_html_e( 'Generate AI-ready or heuristic SEO briefs for any page. Includes keyword context, competitor data, and actionable recommendations.', 'searchforge' ); ?></li>
				<li><strong><?php esc_html_e( 'Keyword Clustering', 'searchforge' ); ?></strong> — <?php esc_html_e( 'Automatically groups related keywords into topic clusters using n-gram Jaccard similarity.', 'searchforge' ); ?></li>
				<li><strong><?php esc_html_e( 'Cannibalization Detection', 'searchforge' ); ?></strong> — <?php esc_html_e( 'Identifies queries where multiple pages compete for the same keyword, ranked by severity (high/medium/low).', 'searchforge' ); ?></li>
			</ul>
		</div>

		<hr style="border: none; border-top: 1px solid #dcdcde; margin: 24px 0;">

		<!-- Competitor Tracking -->
		<div class="sf-instructions-section">
			<h2><?php esc_html_e( 'Competitor Tracking', 'searchforge' ); ?>
				<?php if ( ! $is_pro ) : ?>
					<span class="sf-pro-badge">Pro</span>
				<?php endif; ?>
			</h2>
			<p><?php esc_html_e( 'Add competitor domains in SearchForge → Competitors. SearchForge tracks:', 'searchforge' ); ?></p>
			<ul>
				<li><?php esc_html_e( 'Keyword overlap — keywords you share with competitors', 'searchforge' ); ?></li>
				<li><?php esc_html_e( 'Content gaps — keywords competitors rank for but you don\'t', 'searchforge' ); ?></li>
				<li><?php esc_html_e( 'Visibility comparison — SERP visibility scores across competitors', 'searchforge' ); ?></li>
			</ul>
			<p><?php esc_html_e( 'Pro: up to 3 competitor domains. Agency: unlimited.', 'searchforge' ); ?></p>
		</div>

		<hr style="border: none; border-top: 1px solid #dcdcde; margin: 24px 0;">

		<!-- Property Comparison -->
		<div class="sf-instructions-section">
			<h2><?php esc_html_e( 'Property Comparison', 'searchforge' ); ?>
				<?php if ( ! $is_pro ) : ?>
					<span class="sf-pro-badge">Pro</span>
				<?php endif; ?>
			</h2>
			<p><?php esc_html_e( 'Compare key metrics side-by-side across all your properties. Available under SearchForge → Comparison when you have 2 or more properties configured.', 'searchforge' ); ?></p>
			<p><?php esc_html_e( 'Metrics compared:', 'searchforge' ); ?></p>
			<ul>
				<li><?php esc_html_e( 'Total clicks, impressions, average CTR, average position', 'searchforge' ); ?></li>
				<li><?php esc_html_e( 'Page count, keyword count', 'searchforge' ); ?></li>
				<li><?php esc_html_e( 'SearchForge Score per property', 'searchforge' ); ?></li>
				<li><?php esc_html_e( 'GSC connection status', 'searchforge' ); ?></li>
			</ul>
		</div>

		<hr style="border: none; border-top: 1px solid #dcdcde; margin: 24px 0;">

		<!-- Merger Analysis -->
		<div class="sf-instructions-section">
			<h2><?php esc_html_e( 'Merger Analysis', 'searchforge' ); ?>
				<?php if ( ! $is_pro ) : ?>
					<span class="sf-pro-badge">Pro</span>
				<?php endif; ?>
			</h2>
			<p><?php esc_html_e( 'Generate a comprehensive markdown brief for domain mergers, migrations, and portfolio consolidation. Available under SearchForge → Merger Analysis.', 'searchforge' ); ?></p>
			<h3><?php esc_html_e( 'How to Use', 'searchforge' ); ?></h3>
			<ol>
				<li><?php esc_html_e( 'Ensure at least 2 properties are configured with synced data.', 'searchforge' ); ?></li>
				<li><?php esc_html_e( 'Go to SearchForge → Merger Analysis.', 'searchforge' ); ?></li>
				<li><?php esc_html_e( 'Select 2 or more properties via the checkboxes.', 'searchforge' ); ?></li>
				<li><?php esc_html_e( 'Click "Generate Merger Brief" and wait for the analysis to complete.', 'searchforge' ); ?></li>
				<li><?php esc_html_e( 'Review the rendered brief or click "Download .md" to save it.', 'searchforge' ); ?></li>
			</ol>
			<h3><?php esc_html_e( 'What the Brief Includes', 'searchforge' ); ?></h3>
			<ul>
				<li><strong><?php esc_html_e( 'Executive Summary', 'searchforge' ); ?></strong> — <?php esc_html_e( 'Aggregate traffic, keyword counts, and top-level consolidation recommendations.', 'searchforge' ); ?></li>
				<li><strong><?php esc_html_e( 'URL Pattern Analysis', 'searchforge' ); ?></strong> — <?php esc_html_e( 'Subfolder structure detection across properties.', 'searchforge' ); ?></li>
				<li><strong><?php esc_html_e( 'Navigation Recommendations', 'searchforge' ); ?></strong> — <?php esc_html_e( 'Traffic-weighted header (max 8-10 items) and footer navigation suggestions, plus items to consolidate or retire.', 'searchforge' ); ?></li>
				<li><strong><?php esc_html_e( 'Information Architecture', 'searchforge' ); ?></strong> — <?php esc_html_e( 'Proposed content silos from keyword clusters and URL patterns, redirect map for 301s, orphaned content flagging.', 'searchforge' ); ?></li>
				<li><strong><?php esc_html_e( 'User Funnel Optimization', 'searchforge' ); ?></strong> — <?php esc_html_e( 'Entry points, drop-off pages, conversion corridors to protect, and internal linking recommendations (requires GA4 data).', 'searchforge' ); ?></li>
				<li><strong><?php esc_html_e( 'Cross-Property Cannibalization', 'searchforge' ); ?></strong> — <?php esc_html_e( 'Keywords where multiple properties compete against each other in search results.', 'searchforge' ); ?></li>
			</ul>
		</div>

		<hr style="border: none; border-top: 1px solid #dcdcde; margin: 24px 0;">

		<!-- Export & Output -->
		<div class="sf-instructions-section">
			<h2><?php esc_html_e( 'Export & Output', 'searchforge' ); ?></h2>
			<p><?php esc_html_e( 'SearchForge offers multiple export formats under SearchForge → Export:', 'searchforge' ); ?></p>
			<ul>
				<li><strong><?php esc_html_e( 'Markdown Briefs', 'searchforge' ); ?></strong> — <?php esc_html_e( 'Per-page or full-site SEO briefs ready for LLMs (Claude, ChatGPT, etc.).', 'searchforge' ); ?></li>
				<li><strong><?php esc_html_e( 'CSV / JSON', 'searchforge' ); ?></strong> — <?php esc_html_e( 'Raw data export for pages, keywords, and alerts.', 'searchforge' ); ?></li>
				<li><strong><code>llms.txt</code></strong> — <?php esc_html_e( 'Auto-generated /llms.txt and /llms-full.txt endpoints for AI crawler discovery.', 'searchforge' ); ?></li>
			</ul>
		</div>

		<hr style="border: none; border-top: 1px solid #dcdcde; margin: 24px 0;">

		<!-- Monitoring & Alerts -->
		<div class="sf-instructions-section">
			<h2><?php esc_html_e( 'Monitoring & Alerts', 'searchforge' ); ?>
				<?php if ( ! $is_pro ) : ?>
					<span class="sf-pro-badge">Pro</span>
				<?php endif; ?>
			</h2>
			<p><?php esc_html_e( 'SearchForge continuously monitors your rankings and triggers alerts for:', 'searchforge' ); ?></p>
			<ul>
				<li><?php esc_html_e( 'Ranking drops — when a page loses 3+ positions (configurable threshold)', 'searchforge' ); ?></li>
				<li><?php esc_html_e( 'Traffic anomalies — sudden click or impression changes', 'searchforge' ); ?></li>
				<li><?php esc_html_e( 'Content decay — pages with declining performance over 30+ days', 'searchforge' ); ?></li>
			</ul>
			<p><?php esc_html_e( 'Configure alert thresholds in SearchForge → Settings. Alerts are also included in the weekly digest email.', 'searchforge' ); ?></p>
		</div>

		<hr style="border: none; border-top: 1px solid #dcdcde; margin: 24px 0;">

		<!-- SearchForge Score -->
		<div class="sf-instructions-section">
			<h2><?php esc_html_e( 'SearchForge Score', 'searchforge' ); ?></h2>
			<p><?php esc_html_e( 'A proprietary 0-100 SEO health score calculated per page, combining four equally weighted components (25% each):', 'searchforge' ); ?></p>
			<table class="widefat" style="max-width: 720px;">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Component', 'searchforge' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Weight', 'searchforge' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Measures', 'searchforge' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr><td><?php esc_html_e( 'Technical', 'searchforge' ); ?></td><td>25%</td><td><?php esc_html_e( 'Position quality, keyword breadth, CTR vs. benchmarks', 'searchforge' ); ?></td></tr>
					<tr><td><?php esc_html_e( 'Content', 'searchforge' ); ?></td><td>25%</td><td><?php esc_html_e( 'Keyword diversity, engagement ratio, topic concentration', 'searchforge' ); ?></td></tr>
					<tr><td><?php esc_html_e( 'Authority', 'searchforge' ); ?></td><td>25%</td><td><?php esc_html_e( 'Click volume, impression reach, position authority', 'searchforge' ); ?></td></tr>
					<tr><td><?php esc_html_e( 'Momentum', 'searchforge' ); ?></td><td>25%</td><td><?php esc_html_e( '14-day click trend, position improvement', 'searchforge' ); ?></td></tr>
				</tbody>
			</table>
		</div>

		<hr style="border: none; border-top: 1px solid #dcdcde; margin: 24px 0;">

		<!-- REST API -->
		<div class="sf-instructions-section">
			<h2><?php esc_html_e( 'REST API', 'searchforge' ); ?>
				<?php if ( ! $is_pro ) : ?>
					<span class="sf-pro-badge">Pro</span>
				<?php endif; ?>
			</h2>
			<p><?php printf( esc_html__( 'All endpoints are under %s. Authenticate with an API key via the %s header.', 'searchforge' ), '<code>/wp-json/searchforge/v1/</code>', '<code>X-SearchForge-Key</code>' ); ?></p>
			<table class="widefat" style="max-width: 720px;">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Endpoint', 'searchforge' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Description', 'searchforge' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr><td><code>GET /pages</code></td><td><?php esc_html_e( 'Top pages with metrics', 'searchforge' ); ?></td></tr>
					<tr><td><code>GET /keywords</code></td><td><?php esc_html_e( 'Top keywords with metrics', 'searchforge' ); ?></td></tr>
					<tr><td><code>GET /export/page</code></td><td><?php esc_html_e( 'Single page markdown brief', 'searchforge' ); ?></td></tr>
					<tr><td><code>GET /export/site</code></td><td><?php esc_html_e( 'Full site markdown brief', 'searchforge' ); ?></td></tr>
					<tr><td><code>GET /properties</code></td><td><?php esc_html_e( 'List all properties', 'searchforge' ); ?></td></tr>
					<tr><td><code>GET /comparison</code></td><td><?php esc_html_e( 'Cross-property comparison', 'searchforge' ); ?></td></tr>
					<tr><td><code>GET /merger-analysis</code></td><td><?php esc_html_e( 'Generate merger brief', 'searchforge' ); ?></td></tr>
					<tr><td><code>POST /sync</code></td><td><?php esc_html_e( 'Trigger manual sync', 'searchforge' ); ?></td></tr>
				</tbody>
			</table>
			<p style="margin-top: 8px;"><?php esc_html_e( 'All endpoints accept an optional property_id parameter. Defaults to the active property.', 'searchforge' ); ?></p>
		</div>

		<hr style="border: none; border-top: 1px solid #dcdcde; margin: 24px 0;">

		<!-- WP-CLI -->
		<div class="sf-instructions-section">
			<h2><?php esc_html_e( 'WP-CLI Commands', 'searchforge' ); ?></h2>
			<table class="widefat" style="max-width: 720px;">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Command', 'searchforge' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Description', 'searchforge' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr><td><code>wp searchforge sync</code></td><td><?php esc_html_e( 'Sync all properties (or --property=ID for one)', 'searchforge' ); ?></td></tr>
					<tr><td><code>wp searchforge status</code></td><td><?php esc_html_e( 'Show config, data summary, and properties', 'searchforge' ); ?></td></tr>
					<tr><td><code>wp searchforge properties</code></td><td><?php esc_html_e( 'List all properties with connection status', 'searchforge' ); ?></td></tr>
					<tr><td><code>wp searchforge export pages</code></td><td><?php esc_html_e( 'Export pages (--format=csv|json|md --property=ID)', 'searchforge' ); ?></td></tr>
					<tr><td><code>wp searchforge merger</code></td><td><?php esc_html_e( 'Generate merger brief (--properties=1,2,3 --file=out.md)', 'searchforge' ); ?></td></tr>
				</tbody>
			</table>
		</div>

	</div>
</div>
