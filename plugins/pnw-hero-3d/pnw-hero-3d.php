<?php
/**
 * Plugin Name:       PNW Hero 3D
 * Description:       Blok Gutenberg "Hero 3D (Three.js)" z podglądem GLB na froncie i w edytorze.
 * Version:           1.0.0
 * Author:            PNW
 * Text Domain:       pnw-hero-3d
 *
 * Instrukcja użycia:
 * 1) Wgraj folder wtyczki do /wp-content/plugins/pnw-hero-3d/
 * 2) Aktywuj wtyczkę w panelu WordPress: Wtyczki -> PNW Hero 3D
 * 3) W edytorze Gutenberg dodaj blok: "Hero 3D (Three.js)"
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns filemtime-based version for cache busting.
 *
 * @param string $relative_path Relative path from plugin root.
 * @return string
 */
function pnw_hero_3d_asset_version( $relative_path ) {
	$file = plugin_dir_path( __FILE__ ) . ltrim( $relative_path, '/' );

	return file_exists( $file ) ? (string) filemtime( $file ) : '1.0.0';
}

/**
 * Inject runtime config into registered script handles.
 *
 * @param WP_Block_Type $block_type Block type object.
 * @param string        $base_url   Plugin base URL.
 */
function pnw_hero_3d_add_runtime_config( $block_type, $base_url ) {
	$config = array(
		'modelUrl'      => $base_url . 'assets/3d/benchy.glb',
		'vendorBaseUrl' => $base_url . 'vendor/',
		'fallbackText'  => __( 'Podgląd 3D niedostępny', 'pnw-hero-3d' ),
	);

	$inline = 'window.PNWHero3DConfig = Object.assign({}, window.PNWHero3DConfig || {}, ' .
		wp_json_encode( $config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) .
		');';

	$handles = array_merge( $block_type->editor_script_handles, $block_type->view_script_handles );
	$handles = array_unique( array_filter( $handles ) );

	foreach ( $handles as $handle ) {
		wp_add_inline_script( $handle, $inline, 'before' );
	}
}

/**
 * Registers scripts/styles and block type.
 */
function pnw_hero_3d_register_block() {
	$base_url = plugin_dir_url( __FILE__ );

	wp_register_style(
		'pnw-hero-3d-style',
		$base_url . 'assets/css/style.css',
		array(),
		pnw_hero_3d_asset_version( 'assets/css/style.css' )
	);

	wp_register_style(
		'pnw-hero-3d-editor-style',
		$base_url . 'assets/css/editor.css',
		array( 'pnw-hero-3d-style' ),
		pnw_hero_3d_asset_version( 'assets/css/editor.css' )
	);

	wp_register_script(
		'pnw-hero-3d-view',
		$base_url . 'assets/js/view.js',
		array(),
		pnw_hero_3d_asset_version( 'assets/js/view.js' ),
		true
	);

	wp_register_script(
		'pnw-hero-3d-editor',
		$base_url . 'assets/js/editor.js',
		array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-components', 'wp-block-editor' ),
		pnw_hero_3d_asset_version( 'assets/js/editor.js' ),
		true
	);

	$block_type = register_block_type(
		__DIR__,
		array(
			'render_callback' => 'pnw_hero_3d_render_block',
		)
	);

	if ( $block_type instanceof WP_Block_Type ) {
		pnw_hero_3d_add_runtime_config( $block_type, $base_url );
	}
}
add_action( 'init', 'pnw_hero_3d_register_block' );

/**
 * Dynamic block renderer.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function pnw_hero_3d_render_block( $attributes ) {
	$defaults = array(
		'headline'         => 'Tworzymy z pasją, warstwa po warstwie.',
		'description'      => 'Projektujemy i drukujemy 3D — prototypy, części i personalizowane produkty.',
		'ctaText'          => 'Zobacz ofertę',
		'ctaUrl'           => '/oferta',
		'ctaSecondaryText' => 'Skontaktuj się',
		'ctaSecondaryUrl'  => '/kontakt',
	);

	$attributes         = wp_parse_args( is_array( $attributes ) ? $attributes : array(), $defaults );
	$headline           = sanitize_text_field( (string) $attributes['headline'] );
	$description        = sanitize_textarea_field( (string) $attributes['description'] );
	$cta_text           = sanitize_text_field( (string) $attributes['ctaText'] );
	$cta_url            = esc_url( (string) $attributes['ctaUrl'] );
	$cta_secondary_text = sanitize_text_field( (string) $attributes['ctaSecondaryText'] );
	$cta_secondary_url  = esc_url( (string) $attributes['ctaSecondaryUrl'] );

	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class' => 'pnw-hero-3d',
		)
	);

	ob_start();
	?>
	<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<div class="pnw-hero-3d__grid">
			<div class="pnw-hero-3d__content">
				<h2 class="pnw-hero-3d__headline"><?php echo esc_html( $headline ); ?></h2>
				<p class="pnw-hero-3d__description"><?php echo esc_html( $description ); ?></p>
				<?php if ( '' !== $cta_text || '' !== $cta_secondary_text ) : ?>
					<div class="pnw-hero-3d__actions">
						<?php if ( '' !== $cta_text ) : ?>
							<a class="pnw-hero-3d__cta-link pnw-hero-3d__cta-link--primary" href="<?php echo esc_url( '' !== $cta_url ? $cta_url : '#' ); ?>">
								<?php echo esc_html( $cta_text ); ?>
							</a>
						<?php endif; ?>
						<?php if ( '' !== $cta_secondary_text ) : ?>
							<a class="pnw-hero-3d__cta-link pnw-hero-3d__cta-link--secondary" href="<?php echo esc_url( '' !== $cta_secondary_url ? $cta_secondary_url : '#' ); ?>">
								<?php echo esc_html( $cta_secondary_text ); ?>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
			<div class="pnw-hero-3d__media" data-pnw-hero-3d-scene>
				<div class="pnw-hero-3d__canvas" role="img" aria-label="<?php esc_attr_e( 'Podgląd modelu 3D Benchy', 'pnw-hero-3d' ); ?>"></div>
				<div class="pnw-hero-3d__fallback" hidden><?php esc_html_e( 'Podgląd 3D niedostępny', 'pnw-hero-3d' ); ?></div>
			</div>
		</div>
	</section>
	<?php

	return (string) ob_get_clean();
}
