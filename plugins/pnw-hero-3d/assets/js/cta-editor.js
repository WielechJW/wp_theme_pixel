( function ( wp ) {
	if ( ! wp || ! wp.blocks || ! wp.blockEditor || ! wp.element ) {
		return;
	}

	const { __ } = wp.i18n;
	const { registerBlockType } = wp.blocks;
	const { InspectorControls, RichText, useBlockProps } = wp.blockEditor;
	const { PanelBody, TextControl, ToggleControl, SelectControl } = wp.components;
	const { Fragment, createElement: el } = wp.element;

	const SURFACE_OPTIONS = [
		{ label: __( 'Miętowe', 'pnw-hero-3d' ), value: 'mint' },
		{ label: __( 'Kremowe', 'pnw-hero-3d' ), value: 'cream' },
		{ label: __( 'Białe', 'pnw-hero-3d' ), value: 'white' },
	];

	function safeSurface( value ) {
		const allowed = [ 'mint', 'cream', 'white' ];
		return allowed.indexOf( value ) !== -1 ? value : 'mint';
	}

	function updateAttr( setAttributes, key, value ) {
		const update = {};
		update[ key ] = value;
		setAttributes( update );
	}

	registerBlockType( 'pnw/cta-section', {
		edit: function Edit( props ) {
			const attributes = props.attributes;
			const setAttributes = props.setAttributes;
			const surface = safeSurface( attributes.surface );
			const blockProps = useBlockProps( {
				className: 'pnw-cta pnw-cta--' + surface,
			} );

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
									key: 'layout',
									title: __( 'Ustawienia CTA', 'pnw-hero-3d' ),
									initialOpen: true,
								},
								[
									el( SelectControl, {
										key: 'surface',
										label: __( 'Tło sekcji', 'pnw-hero-3d' ),
										value: surface,
										options: SURFACE_OPTIONS,
										onChange: function ( value ) {
											updateAttr( setAttributes, 'surface', safeSurface( value ) );
										},
									} ),
									el( ToggleControl, {
										key: 'show-secondary',
										label: __( 'Pokaż drugi przycisk', 'pnw-hero-3d' ),
										checked: !! attributes.showSecondaryCta,
										onChange: function ( value ) {
											updateAttr( setAttributes, 'showSecondaryCta', value );
										},
									} ),
								]
							),
							el(
								PanelBody,
								{
									key: 'links',
									title: __( 'Linki przycisków', 'pnw-hero-3d' ),
									initialOpen: false,
								},
								[
									el( TextControl, {
										key: 'primary-url',
										label: __( 'URL przycisku głównego', 'pnw-hero-3d' ),
										value: attributes.primaryCtaUrl || '',
										onChange: function ( value ) {
											updateAttr( setAttributes, 'primaryCtaUrl', value );
										},
									} ),
									el( TextControl, {
										key: 'secondary-url',
										label: __( 'URL przycisku dodatkowego', 'pnw-hero-3d' ),
										value: attributes.secondaryCtaUrl || '',
										onChange: function ( value ) {
											updateAttr( setAttributes, 'secondaryCtaUrl', value );
										},
									} ),
								]
							),
						]
					),
					el(
						'section',
						Object.assign( { key: 'section' }, blockProps ),
						el(
							'div',
							{ className: 'pnw-cta__inner' },
							[
								el(
									'div',
									{ className: 'pnw-cta__content', key: 'content' },
									[
										el( RichText, {
											key: 'eyebrow',
											tagName: 'p',
											className: 'pnw-cta__eyebrow',
											value: attributes.sectionEyebrow || '',
											allowedFormats: [],
											placeholder: __( 'Tekst nad tytułem...', 'pnw-hero-3d' ),
											onChange: function ( value ) {
												updateAttr( setAttributes, 'sectionEyebrow', value );
											},
										} ),
										el( RichText, {
											key: 'title',
											tagName: 'h2',
											className: 'pnw-cta__title',
											value: attributes.sectionTitle || '',
											allowedFormats: [],
											placeholder: __( 'Tytuł CTA...', 'pnw-hero-3d' ),
											onChange: function ( value ) {
												updateAttr( setAttributes, 'sectionTitle', value );
											},
										} ),
										el( RichText, {
											key: 'description',
											tagName: 'p',
											className: 'pnw-cta__description',
											value: attributes.sectionDescription || '',
											allowedFormats: [],
											placeholder: __( 'Krótki opis zachęcający do kontaktu...', 'pnw-hero-3d' ),
											onChange: function ( value ) {
												updateAttr( setAttributes, 'sectionDescription', value );
											},
										} ),
									]
								),
								el(
									'div',
									{ className: 'pnw-cta__actions', key: 'actions' },
									[
										el( RichText, {
											key: 'primary-label',
											tagName: 'span',
											className: 'pnw-cta__button pnw-cta__button--primary',
											value: attributes.primaryCtaText || '',
											allowedFormats: [],
											placeholder: __( 'Tekst głównego CTA', 'pnw-hero-3d' ),
											onChange: function ( value ) {
												updateAttr( setAttributes, 'primaryCtaText', value );
											},
										} ),
										attributes.showSecondaryCta
											? el( RichText, {
													key: 'secondary-label',
													tagName: 'span',
													className: 'pnw-cta__button pnw-cta__button--secondary',
													value: attributes.secondaryCtaText || '',
													allowedFormats: [],
													placeholder: __( 'Tekst dodatkowego CTA', 'pnw-hero-3d' ),
													onChange: function ( value ) {
														updateAttr( setAttributes, 'secondaryCtaText', value );
													},
												} )
											: null,
									]
								),
							]
						)
					),
				]
			);
		},
		save: function () {
			return null;
		},
	} );
}( window.wp ) );
