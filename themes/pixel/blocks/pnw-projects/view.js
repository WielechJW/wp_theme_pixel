( function () {
	'use strict';

	function splitDataList( value, separator ) {
		if ( ! value ) {
			return [];
		}

		return value
			.split( separator )
			.map( function ( item ) {
				return item.trim();
			} )
			.filter( function ( item ) {
				return item.length > 0;
			} );
	}

	function initFilters( blockRoot ) {
		var filterButtons = Array.prototype.slice.call( blockRoot.querySelectorAll( '.pnw-projects__filter-button' ) );
		var projectItems = Array.prototype.slice.call( blockRoot.querySelectorAll( '.pnw-projects__item' ) );
		var emptyMessage = blockRoot.querySelector( '[data-empty-message]' );

		if ( filterButtons.length === 0 || projectItems.length === 0 ) {
			return;
		}

		var applyFilter = function ( selectedFilter ) {
			var visibleCount = 0;

			projectItems.forEach( function ( item ) {
				var categories = splitDataList( item.getAttribute( 'data-categories' ), ',' );
				var matches = selectedFilter === 'all' || categories.indexOf( selectedFilter ) !== -1;

				item.hidden = ! matches;
				if ( matches ) {
					visibleCount += 1;
				}
			} );

			if ( emptyMessage ) {
				emptyMessage.hidden = visibleCount > 0;
			}
		};

		filterButtons.forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				var selectedFilter = button.getAttribute( 'data-filter' ) || 'all';

				filterButtons.forEach( function (otherButton) {
					otherButton.classList.toggle( 'is-active', otherButton === button );
					otherButton.setAttribute( 'aria-pressed', otherButton === button ? 'true' : 'false' );
				} );

				applyFilter( selectedFilter );
			} );
		} );
	}

	function initModal( blockRoot ) {
		var modal = blockRoot.querySelector( '.pnw-projects__modal' );
		var tileButtons = Array.prototype.slice.call( blockRoot.querySelectorAll( '.pnw-projects__tile' ) );

		if ( ! modal || tileButtons.length === 0 ) {
			return;
		}

		var modalDialog = modal.querySelector( '.pnw-projects__dialog' );
		var closeButtons = Array.prototype.slice.call( modal.querySelectorAll( '[data-modal-close]' ) );
		var modalImageWrap = modal.querySelector( '.pnw-projects__modal-media' );
		var modalImage = modal.querySelector( '.pnw-projects__modal-image' );
		var modalCategory = modal.querySelector( '.pnw-projects__modal-category' );
		var modalTitle = modal.querySelector( '.pnw-projects__modal-title' );
		var modalTags = modal.querySelector( '.pnw-projects__modal-tags' );
		var modalContent = modal.querySelector( '.pnw-projects__modal-content' );
		var activeTrigger = null;

		var closeModal = function () {
			modal.classList.remove( 'is-open' );
			modal.hidden = true;
			document.body.classList.remove( 'pnw-projects-modal-open' );

			if ( activeTrigger ) {
				activeTrigger.focus();
			}

			activeTrigger = null;
		};

		var openModal = function ( trigger ) {
			var projectItem = trigger.closest( '.pnw-projects__item' );
			var sourceNode = projectItem ? projectItem.querySelector( '.pnw-projects__modal-source' ) : null;
			var tags = splitDataList( trigger.getAttribute( 'data-project-tags' ), '|' );
			var imageUrl = trigger.getAttribute( 'data-project-image' ) || '';

			activeTrigger = trigger;

			modalTitle.textContent = trigger.getAttribute( 'data-project-title' ) || '';
			modalCategory.textContent = trigger.getAttribute( 'data-project-category' ) || '';
			modalContent.innerHTML = sourceNode ? sourceNode.innerHTML : '';

			modalTags.innerHTML = '';
			if ( tags.length > 0 ) {
				tags.forEach( function ( tagLabel ) {
					var chip = document.createElement( 'span' );
					chip.className = 'pnw-projects__chip pnw-projects__chip--modal';
					chip.textContent = tagLabel;
					modalTags.appendChild( chip );
				} );
				modalTags.hidden = false;
			} else {
				modalTags.hidden = true;
			}

			if ( imageUrl ) {
				modalImage.src = imageUrl;
				modalImage.alt = modalTitle.textContent;
				modalImageWrap.hidden = false;
			} else {
				modalImage.removeAttribute( 'src' );
				modalImage.alt = '';
				modalImageWrap.hidden = true;
			}

			modal.hidden = false;
			modal.classList.add( 'is-open' );
			document.body.classList.add( 'pnw-projects-modal-open' );
			modalDialog.focus();
		};

		tileButtons.forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				openModal( button );
			} );
		} );

		closeButtons.forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				closeModal();
			} );
		} );

		document.addEventListener( 'keydown', function ( event ) {
			if ( event.key === 'Escape' && ! modal.hidden ) {
				closeModal();
			}
		} );
	}

	function initProjectsBlock( blockRoot ) {
		initFilters( blockRoot );
		initModal( blockRoot );
	}

	function boot() {
		var blocks = document.querySelectorAll( '.wp-block-pixel-pnw-projects' );

		blocks.forEach( function ( blockRoot ) {
			initProjectsBlock( blockRoot );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}
} )();
