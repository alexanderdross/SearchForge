<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" type="image/png" sizes="32x32" href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/searchforge-logo.png">
	<link rel="apple-touch-icon" sizes="180x180" href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/searchforge-logo.png">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#main-content" title="Skip to main content">
	<?php esc_html_e( 'Skip to content', 'searchforge-theme' ); ?>
</a>

<header class="sf-header" role="banner">
	<div class="sf-container sf-header__inner">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="sf-header__logo" title="SearchForge for WordPress — SEO Data Aggregation &amp; LLM-Ready Intelligence Plugin" aria-label="<?php esc_attr_e( 'SearchForge Home', 'searchforge-theme' ); ?>">
			<img class="sf-header__logo-img" src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/searchforge-logo.png" alt="SearchForge Logo — WordPress SEO Data Aggregation Plugin" width="40" height="40">
			<span class="sf-header__logo-brand">
				<span class="sf-header__logo-text"><span class="sf-header__logo-search">Search</span>Forge</span>
				<span class="sf-header__logo-claim">for WordPress</span>
			</span>
		</a>

		<nav class="sf-header__nav" role="navigation" aria-label="<?php esc_attr_e( 'Primary Navigation', 'searchforge-theme' ); ?>">
			<ul class="sf-nav-list">
				<li><a href="<?php echo esc_url( home_url( '/#features' ) ); ?>" title="SearchForge Features — SEO Score, AI Briefs, Competitor Analysis &amp; More">Features</a></li>
				<li><a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>" title="SearchForge Pricing — Compare Free, Pro &amp; Agency Plans">Pricing</a></li>
				<li><a href="<?php echo esc_url( home_url( '/docs/' ) ); ?>" title="SearchForge Documentation — Setup, Configuration &amp; API Reference">Docs</a></li>
				<li><a href="<?php echo esc_url( home_url( '/changelog/' ) ); ?>" title="SearchForge Changelog — Version History &amp; Release Notes">Changelog</a></li>
				<li><a href="<?php echo esc_url( home_url( '/enterprise/' ) ); ?>" title="SearchForge Enterprise — Multi-Site, White-Label &amp; Priority Support">Enterprise</a></li>
			</ul>
		</nav>

		<div class="sf-header__actions">
			<a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>" class="sf-btn sf-btn--primary sf-btn--sm" title="SearchForge Pro — Pricing Plans &amp; License Options">Get Pro</a>
		</div>

		<button class="sf-header__toggle" aria-expanded="false" aria-controls="sf-mobile-menu" aria-label="<?php esc_attr_e( 'Toggle navigation', 'searchforge-theme' ); ?>" title="<?php esc_attr_e( 'Toggle navigation menu', 'searchforge-theme' ); ?>">
			<span class="sf-hamburger"></span>
		</button>
	</div>

	<nav id="sf-mobile-menu" class="sf-mobile-menu" hidden aria-label="<?php esc_attr_e( 'Mobile Navigation', 'searchforge-theme' ); ?>">
		<ul class="sf-mobile-nav-list">
			<li><a href="<?php echo esc_url( home_url( '/#features' ) ); ?>" title="SearchForge Features — SEO Score, AI Briefs, Competitor Analysis &amp; More">Features</a></li>
			<li><a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>" title="SearchForge Pricing — Compare Free, Pro &amp; Agency Plans">Pricing</a></li>
			<li><a href="<?php echo esc_url( home_url( '/docs/' ) ); ?>" title="SearchForge Documentation — Setup, Configuration &amp; API Reference">Docs</a></li>
			<li><a href="<?php echo esc_url( home_url( '/changelog/' ) ); ?>" title="SearchForge Changelog — Version History &amp; Release Notes">Changelog</a></li>
			<li><a href="<?php echo esc_url( home_url( '/enterprise/' ) ); ?>" title="SearchForge Enterprise — Multi-Site, White-Label &amp; Priority Support">Enterprise</a></li>
		</ul>
		<a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>" class="sf-btn sf-btn--primary sf-btn--block" title="SearchForge Pro — Pricing Plans &amp; License Options">Get Pro</a>
	</nav>
</header>

<?php get_template_part( 'template-parts/breadcrumb' ); ?>

<main id="main-content" role="main">
<?php

