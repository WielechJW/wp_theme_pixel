( function () {
	const loader = document.getElementById( 'pixel-loader' );
	const body = document.body;
	let isHidden = false;

	if ( ! loader || ! body ) {
		return;
	}

	body.classList.add( 'pixel-loading' );

	function hideLoader() {
		if ( isHidden ) {
			return;
		}

		isHidden = true;
		body.classList.remove( 'pixel-loading' );
		body.classList.add( 'pixel-loaded' );

		window.setTimeout( function () {
			if ( loader.parentNode ) {
				loader.parentNode.removeChild( loader );
			}
		}, 520 );
	}

	window.addEventListener(
		'load',
		function () {
			window.setTimeout( hideLoader, 180 );
		},
		{ once: true }
	);

	window.setTimeout( hideLoader, 3800 );
}() );

( function () {
	const header = document.querySelector( '.site-header' );

	if ( ! header ) {
		return;
	}

	const toggle = header.querySelector( '.menu-toggle' );
	const navigation = header.querySelector( '.main-navigation' );
	const overlay = header.querySelector( '.menu-overlay' );

	if ( ! toggle || ! navigation || ! overlay ) {
		return;
	}

	document.documentElement.classList.add( 'js' );

	const mobileQuery = window.matchMedia( '(max-width: 1000px)' );

	function closeMenu() {
		document.body.classList.remove( 'menu-open' );
		toggle.setAttribute( 'aria-expanded', 'false' );
		if ( mobileQuery.matches ) {
			navigation.setAttribute( 'aria-hidden', 'true' );
		}
		overlay.setAttribute( 'hidden', '' );
	}

	function openMenu() {
		document.body.classList.add( 'menu-open' );
		toggle.setAttribute( 'aria-expanded', 'true' );
		navigation.setAttribute( 'aria-hidden', 'false' );
		overlay.removeAttribute( 'hidden' );
	}

	function toggleMenu() {
		if ( document.body.classList.contains( 'menu-open' ) ) {
			closeMenu();
			return;
		}

		openMenu();
	}

	function handleViewportChange( event ) {
		if ( ! event.matches ) {
			closeMenu();
			navigation.removeAttribute( 'aria-hidden' );
			return;
		}

		navigation.setAttribute( 'aria-hidden', 'true' );
	}

	toggle.addEventListener( 'click', toggleMenu );
	overlay.addEventListener( 'click', closeMenu );

	navigation.addEventListener( 'click', function ( event ) {
		if ( mobileQuery.matches && event.target.closest( 'a' ) ) {
			closeMenu();
		}
	} );

	document.addEventListener( 'keydown', function ( event ) {
		if ( event.key === 'Escape' && document.body.classList.contains( 'menu-open' ) ) {
			closeMenu();
		}
	} );

	if ( typeof mobileQuery.addEventListener === 'function' ) {
		mobileQuery.addEventListener( 'change', handleViewportChange );
	} else {
		mobileQuery.addListener( handleViewportChange );
	}

	handleViewportChange( mobileQuery );
	closeMenu();
}() );
