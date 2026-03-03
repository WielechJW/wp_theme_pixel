<?php
/**
 * PNW Testimonials: custom post type and post meta registration.
 *
 * @package pixel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitizes testimonial rating to integer 1..5.
 *
 * @param mixed $value Raw rating value.
 * @return int
 */
function pixel_sanitize_testimonial_rating( $value ) {
	$rating = absint( $value );

	if ( $rating < 1 ) {
		$rating = 1;
	}

	if ( $rating > 5 ) {
		$rating = 5;
	}

	return $rating;
}

/**
 * Registers PNW Testimonials CPT.
 *
 * @return void
 */
function pixel_register_pnw_testimonials_cpt() {
	register_post_type(
		'pnw_testimonial',
		array(
			'labels' => array(
				'name'                  => __( 'Opinie', 'pixel' ),
				'singular_name'         => __( 'Opinia', 'pixel' ),
				'add_new'               => __( 'Dodaj nową', 'pixel' ),
				'add_new_item'          => __( 'Dodaj opinię', 'pixel' ),
				'edit_item'             => __( 'Edytuj opinię', 'pixel' ),
				'new_item'              => __( 'Nowa opinia', 'pixel' ),
				'view_item'             => __( 'Zobacz opinię', 'pixel' ),
				'search_items'          => __( 'Szukaj opinii', 'pixel' ),
				'not_found'             => __( 'Nie znaleziono opinii.', 'pixel' ),
				'not_found_in_trash'    => __( 'Brak opinii w koszu.', 'pixel' ),
				'all_items'             => __( 'Wszystkie opinie', 'pixel' ),
				'menu_name'             => __( 'Opinie', 'pixel' ),
				'name_admin_bar'        => __( 'Opinia', 'pixel' ),
				'featured_image'        => __( 'Avatar / Logo', 'pixel' ),
				'set_featured_image'    => __( 'Ustaw avatar / logo', 'pixel' ),
				'remove_featured_image' => __( 'Usuń avatar / logo', 'pixel' ),
				'use_featured_image'    => __( 'Użyj jako avatar / logo', 'pixel' ),
			),
			'public'             => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_rest'       => true,
			'publicly_queryable' => true,
			'has_archive'        => false,
			'menu_icon'          => 'dashicons-format-status',
			'supports'           => array( 'title', 'editor', 'thumbnail' ),
			'rewrite'            => array(
				'slug'       => 'opinie',
				'with_front' => false,
			),
		)
	);
}
add_action( 'init', 'pixel_register_pnw_testimonials_cpt' );

/**
 * Registers testimonial meta fields for Gutenberg + REST.
 *
 * @return void
 */
function pixel_register_pnw_testimonials_meta() {
	$meta_auth_callback = static function () {
		return current_user_can( 'edit_posts' );
	};

	register_post_meta(
		'pnw_testimonial',
		'rating',
		array(
			'type'              => 'integer',
			'single'            => true,
			'default'           => 5,
			'sanitize_callback' => 'pixel_sanitize_testimonial_rating',
			'auth_callback'     => $meta_auth_callback,
			'show_in_rest'      => array(
				'schema' => array(
					'type'    => 'integer',
					'minimum' => 1,
					'maximum' => 5,
					'default' => 5,
				),
			),
		)
	);

	register_post_meta(
		'pnw_testimonial',
		'author_name',
		array(
			'type'              => 'string',
			'single'            => true,
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => $meta_auth_callback,
			'show_in_rest'      => true,
		)
	);

	register_post_meta(
		'pnw_testimonial',
		'author_meta',
		array(
			'type'              => 'string',
			'single'            => true,
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => $meta_auth_callback,
			'show_in_rest'      => true,
		)
	);

	register_post_meta(
		'pnw_testimonial',
		'service_tag',
		array(
			'type'              => 'string',
			'single'            => true,
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => $meta_auth_callback,
			'show_in_rest'      => true,
		)
	);
}
add_action( 'init', 'pixel_register_pnw_testimonials_meta' );
