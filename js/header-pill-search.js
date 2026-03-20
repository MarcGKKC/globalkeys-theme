/**
 * Header Pill Search: Lupen-Button öffnet Such-Overlay (Animation von rechts).
 * X-Button rechts neben der Pill in eigenem Bereich (.header-pill-search-close-area).
 * Dropdown mit Produktvorschau, Hintergrund-Overlay wie beim Account-Drawer.
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
		var dropdown = document.getElementById( 'gk-search-dropdown' );
		var searchOverlay = null;

		var ANIMATION_MS = 260;

		function openSearch() {
			document.body.classList.add( 'gk-search-open' );
			searchOverlay = document.createElement( 'div' );
			searchOverlay.id = 'gk-search-overlay';
			searchOverlay.className = 'gk-search-overlay';
			searchOverlay.setAttribute( 'aria-hidden', 'true' );
			searchOverlay.addEventListener( 'click', function() {
				closeSearch();
			} );
			document.body.appendChild( searchOverlay );
			if ( overlay ) {
				overlay.setAttribute( 'aria-hidden', 'false' );
				void overlay.offsetHeight;
			}
			container.classList.add( 'is-search-open' );
			outer.classList.add( 'is-search-open' );
			if ( closeArea ) {
				closeArea.setAttribute( 'aria-hidden', 'false' );
			}
			if ( trigger ) {
				trigger.setAttribute( 'aria-expanded', 'true' );
			}
			updateDropdownVisibility();
			if ( searchInput ) {
				setTimeout( function() {
					searchInput.focus( { preventScroll: true } );
				}, ANIMATION_MS );
			}
		}

		function updateDropdownVisibility() {
			if ( ! dropdown || ! searchInput ) return;
			var val = ( searchInput.value || '' ).trim();
			var allLink = document.getElementById( 'gk-search-dropdown-all-link' );
			if ( val.length > 0 ) {
				dropdown.removeAttribute( 'hidden' );
				dropdown.setAttribute( 'aria-hidden', 'false' );
				if ( allLink ) {
					var base = allLink.getAttribute( 'data-base-url' ) || '';
					var sep = base.indexOf( '?' ) >= 0 ? '&' : '?';
					allLink.href = base + sep + 's=' + encodeURIComponent( val );
				}
			} else {
				dropdown.setAttribute( 'hidden', '' );
				dropdown.setAttribute( 'aria-hidden', 'true' );
				if ( allLink ) {
					allLink.href = allLink.getAttribute( 'data-base-url' ) || '#';
				}
			}
		}

		function forceClose() {
			document.body.classList.remove( 'gk-search-open' );
			if ( searchOverlay && searchOverlay.parentNode ) {
				searchOverlay.parentNode.removeChild( searchOverlay );
			}
			searchOverlay = null;
			if ( dropdown ) {
				dropdown.setAttribute( 'hidden', '' );
				dropdown.setAttribute( 'aria-hidden', 'true' );
			}
			container.classList.remove( 'is-search-open' );
			outer.classList.remove( 'is-search-open' );
			if ( closeArea ) {
				closeArea.setAttribute( 'aria-hidden', 'true' );
			}
			if ( trigger ) {
				trigger.setAttribute( 'aria-expanded', 'false' );
			}
			if ( overlay ) {
				overlay.setAttribute( 'aria-hidden', 'true' );
			}
		}

		function closeSearch() {
			try {
				document.body.classList.remove( 'gk-search-open' );
				if ( searchOverlay && searchOverlay.parentNode ) {
					searchOverlay.parentNode.removeChild( searchOverlay );
				}
				searchOverlay = null;
				if ( dropdown ) {
					dropdown.setAttribute( 'hidden', '' );
					dropdown.setAttribute( 'aria-hidden', 'true' );
				}
				container.classList.remove( 'is-search-open' );
				outer.classList.remove( 'is-search-open' );
				if ( closeArea ) {
					closeArea.setAttribute( 'aria-hidden', 'true' );
				}
				if ( trigger ) {
					trigger.setAttribute( 'aria-expanded', 'false' );
				}
				if ( overlay ) {
					overlay.setAttribute( 'aria-hidden', 'true' );
				}
			} catch ( err ) {
				forceClose();
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
			closeBtn.addEventListener( 'click', function( e ) {
				e.preventDefault();
				e.stopPropagation();
				closeSearch();
			} );
		}

		if ( searchInput ) {
			searchInput.addEventListener( 'input', updateDropdownVisibility );
			searchInput.addEventListener( 'keyup', updateDropdownVisibility );
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
			if ( searchOverlay && e.target === searchOverlay ) {
				closeSearch();
				return;
			}
			if ( ! outer.contains( e.target ) ) {
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
