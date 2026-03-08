<?php
/**
 * Template part: Features grid.
 *
 * @package SearchForge_Theme
 */

$features = [
	[
		'icon'  => 'score',
		'title' => 'SearchForge Score',
		'desc'  => 'Proprietary 0-100 SEO score with breakdown across Technical SEO, Content Quality, Authority, and Momentum.',
	],
	[
		'icon'  => 'aeo',
		'title' => 'AI Visibility Monitor',
		'desc'  => 'Track citations in ChatGPT, Perplexity, Google AI Overviews, and Bing Copilot.',
	],
	[
		'icon'  => 'competitors',
		'title' => 'Competitor Intelligence',
		'desc'  => 'See who outranks you on shared keywords, SERP features, and content gaps.',
	],
	[
		'icon'  => 'briefs',
		'title' => 'AI Content Briefs',
		'desc'  => 'AI-generated per-page briefs with specific recommendations. Uses your own API key.',
	],
	[
		'icon'  => 'llms-txt',
		'title' => 'llms.txt Generation',
		'desc'  => 'Auto-generate and maintain /llms.txt for AI crawler discoverability.',
	],
	[
		'icon'  => 'clustering',
		'title' => 'Keyword Clustering',
		'desc'  => 'Group keywords into topic clusters with pillar page suggestions.',
	],
	[
		'icon'  => 'decay',
		'title' => 'Content Decay Alerts',
		'desc'  => 'Get notified when rankings decline. Detect decay before it costs you traffic.',
	],
	[
		'icon'  => 'history',
		'title' => 'Historical Trends',
		'desc'  => '12-month rolling snapshots with year-over-year comparison and velocity metrics.',
	],
	[
		'icon'  => 'cannibalization',
		'title' => 'Cannibalization Detection',
		'desc'  => 'Find pages competing for the same keywords. Resolve conflicts, consolidate authority.',
	],
	[
		'icon'  => 'export',
		'title' => 'Multi-Source Export',
		'desc'  => 'Combined markdown briefs from all 8 data sources. WP-CLI, REST API, or ZIP download.',
	],
];
?>

<section class="sf-section" id="features">
	<div class="sf-container">
		<div class="sf-section__header">
			<h2>Everything You Need to Win in Search + AI</h2>
		</div>

		<div class="sf-grid sf-grid--2">
			<?php foreach ( $features as $feature ) : ?>
				<div class="sf-feature-card">
					<div class="sf-feature-card__icon" aria-hidden="true">
						<img src="<?php echo esc_url( SF_THEME_URI ); ?>/assets/images/icons/<?php echo esc_attr( $feature['icon'] ); ?>.svg" alt="" width="24" height="24">
					</div>
					<div class="sf-feature-card__content">
						<h3><?php echo esc_html( $feature['title'] ); ?></h3>
						<p><?php echo esc_html( $feature['desc'] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
