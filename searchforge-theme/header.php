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

<a class="skip-link screen-reader-text" href="#main-content">
	<?php esc_html_e( 'Skip to content', 'searchforge-theme' ); ?>
</a>

<header class="sf-header" role="banner">
	<div class="sf-container sf-header__inner">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="sf-header__logo" aria-label="<?php esc_attr_e( 'SearchForge Home', 'searchforge-theme' ); ?>">
			<img class="sf-header__logo-img" src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/searchforge-logo.png" alt="" aria-hidden="true" width="40" height="40">
			<span class="sf-header__logo-brand">
				<span class="sf-header__logo-text"><span class="sf-header__logo-search">Search</span>Forge</span>
				<span class="sf-header__logo-claim">for WordPress</span>
			</span>
		</a>

		<nav class="sf-header__nav" role="navigation" aria-label="<?php esc_attr_e( 'Primary Navigation', 'searchforge-theme' ); ?>">
			<?php
			wp_nav_menu( [
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'sf-nav-list',
				'depth'          => 2,
				'fallback_cb'    => 'sf_default_nav',
			] );
			?>
		</nav>

		<div class="sf-header__actions">
			<a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>" class="sf-btn sf-btn--primary sf-btn--sm" title="SearchForge Pro — Pricing Plans &amp; License Options">Get Pro</a>
		</div>

		<button class="sf-header__toggle" aria-expanded="false" aria-controls="sf-mobile-menu" aria-label="<?php esc_attr_e( 'Toggle navigation', 'searchforge-theme' ); ?>">
			<span class="sf-hamburger"></span>
		</button>
	</div>

	<nav id="sf-mobile-menu" class="sf-mobile-menu" hidden aria-label="<?php esc_attr_e( 'Mobile Navigation', 'searchforge-theme' ); ?>">
		<?php
		wp_nav_menu( [
			'theme_location' => 'primary',
			'container'      => false,
			'menu_class'     => 'sf-mobile-nav-list',
			'depth'          => 1,
		] );
		?>
		<a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>" class="sf-btn sf-btn--primary sf-btn--block" title="SearchForge Pro — Pricing Plans &amp; License Options">Get Pro</a>
	</nav>
</header>

<?php get_template_part( 'template-parts/breadcrumb' ); ?>

<main id="main-content" role="main">
<?php

