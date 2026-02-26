( function ( wp ) {
	if ( ! wp || ! wp.blocks || ! wp.blockEditor || ! wp.element ) {
		return;
	}

	const { __ } = wp.i18n;
	const { registerBlockType } = wp.blocks;
	const { InspectorControls, RichText, useBlockProps } = wp.blockEditor;
	const { PanelBody, TextControl, TextareaControl, SelectControl } = wp.components;
	const { Fragment, createElement: el } = wp.element;

	const ICON_OPTIONS = [
		{ label: 'Narzędzia', value: 'dashicons-admin-tools' },
		{ label: 'Młotek', value: 'dashicons-hammer' },
		{ label: 'Układ', value: 'dashicons-screenoptions' },
		{ label: 'Obraz', value: 'dashicons-format-image' },
		{ label: 'Konfiguracja', value: 'dashicons-admin-customizer' },
		{ label: 'Portfolio', value: 'dashicons-portfolio' },
		{ label: 'Produkty', value: 'dashicons-products' },
		{ label: 'Ustawienia', value: 'dashicons-admin-generic' },
	];

	const CARD_CONFIG = [
		{ id: 1, titleKey: 'card1Title', descKey: 'card1Description', iconKey: 'card1Icon' },
		{ id: 2, titleKey: 'card2Title', descKey: 'card2Description', iconKey: 'card2Icon' },
		{ id: 3, titleKey: 'card3Title', descKey: 'card3Description', iconKey: 'card3Icon' },
		{ id: 4, titleKey: 'card4Title', descKey: 'card4Description', iconKey: 'card4Icon' },
	];

	const ALLOWED_ICONS = ICON_OPTIONS.map( function ( option ) {
		return option.value;
	} );

	function safeIcon( value ) {
		if ( ALLOWED_ICONS.indexOf( value ) !== -1 ) {
			return value;
		}

		return 'dashicons-admin-tools';
	}

	function updateAttr( setAttributes, key, value ) {
		const update = {};
		update[ key ] = value;
		setAttributes( update );
	}

	function renderCardPanel( card, attributes, setAttributes ) {
		return el(
			PanelBody,
			{
				key: 'card-panel-' + card.id,
				title: __( 'Kafelek ', 'pnw-hero-3d' ) + card.id,
				initialOpen: false,
			},
			[
				el( TextControl, {
					key: 'title-' + card.id,
					label: __( 'Tytuł', 'pnw-hero-3d' ),
					value: attributes[ card.titleKey ] || '',
					onChange: function ( value ) {
						updateAttr( setAttributes, card.titleKey, value );
					},
				} ),
				el( TextareaControl, {
					key: 'description-' + card.id,
					label: __( 'Opis', 'pnw-hero-3d' ),
					value: attributes[ card.descKey ] || '',
					onChange: function ( value ) {
						updateAttr( setAttributes, card.descKey, value );
					},
				} ),
				el( SelectControl, {
					key: 'icon-' + card.id,
					label: __( 'Ikona', 'pnw-hero-3d' ),
					value: safeIcon( attributes[ card.iconKey ] ),
					options: ICON_OPTIONS,
					onChange: function ( value ) {
						updateAttr( setAttributes, card.iconKey, value );
					},
				} ),
			]
		);
	}

	function renderCard( card, attributes, setAttributes ) {
		return el(
			'article',
			{ className: 'pnw-offer-grid__card', key: 'card-' + card.id },
			[
				el( 'span', {
					key: 'icon-' + card.id,
					className: 'pnw-offer-grid__icon dashicons ' + safeIcon( attributes[ card.iconKey ] ),
					'aria-hidden': true,
				} ),
				el( RichText, {
					key: 'title-' + card.id,
					tagName: 'h3',
					className: 'pnw-offer-grid__card-title',
					value: attributes[ card.titleKey ] || '',
					allowedFormats: [],
					placeholder: __( 'Tytuł kafelka...', 'pnw-hero-3d' ),
					onChange: function ( value ) {
						updateAttr( setAttributes, card.titleKey, value );
					},
				} ),
				el( RichText, {
					key: 'description-' + card.id,
					tagName: 'p',
					className: 'pnw-offer-grid__card-description',
					value: attributes[ card.descKey ] || '',
					allowedFormats: [],
					placeholder: __( 'Opis kafelka...', 'pnw-hero-3d' ),
					onChange: function ( value ) {
						updateAttr( setAttributes, card.descKey, value );
					},
				} ),
			]
		);
	}

	registerBlockType( 'pnw/oferta-grid', {
		edit: function Edit( props ) {
			const attributes = props.attributes;
			const setAttributes = props.setAttributes;
			const blockProps = useBlockProps( { className: 'pnw-offer-grid' } );

			return el(
				Fragment,
				null,
				[
					el(
						InspectorControls,
						{ key: 'inspector' },
						[
							el(
								PanelBody,
								{
									key: 'intro',
									title: __( 'Nagłówek sekcji', 'pnw-hero-3d' ),
									initialOpen: true,
								},
								[
									el( TextControl, {
										key: 'section-title',
										label: __( 'Tytuł sekcji', 'pnw-hero-3d' ),
										value: attributes.sectionTitle || '',
										onChange: function ( value ) {
											updateAttr( setAttributes, 'sectionTitle', value );
										},
									} ),
									el( TextareaControl, {
										key: 'section-description',
										label: __( 'Opis sekcji', 'pnw-hero-3d' ),
										value: attributes.sectionDescription || '',
										onChange: function ( value ) {
											updateAttr( setAttributes, 'sectionDescription', value );
										},
									} ),
								]
							),
						].concat(
							CARD_CONFIG.map( function ( card ) {
								return renderCardPanel( card, attributes, setAttributes );
							} )
						)
					),
					el(
						'section',
						Object.assign( { key: 'section' }, blockProps ),
						[
							el(
								'div',
								{ className: 'pnw-offer-grid__intro', key: 'intro' },
								[
									el( RichText, {
										key: 'section-title-rich',
										tagName: 'h2',
										className: 'pnw-offer-grid__title',
										value: attributes.sectionTitle || '',
										allowedFormats: [],
										placeholder: __( 'Tytuł sekcji...', 'pnw-hero-3d' ),
										onChange: function ( value ) {
											updateAttr( setAttributes, 'sectionTitle', value );
										},
									} ),
									el( RichText, {
										key: 'section-description-rich',
										tagName: 'p',
										className: 'pnw-offer-grid__description',
										value: attributes.sectionDescription || '',
										allowedFormats: [],
										placeholder: __( 'Opis sekcji...', 'pnw-hero-3d' ),
										onChange: function ( value ) {
											updateAttr( setAttributes, 'sectionDescription', value );
										},
									} ),
								]
							),
							el(
								'div',
								{ className: 'pnw-offer-grid__cards', key: 'cards' },
								CARD_CONFIG.map( function ( card ) {
									return renderCard( card, attributes, setAttributes );
								} )
							),
						]
					),
				]
			);
		},
		save: function () {
			return null;
		},
	} );
}( window.wp ) );
