( function ( wp ) {
	if ( ! wp || ! wp.blocks || ! wp.blockEditor || ! wp.element ) {
		return;
	}

	const { __ } = wp.i18n;
	const { registerBlockType } = wp.blocks;
	const { InspectorControls, RichText, useBlockProps } = wp.blockEditor;
	const { Button, PanelBody, SelectControl, TextControl, TextareaControl } = wp.components;
	const { Fragment, createElement: el, useEffect } = wp.element;

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

	const ALLOWED_ICONS = ICON_OPTIONS.map( function ( option ) {
		return option.value;
	} );

	const DEFAULT_REASONS = [
		{
			icon: 'dashicons-screenoptions',
			title: 'Szybki start projektu',
			description: 'Pierwszą propozycję techniczną dostajesz sprawnie, bez długiego oczekiwania.',
		},
		{
			icon: 'dashicons-admin-tools',
			title: 'Wsparcie techniczne 1:1',
			description: 'Doradzamy materiał, orientację druku i najlepszy sposób wykonania.',
		},
		{
			icon: 'dashicons-hammer',
			title: 'Precyzja i kontrola jakości',
			description: 'Każdy etap przechodzi kontrolę, aby finalny produkt był powtarzalny i solidny.',
		},
		{
			icon: 'dashicons-admin-customizer',
			title: 'Elastyczność realizacji',
			description: 'Obsługujemy zarówno pojedyncze prototypy, jak i krótkie serie produkcyjne.',
		},
	];

	function normalizeText( value ) {
		return 'string' === typeof value ? value : '';
	}

	function safeIcon( value ) {
		return ALLOWED_ICONS.indexOf( value ) !== -1 ? value : 'dashicons-admin-tools';
	}

	function normalizeReason( reason, fallback ) {
		const source = reason && 'object' === typeof reason ? reason : {};
		const defaults = fallback && 'object' === typeof fallback ? fallback : {
			icon: 'dashicons-admin-tools',
			title: '',
			description: '',
		};
		const hasIcon =
			Object.prototype.hasOwnProperty.call( source, 'icon' ) &&
			'string' === typeof source.icon;
		const hasTitle =
			Object.prototype.hasOwnProperty.call( source, 'title' ) &&
			'string' === typeof source.title;
		const hasDescription =
			Object.prototype.hasOwnProperty.call( source, 'description' ) &&
			'string' === typeof source.description;

		return {
			icon: hasIcon ? safeIcon( source.icon ) : safeIcon( defaults.icon ),
			title: hasTitle ? source.title : defaults.title,
			description: hasDescription ? source.description : defaults.description,
		};
	}

	function createDefaultReasons() {
		return DEFAULT_REASONS.map( function ( reason ) {
			return {
				icon: reason.icon,
				title: reason.title,
				description: reason.description,
			};
		} );
	}

	function createNewReason() {
		return {
			icon: 'dashicons-admin-tools',
			title: __( 'Nowy wyróżnik', 'pnw-hero-3d' ),
			description: __( 'Dodaj krótki opis tej przewagi.', 'pnw-hero-3d' ),
		};
	}

	function getNormalizedReasons( attributes ) {
		if ( Array.isArray( attributes.reasons ) && attributes.reasons.length > 0 ) {
			return attributes.reasons.map( function ( reason ) {
				return normalizeReason( reason, createNewReason() );
			} );
		}

		return createDefaultReasons();
	}

	function areReasonsEqual( first, second ) {
		if ( first.length !== second.length ) {
			return false;
		}

		return first.every( function ( reason, index ) {
			return (
				reason.icon === second[ index ].icon &&
				reason.title === second[ index ].title &&
				reason.description === second[ index ].description
			);
		} );
	}

	function updateAttr( setAttributes, key, value ) {
		const update = {};
		update[ key ] = value;
		setAttributes( update );
	}

	function updateReason( setAttributes, reasons, reasonIndex, key, value ) {
		const nextReasons = reasons.map( function ( reason ) {
			return {
				icon: safeIcon( reason.icon ),
				title: normalizeText( reason.title ),
				description: normalizeText( reason.description ),
			};
		} );

		if ( ! nextReasons[ reasonIndex ] ) {
			return;
		}

		nextReasons[ reasonIndex ][ key ] = 'icon' === key ? safeIcon( value ) : value;
		setAttributes( { reasons: nextReasons } );
	}

	function addReason( setAttributes, reasons ) {
		setAttributes( { reasons: reasons.concat( [ createNewReason() ] ) } );
	}

	function removeReason( setAttributes, reasons, reasonIndex ) {
		if ( reasons.length <= 1 ) {
			return;
		}

		setAttributes(
			{
				reasons: reasons.filter( function ( reason, index ) {
					return index !== reasonIndex;
				} ),
			}
		);
	}

	function renderReasonPanel( reason, reasonIndex, reasons, setAttributes ) {
		return el(
			PanelBody,
			{
				key: 'reason-panel-' + reasonIndex,
				title: __( 'Wyróżnik ', 'pnw-hero-3d' ) + ( reasonIndex + 1 ),
				initialOpen: 0 === reasonIndex,
			},
			[
				el( SelectControl, {
					key: 'icon-' + reasonIndex,
					label: __( 'Ikona', 'pnw-hero-3d' ),
					value: safeIcon( reason.icon ),
					options: ICON_OPTIONS,
					onChange: function ( value ) {
						updateReason( setAttributes, reasons, reasonIndex, 'icon', value );
					},
				} ),
				el( TextControl, {
					key: 'title-' + reasonIndex,
					label: __( 'Tytuł', 'pnw-hero-3d' ),
					value: reason.title || '',
					onChange: function ( value ) {
						updateReason( setAttributes, reasons, reasonIndex, 'title', value );
					},
				} ),
				el( TextareaControl, {
					key: 'description-' + reasonIndex,
					label: __( 'Opis', 'pnw-hero-3d' ),
					value: reason.description || '',
					onChange: function ( value ) {
						updateReason( setAttributes, reasons, reasonIndex, 'description', value );
					},
				} ),
				el( Button, {
					key: 'remove-' + reasonIndex,
					variant: 'secondary',
					isDestructive: true,
					disabled: reasons.length <= 1,
					onClick: function () {
						removeReason( setAttributes, reasons, reasonIndex );
					},
					children: __( 'Usuń wyróżnik', 'pnw-hero-3d' ),
				} ),
			]
		);
	}

	function renderReasonCard( reason, reasonIndex, reasons, setAttributes ) {
		return el(
			'article',
			{ className: 'pnw-why-us__card', key: 'reason-card-' + reasonIndex },
			[
				el(
					'div',
					{ className: 'pnw-why-us__card-head', key: 'head-' + reasonIndex },
					[
						el(
							'span',
							{ className: 'pnw-why-us__index', key: 'index-' + reasonIndex },
							String( reasonIndex + 1 ).padStart( 2, '0' )
						),
						el( 'span', {
							key: 'icon-' + reasonIndex,
							className: 'pnw-why-us__icon dashicons ' + safeIcon( reason.icon ),
							'aria-hidden': true,
						} ),
					]
				),
				el( RichText, {
					key: 'title-rich-' + reasonIndex,
					tagName: 'h3',
					className: 'pnw-why-us__card-title',
					value: reason.title || '',
					allowedFormats: [],
					placeholder: __( 'Tytuł wyróżnika...', 'pnw-hero-3d' ),
					onChange: function ( value ) {
						updateReason( setAttributes, reasons, reasonIndex, 'title', value );
					},
				} ),
				el( RichText, {
					key: 'description-rich-' + reasonIndex,
					tagName: 'p',
					className: 'pnw-why-us__card-description',
					value: reason.description || '',
					allowedFormats: [],
					placeholder: __( 'Opis wyróżnika...', 'pnw-hero-3d' ),
					onChange: function ( value ) {
						updateReason( setAttributes, reasons, reasonIndex, 'description', value );
					},
				} ),
				el(
					'div',
					{ className: 'pnw-why-us__step-actions', key: 'actions-' + reasonIndex },
					el( Button, {
						variant: 'secondary',
						isDestructive: true,
						disabled: reasons.length <= 1,
						onClick: function () {
							removeReason( setAttributes, reasons, reasonIndex );
						},
						children: __( 'Usuń', 'pnw-hero-3d' ),
					} )
				),
			]
		);
	}

	registerBlockType( 'pnw/dlaczego-my', {
		edit: function Edit( props ) {
			const attributes = props.attributes;
			const setAttributes = props.setAttributes;
			const blockProps = useBlockProps( { className: 'pnw-why-us' } );
			const reasons = getNormalizedReasons( attributes );

			useEffect(
				function () {
					const current = Array.isArray( attributes.reasons ) ? attributes.reasons : [];
					const normalizedCurrent = current.map( function ( reason ) {
						return normalizeReason( reason, createNewReason() );
					} );
					const target = current.length > 0 ? normalizedCurrent : reasons;

					if ( ! areReasonsEqual( current, target ) ) {
						setAttributes( { reasons: target } );
					}
				},
				[ attributes.reasons ]
			);

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
									key: 'section',
									title: __( 'Nagłówek sekcji', 'pnw-hero-3d' ),
									initialOpen: true,
								},
								[
									el( TextControl, {
										key: 'eyebrow',
										label: __( 'Nadtytuł', 'pnw-hero-3d' ),
										value: attributes.sectionEyebrow || '',
										onChange: function ( value ) {
											updateAttr( setAttributes, 'sectionEyebrow', value );
										},
									} ),
									el( TextControl, {
										key: 'title',
										label: __( 'Tytuł', 'pnw-hero-3d' ),
										value: attributes.sectionTitle || '',
										onChange: function ( value ) {
											updateAttr( setAttributes, 'sectionTitle', value );
										},
									} ),
									el( TextareaControl, {
										key: 'description',
										label: __( 'Opis', 'pnw-hero-3d' ),
										value: attributes.sectionDescription || '',
										onChange: function ( value ) {
											updateAttr( setAttributes, 'sectionDescription', value );
										},
									} ),
								]
							),
							el(
								PanelBody,
								{
									key: 'reasons-toolbar',
									title: __( 'Wyróżniki', 'pnw-hero-3d' ),
									initialOpen: true,
								},
								el( Button, {
									variant: 'primary',
									onClick: function () {
										addReason( setAttributes, reasons );
									},
									children: __( 'Dodaj wyróżnik', 'pnw-hero-3d' ),
								} )
							),
						].concat(
							reasons.map( function ( reason, index ) {
								return renderReasonPanel( reason, index, reasons, setAttributes );
							} )
						)
					),
					el(
						'section',
						Object.assign( { key: 'preview' }, blockProps ),
						[
							el(
								'div',
								{ className: 'pnw-why-us__intro', key: 'intro' },
								[
									el( RichText, {
										key: 'eyebrow-rich',
										tagName: 'p',
										className: 'pnw-why-us__eyebrow',
										value: attributes.sectionEyebrow || '',
										allowedFormats: [],
										placeholder: __( 'Nadtytuł...', 'pnw-hero-3d' ),
										onChange: function ( value ) {
											updateAttr( setAttributes, 'sectionEyebrow', value );
										},
									} ),
									el( RichText, {
										key: 'title-rich',
										tagName: 'h2',
										className: 'pnw-why-us__title',
										value: attributes.sectionTitle || '',
										allowedFormats: [],
										placeholder: __( 'Tytuł sekcji...', 'pnw-hero-3d' ),
										onChange: function ( value ) {
											updateAttr( setAttributes, 'sectionTitle', value );
										},
									} ),
									el( RichText, {
										key: 'description-rich',
										tagName: 'p',
										className: 'pnw-why-us__description',
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
								{ className: 'pnw-why-us__grid', key: 'grid' },
								reasons.map( function ( reason, index ) {
									return renderReasonCard( reason, index, reasons, setAttributes );
								} )
							),
							el(
								'div',
								{ className: 'pnw-why-us__editor-toolbar', key: 'toolbar' },
								el( Button, {
									variant: 'secondary',
									onClick: function () {
										addReason( setAttributes, reasons );
									},
									children: __( 'Dodaj kolejny wyróżnik', 'pnw-hero-3d' ),
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
