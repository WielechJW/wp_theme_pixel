<?php
/**
 * Template part for displaying posts and pages.
 *
 * @package pixel
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry' ); ?>>
	<header class="entry-header">
		<?php if ( is_singular() ) : ?>
			<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
		<?php else : ?>
			<?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>
		<?php endif; ?>

		<?php if ( 'post' === get_post_type() ) : ?>
			<div class="entry-meta">
				<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
					<?php echo esc_html( get_the_date() ); ?>
				</time>
				<span class="entry-meta-sep">·</span>
				<span class="entry-author">
					<?php echo esc_html( get_the_author() ); ?>
				</span>
			</div>
		<?php endif; ?>
	</header>

	<div class="entry-content">
		<?php
		if ( is_singular() ) {
			the_content();
		} else {
			the_excerpt();
		}

		wp_link_pages(
			array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'pixel' ),
				'after'  => '</div>',
			)
		);
		?>
	</div>
</article>
