/**
 * Header Pill Search: Lupen-Button öffnet Such-Overlay (Animation von rechts).
 *
 * @package globalkeys
 */
( function() {
	const container = document.querySelector( '.header-pill-container' );
	if ( ! container ) {
		return;
	}

	const trigger = container.querySelector( '.header-pill-search-trigger' );
	const overlay = document.getElementById( 'gk-pill-search-overlay' );
	const searchInput = overlay ? overlay.querySelector( '.header-pill-search-input' ) : null;
	const closeBtn = container.parentNode ? container.parentNode.querySelector( '.header-pill-search-close' ) : null;

	var closeFallbackTimeout = null;
	var closeTransitionEndHandler = null;
	var ANIMATION_MS = 520;

	function cancelPendingClose() {
		if ( closeFallbackTimeout ) {
			clearTimeout( closeFallbackTimeout );
			closeFallbackTimeout = null;
		}
		if ( overlay && closeTransitionEndHandler ) {
			overlay.removeEventListener( 'transitionend', closeTransitionEndHandler );
			closeTransitionEndHandler = null;
		}
	}

	function openSearch() {
		cancelPendingClose();
		if ( overlay ) {
			overlay.removeAttribute( 'hidden' );
			void overlay.offsetHeight;
		}
		requestAnimationFrame( function() {
			container.classList.add( 'is-search-open' );
		} );
		if ( trigger ) {
			trigger.setAttribute( 'aria-expanded', 'true' );
		}
		if ( closeBtn ) {
			closeBtn.setAttribute( 'aria-hidden', 'false' );
		}
		if ( searchInput ) {
			setTimeout( function() {
				searchInput.focus();
			}, ANIMATION_MS );
		}
	}

	function closeSearch() {
		cancelPendingClose();
		container.classList.remove( 'is-search-open' );
		if ( trigger ) {
			trigger.setAttribute( 'aria-expanded', 'false' );
		}
		if ( closeBtn ) {
			closeBtn.setAttribute( 'aria-hidden', 'true' );
		}
		if ( overlay ) {
			closeFallbackTimeout = setTimeout( function() {
				closeFallbackTimeout = null;
				closeTransitionEndHandler = null;
				overlay.setAttribute( 'hidden', '' );
			}, ANIMATION_MS );
			closeTransitionEndHandler = function onTransitionEnd( e ) {
				if ( e.propertyName !== 'transform' ) {
					return;
				}
				overlay.removeEventListener( 'transitionend', closeTransitionEndHandler );
				closeTransitionEndHandler = null;
				if ( closeFallbackTimeout ) {
					clearTimeout( closeFallbackTimeout );
					closeFallbackTimeout = null;
				}
				overlay.setAttribute( 'hidden', '' );
			};
			overlay.addEventListener( 'transitionend', closeTransitionEndHandler );
		}
	}

	if ( trigger ) {
		trigger.addEventListener( 'click', function( e ) {
			e.preventDefault();
			if ( container.classList.contains( 'is-search-open' ) ) {
				closeSearch();
			} else {
				openSearch();
			}
		} );
	}

	if ( closeBtn ) {
		closeBtn.addEventListener( 'click', closeSearch );
	}

	document.addEventListener( 'keydown', function( e ) {
		if ( e.key === 'Escape' && container.classList.contains( 'is-search-open' ) ) {
			closeSearch();
		}
	} );

	document.addEventListener( 'click', function( e ) {
		if ( ! container.classList.contains( 'is-search-open' ) ) {
			return;
		}
		if ( closeBtn && closeBtn.contains( e.target ) ) {
			return;
		}
		if ( ! container.contains( e.target ) ) {
			closeSearch();
		}
	} );
}() );
