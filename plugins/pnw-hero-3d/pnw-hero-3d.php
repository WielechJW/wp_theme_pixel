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

	wp_register_script(
		'pnw-oferta-grid-editor',
		$base_url . 'assets/js/oferta-editor.js',
		array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-components', 'wp-block-editor' ),
		pnw_hero_3d_asset_version( 'assets/js/oferta-editor.js' ),
		true
	);

	wp_register_script(
		'pnw-jak-to-dziala-editor',
		$base_url . 'assets/js/jak-to-dziala-editor.js',
		array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-components', 'wp-block-editor' ),
		pnw_hero_3d_asset_version( 'assets/js/jak-to-dziala-editor.js' ),
		true
	);

	wp_register_script(
		'pnw-jak-to-dziala-view',
		$base_url . 'assets/js/jak-to-dziala-view.js',
		array(),
		pnw_hero_3d_asset_version( 'assets/js/jak-to-dziala-view.js' ),
		true
	);

	wp_register_script(
		'pnw-dlaczego-my-editor',
		$base_url . 'assets/js/dlaczego-my-editor.js',
		array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-components', 'wp-block-editor' ),
		pnw_hero_3d_asset_version( 'assets/js/dlaczego-my-editor.js' ),
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

	register_block_type(
		__DIR__ . '/blocks/oferta-grid',
		array(
			'render_callback' => 'pnw_oferta_grid_render_block',
		)
	);

	register_block_type(
		__DIR__ . '/blocks/jak-to-dziala',
		array(
			'render_callback' => 'pnw_jak_to_dziala_render_block',
		)
	);

	register_block_type(
		__DIR__ . '/blocks/dlaczego-my',
		array(
			'render_callback' => 'pnw_dlaczego_my_render_block',
		)
	);
}
add_action( 'init', 'pnw_hero_3d_register_block' );

/**
 * Returns allowed Dashicons classes for the oferta block.
 *
 * @return string[]
 */
function pnw_oferta_allowed_icon_classes() {
	return array(
		'dashicons-admin-tools',
		'dashicons-hammer',
		'dashicons-screenoptions',
		'dashicons-format-image',
		'dashicons-admin-customizer',
		'dashicons-portfolio',
		'dashicons-products',
		'dashicons-admin-generic',
	);
}

/**
 * Sanitizes Dashicons class value.
 *
 * @param string $icon_class Icon class.
 * @return string
 */
function pnw_oferta_sanitize_icon_class( $icon_class ) {
	$allowed = pnw_oferta_allowed_icon_classes();
	$icon    = sanitize_html_class( (string) $icon_class );

	return in_array( $icon, $allowed, true ) ? $icon : 'dashicons-admin-tools';
}

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

/**
 * Dynamic renderer for oferta cards block.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function pnw_oferta_grid_render_block( $attributes ) {
	$defaults = array(
		'sectionTitle'       => 'Co robimy?',
		'sectionDescription' => 'Projektujemy, prototypujemy i produkujemy elementy 3D dopasowane do Twoich potrzeb.',
		'card1Title'         => 'Projekt 3D',
		'card1Description'   => 'Tworzymy modele od zera albo dopracowujemy gotowe pliki pod druk.',
		'card1Icon'          => 'dashicons-admin-tools',
		'card2Title'         => 'Prototypowanie',
		'card2Description'   => 'Szybkie iteracje i testowe wydruki, zanim przejdziesz do finalnej wersji.',
		'card2Icon'          => 'dashicons-hammer',
		'card3Title'         => 'Produkcja seryjna',
		'card3Description'   => 'Powtarzalne serie elementow z kontrola jakosci i terminow.',
		'card3Icon'          => 'dashicons-screenoptions',
		'card4Title'         => 'Personalizacja',
		'card4Description'   => 'Unikalne produkty na zamowienie: od gadzetow po elementy uzytkowe.',
		'card4Icon'          => 'dashicons-format-image',
	);

	$attributes = wp_parse_args( is_array( $attributes ) ? $attributes : array(), $defaults );

	$section_title       = sanitize_text_field( (string) $attributes['sectionTitle'] );
	$section_description = sanitize_textarea_field( (string) $attributes['sectionDescription'] );

	$cards = array(
		array(
			'title'       => sanitize_text_field( (string) $attributes['card1Title'] ),
			'description' => sanitize_textarea_field( (string) $attributes['card1Description'] ),
			'icon'        => pnw_oferta_sanitize_icon_class( (string) $attributes['card1Icon'] ),
		),
		array(
			'title'       => sanitize_text_field( (string) $attributes['card2Title'] ),
			'description' => sanitize_textarea_field( (string) $attributes['card2Description'] ),
			'icon'        => pnw_oferta_sanitize_icon_class( (string) $attributes['card2Icon'] ),
		),
		array(
			'title'       => sanitize_text_field( (string) $attributes['card3Title'] ),
			'description' => sanitize_textarea_field( (string) $attributes['card3Description'] ),
			'icon'        => pnw_oferta_sanitize_icon_class( (string) $attributes['card3Icon'] ),
		),
		array(
			'title'       => sanitize_text_field( (string) $attributes['card4Title'] ),
			'description' => sanitize_textarea_field( (string) $attributes['card4Description'] ),
			'icon'        => pnw_oferta_sanitize_icon_class( (string) $attributes['card4Icon'] ),
		),
	);

	wp_enqueue_style( 'dashicons' );

	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class' => 'pnw-offer-grid',
		)
	);

	ob_start();
	?>
	<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<div class="pnw-offer-grid__intro">
			<h2 class="pnw-offer-grid__title"><?php echo esc_html( $section_title ); ?></h2>
			<p class="pnw-offer-grid__description"><?php echo esc_html( $section_description ); ?></p>
		</div>

		<div class="pnw-offer-grid__cards">
			<?php foreach ( $cards as $card ) : ?>
				<article class="pnw-offer-grid__card">
					<span class="pnw-offer-grid__icon dashicons <?php echo esc_attr( $card['icon'] ); ?>" aria-hidden="true"></span>
					<h3 class="pnw-offer-grid__card-title"><?php echo esc_html( $card['title'] ); ?></h3>
					<p class="pnw-offer-grid__card-description"><?php echo esc_html( $card['description'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</section>
	<?php

	return (string) ob_get_clean();
}

/**
 * Dynamic renderer for "Jak to dziala" timeline block.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function pnw_jak_to_dziala_render_block( $attributes ) {
	$default_steps = array(
		array(
			'title'       => 'Konsultacja i brief',
			'description' => 'Zbieramy wymagania, ustalamy zastosowanie i dobieramy najlepszą technologię.',
		),
		array(
			'title'       => 'Projekt lub analiza modelu',
			'description' => 'Tworzymy model od zera albo weryfikujemy dostarczony plik pod druk.',
		),
		array(
			'title'       => 'Prototyp i testy',
			'description' => 'Wykonujemy próbny wydruk i nanosimy poprawki, aż efekt będzie zgodny z oczekiwaniami.',
		),
		array(
			'title'       => 'Produkcja właściwa',
			'description' => 'Uruchamiamy finalny druk 3D z kontrolą jakości na każdym etapie.',
		),
		array(
			'title'       => 'Wykończenie',
			'description' => 'W razie potrzeby wykonujemy obróbkę, montaż lub personalizację produktu.',
		),
		array(
			'title'       => 'Odbiór lub wysyłka',
			'description' => 'Gotowe elementy dostarczamy bezpiecznie pod wskazany adres lub do odbioru osobistego.',
		),
	);

	$defaults = array(
		'sectionTitle'       => 'Jak to działa?',
		'sectionDescription' => 'Od pomysłu do gotowego elementu 3D - krok po kroku.',
		'steps'              => array(),
	);

	$attributes = wp_parse_args( is_array( $attributes ) ? $attributes : array(), $defaults );

	$section_title       = sanitize_text_field( (string) $attributes['sectionTitle'] );
	$section_description = sanitize_textarea_field( (string) $attributes['sectionDescription'] );

	$raw_steps = isset( $attributes['steps'] ) && is_array( $attributes['steps'] ) ? $attributes['steps'] : array();

	if ( empty( $raw_steps ) ) {
		$legacy_steps_found = false;
		$raw_steps          = array();

		for ( $index = 1; $index <= 6; $index++ ) {
			$title_key       = 'step' . $index . 'Title';
			$description_key = 'step' . $index . 'Description';
			$fallback        = isset( $default_steps[ $index - 1 ] ) ? $default_steps[ $index - 1 ] : array(
				'title'       => '',
				'description' => '',
			);
			$has_title       = array_key_exists( $title_key, $attributes ) && is_string( $attributes[ $title_key ] );
			$has_description = array_key_exists( $description_key, $attributes ) && is_string( $attributes[ $description_key ] );

			if ( $has_title || $has_description ) {
				$legacy_steps_found = true;
			}

			$raw_steps[] = array(
				'title'       => $has_title ? $attributes[ $title_key ] : $fallback['title'],
				'description' => $has_description ? $attributes[ $description_key ] : $fallback['description'],
			);
		}

		if ( ! $legacy_steps_found ) {
			$raw_steps = $default_steps;
		}
	}

	$steps = array();
	foreach ( $raw_steps as $step ) {
		if ( ! is_array( $step ) ) {
			continue;
		}

		$title       = sanitize_text_field( (string) ( $step['title'] ?? '' ) );
		$description = sanitize_textarea_field( (string) ( $step['description'] ?? '' ) );

		if ( '' === $title && '' === $description ) {
			continue;
		}

		$steps[] = array(
			'title'       => $title,
			'description' => $description,
		);
	}

	if ( empty( $steps ) ) {
		foreach ( $default_steps as $default_step ) {
			$steps[] = array(
				'title'       => sanitize_text_field( (string) $default_step['title'] ),
				'description' => sanitize_textarea_field( (string) $default_step['description'] ),
			);
		}
	}

	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class' => 'pnw-process',
		)
	);

	ob_start();
	?>
	<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<div class="pnw-process__intro">
			<h2 class="pnw-process__title"><?php echo esc_html( $section_title ); ?></h2>
			<p class="pnw-process__description"><?php echo esc_html( $section_description ); ?></p>
		</div>

		<div class="pnw-process__timeline" data-pnw-process>
			<?php foreach ( $steps as $index => $step ) : ?>
				<?php $is_left = 0 === $index % 2; ?>
				<article
					class="pnw-process__item <?php echo esc_attr( $is_left ? 'pnw-process__item--left' : 'pnw-process__item--right' ); ?>"
					data-pnw-process-item
					data-step-index="<?php echo esc_attr( (string) ( $index + 1 ) ); ?>"
				>
					<div class="pnw-process__card">
						<span class="pnw-process__badge">
							<?php echo esc_html( sprintf( __( 'Krok %d', 'pnw-hero-3d' ), ( $index + 1 ) ) ); ?>
						</span>
						<h3 class="pnw-process__card-title"><?php echo esc_html( $step['title'] ); ?></h3>
						<p class="pnw-process__card-description"><?php echo esc_html( $step['description'] ); ?></p>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</section>
	<?php

	return (string) ob_get_clean();
}

/**
 * Dynamic renderer for "Dlaczego my" section block.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function pnw_dlaczego_my_render_block( $attributes ) {
	$default_reasons = array(
		array(
			'icon'        => 'dashicons-screenoptions',
			'title'       => 'Szybki start projektu',
			'description' => 'Pierwszą propozycję techniczną dostajesz sprawnie, bez długiego oczekiwania.',
		),
		array(
			'icon'        => 'dashicons-admin-tools',
			'title'       => 'Wsparcie techniczne 1:1',
			'description' => 'Doradzamy materiał, orientację druku i najlepszy sposób wykonania.',
		),
		array(
			'icon'        => 'dashicons-hammer',
			'title'       => 'Precyzja i kontrola jakości',
			'description' => 'Każdy etap przechodzi kontrolę, aby finalny produkt był powtarzalny i solidny.',
		),
		array(
			'icon'        => 'dashicons-admin-customizer',
			'title'       => 'Elastyczność realizacji',
			'description' => 'Obsługujemy zarówno pojedyncze prototypy, jak i krótkie serie produkcyjne.',
		),
	);

	$defaults = array(
		'sectionEyebrow'     => 'Dlaczego my',
		'sectionTitle'       => 'Twój projekt 3D od pomysłu po finalny detal.',
		'sectionDescription' => 'Pracujemy szybko, transparentnie i z naciskiem na jakość, żebyś od początku wiedział czego się spodziewać.',
		'reasons'            => array(),
	);

	$attributes = wp_parse_args( is_array( $attributes ) ? $attributes : array(), $defaults );

	$section_eyebrow     = sanitize_text_field( (string) $attributes['sectionEyebrow'] );
	$section_title       = sanitize_text_field( (string) $attributes['sectionTitle'] );
	$section_description = sanitize_textarea_field( (string) $attributes['sectionDescription'] );

	$raw_reasons = isset( $attributes['reasons'] ) && is_array( $attributes['reasons'] ) ? $attributes['reasons'] : array();
	if ( empty( $raw_reasons ) ) {
		$raw_reasons = $default_reasons;
	}

	$reasons = array();
	foreach ( $raw_reasons as $reason ) {
		if ( ! is_array( $reason ) ) {
			continue;
		}

		$icon        = pnw_oferta_sanitize_icon_class( (string) ( $reason['icon'] ?? '' ) );
		$title       = sanitize_text_field( (string) ( $reason['title'] ?? '' ) );
		$description = sanitize_textarea_field( (string) ( $reason['description'] ?? '' ) );

		if ( '' === $title && '' === $description ) {
			continue;
		}

		$reasons[] = array(
			'icon'        => $icon,
			'title'       => $title,
			'description' => $description,
		);
	}

	if ( empty( $reasons ) ) {
		foreach ( $default_reasons as $default_reason ) {
			$reasons[] = array(
				'icon'        => pnw_oferta_sanitize_icon_class( (string) $default_reason['icon'] ),
				'title'       => sanitize_text_field( (string) $default_reason['title'] ),
				'description' => sanitize_textarea_field( (string) $default_reason['description'] ),
			);
		}
	}

	wp_enqueue_style( 'dashicons' );

	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class' => 'pnw-why-us',
		)
	);

	ob_start();
	?>
	<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<div class="pnw-why-us__intro">
			<?php if ( '' !== $section_eyebrow ) : ?>
				<p class="pnw-why-us__eyebrow"><?php echo esc_html( $section_eyebrow ); ?></p>
			<?php endif; ?>
			<h2 class="pnw-why-us__title"><?php echo esc_html( $section_title ); ?></h2>
			<p class="pnw-why-us__description"><?php echo esc_html( $section_description ); ?></p>
		</div>

		<div class="pnw-why-us__grid">
			<?php foreach ( $reasons as $index => $reason ) : ?>
				<article class="pnw-why-us__card">
					<div class="pnw-why-us__card-head">
						<span class="pnw-why-us__index"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
						<span class="pnw-why-us__icon dashicons <?php echo esc_attr( $reason['icon'] ); ?>" aria-hidden="true"></span>
					</div>
					<h3 class="pnw-why-us__card-title"><?php echo esc_html( $reason['title'] ); ?></h3>
					<p class="pnw-why-us__card-description"><?php echo esc_html( $reason['description'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</section>
	<?php

	return (string) ob_get_clean();
}
