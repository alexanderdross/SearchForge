<?php
/**
 * 404 error page.
 *
 * @package SearchForge_Theme
 */

get_header();
?>

<section class="sf-section sf-section--dark sf-404">
	<div class="sf-container">
		<h1 class="sf-404__heading">404</h1>
		<p class="sf-text--large">This page could not be found.</p>
		<p class="sf-text--muted">The page you're looking for doesn't exist or has been moved.</p>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="sf-btn sf-btn--primary sf-404__btn" title="SearchForge Home - Return to the Homepage">Back to Home</a>
	</div>
</section>

<?php
get_footer();
