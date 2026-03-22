<?php
/**
 * WooCommerce product archive template.
 *
 * @package pixel
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

	<main id="primary" class="site-main site-main--front site-main--shop">
		<section class="shop-hero section-cream">
			<div class="shop-hero__inner">
				<?php woocommerce_breadcrumb(); ?>

				<p class="shop-eyebrow"><?php esc_html_e( 'Sklep', 'pixel' ); ?></p>
				<h1 class="shop-hero__title"><?php woocommerce_page_title(); ?></h1>

				<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
					<?php do_action( 'woocommerce_archive_description' ); ?>
				<?php endif; ?>
			</div>
		</section>

		<section class="shop-catalog section-white">
			<div class="shop-catalog__inner">
				<?php woocommerce_output_all_notices(); ?>

				<?php if ( woocommerce_product_loop() ) : ?>
					<div class="shop-toolbar">
						<?php woocommerce_result_count(); ?>
						<?php woocommerce_catalog_ordering(); ?>
					</div>

					<?php woocommerce_product_loop_start(); ?>

					<?php if ( wc_get_loop_prop( 'total' ) ) : ?>
						<?php while ( have_posts() ) : ?>
							<?php
							the_post();
							do_action( 'woocommerce_shop_loop' );
							wc_get_template_part( 'content', 'product' );
							?>
						<?php endwhile; ?>
					<?php endif; ?>

					<?php woocommerce_product_loop_end(); ?>

					<?php woocommerce_pagination(); ?>
				<?php else : ?>
					<?php wc_no_products_found(); ?>
				<?php endif; ?>
			</div>
		</section>
	</main>

<?php
get_footer();
