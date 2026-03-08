<?php
/**
 * Template part: Problem section.
 *
 * @package SearchForge_Theme
 */

$problems = [
	[
		'icon'  => 'lock',
		'title' => 'Locked in Separate Consoles',
		'desc'  => 'GSC, Bing, GA4 each live in their own dashboard. You never see the full picture.',
	],
	[
		'icon'  => 'clipboard',
		'title' => 'Manual Copy-Paste',
		'desc'  => 'Hours spent exporting CSVs and building spreadsheets nobody reads twice.',
	],
	[
		'icon'  => 'eye-off',
		'title' => 'AI-Blind SEO',
		'desc'  => 'ChatGPT and Perplexity can\'t see your SEO performance. Your data stays invisible to AI.',
	],
	[
		'icon'  => 'layers',
		'title' => 'No Combined View',
		'desc'  => 'Google vs Bing vs GA4 data never gets correlated. Insights fall through the cracks.',
	],
	[
		'icon'  => 'clock',
		'title' => 'Outdated on Arrival',
		'desc'  => 'Weekly CSV exports are stale the moment you open them. Trends move faster.',
	],
	[
		'icon'  => 'dollar',
		'title' => '$1,400/yr for Competitor Data',
		'desc'  => 'Semrush and Ahrefs are powerful but overkill and overpriced for most WordPress sites.',
	],
];
?>

<section class="sf-section sf-section--light" id="problems">
	<div class="sf-container">
		<div class="sf-section__header">
			<h2>Your SEO Data Is Trapped in Silos</h2>
			<p class="sf-text--muted">Sound familiar? You're not alone.</p>
		</div>

		<div class="sf-grid sf-grid--3">
			<?php foreach ( $problems as $problem ) : ?>
				<div class="sf-card sf-card--bordered">
					<div class="sf-card__icon sf-card__icon--muted" aria-hidden="true">
						<img src="<?php echo esc_url( SF_THEME_URI ); ?>/assets/images/icons/<?php echo esc_attr( $problem['icon'] ); ?>.svg" alt="" width="24" height="24">
					</div>
					<h3 class="sf-card__title"><?php echo esc_html( $problem['title'] ); ?></h3>
					<p class="sf-card__desc"><?php echo esc_html( $problem['desc'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
