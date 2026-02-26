( function () {
	function initTimeline( timeline ) {
		const items = Array.prototype.slice.call(
			timeline.querySelectorAll( '[data-pnw-process-item]' )
		);

		if ( items.length === 0 ) {
			return;
		}

		if ( ! ( 'IntersectionObserver' in window ) ) {
			items.forEach( function ( item ) {
				item.classList.remove( 'is-pending' );
				item.classList.add( 'is-visible' );
			} );
			return;
		}

		const observer = new IntersectionObserver(
			function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( ! entry.isIntersecting ) {
						return;
					}

					entry.target.classList.remove( 'is-pending' );
					entry.target.classList.add( 'is-visible' );
					observer.unobserve( entry.target );
				} );
			},
			{
				threshold: 0.2,
				rootMargin: '0px 0px -8% 0px',
			}
		);

		items.forEach( function ( item, index ) {
			item.classList.add( 'is-pending' );
			item.style.transitionDelay = String( index * 70 ) + 'ms';
			observer.observe( item );
		} );
	}

	function init() {
		const timelines = document.querySelectorAll( '[data-pnw-process]' );
		timelines.forEach( initTimeline );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init, { once: true } );
	} else {
		init();
	}
}() );
