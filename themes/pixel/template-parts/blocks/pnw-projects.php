<?php
/**
 * Template part for dynamic block: PNW Projects.
 *
 * @package pixel
 */

if ( empty( $args ) || empty( $args['projects'] ) ) {
	return;
}

$wrapper_attributes = isset( $args['wrapper_attributes'] ) ? (string) $args['wrapper_attributes'] : 'class="pnw-projects"';
$section_title      = isset( $args['section_title'] ) ? (string) $args['section_title'] : '';
$section_description = isset( $args['section_description'] ) ? (string) $args['section_description'] : '';
$show_filters       = ! empty( $args['show_filters'] );
$show_tags          = ! empty( $args['show_tags'] );
$projects           = is_array( $args['projects'] ) ? $args['projects'] : array();
$categories         = is_array( $args['categories'] ) ? $args['categories'] : array();
$instance_id        = isset( $args['instance_id'] ) ? sanitize_html_class( (string) $args['instance_id'] ) : wp_unique_id( 'pnw-projects-modal-' );
$modal_title_id     = $instance_id . '-title';
?>

<section <?php echo wp_kses_data( $wrapper_attributes ); ?>>
	<div class="pnw-projects__inner">
		<?php if ( '' !== $section_title || '' !== $section_description ) : ?>
			<header class="pnw-projects__header">
				<?php if ( '' !== $section_title ) : ?>
					<h2 class="pnw-projects__title"><?php echo esc_html( $section_title ); ?></h2>
				<?php endif; ?>

				<?php if ( '' !== $section_description ) : ?>
					<p class="pnw-projects__description"><?php echo esc_html( $section_description ); ?></p>
				<?php endif; ?>
			</header>
		<?php endif; ?>

		<?php if ( $show_filters && ! empty( $categories ) ) : ?>
			<div class="pnw-projects__filters" aria-label="<?php echo esc_attr__( 'Filtry realizacji', 'pixel' ); ?>">
				<button class="pnw-projects__filter-button is-active" type="button" data-filter="all" aria-pressed="true">
					<?php echo esc_html__( 'Wszystkie', 'pixel' ); ?>
				</button>
				<?php foreach ( $categories as $category ) : ?>
					<button class="pnw-projects__filter-button" type="button" data-filter="<?php echo esc_attr( (string) $category->term_id ); ?>" aria-pressed="false">
						<?php echo esc_html( $category->name ); ?>
					</button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="pnw-projects__grid">
			<?php foreach ( $projects as $project ) : ?>
				<?php
				$category_ids_attr = implode(
					',',
					array_map(
						'absint',
						isset( $project['category_ids'] ) && is_array( $project['category_ids'] ) ? $project['category_ids'] : array()
					)
				);
				$category_names = isset( $project['category_names'] ) && is_array( $project['category_names'] ) ? $project['category_names'] : array();
				$tag_names      = isset( $project['tag_names'] ) && is_array( $project['tag_names'] ) ? $project['tag_names'] : array();
				$tag_list_attr  = implode( '|', array_map( 'sanitize_text_field', $tag_names ) );
				$category_label = implode( ', ', array_map( 'sanitize_text_field', $category_names ) );
				$image_url      = isset( $project['image'] ) ? (string) $project['image'] : '';
				$title          = isset( $project['title'] ) ? (string) $project['title'] : '';
				?>

				<article class="pnw-projects__item" data-categories="<?php echo esc_attr( $category_ids_attr ); ?>">
					<button
						class="pnw-projects__tile"
						type="button"
						data-project-title="<?php echo esc_attr( $title ); ?>"
						data-project-category="<?php echo esc_attr( $category_label ); ?>"
						data-project-tags="<?php echo esc_attr( $tag_list_attr ); ?>"
						data-project-image="<?php echo esc_url( $image_url ); ?>"
					>
						<div class="pnw-projects__image-wrap">
							<?php if ( '' !== $image_url ) : ?>
								<img class="pnw-projects__image" src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy" />
							<?php else : ?>
								<div class="pnw-projects__image-placeholder"><?php echo esc_html__( 'Brak obrazka', 'pixel' ); ?></div>
							<?php endif; ?>
						</div>

						<div class="pnw-projects__body">
							<h3 class="pnw-projects__project-title"><?php echo esc_html( $title ); ?></h3>

							<?php if ( $show_tags && ! empty( $tag_names ) ) : ?>
								<div class="pnw-projects__chips">
									<?php foreach ( $tag_names as $tag_name ) : ?>
										<span class="pnw-projects__chip"><?php echo esc_html( $tag_name ); ?></span>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>
					</button>

					<div class="pnw-projects__modal-source" hidden>
						<?php
						echo isset( $project['content'] ) ? wp_kses_post( $project['content'] ) : '';
						?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>

		<p class="pnw-projects__empty" data-empty-message hidden>
			<?php echo esc_html__( 'Brak realizacji w wybranej kategorii.', 'pixel' ); ?>
		</p>
	</div>

	<div class="pnw-projects__modal" hidden>
		<div class="pnw-projects__backdrop" data-modal-close></div>
		<div class="pnw-projects__dialog" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $modal_title_id ); ?>" tabindex="-1">
			<button class="pnw-projects__modal-close" type="button" data-modal-close aria-label="<?php echo esc_attr__( 'Zamknij modal', 'pixel' ); ?>">
				&times;
			</button>

			<div class="pnw-projects__modal-media">
				<img class="pnw-projects__modal-image" src="" alt="" loading="lazy" />
			</div>

			<div class="pnw-projects__modal-body">
				<p class="pnw-projects__modal-category"></p>
				<h3 class="pnw-projects__modal-title" id="<?php echo esc_attr( $modal_title_id ); ?>"></h3>
				<div class="pnw-projects__chips pnw-projects__modal-tags"></div>
				<div class="pnw-projects__modal-content"></div>
			</div>
		</div>
	</div>
</section>
