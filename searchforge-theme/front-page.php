<?php
/**
 * SearchForge landing page template.
 *
 * @package SearchForge_Theme
 */

get_header();

get_template_part( 'template-parts/hero' );
get_template_part( 'template-parts/stats-bar' );
get_template_part( 'template-parts/problems' );
get_template_part( 'template-parts/solutions' );
get_template_part( 'template-parts/data-sources' );
get_template_part( 'template-parts/features' );
get_template_part( 'template-parts/setup-steps' );
get_template_part( 'template-parts/comparison' );
get_template_part( 'template-parts/pricing' );
get_template_part( 'template-parts/compatibility' );
get_template_part( 'template-parts/cachewarmer-bundle' );
get_template_part( 'template-parts/faq' );
get_template_part( 'template-parts/final-cta' );

get_footer();
