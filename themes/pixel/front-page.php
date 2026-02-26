<?php
/**
 * Front page template.
 *
 * @package pixel
 */

get_header();
?>

	<main id="primary" class="site-main site-main--front">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'front-page-content' ); ?>>
				<div class="entry-content">
					<?php
					the_content();

					wp_link_pages(
						array(
							'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'pixel' ),
							'after'  => '</div>',
						)
					);
					?>
				</div>
			</article>
			<?php
		endwhile;
		?>
	</main>

<?php
get_footer();
