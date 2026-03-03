<?php
/**
 * PNW Projects content model: custom post type + taxonomies.
 *
 * @package pixel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the projects custom post type and project taxonomies.
 *
 * @return void
 */
function pixel_register_pnw_projects_content_model() {
	register_post_type(
		'pnw_project',
		array(
			'labels' => array(
				'name'                  => __( 'Realizacje', 'pixel' ),
				'singular_name'         => __( 'Realizacja', 'pixel' ),
				'add_new'               => __( 'Dodaj nową', 'pixel' ),
				'add_new_item'          => __( 'Dodaj realizację', 'pixel' ),
				'edit_item'             => __( 'Edytuj realizację', 'pixel' ),
				'new_item'              => __( 'Nowa realizacja', 'pixel' ),
				'view_item'             => __( 'Zobacz realizację', 'pixel' ),
				'search_items'          => __( 'Szukaj realizacji', 'pixel' ),
				'not_found'             => __( 'Nie znaleziono realizacji.', 'pixel' ),
				'not_found_in_trash'    => __( 'Brak realizacji w koszu.', 'pixel' ),
				'all_items'             => __( 'Wszystkie realizacje', 'pixel' ),
				'menu_name'             => __( 'Realizacje', 'pixel' ),
				'name_admin_bar'        => __( 'Realizacja', 'pixel' ),
				'featured_image'        => __( 'Obrazek wyróżniający', 'pixel' ),
				'set_featured_image'    => __( 'Ustaw obrazek wyróżniający', 'pixel' ),
				'remove_featured_image' => __( 'Usuń obrazek wyróżniający', 'pixel' ),
				'use_featured_image'    => __( 'Użyj jako obrazek wyróżniający', 'pixel' ),
			),
			'public'             => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_rest'       => true,
			'has_archive'        => false,
			'publicly_queryable' => true,
			'menu_icon'          => 'dashicons-portfolio',
			'supports'           => array( 'title', 'editor', 'thumbnail' ),
			'taxonomies'         => array( 'pnw_project_category', 'pnw_project_tag' ),
			'rewrite'            => array(
				'slug'       => 'realizacje',
				'with_front' => false,
			),
		)
	);

	register_taxonomy(
		'pnw_project_category',
		array( 'pnw_project' ),
		array(
			'labels' => array(
				'name'              => __( 'Kategorie realizacji', 'pixel' ),
				'singular_name'     => __( 'Kategoria realizacji', 'pixel' ),
				'search_items'      => __( 'Szukaj kategorii', 'pixel' ),
				'all_items'         => __( 'Wszystkie kategorie', 'pixel' ),
				'parent_item'       => __( 'Kategoria nadrzędna', 'pixel' ),
				'parent_item_colon' => __( 'Kategoria nadrzędna:', 'pixel' ),
				'edit_item'         => __( 'Edytuj kategorię', 'pixel' ),
				'update_item'       => __( 'Zaktualizuj kategorię', 'pixel' ),
				'add_new_item'      => __( 'Dodaj nową kategorię', 'pixel' ),
				'new_item_name'     => __( 'Nazwa nowej kategorii', 'pixel' ),
				'menu_name'         => __( 'Kategorie', 'pixel' ),
			),
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array(
				'slug'       => 'realizacje-kategoria',
				'with_front' => false,
			),
		)
	);

	register_taxonomy(
		'pnw_project_tag',
		array( 'pnw_project' ),
		array(
			'labels' => array(
				'name'                       => __( 'Tagi realizacji', 'pixel' ),
				'singular_name'              => __( 'Tag realizacji', 'pixel' ),
				'search_items'               => __( 'Szukaj tagów', 'pixel' ),
				'popular_items'              => __( 'Popularne tagi', 'pixel' ),
				'all_items'                  => __( 'Wszystkie tagi', 'pixel' ),
				'edit_item'                  => __( 'Edytuj tag', 'pixel' ),
				'update_item'                => __( 'Zaktualizuj tag', 'pixel' ),
				'add_new_item'               => __( 'Dodaj nowy tag', 'pixel' ),
				'new_item_name'              => __( 'Nazwa nowego tagu', 'pixel' ),
				'separate_items_with_commas' => __( 'Oddziel tagi przecinkami', 'pixel' ),
				'add_or_remove_items'        => __( 'Dodaj lub usuń tagi', 'pixel' ),
				'choose_from_most_used'      => __( 'Wybierz z najczęściej używanych', 'pixel' ),
				'menu_name'                  => __( 'Tagi', 'pixel' ),
			),
			'hierarchical'          => false,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'show_in_rest'          => true,
			'update_count_callback' => '_update_post_term_count',
			'rewrite'               => array(
				'slug'       => 'realizacje-tag',
				'with_front' => false,
			),
		)
	);
}
add_action( 'init', 'pixel_register_pnw_projects_content_model' );
