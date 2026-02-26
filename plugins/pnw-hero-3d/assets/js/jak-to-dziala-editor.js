( function ( wp ) {
	if ( ! wp || ! wp.blocks || ! wp.blockEditor || ! wp.element ) {
		return;
	}

	const { __ } = wp.i18n;
	const { registerBlockType } = wp.blocks;
	const { InspectorControls, RichText, useBlockProps } = wp.blockEditor;
	const { Button, PanelBody, TextControl, TextareaControl } = wp.components;
	const { Fragment, createElement: el, useEffect } = wp.element;

	const LEGACY_STEP_CONFIG = [ 1, 2, 3, 4, 5, 6 ].map( function ( index ) {
		return {
			index: index,
			titleKey: 'step' + index + 'Title',
			descriptionKey: 'step' + index + 'Description',
		};
	} );

	const DEFAULT_STEPS = [
		{
			title: 'Konsultacja i brief',
			description:
				'Zbieramy wymagania, ustalamy zastosowanie i dobieramy najlepszą technologię.',
		},
		{
			title: 'Projekt lub analiza modelu',
			description: 'Tworzymy model od zera albo weryfikujemy dostarczony plik pod druk.',
		},
		{
			title: 'Prototyp i testy',
			description:
				'Wykonujemy próbny wydruk i nanosimy poprawki, aż efekt będzie zgodny z oczekiwaniami.',
		},
		{
			title: 'Produkcja właściwa',
			description: 'Uruchamiamy finalny druk 3D z kontrolą jakości na każdym etapie.',
		},
		{
			title: 'Wykończenie',
			description: 'W razie potrzeby wykonujemy obróbkę, montaż lub personalizację produktu.',
		},
		{
			title: 'Odbiór lub wysyłka',
			description:
				'Gotowe elementy dostarczamy bezpiecznie pod wskazany adres lub do odbioru osobistego.',
		},
	];

	function createDefaultSteps() {
		return DEFAULT_STEPS.map( function ( step ) {
			return {
				title: step.title,
				description: step.description,
			};
		} );
	}

	function createNewStep() {
		return {
			title: __( 'Nowy krok', 'pnw-hero-3d' ),
			description: __( 'Uzupełnij opis kroku.', 'pnw-hero-3d' ),
		};
	}

	function normalizeText( value ) {
		return 'string' === typeof value ? value : '';
	}

	function normalizeStep( step, fallback ) {
		const source = step && 'object' === typeof step ? step : {};
		const defaults = fallback && 'object' === typeof fallback ? fallback : createNewStep();
		const hasTitle =
			Object.prototype.hasOwnProperty.call( source, 'title' ) &&
			'string' === typeof source.title;
		const hasDescription =
			Object.prototype.hasOwnProperty.call( source, 'description' ) &&
			'string' === typeof source.description;

		return {
			title: hasTitle ? source.title : defaults.title,
			description: hasDescription ? source.description : defaults.description,
		};
	}

	function readLegacySteps( attributes ) {
		const hasLegacyValues = LEGACY_STEP_CONFIG.some( function ( step ) {
			return (
				'string' === typeof attributes[ step.titleKey ] ||
				'string' === typeof attributes[ step.descriptionKey ]
			);
		} );

		if ( ! hasLegacyValues ) {
			return [];
		}

		return LEGACY_STEP_CONFIG.map( function ( step, index ) {
			const fallback = DEFAULT_STEPS[ index ] || createNewStep();

			return {
				title: normalizeText( attributes[ step.titleKey ] ) || fallback.title,
				description: normalizeText( attributes[ step.descriptionKey ] ) || fallback.description,
			};
		} );
	}

	function getNormalizedSteps( attributes ) {
		if ( Array.isArray( attributes.steps ) && attributes.steps.length > 0 ) {
			return attributes.steps.map( function ( step ) {
				return normalizeStep( step, createNewStep() );
			} );
		}

		const legacySteps = readLegacySteps( attributes );
		if ( legacySteps.length > 0 ) {
			return legacySteps;
		}

		return createDefaultSteps();
	}

	function areStepsEqual( first, second ) {
		if ( first.length !== second.length ) {
			return false;
		}

		return first.every( function ( step, index ) {
			return step.title === second[ index ].title && step.description === second[ index ].description;
		} );
	}

	function updateAttr( setAttributes, key, value ) {
		const update = {};
		update[ key ] = value;
		setAttributes( update );
	}

	function updateStepValue( setAttributes, steps, stepIndex, key, value ) {
		const nextSteps = steps.map( function ( step ) {
			return {
				title: normalizeText( step.title ),
				description: normalizeText( step.description ),
			};
		} );

		if ( ! nextSteps[ stepIndex ] ) {
			return;
		}

		nextSteps[ stepIndex ][ key ] = value;
		setAttributes( { steps: nextSteps } );
	}

	function addStep( setAttributes, steps ) {
		setAttributes( { steps: steps.concat( [ createNewStep() ] ) } );
	}

	function removeStep( setAttributes, steps, stepIndex ) {
		if ( steps.length <= 1 ) {
			return;
		}

		setAttributes(
			{
				steps: steps.filter( function ( step, index ) {
					return index !== stepIndex;
				} ),
			}
		);
	}

	function renderStepPanel( step, stepIndex, steps, setAttributes ) {
		return el(
			PanelBody,
			{
				key: 'step-panel-' + stepIndex,
				title: __( 'Krok ', 'pnw-hero-3d' ) + ( stepIndex + 1 ),
				initialOpen: stepIndex === 0,
			},
			[
				el( TextControl, {
					key: 'title-' + stepIndex,
					label: __( 'Tytuł', 'pnw-hero-3d' ),
					value: step.title || '',
					onChange: function ( value ) {
						updateStepValue( setAttributes, steps, stepIndex, 'title', value );
					},
				} ),
				el( TextareaControl, {
					key: 'description-' + stepIndex,
					label: __( 'Opis', 'pnw-hero-3d' ),
					value: step.description || '',
					onChange: function ( value ) {
						updateStepValue( setAttributes, steps, stepIndex, 'description', value );
					},
				} ),
				el( Button, {
					key: 'remove-step-' + stepIndex,
					variant: 'secondary',
					isDestructive: true,
					disabled: steps.length <= 1,
					onClick: function () {
						removeStep( setAttributes, steps, stepIndex );
					},
					children: __( 'Usuń krok', 'pnw-hero-3d' ),
				} ),
			]
		);
	}

	function renderStepItem( step, stepIndex, steps, setAttributes ) {
		const isLeft = 0 === stepIndex % 2;
		const sideClass = isLeft ? 'pnw-process__item--left' : 'pnw-process__item--right';

		return el(
			'article',
			{
				className: 'pnw-process__item ' + sideClass + ' is-visible',
				key: 'step-item-' + stepIndex,
			},
			el(
				'div',
				{ className: 'pnw-process__card' },
				[
					el(
						'span',
						{ className: 'pnw-process__badge', key: 'badge-' + stepIndex },
						__( 'Krok ', 'pnw-hero-3d' ) + ( stepIndex + 1 )
					),
					el( RichText, {
						key: 'title-rich-' + stepIndex,
						tagName: 'h3',
						className: 'pnw-process__card-title',
						value: step.title || '',
						allowedFormats: [],
						placeholder: __( 'Tytuł kroku...', 'pnw-hero-3d' ),
						onChange: function ( value ) {
							updateStepValue( setAttributes, steps, stepIndex, 'title', value );
						},
					} ),
					el( RichText, {
						key: 'description-rich-' + stepIndex,
						tagName: 'p',
						className: 'pnw-process__card-description',
						value: step.description || '',
						allowedFormats: [],
						placeholder: __( 'Opis kroku...', 'pnw-hero-3d' ),
						onChange: function ( value ) {
							updateStepValue( setAttributes, steps, stepIndex, 'description', value );
						},
					} ),
					el(
						'div',
						{
							className: 'pnw-process__step-actions',
							key: 'actions-' + stepIndex,
						},
						el( Button, {
							variant: 'secondary',
							isDestructive: true,
							disabled: steps.length <= 1,
							onClick: function () {
								removeStep( setAttributes, steps, stepIndex );
							},
							children: __( 'Usuń', 'pnw-hero-3d' ),
						} )
					),
				]
			)
		);
	}

	registerBlockType( 'pnw/jak-to-dziala', {
		edit: function Edit( props ) {
			const attributes = props.attributes;
			const setAttributes = props.setAttributes;
			const blockProps = useBlockProps( { className: 'pnw-process' } );
			const steps = getNormalizedSteps( attributes );

			useEffect(
				function () {
					const current = Array.isArray( attributes.steps ) ? attributes.steps : [];
					const normalizedCurrent = current.map( function ( step ) {
						return normalizeStep( step, createNewStep() );
					} );
					const target = current.length > 0 ? normalizedCurrent : steps;

					if ( ! areStepsEqual( current, target ) ) {
						setAttributes( { steps: target } );
					}
				},
				[ attributes.steps ]
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
							el(
								PanelBody,
								{
									key: 'steps-controls',
									title: __( 'Kroki', 'pnw-hero-3d' ),
									initialOpen: true,
								},
								el( Button, {
									variant: 'primary',
									onClick: function () {
										addStep( setAttributes, steps );
									},
									children: __( 'Dodaj krok', 'pnw-hero-3d' ),
								} )
							),
						].concat(
							steps.map( function ( step, index ) {
								return renderStepPanel( step, index, steps, setAttributes );
							} )
						)
					),
					el(
						'section',
						Object.assign( { key: 'section' }, blockProps ),
						[
							el(
								'div',
								{ className: 'pnw-process__intro', key: 'intro-content' },
								[
									el( RichText, {
										key: 'title-rich',
										tagName: 'h2',
										className: 'pnw-process__title',
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
										className: 'pnw-process__description',
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
								{ className: 'pnw-process__timeline', key: 'timeline', 'data-pnw-process': 'editor' },
								steps.map( function ( step, index ) {
									return renderStepItem( step, index, steps, setAttributes );
								} )
							),
							el(
								'div',
								{ className: 'pnw-process__editor-toolbar', key: 'timeline-controls' },
								el( Button, {
									variant: 'secondary',
									onClick: function () {
										addStep( setAttributes, steps );
									},
									children: __( 'Dodaj kolejny krok', 'pnw-hero-3d' ),
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
