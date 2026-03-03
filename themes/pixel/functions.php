<?php
/**
 * Theme setup and assets.
 *
 * @package pixel
 */

if ( ! defined( 'PIXEL_VERSION' ) ) {
	define( 'PIXEL_VERSION', wp_get_theme()->get( 'Version' ) );
}

require_once get_template_directory() . '/inc/projects-cpt.php';
require_once get_template_directory() . '/inc/testimonials-cpt.php';

function pixel_setup() {
	load_theme_textdomain( 'pixel', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array( 'height' => 120, 'width' => 320, 'flex-height' => true, 'flex-width' => true ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	register_nav_menus(
		array(
			'primary' => esc_html__( 'Primary', 'pixel' ),
		)
	);

	add_theme_support( 'editor-styles' );
	add_editor_style( 'editor-style.css' );
}
add_action( 'after_setup_theme', 'pixel_setup' );

function pixel_assets() {
	$main_style_path = get_template_directory() . '/assets/css/main.css';
	$main_style_uri  = get_template_directory_uri() . '/assets/css/main.css';
	$main_style_ver  = file_exists( $main_style_path ) ? (string) filemtime( $main_style_path ) : PIXEL_VERSION;
	$font_query      = 'family=Inter:wght@400;500&family=Poppins:wght@500;600;700&display=swap';
	$font_url        = 'https://fonts.googleapis.com/css2?' . $font_query;

	wp_enqueue_style( 'pixel-fonts', $font_url, array(), null );
	wp_enqueue_style( 'pixel-style', $main_style_uri, array( 'pixel-fonts' ), $main_style_ver );

	$navigation_script_path = get_template_directory() . '/assets/js/navigation.js';
	$navigation_script_uri  = get_template_directory_uri() . '/assets/js/navigation.js';

	if ( file_exists( $navigation_script_path ) ) {
		wp_enqueue_script(
			'pixel-navigation',
			$navigation_script_uri,
			array(),
			(string) filemtime( $navigation_script_path ),
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'pixel_assets' );

function pixel_editor_assets() {
	$font_query = 'family=Inter:wght@400;500&family=Poppins:wght@500;600;700&display=swap';
	$font_url   = 'https://fonts.googleapis.com/css2?' . $font_query;

	wp_enqueue_style( 'pixel-editor-fonts', $font_url, array(), null );
}
add_action( 'enqueue_block_editor_assets', 'pixel_editor_assets' );

/**
 * Registers block metadata and block assets.
 *
 * @return void
 */
function pixel_register_pnw_projects_block() {
	$block_dir = get_template_directory() . '/blocks/pnw-projects';

	if ( ! file_exists( $block_dir . '/block.json' ) ) {
		return;
	}

	$editor_script_path = $block_dir . '/editor.js';
	$view_script_path   = $block_dir . '/view.js';
	$style_path         = $block_dir . '/style.css';

	wp_register_script(
		'pixel-pnw-projects-editor',
		get_template_directory_uri() . '/blocks/pnw-projects/editor.js',
		array( 'wp-blocks', 'wp-block-editor', 'wp-components', 'wp-data', 'wp-element', 'wp-i18n' ),
		file_exists( $editor_script_path ) ? (string) filemtime( $editor_script_path ) : PIXEL_VERSION,
		true
	);

	wp_register_script(
		'pixel-pnw-projects-view',
		get_template_directory_uri() . '/blocks/pnw-projects/view.js',
		array(),
		file_exists( $view_script_path ) ? (string) filemtime( $view_script_path ) : PIXEL_VERSION,
		true
	);

	wp_register_style(
		'pixel-pnw-projects-style',
		get_template_directory_uri() . '/blocks/pnw-projects/style.css',
		array( 'pixel-style' ),
		file_exists( $style_path ) ? (string) filemtime( $style_path ) : PIXEL_VERSION
	);

	wp_register_style(
		'pixel-pnw-projects-editor-style',
		get_template_directory_uri() . '/blocks/pnw-projects/style.css',
		array( 'wp-edit-blocks' ),
		file_exists( $style_path ) ? (string) filemtime( $style_path ) : PIXEL_VERSION
	);

	register_block_type(
		$block_dir,
		array(
			'editor_script'   => 'pixel-pnw-projects-editor',
			'editor_style'    => 'pixel-pnw-projects-editor-style',
			'render_callback' => 'pixel_render_pnw_projects_block',
		)
	);
}
add_action( 'init', 'pixel_register_pnw_projects_block' );

/**
 * Dynamic rendering callback for PNW Projects block.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content Saved block content (unused).
 * @param WP_Block $block Parsed block object.
 * @return string
 */
function pixel_render_pnw_projects_block( $attributes, $content = '', $block = null ) {
	$defaults = array(
		'sectionTitle'       => 'Nasze realizacje',
		'sectionDescription' => 'Zobacz wybrane projekty, które wykonaliśmy dla klientów indywidualnych i firm.',
		'maxItems'           => 8,
		'showFilters'        => true,
		'showTags'           => true,
		'defaultCategory'    => 0,
	);

	$attributes          = wp_parse_args( (array) $attributes, $defaults );
	$section_title       = sanitize_text_field( $attributes['sectionTitle'] );
	$section_description = sanitize_textarea_field( $attributes['sectionDescription'] );
	$max_items           = absint( $attributes['maxItems'] );
	$max_items           = $max_items > 0 ? min( $max_items, 24 ) : 8;
	$show_filters        = ! empty( $attributes['showFilters'] );
	$show_tags           = ! empty( $attributes['showTags'] );
	$default_category    = absint( $attributes['defaultCategory'] );

	$query_args = array(
		'post_type'           => 'pnw_project',
		'post_status'         => 'publish',
		'posts_per_page'      => $max_items,
		'orderby'             => 'date',
		'order'               => 'DESC',
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
	);

	if ( $default_category > 0 ) {
		$query_args['tax_query'] = array(
			array(
				'taxonomy' => 'pnw_project_category',
				'field'    => 'term_id',
				'terms'    => array( $default_category ),
			),
		);
	}

	$projects_query = new WP_Query( $query_args );

	if ( ! $projects_query->have_posts() ) {
		return '';
	}

	$projects        = array();
	$used_categories = array();

	while ( $projects_query->have_posts() ) {
		$projects_query->the_post();

		$project_id    = get_the_ID();
		$project_title = get_the_title( $project_id );
		$image_url     = get_the_post_thumbnail_url( $project_id, 'large' );
		$raw_content   = get_post_field( 'post_content', $project_id );

		$categories = get_the_terms( $project_id, 'pnw_project_category' );
		$categories = is_wp_error( $categories ) || empty( $categories ) ? array() : $categories;

		$tags = get_the_terms( $project_id, 'pnw_project_tag' );
		$tags = is_wp_error( $tags ) || empty( $tags ) ? array() : $tags;

		$category_ids   = array();
		$category_names = array();

		foreach ( $categories as $category ) {
			$category_ids[]   = (int) $category->term_id;
			$category_names[] = $category->name;

			if ( ! isset( $used_categories[ $category->term_id ] ) ) {
				$used_categories[ $category->term_id ] = $category;
			}
		}

		$tag_names = array();
		foreach ( $tags as $tag ) {
			$tag_names[] = $tag->name;
		}

		$projects[] = array(
			'id'             => $project_id,
			'title'          => $project_title ? $project_title : __( '(Bez tytułu)', 'pixel' ),
			'image'          => $image_url ? $image_url : '',
			'category_ids'   => $category_ids,
			'category_names' => $category_names,
			'tag_names'      => $tag_names,
			'content'        => wp_kses_post( apply_filters( 'the_content', $raw_content ) ),
		);
	}

	wp_reset_postdata();

	if ( empty( $projects ) ) {
		return '';
	}

	uasort(
		$used_categories,
		static function ( $first_term, $second_term ) {
			return strcasecmp( $first_term->name, $second_term->name );
		}
	);

	if ( ! is_admin() ) {
		wp_enqueue_style( 'pixel-pnw-projects-style' );
		wp_enqueue_script( 'pixel-pnw-projects-view' );
	}

	$template_args = array(
		'wrapper_attributes' => get_block_wrapper_attributes( array( 'class' => 'pnw-projects' ) ),
		'section_title'      => $section_title,
		'section_description' => $section_description,
		'show_filters'       => $show_filters,
		'show_tags'          => $show_tags,
		'projects'           => $projects,
		'categories'         => array_values( $used_categories ),
		'instance_id'        => wp_unique_id( 'pnw-projects-modal-' ),
	);

	ob_start();
	get_template_part( 'template-parts/blocks/pnw-projects', null, $template_args );
	return (string) ob_get_clean();
}

/**
 * Registers testimonials block metadata and assets.
 *
 * @return void
 */
function pixel_register_pnw_testimonials_block() {
	$block_dir = get_template_directory() . '/blocks/pnw-testimonials';

	if ( ! file_exists( $block_dir . '/block.json' ) ) {
		return;
	}

	$editor_script_path = $block_dir . '/editor.js';
	$view_script_path   = $block_dir . '/view.js';
	$style_path         = $block_dir . '/style.css';

	wp_register_script(
		'pixel-pnw-testimonials-editor',
		get_template_directory_uri() . '/blocks/pnw-testimonials/editor.js',
		array( 'wp-blocks', 'wp-block-editor', 'wp-components', 'wp-data', 'wp-edit-post', 'wp-element', 'wp-i18n', 'wp-plugins' ),
		file_exists( $editor_script_path ) ? (string) filemtime( $editor_script_path ) : PIXEL_VERSION,
		true
	);

	wp_register_script(
		'pixel-pnw-testimonials-view',
		get_template_directory_uri() . '/blocks/pnw-testimonials/view.js',
		array(),
		file_exists( $view_script_path ) ? (string) filemtime( $view_script_path ) : PIXEL_VERSION,
		true
	);

	wp_register_style(
		'pixel-pnw-testimonials-style',
		get_template_directory_uri() . '/blocks/pnw-testimonials/style.css',
		array( 'pixel-style' ),
		file_exists( $style_path ) ? (string) filemtime( $style_path ) : PIXEL_VERSION
	);

	wp_register_style(
		'pixel-pnw-testimonials-editor-style',
		get_template_directory_uri() . '/blocks/pnw-testimonials/style.css',
		array( 'wp-edit-blocks' ),
		file_exists( $style_path ) ? (string) filemtime( $style_path ) : PIXEL_VERSION
	);

	register_block_type(
		$block_dir,
		array(
			'editor_script'   => 'pixel-pnw-testimonials-editor',
			'editor_style'    => 'pixel-pnw-testimonials-editor-style',
			'render_callback' => 'pixel_render_pnw_testimonials_block',
		)
	);
}
add_action( 'init', 'pixel_register_pnw_testimonials_block' );

/**
 * Dynamic rendering callback for PNW Testimonials block.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content Saved block content (unused).
 * @param WP_Block $block Parsed block object.
 * @return string
 */
function pixel_render_pnw_testimonials_block( $attributes, $content = '', $block = null ) {
	$defaults = array(
		'sectionTitle'       => 'Opinie klientów',
		'sectionDescription' => 'Kilka słów od osób, które nam zaufały.',
		'maxItems'           => 6,
		'layout'             => 'slider',
		'showRating'         => true,
		'showAvatar'         => true,
		'showServiceTag'     => true,
	);

	$attributes          = wp_parse_args( (array) $attributes, $defaults );
	$section_title       = sanitize_text_field( $attributes['sectionTitle'] );
	$section_description = sanitize_textarea_field( $attributes['sectionDescription'] );
	$max_items           = absint( $attributes['maxItems'] );
	$max_items           = $max_items > 0 ? min( $max_items, 24 ) : 6;
	$layout              = in_array( $attributes['layout'], array( 'grid', 'slider' ), true ) ? $attributes['layout'] : 'slider';
	$show_rating         = ! empty( $attributes['showRating'] );
	$show_avatar         = ! empty( $attributes['showAvatar'] );
	$show_service_tag    = ! empty( $attributes['showServiceTag'] );

	$query = new WP_Query(
		array(
			'post_type'           => 'pnw_testimonial',
			'post_status'         => 'publish',
			'posts_per_page'      => $max_items,
			'orderby'             => 'date',
			'order'               => 'DESC',
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
		)
	);

	if ( ! $query->have_posts() ) {
		return '';
	}

	$testimonials = array();
	while ( $query->have_posts() ) {
		$query->the_post();

		$post_id      = get_the_ID();
		$rating_raw   = (int) get_post_meta( $post_id, 'rating', true );
		$rating_value = max( 1, min( 5, $rating_raw > 0 ? $rating_raw : 5 ) );

		$author_name_meta = sanitize_text_field( (string) get_post_meta( $post_id, 'author_name', true ) );
		$author_meta      = sanitize_text_field( (string) get_post_meta( $post_id, 'author_meta', true ) );
		$service_tag      = sanitize_text_field( (string) get_post_meta( $post_id, 'service_tag', true ) );
		$title            = get_the_title( $post_id );
		$author_name      = '' !== $title ? $title : $author_name_meta;
		$author_name      = '' !== $author_name ? $author_name : __( 'Anonimowy klient', 'pixel' );

		$raw_content = get_post_field( 'post_content', $post_id );

		$testimonials[] = array(
			'id'          => $post_id,
			'author'      => $author_name,
			'author_meta' => $author_meta,
			'service_tag' => $service_tag,
			'rating'      => $rating_value,
			'content'     => wp_kses_post( apply_filters( 'the_content', $raw_content ) ),
			'avatar'      => get_the_post_thumbnail_url( $post_id, 'thumbnail' ),
		);
	}
	wp_reset_postdata();

	if ( empty( $testimonials ) ) {
		return '';
	}

	if ( ! is_admin() ) {
		wp_enqueue_style( 'pixel-pnw-testimonials-style' );
		wp_enqueue_script( 'pixel-pnw-testimonials-view' );
	}

	$template_args = array(
		'wrapper_attributes'  => get_block_wrapper_attributes(
			array(
				'class' => 'pnw-testimonials pnw-testimonials--' . $layout,
			)
		),
		'section_title'       => $section_title,
		'section_description' => $section_description,
		'layout'              => $layout,
		'show_rating'         => $show_rating,
		'show_avatar'         => $show_avatar,
		'show_service_tag'    => $show_service_tag,
		'testimonials'        => $testimonials,
		'instance_id'         => wp_unique_id( 'pnw-testimonials-' ),
	);

	ob_start();
	get_template_part( 'template-parts/blocks/pnw-testimonials', null, $template_args );
	return (string) ob_get_clean();
}
