<?php
/**
 * Fallback template.
 *
 * @package SearchForge_Theme
 */

get_header();
?>

<section class="sf-section">
	<div class="sf-container">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<h1><?php the_title(); ?></h1>
					<div class="sf-content"><?php the_content(); ?></div>
				</article>
				<?php
			endwhile;
		else :
			?>
			<p><?php esc_html_e( 'No content found.', 'searchforge-theme' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
