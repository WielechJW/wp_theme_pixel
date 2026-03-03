( function () {
	'use strict';

	function syncCardHeights( blockRoot ) {
		var cards = Array.prototype.slice.call( blockRoot.querySelectorAll( '.pnw-testimonials__card' ) );
		var maxHeight = 0;

		if ( cards.length === 0 ) {
			return;
		}

		cards.forEach( function ( card ) {
			card.style.minHeight = '';
		} );

		cards.forEach( function ( card ) {
			maxHeight = Math.max( maxHeight, card.offsetHeight );
		} );

		if ( maxHeight > 0 ) {
			cards.forEach( function ( card ) {
				card.style.minHeight = maxHeight + 'px';
			} );
		}
	}

	function initCardHeights( blockRoot ) {
		var scheduleSync = function () {
			window.requestAnimationFrame( function () {
				syncCardHeights( blockRoot );
			} );
		};

		scheduleSync();
		window.addEventListener( 'resize', scheduleSync );
		window.addEventListener( 'load', scheduleSync );
	}

	function initSlider( blockRoot ) {
		var track = blockRoot.querySelector( '[data-slider-track]' );
		var prevButton = blockRoot.querySelector( '[data-slider-prev]' );
		var nextButton = blockRoot.querySelector( '[data-slider-next]' );
		var controls = blockRoot.querySelector( '[data-slider-controls]' );

		if ( ! track || ! prevButton || ! nextButton ) {
			return;
		}

		var getStep = function () {
			var firstItem = track.querySelector( '[data-slide-item]' );
			var styles;
			var gap = 0;

			if ( ! firstItem ) {
				return track.clientWidth;
			}

			styles = window.getComputedStyle( track );
			gap = parseFloat( styles.columnGap || styles.gap || '0' ) || 0;

			return firstItem.getBoundingClientRect().width + gap;
		};

		var updateState = function () {
			var maxScrollLeft = Math.max( 0, track.scrollWidth - track.clientWidth );
			var canScroll = maxScrollLeft > 2;

			if ( controls ) {
				controls.hidden = ! canScroll;
			}

			prevButton.disabled = ! canScroll || track.scrollLeft <= 2;
			nextButton.disabled = ! canScroll || track.scrollLeft >= maxScrollLeft - 2;
		};

		prevButton.addEventListener( 'click', function () {
			track.scrollBy( {
				left: -getStep(),
				behavior: 'smooth'
			} );
		} );

		nextButton.addEventListener( 'click', function () {
			track.scrollBy( {
				left: getStep(),
				behavior: 'smooth'
			} );
		} );

		track.addEventListener( 'scroll', updateState, { passive: true } );
		window.addEventListener( 'resize', updateState );
		updateState();
	}

	function boot() {
		var allBlocks = document.querySelectorAll( '.wp-block-pixel-pnw-testimonials' );
		var sliderBlocks = document.querySelectorAll( '.wp-block-pixel-pnw-testimonials[data-layout="slider"]' );

		allBlocks.forEach( function ( blockRoot ) {
			initCardHeights( blockRoot );
		} );

		sliderBlocks.forEach( function ( blockRoot ) {
			initSlider( blockRoot );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}
} )();
