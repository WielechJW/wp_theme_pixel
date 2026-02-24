<?php
/**
 * The template for displaying 404 pages.
 *
 * @package pixel
 */

get_header();
?>

	<main id="primary" class="site-main">
		<section class="error-404 not-found">
			<header class="page-header">
				<h1 class="page-title"><?php esc_html_e( 'Page not found', 'pixel' ); ?></h1>
			</header>
			<div class="page-content">
				<p><?php esc_html_e( 'Try searching for what you need.', 'pixel' ); ?></p>
				<?php get_search_form(); ?>
			</div>
		</section>
	</main>

<?php
get_footer();
