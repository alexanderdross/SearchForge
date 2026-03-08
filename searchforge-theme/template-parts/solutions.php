<?php
/**
 * Template part: Solutions section.
 *
 * @package SearchForge_Theme
 */

$solutions = [
	[
		'icon'  => 'dashboard',
		'title' => 'Unified Dashboard',
		'desc'  => 'GSC + Bing + GA4 + Trends in one place. See all your data correlated and actionable.',
	],
	[
		'icon'  => 'sync',
		'title' => 'Auto-Sync Daily',
		'desc'  => 'Background sync keeps data fresh. No manual exports, no stale spreadsheets.',
	],
	[
		'icon'  => 'markdown',
		'title' => 'LLM-Ready Markdown',
		'desc'  => 'Per-page briefs ready for Claude, ChatGPT, or any LLM. One click to export.',
	],
	[
		'icon'  => 'aeo',
		'title' => 'AI Visibility Tracked',
		'desc'  => 'Know when Google AI, ChatGPT, and Perplexity cite your content.',
	],
	[
		'icon'  => 'competitors',
		'title' => 'Competitor Intel Included',
		'desc'  => 'See who outranks you and why. No $1,400/yr subscription needed.',
	],
	[
		'icon'  => 'score',
		'title' => 'SearchForge Score',
		'desc'  => 'Proprietary 0-100 score with actionable breakdown. Know exactly where to focus.',
	],
];
?>

<section class="sf-section">
	<div class="sf-container">
		<div class="sf-section__header">
			<h2>One Plugin. All Your SEO Data. AI-Ready.</h2>
			<p class="sf-text--muted">SearchForge brings it all together.</p>
		</div>

		<div class="sf-grid sf-grid--3">
			<?php foreach ( $solutions as $solution ) : ?>
				<div class="sf-card sf-card--accent">
					<div class="sf-card__icon" aria-hidden="true">
						<img src="<?php echo esc_url( SF_THEME_URI ); ?>/assets/images/icons/<?php echo esc_attr( $solution['icon'] ); ?>.svg" alt="" width="24" height="24">
					</div>
					<h3 class="sf-card__title"><?php echo esc_html( $solution['title'] ); ?></h3>
					<p class="sf-card__desc"><?php echo esc_html( $solution['desc'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
