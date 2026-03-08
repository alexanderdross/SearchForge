<?php
/**
 * Template part: Pricing section.
 *
 * @package SearchForge_Theme
 */

$tiers = [
	[
		'name'       => 'Free',
		'price'      => '0',
		'period'     => 'forever',
		'popular'    => false,
		'cta_text'   => 'Start Free',
		'cta_class'  => 'sf-btn--outline',
		'cta_url'    => 'https://wordpress.org/plugins/searchforge/',
		'features'   => [
			'Google Search Console (10 pages)',
			'SearchForge Score (overall)',
			'Basic llms.txt generation',
			'Dashboard with GSC overview',
			'30-day data retention',
			'1 site',
		],
	],
	[
		'name'       => 'Pro',
		'price'      => '99',
		'period'     => '/year',
		'popular'    => true,
		'cta_text'   => 'Get Pro',
		'cta_class'  => 'sf-btn--primary',
		'cta_url'    => '/checkout/?tier=pro',
		'features'   => [
			'All 8 data sources (unlimited)',
			'Full SearchForge Score breakdown',
			'AI Visibility Monitor (20 queries/mo)',
			'Competitor Intelligence (10 keywords/mo)',
			'AI Content Briefs (10/mo)',
			'Combined Master Brief export',
			'Keyword clustering & cannibalization',
			'Content decay alerts (email)',
			'12-month data retention',
			'WP-CLI + Read-only REST API',
		],
	],
	[
		'name'       => 'Agency',
		'price'      => '249',
		'period'     => '/year',
		'popular'    => false,
		'cta_text'   => 'Contact Sales',
		'cta_class'  => 'sf-btn--outline',
		'cta_url'    => '/enterprise/',
		'features'   => [
			'Everything in Pro',
			'10 sites, unlimited team members',
			'AEO: 200 queries/mo',
			'Competitors: 100 keywords/mo',
			'White-label reports',
			'Client portal (read-only links)',
			'Slack/Discord + Webhook alerts',
			'GitHub/Notion/Sheets sync',
			'Full CRUD REST API',
			'24-month data retention',
		],
	],
];
?>

<section class="sf-section sf-section--light" id="pricing">
	<div class="sf-container">
		<div class="sf-section__header">
			<h2>Simple, Transparent Pricing</h2>
			<p class="sf-text--muted">Start free. Upgrade when you need more.</p>
		</div>

		<div class="sf-pricing-grid">
			<?php foreach ( $tiers as $tier ) : ?>
				<div class="sf-pricing-card <?php echo $tier['popular'] ? 'sf-pricing-card--popular' : ''; ?>">
					<?php if ( $tier['popular'] ) : ?>
						<span class="sf-pricing-card__badge">Most Popular</span>
					<?php endif; ?>

					<h3 class="sf-pricing-card__name"><?php echo esc_html( $tier['name'] ); ?></h3>
					<div class="sf-pricing-card__price">
						<span class="sf-pricing-card__currency">&euro;</span>
						<span class="sf-pricing-card__amount"><?php echo esc_html( $tier['price'] ); ?></span>
						<span class="sf-pricing-card__period"><?php echo esc_html( $tier['period'] ); ?></span>
					</div>

					<ul class="sf-pricing-card__features">
						<?php foreach ( $tier['features'] as $feature ) : ?>
							<li><?php echo esc_html( $feature ); ?></li>
						<?php endforeach; ?>
					</ul>

					<a href="<?php echo esc_url( $tier['cta_url'] ); ?>" class="sf-btn <?php echo esc_attr( $tier['cta_class'] ); ?> sf-btn--block">
						<?php echo esc_html( $tier['cta_text'] ); ?>
					</a>
				</div>
			<?php endforeach; ?>
		</div>

		<p class="sf-pricing-note">
			Enterprise (unlimited sites) available at &euro;599/year.
			Lifetime deals: Pro &euro;249 one-time, Agency &euro;599 one-time.
		</p>
	</div>
</section>
