( function ( wp ) {
	var registerBlockType = wp.blocks.registerBlockType;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var PanelBody = wp.components.PanelBody;
	var Placeholder = wp.components.Placeholder;
	var TextControl = wp.components.TextControl;
	var TextareaControl = wp.components.TextareaControl;
	var RangeControl = wp.components.RangeControl;
	var ToggleControl = wp.components.ToggleControl;
	var SelectControl = wp.components.SelectControl;
	var Notice = wp.components.Notice;
	var Spinner = wp.components.Spinner;
	var Fragment = wp.element.Fragment;
	var el = wp.element.createElement;
	var useSelect = wp.data.useSelect;
	var useDispatch = wp.data.useDispatch;
	var __ = wp.i18n.__;

	registerBlockType( 'pixel/pnw-testimonials', {
		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var maxItems = Number( attributes.maxItems ) || 6;

			var testimonials = useSelect(
				function ( select ) {
					return select( 'core' ).getEntityRecords( 'postType', 'pnw_testimonial', {
						per_page: Math.max( 1, Math.min( maxItems, 24 ) ),
						status: 'publish',
						orderby: 'date',
						order: 'desc'
					} );
				},
				[ maxItems ]
			);

			var previewContent;

			if ( testimonials === null ) {
				previewContent = el( Spinner );
			} else if ( Array.isArray( testimonials ) && testimonials.length === 0 ) {
				previewContent = el(
					Notice,
					{
						status: 'info',
						isDismissible: false
					},
					__( 'Brak opinii do wyświetlenia', 'pixel' )
				);
			} else {
				previewContent = el(
					'ul',
					{ className: 'pnw-testimonials-editor__list' },
					testimonials.slice( 0, 5 ).map( function ( item ) {
						return el(
							'li',
							{ key: item.id },
							item.title && item.title.rendered ? item.title.rendered : __( '(Bez tytułu)', 'pixel' )
						);
					} )
				);
			}

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{
							title: __( 'Ustawienia sekcji Opinie', 'pixel' ),
							initialOpen: true
						},
						el( TextControl, {
							label: __( 'Tytuł sekcji', 'pixel' ),
							value: attributes.sectionTitle || '',
							onChange: function ( value ) {
								setAttributes( { sectionTitle: value } );
							}
						} ),
						el( TextareaControl, {
							label: __( 'Opis sekcji', 'pixel' ),
							value: attributes.sectionDescription || '',
							onChange: function ( value ) {
								setAttributes( { sectionDescription: value } );
							}
						} ),
						el( RangeControl, {
							label: __( 'Maksymalna liczba opinii', 'pixel' ),
							value: maxItems,
							min: 1,
							max: 24,
							onChange: function ( value ) {
								setAttributes( { maxItems: value || 1 } );
							}
						} ),
						el( SelectControl, {
							label: __( 'Układ sekcji', 'pixel' ),
							value: attributes.layout || 'slider',
							options: [
								{ label: __( 'Slider', 'pixel' ), value: 'slider' },
								{ label: __( 'Grid', 'pixel' ), value: 'grid' }
							],
							onChange: function ( value ) {
								setAttributes( { layout: value } );
							}
						} ),
						el( ToggleControl, {
							label: __( 'Pokaż ocenę (gwiazdki)', 'pixel' ),
							checked: !! attributes.showRating,
							onChange: function ( value ) {
								setAttributes( { showRating: value } );
							}
						} ),
						el( ToggleControl, {
							label: __( 'Pokaż avatar/logo', 'pixel' ),
							checked: !! attributes.showAvatar,
							onChange: function ( value ) {
								setAttributes( { showAvatar: value } );
							}
						} ),
						el( ToggleControl, {
							label: __( 'Pokaż tag usługi', 'pixel' ),
							checked: !! attributes.showServiceTag,
							onChange: function ( value ) {
								setAttributes( { showServiceTag: value } );
							}
						} )
					)
				),
				el(
					'div',
					useBlockProps( { className: 'pnw-testimonials-editor' } ),
					el(
						Placeholder,
						{
							icon: 'format-quote',
							label: __( 'PNW Opinie', 'pixel' ),
							instructions: __( 'Sekcja opinii z CPT pnw_testimonial.', 'pixel' )
						},
						previewContent
					)
				)
			);
		},
		save: function () {
			return null;
		}
	} );

	if ( wp.editPost && wp.plugins ) {
		var PluginDocumentSettingPanel = wp.editPost.PluginDocumentSettingPanel;
		var registerPlugin = wp.plugins.registerPlugin;

		var TestimonialMetaPanel = function () {
			var postType = useSelect( function ( select ) {
				return select( 'core/editor' ).getCurrentPostType();
			} );

			var meta = useSelect( function ( select ) {
				return select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
			} );

			var editPost = useDispatch( 'core/editor' ).editPost;

			if ( 'pnw_testimonial' !== postType ) {
				return null;
			}

			var rating = parseInt( meta.rating, 10 );
			if ( Number.isNaN( rating ) || rating < 1 || rating > 5 ) {
				rating = 5;
			}

			var setMetaValue = function ( key, value ) {
				editPost( {
					meta: Object.assign( {}, meta, ( function () {
						var update = {};
						update[ key ] = value;
						return update;
					} )() )
				} );
			};

			return el(
				PluginDocumentSettingPanel,
				{
					name: 'pixel-pnw-testimonial-meta-panel',
					title: __( 'Dane opinii (PNW)', 'pixel' ),
					className: 'pixel-pnw-testimonial-meta-panel'
				},
				el( SelectControl, {
					label: __( 'Ocena (1-5)', 'pixel' ),
					value: String( rating ),
					options: [
						{ label: '1', value: '1' },
						{ label: '2', value: '2' },
						{ label: '3', value: '3' },
						{ label: '4', value: '4' },
						{ label: '5', value: '5' }
					],
					onChange: function ( value ) {
						setMetaValue( 'rating', parseInt( value, 10 ) || 5 );
					}
				} ),
				el( TextControl, {
					label: __( 'author_name (opcjonalnie)', 'pixel' ),
					help: __( 'Używany jako fallback, gdy tytuł jest pusty.', 'pixel' ),
					value: meta.author_name || '',
					onChange: function ( value ) {
						setMetaValue( 'author_name', value );
					}
				} ),
				el( TextControl, {
					label: __( 'author_meta', 'pixel' ),
					help: __( 'Np. Wrocław, Firma XYZ.', 'pixel' ),
					value: meta.author_meta || '',
					onChange: function ( value ) {
						setMetaValue( 'author_meta', value );
					}
				} ),
				el( TextControl, {
					label: __( 'service_tag', 'pixel' ),
					help: __( 'Np. Prototypy, Części, Personalizacja.', 'pixel' ),
					value: meta.service_tag || '',
					onChange: function ( value ) {
						setMetaValue( 'service_tag', value );
					}
				} )
			);
		};

		registerPlugin( 'pixel-pnw-testimonial-meta-sidebar', {
			render: TestimonialMetaPanel,
			icon: 'star-filled'
		} );
	}
} )( window.wp );
