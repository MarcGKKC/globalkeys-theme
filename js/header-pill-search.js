/**
 * Header Pill Search: Lupen-Button öffnet Such-Overlay (Animation von rechts).
 * X-Button rechts neben der Pill in eigenem Bereich (.header-pill-search-close-area).
 *
 * @package globalkeys
 */
( function() {
	function init() {
		var container = document.querySelector( '.header-pill-container' );
		if ( ! container ) {
			return;
		}

		var trigger = container.querySelector( '.header-pill-search-trigger' );
		var overlay = document.getElementById( 'gk-pill-search-overlay' );
		var searchInput = overlay ? overlay.querySelector( '.header-pill-search-input' ) : null;
		var outer = container.parentNode;
		if ( ! outer || ! outer.classList.contains( 'header-pill-search-outer' ) ) {
			return;
		}
		var closeArea = outer.querySelector( '.header-pill-search-close-area' );
		var closeBtn = closeArea ? closeArea.querySelector( '.header-pill-search-close' ) : null;

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
				outer.classList.add( 'is-search-open' );
				if ( closeArea ) {
					closeArea.setAttribute( 'aria-hidden', 'false' );
				}
			} );
			if ( trigger ) {
				trigger.setAttribute( 'aria-expanded', 'true' );
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
			outer.classList.remove( 'is-search-open' );
			if ( closeArea ) {
				closeArea.setAttribute( 'aria-hidden', 'true' );
			}
			if ( trigger ) {
				trigger.setAttribute( 'aria-expanded', 'false' );
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
			if ( closeArea && closeArea.contains( e.target ) ) {
				return;
			}
			if ( ! container.contains( e.target ) ) {
				closeSearch();
			}
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
