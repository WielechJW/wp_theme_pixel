<?php
/**
 * Theme setup and assets.
 *
 * @package pixel
 */

if ( ! defined( 'PIXEL_VERSION' ) ) {
	define( 'PIXEL_VERSION', wp_get_theme()->get( 'Version' ) );
}

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
