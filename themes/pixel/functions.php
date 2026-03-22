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
 * Handles contact form submissions from the contact page template.
 *
 * @return void
 */
function pixel_handle_contact_form_submission() {
	$redirect_url = wp_get_referer();

	if ( ! $redirect_url ) {
		$contact_page = get_page_by_path( 'kontakt' );
		$redirect_url = $contact_page instanceof WP_Post ? get_permalink( $contact_page ) : home_url( '/kontakt' );
	}

	if ( ! isset( $_POST['pixel_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pixel_contact_nonce'] ) ), 'pixel_contact_form_submit' ) ) {
		wp_safe_redirect( add_query_arg( 'contact_status', 'error', $redirect_url ) );
		exit;
	}

	$honeypot = isset( $_POST['contact_company'] ) ? trim( (string) wp_unslash( $_POST['contact_company'] ) ) : '';

	if ( '' !== $honeypot ) {
		wp_safe_redirect( add_query_arg( 'contact_status', 'success', $redirect_url ) );
		exit;
	}

	$name    = isset( $_POST['contact_name'] ) ? sanitize_text_field( wp_unslash( $_POST['contact_name'] ) ) : '';
	$email   = isset( $_POST['contact_email'] ) ? sanitize_email( wp_unslash( $_POST['contact_email'] ) ) : '';
	$subject = isset( $_POST['contact_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['contact_subject'] ) ) : '';
	$message = isset( $_POST['contact_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['contact_message'] ) ) : '';

	if ( '' === $name || '' === $subject || '' === $message || ! is_email( $email ) ) {
		wp_safe_redirect( add_query_arg( 'contact_status', 'error', $redirect_url ) );
		exit;
	}

	$admin_email = sanitize_email( (string) get_option( 'admin_email' ) );

	if ( '' === $admin_email || ! is_email( $admin_email ) ) {
		wp_safe_redirect( add_query_arg( 'contact_status', 'error', $redirect_url ) );
		exit;
	}

	$mail_subject = sprintf( __( '[%1$s] %2$s', 'pixel' ), wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), $subject );
	$mail_message = implode(
		"\n\n",
		array(
			sprintf( __( 'Imię: %s', 'pixel' ), $name ),
			sprintf( __( 'E-mail: %s', 'pixel' ), $email ),
			__( 'Wiadomość:', 'pixel' ),
			$message,
		)
	);

	$headers = array(
		'Reply-To: ' . $name . ' <' . $email . '>',
	);

	$sent = wp_mail( $admin_email, $mail_subject, $mail_message, $headers );

	wp_safe_redirect( add_query_arg( 'contact_status', $sent ? 'success' : 'error', $redirect_url ) );
	exit;
}
add_action( 'admin_post_nopriv_pixel_contact_form_submit', 'pixel_handle_contact_form_submission' );
add_action( 'admin_post_pixel_contact_form_submit', 'pixel_handle_contact_form_submission' );

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

/**
 * Registers custom block patterns used across marketing pages.
 *
 * @return void
 */
function pixel_register_block_patterns() {
	if ( ! function_exists( 'register_block_pattern' ) ) {
		return;
	}

	if ( function_exists( 'register_block_pattern_category' ) ) {
		register_block_pattern_category(
			'pixel-pages',
			array(
				'label' => __( 'Pixel Pages', 'pixel' ),
			)
		);
	}

	$contact_page = get_page_by_path( 'kontakt' );
	$contact_url  = $contact_page instanceof WP_Post ? get_permalink( $contact_page ) : home_url( '/kontakt' );
	$projects_page = get_page_by_path( 'realizacje' );

	if ( ! $projects_page instanceof WP_Post ) {
		$projects_page = get_page_by_path( 'projekty' );
	}

	$projects_url = $projects_page instanceof WP_Post ? get_permalink( $projects_page ) : home_url( '/#realizacje' );

	$about_pattern_content = sprintf(
		'<!-- wp:group {"tagName":"section","className":"about-hero section-cream","layout":{"type":"constrained"}} -->
<section class="wp-block-group about-hero section-cream"><div class="wp-block-group__inner-container">
<!-- wp:columns {"verticalAlignment":"center","className":"about-hero__columns"} -->
<div class="wp-block-columns are-vertically-aligned-center about-hero__columns"><!-- wp:column {"verticalAlignment":"center","width":"60%%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:60%%"><!-- wp:paragraph {"className":"about-eyebrow"} -->
<p class="about-eyebrow">O nas</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1,"className":"about-hero__title"} -->
<h1 class="wp-block-heading about-hero__title">Projektujemy i realizujemy wydruki 3D, które mają działać, wyglądać i ułatwiać pracę.</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"about-hero__lead"} -->
<p class="about-hero__lead">Łączymy precyzję wykonania z praktycznym podejściem do projektu. Od pierwszej rozmowy skupiamy się na tym, jak element będzie używany, jakie ma ograniczenia i co musi wytrzymać.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"className":"about-actions"} -->
<div class="wp-block-buttons about-actions"><!-- wp:button {"className":"is-style-cta"} -->
<div class="wp-block-button is-style-cta"><a class="wp-block-button__link wp-element-button" href="%1$s">Porozmawiajmy o projekcie</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-secondary"} -->
<div class="wp-block-button is-style-secondary"><a class="wp-block-button__link wp-element-button" href="%2$s">Zobacz realizacje</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"40%%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:40%%"><!-- wp:group {"className":"about-highlight-card card","layout":{"type":"constrained"}} -->
<div class="wp-block-group about-highlight-card card"><div class="wp-block-group__inner-container"><!-- wp:paragraph {"className":"about-highlight-card__label"} -->
<p class="about-highlight-card__label">Jak pracujemy</p>
<!-- /wp:paragraph -->

<!-- wp:list {"className":"about-feature-list"} -->
<ul class="about-feature-list"><li>dobieramy technologię do zastosowania, nie odwrotnie</li><li>upraszczamy proces od pomysłu do gotowego modelu</li><li>dbamy o estetykę, trwałość i powtarzalność wykonania</li></ul>
<!-- /wp:list --></div></div>
<!-- /wp:group -->

<!-- wp:columns {"className":"about-metrics"} -->
<div class="wp-block-columns about-metrics"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"about-metric card","layout":{"type":"constrained"}} -->
<div class="wp-block-group about-metric card"><div class="wp-block-group__inner-container"><!-- wp:paragraph {"className":"about-metric__value"} -->
<p class="about-metric__value">100%%</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"about-metric__label"} -->
<p class="about-metric__label">skupienia na funkcji projektu</p>
<!-- /wp:paragraph --></div></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"about-metric card","layout":{"type":"constrained"}} -->
<div class="wp-block-group about-metric card"><div class="wp-block-group__inner-container"><!-- wp:paragraph {"className":"about-metric__value"} -->
<p class="about-metric__value">3D</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"about-metric__label"} -->
<p class="about-metric__label">druk, prototypy i personalizacja</p>
<!-- /wp:paragraph --></div></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"about-story section-white","layout":{"type":"constrained"}} -->
<section class="wp-block-group about-story section-white"><div class="wp-block-group__inner-container">
<!-- wp:columns {"className":"about-story__columns"} -->
<div class="wp-block-columns about-story__columns"><!-- wp:column {"width":"38%%"} -->
<div class="wp-block-column" style="flex-basis:38%%"><!-- wp:paragraph {"className":"about-eyebrow"} -->
<p class="about-eyebrow">Kim jesteśmy</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"className":"about-section-title"} -->
<h2 class="wp-block-heading about-section-title">Budujemy współpracę wokół dobrego procesu i czytelnej komunikacji.</h2>
<!-- /wp:heading --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"62%%"} -->
<div class="wp-block-column" style="flex-basis:62%%"><!-- wp:paragraph -->
<p>Pracujemy zarówno z klientami, którzy mają gotowy model i wiedzą dokładnie, czego potrzebują, jak i z osobami, które dopiero szukają najlepszego rozwiązania. W obu przypadkach naszym zadaniem jest przełożyć pomysł na realny, dobrze wykonany element.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Nie traktujemy projektu jak samego wydruku. Zaczynamy od zrozumienia potrzeb, dobieramy parametry wykonania, a potem pilnujemy jakości na etapie przygotowania, produkcji i wykończenia. Dzięki temu finalny efekt ma sens nie tylko na ekranie, ale też w codziennym użyciu.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"about-values section-mint","layout":{"type":"constrained"}} -->
<section class="wp-block-group about-values section-mint"><div class="wp-block-group__inner-container">
<!-- wp:paragraph {"className":"about-eyebrow"} -->
<p class="about-eyebrow">Co jest dla nas ważne</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"className":"about-section-title"} -->
<h2 class="wp-block-heading about-section-title">Kilka zasad, które prowadzą każdy projekt.</h2>
<!-- /wp:heading -->

<!-- wp:columns {"className":"about-cards"} -->
<div class="wp-block-columns about-cards"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"about-card card","layout":{"type":"constrained"}} -->
<div class="wp-block-group about-card card"><div class="wp-block-group__inner-container"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Precyzja</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Każdy detal ma znaczenie, dlatego pilnujemy ustawień, materiału i jakości wykończenia.</p>
<!-- /wp:paragraph --></div></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"about-card card","layout":{"type":"constrained"}} -->
<div class="wp-block-group about-card card"><div class="wp-block-group__inner-container"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Praktyczność</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Projekt ma działać w konkretnych warunkach, więc myślimy o użyciu, obciążeniu i montażu.</p>
<!-- /wp:paragraph --></div></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"about-card card","layout":{"type":"constrained"}} -->
<div class="wp-block-group about-card card"><div class="wp-block-group__inner-container"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Partnerstwo</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Mówimy jasno, co warto zrobić, co można uprościć i jak dojść do najlepszego efektu.</p>
<!-- /wp:paragraph --></div></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"about-process section-white","layout":{"type":"constrained"}} -->
<section class="wp-block-group about-process section-white"><div class="wp-block-group__inner-container">
<!-- wp:paragraph {"className":"about-eyebrow"} -->
<p class="about-eyebrow">Jak wygląda współpraca</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"className":"about-section-title"} -->
<h2 class="wp-block-heading about-section-title">Prosty proces, który pozwala szybciej dojść do dobrego efektu.</h2>
<!-- /wp:heading -->

<!-- wp:columns {"className":"about-steps"} -->
<div class="wp-block-columns about-steps"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"about-step card","layout":{"type":"constrained"}} -->
<div class="wp-block-group about-step card"><div class="wp-block-group__inner-container"><!-- wp:paragraph {"className":"about-step__index"} -->
<p class="about-step__index">01</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Rozpoznanie potrzeb</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Ustalamy zastosowanie, oczekiwania i ograniczenia projektu.</p>
<!-- /wp:paragraph --></div></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"about-step card","layout":{"type":"constrained"}} -->
<div class="wp-block-group about-step card"><div class="wp-block-group__inner-container"><!-- wp:paragraph {"className":"about-step__index"} -->
<p class="about-step__index">02</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Dobór rozwiązania</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Wybieramy materiał, technologię i sposób przygotowania modelu.</p>
<!-- /wp:paragraph --></div></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"about-step card","layout":{"type":"constrained"}} -->
<div class="wp-block-group about-step card"><div class="wp-block-group__inner-container"><!-- wp:paragraph {"className":"about-step__index"} -->
<p class="about-step__index">03</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Produkcja i kontrola</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Realizujemy wydruk i sprawdzamy, czy końcowy element spełnia założenia.</p>
<!-- /wp:paragraph --></div></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"about-step card","layout":{"type":"constrained"}} -->
<div class="wp-block-group about-step card"><div class="wp-block-group__inner-container"><!-- wp:paragraph {"className":"about-step__index"} -->
<p class="about-step__index">04</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Oddanie gotowego projektu</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Dostarczamy dopracowany element lub serię części gotowych do użycia.</p>
<!-- /wp:paragraph --></div></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div></section>
<!-- /wp:group -->

<!-- wp:pixel/pnw-testimonials {"sectionTitle":"Co mówią o współpracy","sectionDescription":"Kilka opinii od klientów, którzy przeszli z nami cały proces od pomysłu do realizacji.","layout":"grid","maxItems":3} /-->

<!-- wp:group {"tagName":"section","className":"about-cta section-cream","layout":{"type":"constrained"}} -->
<section class="wp-block-group about-cta section-cream"><div class="wp-block-group__inner-container">
<!-- wp:paragraph {"className":"about-eyebrow"} -->
<p class="about-eyebrow">Porozmawiajmy</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"className":"about-section-title"} -->
<h2 class="wp-block-heading about-section-title">Masz pomysł, model albo tylko punkt wyjścia? Zobaczymy, jak przełożyć go na gotowy projekt.</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"about-cta__text"} -->
<p class="about-cta__text">Napisz, czego potrzebujesz. Ustalimy zakres, podpowiemy najlepsze rozwiązanie i wrócimy z konkretną propozycją działania.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"className":"about-actions"} -->
<div class="wp-block-buttons about-actions"><!-- wp:button {"className":"is-style-cta"} -->
<div class="wp-block-button is-style-cta"><a class="wp-block-button__link wp-element-button" href="%1$s">Przejdź do kontaktu</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div></section>
<!-- /wp:group -->',
		esc_url( $contact_url ),
		esc_url( $projects_url )
	);

	register_block_pattern(
		'pixel/about-page',
		array(
			'title'         => __( 'O nas', 'pixel' ),
			'description'   => __( 'Gotowy układ strony O nas spójny z estetyką motywu Pixel.', 'pixel' ),
			'categories'    => array( 'pixel-pages' ),
			'viewportWidth' => 1440,
			'content'       => $about_pattern_content,
		)
	);

	$projects_pattern_content = '<!-- wp:group {"tagName":"section","className":"projects-hero section-cream","layout":{"type":"constrained"}} -->
<section class="wp-block-group projects-hero section-cream"><div class="wp-block-group__inner-container">
<!-- wp:paragraph {"className":"projects-eyebrow"} -->
<p class="projects-eyebrow">Realizacje</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1,"className":"projects-hero__title"} -->
<h1 class="wp-block-heading projects-hero__title">Zobacz nasze realizacje.</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"projects-hero__lead"} -->
<p class="projects-hero__lead">Wybrane projekty, które pokazują, jak pracujemy od pomysłu do gotowego efektu.</p>
<!-- /wp:paragraph --></div></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"projects-showcase section-white","layout":{"type":"constrained"}} -->
<section class="wp-block-group projects-showcase section-white"><div class="wp-block-group__inner-container">
<!-- wp:pixel/pnw-projects {"sectionTitle":"","sectionDescription":"","maxItems":16,"showFilters":false,"showTags":false} /--></div></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"projects-cta section-cream","layout":{"type":"constrained"}} -->
<section class="wp-block-group projects-cta section-cream"><div class="wp-block-group__inner-container">
<!-- wp:heading {"className":"projects-section-title"} -->
<h2 class="wp-block-heading projects-section-title">Masz podobny projekt?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"projects-cta__text"} -->
<p class="projects-cta__text">Napisz do nas i zobaczmy, jak możemy przełożyć go na gotową realizację.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"className":"projects-actions"} -->
<div class="wp-block-buttons projects-actions"><!-- wp:button {"className":"is-style-cta"} -->
<div class="wp-block-button is-style-cta"><a class="wp-block-button__link wp-element-button" href="%1$s">Przejdź do kontaktu</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div></section>
<!-- /wp:group -->';

	register_block_pattern(
		'pixel/projects-page',
		array(
			'title'         => __( 'Realizacje', 'pixel' ),
			'description'   => __( 'Gotowy układ strony realizacji oparty o projekty i filtry z motywu Pixel.', 'pixel' ),
			'categories'    => array( 'pixel-pages' ),
			'viewportWidth' => 1440,
			'content'       => $projects_pattern_content,
		)
	);
}
add_action( 'init', 'pixel_register_block_patterns' );
