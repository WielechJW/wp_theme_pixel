<?php
/**
 * Template part for displaying a message that posts cannot be found.
 *
 * @package pixel
 */

?>

<section class="no-results not-found">
	<header class="page-header">
		<h1 class="page-title"><?php esc_html_e( 'Nothing found', 'pixel' ); ?></h1>
	</header>
	<div class="page-content">
		<p><?php esc_html_e( 'No content matched your request.', 'pixel' ); ?></p>
		<?php get_search_form(); ?>
	</div>
</section>
