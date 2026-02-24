<?php
/**
 * The template for displaying pages.
 *
 * @package pixel
 */

get_header();
?>

	<main id="primary" class="site-main">
		<?php
		while ( have_posts() ) :
			the_post();
			get_template_part( 'template-parts/content' );
		endwhile;
		?>
	</main>

<?php
get_footer();
