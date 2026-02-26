( function ( wp ) {
	if ( ! wp || ! wp.blocks || ! wp.blockEditor || ! wp.element ) {
		return;
	}

	const CONFIG_KEY = 'PNWHero3DConfig';
	const { __ } = wp.i18n;
	const { registerBlockType } = wp.blocks;
	const { InspectorControls, RichText, URLInputButton, useBlockProps } = wp.blockEditor;
	const { PanelBody, TextControl, TextareaControl } = wp.components;
	const { Fragment, createElement: el, useEffect, useRef } = wp.element;

	function getConfig() {
		return window[ CONFIG_KEY ] || {};
	}

	function isWebGLAvailable() {
		try {
			const canvas = document.createElement( 'canvas' );

			return Boolean(
				window.WebGLRenderingContext &&
				( canvas.getContext( 'webgl2' ) || canvas.getContext( 'webgl' ) || canvas.getContext( 'experimental-webgl' ) )
			);
		} catch ( error ) {
			return false;
		}
	}

	function clamp( value, min, max ) {
		return Math.min( Math.max( value, min ), max );
	}

	function showFallback( canvasHost, fallbackNode, fallbackText ) {
		if ( canvasHost ) {
			canvasHost.innerHTML = '';
		}

		if ( fallbackNode ) {
			fallbackNode.hidden = false;
			fallbackNode.textContent = fallbackText;
			return;
		}

		if ( canvasHost ) {
			canvasHost.textContent = fallbackText;
		}
	}

	function disposeMaterial( material ) {
		if ( ! material ) {
			return;
		}

		const mapKeys = [
			'map',
			'aoMap',
			'normalMap',
			'roughnessMap',
			'metalnessMap',
			'alphaMap',
			'emissiveMap',
			'envMap',
		];

		mapKeys.forEach( function ( key ) {
			if ( material[ key ] && typeof material[ key ].dispose === 'function' ) {
				material[ key ].dispose();
			}
		} );

		if ( typeof material.dispose === 'function' ) {
			material.dispose();
		}
	}

	function disposeObjectTree( object3d ) {
		if ( ! object3d ) {
			return;
		}

		object3d.traverse( function ( node ) {
			if ( node.geometry && typeof node.geometry.dispose === 'function' ) {
				node.geometry.dispose();
			}

			if ( Array.isArray( node.material ) ) {
				node.material.forEach( disposeMaterial );
				return;
			}

			disposeMaterial( node.material );
		} );
	}

	async function mountThreeScene( canvasHost, fallbackNode ) {
		let animationFrame = 0;
		let resizeObserver = null;
		let resizeHandler = null;
		let detachInteractions = function () {};
		let destroyed = false;
		let renderer = null;
		let modelPivot = null;
		let camera = null;
		let rotationX = -0.18;
		let rotationY = 0.62;
		let zoomFactor = 0.95;
		let isDragging = false;
		let dragX = 0;
		let dragY = 0;
		let cameraDistance = 3;

		function cleanup() {
			destroyed = true;
			detachInteractions();

			if ( animationFrame ) {
				window.cancelAnimationFrame( animationFrame );
				animationFrame = 0;
			}

			if ( resizeObserver ) {
				resizeObserver.disconnect();
				resizeObserver = null;
			}

			if ( resizeHandler ) {
				window.removeEventListener( 'resize', resizeHandler );
				resizeHandler = null;
			}

			disposeObjectTree( modelPivot );

			if ( renderer ) {
				renderer.dispose();
				if ( typeof renderer.forceContextLoss === 'function' ) {
					renderer.forceContextLoss();
				}

				if ( renderer.domElement && renderer.domElement.parentNode ) {
					renderer.domElement.parentNode.removeChild( renderer.domElement );
				}
			}

			if ( canvasHost ) {
				canvasHost.classList.remove( 'is-dragging' );
				canvasHost.innerHTML = '';
			}
		}

		const config = getConfig();
		const fallbackText = config.fallbackText || __( 'Podglad 3D niedostepny', 'pnw-hero-3d' );

		if ( ! canvasHost || ! isWebGLAvailable() || ! config.modelUrl || ! config.vendorBaseUrl ) {
			showFallback( canvasHost, fallbackNode, fallbackText );
			return cleanup;
		}

		try {
			const vendorBaseUrl = String( config.vendorBaseUrl ).replace( /\/?$/, '/' );
			const threeModuleUrl = vendorBaseUrl + 'three/three.module.js';
			const loaderModuleUrl = vendorBaseUrl + 'three/examples/jsm/loaders/GLTFLoader.js';

			const modules = await Promise.all( [
				import( threeModuleUrl ),
				import( loaderModuleUrl ),
			] );

			if ( destroyed ) {
				return cleanup;
			}

			const THREE = modules[ 0 ];
			const GLTFLoader = modules[ 1 ].GLTFLoader;
			const scene = new THREE.Scene();
			camera = new THREE.PerspectiveCamera( 38, 1, 0.01, 1000 );
			renderer = new THREE.WebGLRenderer( { antialias: true, alpha: true } );

			renderer.setPixelRatio( Math.min( window.devicePixelRatio || 1, 2 ) );
			renderer.outputColorSpace = THREE.SRGBColorSpace;

			canvasHost.innerHTML = '';
			canvasHost.appendChild( renderer.domElement );

			const ambientLight = new THREE.AmbientLight( 0xffffff, 1.25 );
			scene.add( ambientLight );

			const directionalLight = new THREE.DirectionalLight( 0xffffff, 1.6 );
			directionalLight.position.set( 3, 4, 5 );
			scene.add( directionalLight );
			scene.add( directionalLight.target );

			modelPivot = new THREE.Group();
			scene.add( modelPivot );

			const loader = new GLTFLoader();
			const gltf = await new Promise( function ( resolve, reject ) {
				loader.load( config.modelUrl, resolve, undefined, reject );
			} );

			if ( destroyed ) {
				return cleanup;
			}

			const model = gltf.scene || ( gltf.scenes && gltf.scenes[ 0 ] );

			if ( ! model ) {
				throw new Error( 'GLB scene is empty.' );
			}

			modelPivot.add( model );

			const bounds = new THREE.Box3().setFromObject( model );
			const center = bounds.getCenter( new THREE.Vector3() );
			const size = bounds.getSize( new THREE.Vector3() );
			const maxSize = Math.max( size.x, size.y, size.z ) || 1;
			cameraDistance = ( maxSize / 2 ) / Math.tan( ( Math.PI * camera.fov ) / 360 ) * 1.02;

			model.position.sub( center );
			directionalLight.target.position.set( 0, 0, 0 );

			function applyModelRotation() {
				modelPivot.rotation.x = rotationX;
				modelPivot.rotation.y = rotationY;
			}

			function applyCameraPosition() {
				const distance = cameraDistance * zoomFactor;

				camera.position.set( distance * 0.75, distance * 0.44, distance * 0.97 );
				camera.near = Math.max( distance / 120, 0.01 );
				camera.far = distance * 35;
				camera.lookAt( 0, 0, 0 );
				camera.updateProjectionMatrix();
			}

			function resize() {
				if ( ! camera || ! renderer ) {
					return;
				}

				const rect = canvasHost.getBoundingClientRect();
				const width = Math.max( Math.floor( rect.width ), 1 );
				const height = Math.max( Math.floor( rect.height ), 1 );

				renderer.setSize( width, height, false );
				camera.aspect = width / height;
				camera.updateProjectionMatrix();
			}

			function handlePointerDown( event ) {
				isDragging = true;
				dragX = event.clientX;
				dragY = event.clientY;
				canvasHost.classList.add( 'is-dragging' );

				if ( renderer.domElement.setPointerCapture ) {
					renderer.domElement.setPointerCapture( event.pointerId );
				}
			}

			function handlePointerMove( event ) {
				if ( ! isDragging ) {
					return;
				}

				const dx = event.clientX - dragX;
				const dy = event.clientY - dragY;

				dragX = event.clientX;
				dragY = event.clientY;
				rotationY += dx * 0.009;
				rotationX = clamp( rotationX + dy * 0.0064, -0.72, 0.64 );
				applyModelRotation();
			}

			function handlePointerUp( event ) {
				isDragging = false;
				canvasHost.classList.remove( 'is-dragging' );

				if ( renderer.domElement.releasePointerCapture ) {
					try {
						renderer.domElement.releasePointerCapture( event.pointerId );
					} catch ( error ) {
						// no-op
					}
				}
			}

			function handleWheel( event ) {
				event.preventDefault();
				zoomFactor = clamp( zoomFactor + event.deltaY * 0.0012, 0.65, 1.55 );
				applyCameraPosition();
			}

			detachInteractions = function () {
				if ( ! renderer || ! renderer.domElement ) {
					return;
				}

				renderer.domElement.removeEventListener( 'pointerdown', handlePointerDown );
				renderer.domElement.removeEventListener( 'pointermove', handlePointerMove );
				renderer.domElement.removeEventListener( 'pointerup', handlePointerUp );
				renderer.domElement.removeEventListener( 'pointerleave', handlePointerUp );
				renderer.domElement.removeEventListener( 'pointercancel', handlePointerUp );
				renderer.domElement.removeEventListener( 'wheel', handleWheel );
			};

			renderer.domElement.addEventListener( 'pointerdown', handlePointerDown );
			renderer.domElement.addEventListener( 'pointermove', handlePointerMove );
			renderer.domElement.addEventListener( 'pointerup', handlePointerUp );
			renderer.domElement.addEventListener( 'pointerleave', handlePointerUp );
			renderer.domElement.addEventListener( 'pointercancel', handlePointerUp );
			renderer.domElement.addEventListener( 'wheel', handleWheel, { passive: false } );

			if ( fallbackNode ) {
				fallbackNode.hidden = true;
			}

			applyModelRotation();
			applyCameraPosition();
			resize();

			if ( 'ResizeObserver' in window ) {
				resizeObserver = new ResizeObserver( resize );
				resizeObserver.observe( canvasHost );
			} else {
				resizeHandler = resize;
				window.addEventListener( 'resize', resizeHandler );
			}

			function animate() {
				if ( destroyed ) {
					return;
				}

				if ( ! isDragging ) {
					rotationY += 0.0038;
					applyModelRotation();
				}

				renderer.render( scene, camera );
				animationFrame = window.requestAnimationFrame( animate );
			}

			animate();
		} catch ( error ) {
			cleanup();
			showFallback( canvasHost, fallbackNode, fallbackText );
		}

		return cleanup;
	}

	function ThreePreview() {
		const canvasRef = useRef( null );
		const fallbackRef = useRef( null );

		useEffect( function () {
			let isMounted = true;
			let cleanup = function () {};

			mountThreeScene( canvasRef.current, fallbackRef.current ).then( function ( destroy ) {
				if ( ! isMounted ) {
					destroy();
					return;
				}

				cleanup = destroy;
			} );

			return function () {
				isMounted = false;
				cleanup();
			};
		}, [] );

		return el(
			'div',
			{ className: 'pnw-hero-3d__media' },
			[
				el( 'div', { className: 'pnw-hero-3d__canvas', ref: canvasRef, key: 'canvas' } ),
				el(
					'div',
					{ className: 'pnw-hero-3d__fallback', hidden: true, ref: fallbackRef, key: 'fallback' },
					__( 'Podglad 3D niedostepny', 'pnw-hero-3d' )
				),
			]
		);
	}

	registerBlockType( 'pnw/hero-3d', {
		edit: function Edit( props ) {
			const attributes = props.attributes;
			const setAttributes = props.setAttributes;
			const blockProps = useBlockProps( { className: 'pnw-hero-3d' } );

			return el(
				Fragment,
				null,
				[
					el(
						InspectorControls,
						{ key: 'inspector' },
						el(
							PanelBody,
							{ title: __( 'Ustawienia Hero 3D', 'pnw-hero-3d' ), initialOpen: true },
							[
								el( TextControl, {
									key: 'headline',
									label: __( 'Headline', 'pnw-hero-3d' ),
									value: attributes.headline || '',
									onChange: function ( value ) {
										setAttributes( { headline: value } );
									},
								} ),
								el( TextareaControl, {
									key: 'description',
									label: __( 'Description', 'pnw-hero-3d' ),
									value: attributes.description || '',
									onChange: function ( value ) {
										setAttributes( { description: value } );
									},
								} ),
								el( TextControl, {
									key: 'ctaText',
									label: __( 'CTA Text', 'pnw-hero-3d' ),
									value: attributes.ctaText || '',
									onChange: function ( value ) {
										setAttributes( { ctaText: value } );
									},
								} ),
								el( TextControl, {
									key: 'ctaUrl',
									label: __( 'CTA URL', 'pnw-hero-3d' ),
									value: attributes.ctaUrl || '',
									onChange: function ( value ) {
										setAttributes( { ctaUrl: value } );
									},
								} ),
							]
						)
					),
					el(
						'section',
						Object.assign( { key: 'hero' }, blockProps ),
						el(
							'div',
							{ className: 'pnw-hero-3d__grid' },
							[
								el(
									'div',
									{ className: 'pnw-hero-3d__content', key: 'content' },
									[
										el( RichText, {
											key: 'headlineRich',
											tagName: 'h2',
											className: 'pnw-hero-3d__headline',
											value: attributes.headline || '',
											allowedFormats: [],
											placeholder: __( 'Dodaj headline...', 'pnw-hero-3d' ),
											onChange: function ( value ) {
												setAttributes( { headline: value } );
											},
										} ),
										el( RichText, {
											key: 'descriptionRich',
											tagName: 'p',
											className: 'pnw-hero-3d__description',
											value: attributes.description || '',
											allowedFormats: [],
											placeholder: __( 'Dodaj description...', 'pnw-hero-3d' ),
											onChange: function ( value ) {
												setAttributes( { description: value } );
											},
										} ),
										el(
											'div',
											{ className: 'pnw-hero-3d__cta-row', key: 'ctaRow' },
											[
												el( RichText, {
													key: 'ctaTextRich',
													tagName: 'span',
													className: 'pnw-hero-3d__cta-label',
													value: attributes.ctaText || '',
													allowedFormats: [],
													placeholder: __( 'Tekst CTA', 'pnw-hero-3d' ),
													onChange: function ( value ) {
														setAttributes( { ctaText: value } );
													},
												} ),
												el( URLInputButton, {
													key: 'ctaUrlInput',
													url: attributes.ctaUrl || '',
													onChange: function ( url ) {
														setAttributes( { ctaUrl: url || '' } );
													},
												} ),
											]
										),
									]
								),
								el( ThreePreview, { key: 'preview3d' } ),
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
