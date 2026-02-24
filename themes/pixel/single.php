<?php
/**
 * The template for displaying all single posts.
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
			the_post_navigation(
				array(
					'prev_text' => esc_html__( 'Previous', 'pixel' ),
					'next_text' => esc_html__( 'Next', 'pixel' ),
				)
			);
		endwhile;
		?>
	</main>

<?php
get_footer();
