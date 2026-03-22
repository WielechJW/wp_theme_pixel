<?php
/**
 * WooCommerce single product template.
 *
 * @package pixel
 * @version 1.6.4
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

	<main id="primary" class="site-main site-main--front site-main--shop site-main--product">
		<section class="shop-product section-white">
			<div class="shop-product__inner">
				<?php woocommerce_breadcrumb(); ?>
				<?php woocommerce_output_all_notices(); ?>

				<?php while ( have_posts() ) : ?>
					<?php
					the_post();
					wc_get_template_part( 'content', 'single-product' );
					?>
				<?php endwhile; ?>
			</div>
		</section>
	</main>

<?php
get_footer();
