<?php
/**
 * Template part for dynamic block: PNW Testimonials.
 *
 * @package pixel
 */

if ( empty( $args ) || empty( $args['testimonials'] ) ) {
	return;
}

$wrapper_attributes  = isset( $args['wrapper_attributes'] ) ? (string) $args['wrapper_attributes'] : 'class="pnw-testimonials"';
$section_title       = isset( $args['section_title'] ) ? (string) $args['section_title'] : '';
$section_description = isset( $args['section_description'] ) ? (string) $args['section_description'] : '';
$layout              = isset( $args['layout'] ) && 'grid' === $args['layout'] ? 'grid' : 'slider';
$show_rating         = ! empty( $args['show_rating'] );
$show_avatar         = ! empty( $args['show_avatar'] );
$show_service_tag    = ! empty( $args['show_service_tag'] );
$testimonials        = is_array( $args['testimonials'] ) ? $args['testimonials'] : array();
$instance_id         = isset( $args['instance_id'] ) ? sanitize_html_class( (string) $args['instance_id'] ) : wp_unique_id( 'pnw-testimonials-' );
$track_id            = $instance_id . '-track';
?>

<section <?php echo wp_kses_data( $wrapper_attributes ); ?> data-layout="<?php echo esc_attr( $layout ); ?>">
	<div class="pnw-testimonials__inner">
		<?php if ( '' !== $section_title || '' !== $section_description ) : ?>
			<header class="pnw-testimonials__header">
				<?php if ( '' !== $section_title ) : ?>
					<h2 class="pnw-testimonials__title"><?php echo esc_html( $section_title ); ?></h2>
				<?php endif; ?>
				<?php if ( '' !== $section_description ) : ?>
					<p class="pnw-testimonials__description"><?php echo esc_html( $section_description ); ?></p>
				<?php endif; ?>
			</header>
		<?php endif; ?>

		<?php if ( 'slider' === $layout ) : ?>
			<div class="pnw-testimonials__controls" data-slider-controls>
				<button type="button" class="pnw-testimonials__nav-button pnw-testimonials__nav-button--prev" data-slider-prev aria-controls="<?php echo esc_attr( $track_id ); ?>" aria-label="<?php echo esc_attr__( 'Poprzednia opinia', 'pixel' ); ?>">
					<span class="pnw-testimonials__nav-icon" aria-hidden="true">&#8592;</span>
				</button>
				<button type="button" class="pnw-testimonials__nav-button pnw-testimonials__nav-button--next" data-slider-next aria-controls="<?php echo esc_attr( $track_id ); ?>" aria-label="<?php echo esc_attr__( 'Następna opinia', 'pixel' ); ?>">
					<span class="pnw-testimonials__nav-icon" aria-hidden="true">&#8594;</span>
				</button>
			</div>
		<?php endif; ?>

		<div class="pnw-testimonials__list" id="<?php echo esc_attr( $track_id ); ?>" data-slider-track>
			<?php foreach ( $testimonials as $testimonial ) : ?>
				<?php
				$author      = isset( $testimonial['author'] ) ? (string) $testimonial['author'] : '';
				$author_meta = isset( $testimonial['author_meta'] ) ? (string) $testimonial['author_meta'] : '';
				$service_tag = isset( $testimonial['service_tag'] ) ? (string) $testimonial['service_tag'] : '';
				$rating      = isset( $testimonial['rating'] ) ? (int) $testimonial['rating'] : 5;
				$rating      = max( 1, min( 5, $rating ) );
				$content     = isset( $testimonial['content'] ) ? (string) $testimonial['content'] : '';
				$avatar      = isset( $testimonial['avatar'] ) ? (string) $testimonial['avatar'] : '';
				?>
				<article class="pnw-testimonials__card" data-slide-item>
					<?php if ( $show_service_tag && '' !== $service_tag ) : ?>
						<p class="pnw-testimonials__service-tag"><?php echo esc_html( $service_tag ); ?></p>
					<?php endif; ?>

					<?php if ( $show_rating ) : ?>
						<p class="pnw-testimonials__rating" aria-label="<?php echo esc_attr( sprintf( __( 'Ocena: %d na 5', 'pixel' ), $rating ) ); ?>">
							<?php
							for ( $i = 1; $i <= 5; $i++ ) {
								echo wp_kses_post( $i <= $rating ? '<span aria-hidden="true">&#9733;</span>' : '<span aria-hidden="true">&#9734;</span>' );
							}
							?>
						</p>
					<?php endif; ?>

					<div class="pnw-testimonials__content">
						<?php echo wp_kses_post( $content ); ?>
					</div>

					<div class="pnw-testimonials__author-row">
						<?php if ( $show_avatar && '' !== $avatar ) : ?>
							<img class="pnw-testimonials__avatar" src="<?php echo esc_url( $avatar ); ?>" alt="<?php echo esc_attr( $author ); ?>" loading="lazy" />
						<?php endif; ?>
						<div class="pnw-testimonials__author-meta-wrap">
							<p class="pnw-testimonials__author"><?php echo esc_html( $author ); ?></p>
							<?php if ( '' !== $author_meta ) : ?>
								<p class="pnw-testimonials__author-meta"><?php echo esc_html( $author_meta ); ?></p>
							<?php endif; ?>
						</div>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
