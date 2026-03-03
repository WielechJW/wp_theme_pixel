( function ( blocks, blockEditor, components, data, element, i18n ) {
	var registerBlockType = blocks.registerBlockType;
	var InspectorControls = blockEditor.InspectorControls;
	var useBlockProps = blockEditor.useBlockProps;
	var PanelBody = components.PanelBody;
	var Placeholder = components.Placeholder;
	var TextControl = components.TextControl;
	var TextareaControl = components.TextareaControl;
	var RangeControl = components.RangeControl;
	var ToggleControl = components.ToggleControl;
	var SelectControl = components.SelectControl;
	var Notice = components.Notice;
	var Spinner = components.Spinner;
	var Fragment = element.Fragment;
	var el = element.createElement;
	var useSelect = data.useSelect;
	var __ = i18n.__;

	registerBlockType( 'pixel/pnw-projects', {
		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var maxItems = Number( attributes.maxItems ) || 8;
			var defaultCategory = Number( attributes.defaultCategory ) || 0;

			var queryResult = useSelect(
				function ( select ) {
					var coreStore = select( 'core' );
					var terms = coreStore.getEntityRecords(
						'taxonomy',
						'pnw_project_category',
						{
							per_page: 100,
							hide_empty: false,
							orderby: 'name',
							order: 'asc'
						}
					);

					var projectQuery = {
						per_page: Math.max( 1, Math.min( maxItems, 24 ) ),
						status: 'publish',
						orderby: 'date',
						order: 'desc'
					};

					if ( defaultCategory > 0 ) {
						projectQuery.pnw_project_category = defaultCategory;
					}

					var projects = coreStore.getEntityRecords( 'postType', 'pnw_project', projectQuery );

					return {
						terms: terms,
						projects: projects
					};
				},
				[ maxItems, defaultCategory ]
			);

			var terms = queryResult.terms;
			var projects = queryResult.projects;
			var isLoading = terms === null || projects === null;

			var categoryOptions = [
				{
					label: __( 'Wszystkie kategorie', 'pixel' ),
					value: '0'
				}
			];

			if ( Array.isArray( terms ) ) {
				terms.forEach( function ( term ) {
					categoryOptions.push(
						{
							label: term.name,
							value: String( term.id )
						}
					);
				} );
			}

			var previewContent;

			if ( isLoading ) {
				previewContent = el( Spinner );
			} else if ( Array.isArray( projects ) && projects.length === 0 ) {
				previewContent = el(
					Notice,
					{
						status: 'info',
						isDismissible: false
					},
					__( 'Brak realizacji do wyświetlenia', 'pixel' )
				);
			} else {
				var listedProjects = projects.slice( 0, 5 ).map(
					function ( project ) {
						return el(
							'li',
							{
								key: project.id
							},
							project.title && project.title.rendered ? project.title.rendered : __( '(Bez tytułu)', 'pixel' )
						);
					}
				);

				previewContent = el(
					Fragment,
					null,
					el(
						'p',
						{
							className: 'pnw-projects-editor__summary'
						},
						__(
							'Blok jest renderowany dynamicznie na froncie. Poniżej podgląd tytułów aktualnie pobranych realizacji:',
							'pixel'
						)
					),
					el(
						'ul',
						{
							className: 'pnw-projects-editor__list'
						},
						listedProjects
					)
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
							title: __( 'Ustawienia sekcji Realizacje', 'pixel' ),
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
							label: __( 'Maksymalna liczba realizacji', 'pixel' ),
							value: maxItems,
							min: 1,
							max: 24,
							onChange: function ( value ) {
								setAttributes( { maxItems: value || 1 } );
							}
						} ),
						el( ToggleControl, {
							label: __( 'Pokaż filtry kategorii', 'pixel' ),
							checked: !! attributes.showFilters,
							onChange: function ( value ) {
								setAttributes( { showFilters: value } );
							}
						} ),
						el( ToggleControl, {
							label: __( 'Pokaż tagi na kafelkach', 'pixel' ),
							checked: !! attributes.showTags,
							onChange: function ( value ) {
								setAttributes( { showTags: value } );
							}
						} ),
						el( SelectControl, {
							label: __( 'Domyślna kategoria', 'pixel' ),
							value: String( defaultCategory ),
							options: categoryOptions,
							onChange: function ( value ) {
								setAttributes( { defaultCategory: parseInt( value, 10 ) || 0 } );
							}
						} )
					)
				),
				el(
					'div',
					useBlockProps( { className: 'pnw-projects-editor' } ),
					el(
						Placeholder,
						{
							icon: 'grid-view',
							label: __( 'PNW Realizacje', 'pixel' ),
							instructions: __( 'Sekcja realizacji pobierana z CPT pnw_project.', 'pixel' )
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
} )(
	window.wp.blocks,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.data,
	window.wp.element,
	window.wp.i18n
);
