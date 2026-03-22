<?php
/**
 * Blog posts index template.
 *
 * @package pixel
 */

get_header();

$posts_page_id    = (int) get_option( 'page_for_posts' );
$blog_title       = $posts_page_id ? get_the_title( $posts_page_id ) : __( 'Blog', 'pixel' );
$blog_description = $posts_page_id ? wp_strip_all_tags( get_post_field( 'post_excerpt', $posts_page_id ) ) : '';

if ( '' === $blog_description ) {
	$blog_description = __( 'Porady, inspiracje i krótkie wpisy o druku 3D, projektowaniu i realizacjach.', 'pixel' );
}
?>

	<main id="primary" class="site-main site-main--front site-main--blog">
		<section class="blog-hero section-cream">
			<div class="blog-hero__inner">
				<p class="blog-eyebrow"><?php esc_html_e( 'Blog', 'pixel' ); ?></p>
				<h1 class="blog-hero__title"><?php echo esc_html( $blog_title ); ?></h1>
				<p class="blog-hero__lead"><?php echo esc_html( $blog_description ); ?></p>
			</div>
		</section>

		<section class="blog-feed section-white">
			<div class="blog-feed__inner">
				<?php if ( have_posts() ) : ?>
					<div class="blog-grid">
						<?php
						while ( have_posts() ) :
							the_post();

							$post_id       = get_the_ID();
							$thumbnail_url = get_the_post_thumbnail_url( $post_id, 'large' );
							$categories    = get_the_category( $post_id );
							$category_name = ! empty( $categories ) ? $categories[0]->name : '';
							?>
							<article id="post-<?php the_ID(); ?>" <?php post_class( 'blog-card card' ); ?>>
								<a class="blog-card__link" href="<?php the_permalink(); ?>">
									<div class="blog-card__media">
										<?php if ( $thumbnail_url ) : ?>
											<img class="blog-card__image" src="<?php echo esc_url( $thumbnail_url ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy" />
										<?php else : ?>
											<div class="blog-card__placeholder"><?php esc_html_e( 'Pixel Blog', 'pixel' ); ?></div>
										<?php endif; ?>
									</div>

									<div class="blog-card__body">
										<?php if ( '' !== $category_name ) : ?>
											<p class="blog-card__category"><?php echo esc_html( $category_name ); ?></p>
										<?php endif; ?>

										<h2 class="blog-card__title"><?php the_title(); ?></h2>

										<p class="blog-card__meta">
											<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
												<?php echo esc_html( get_the_date() ); ?>
											</time>
										</p>

										<div class="blog-card__excerpt">
											<?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?>
										</div>

										<span class="blog-card__cta"><?php esc_html_e( 'Czytaj wpis', 'pixel' ); ?></span>
									</div>
								</a>
							</article>
							<?php
						endwhile;
						?>
					</div>

					<?php
					the_posts_pagination(
						array(
							'mid_size'           => 1,
							'prev_text'          => __( 'Poprzednie', 'pixel' ),
							'next_text'          => __( 'Następne', 'pixel' ),
							'screen_reader_text' => __( 'Nawigacja wpisów', 'pixel' ),
						)
					);
					?>
				<?php else : ?>
					<?php get_template_part( 'template-parts/content', 'none' ); ?>
				<?php endif; ?>
			</div>
		</section>
	</main>

<?php
get_footer();
