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
	const closeBtn = overlay ? overlay.querySelector( '.header-pill-search-close' ) : null;

	function openSearch() {
		container.classList.add( 'is-search-open' );
		if ( overlay ) {
			overlay.removeAttribute( 'hidden' );
		}
		if ( trigger ) {
			trigger.setAttribute( 'aria-expanded', 'true' );
		}
		if ( searchInput ) {
			searchInput.focus();
		}
	}

	function closeSearch() {
		container.classList.remove( 'is-search-open' );
		if ( overlay ) {
			overlay.setAttribute( 'hidden', '' );
		}
		if ( trigger ) {
			trigger.setAttribute( 'aria-expanded', 'false' );
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
		if ( ! container.contains( e.target ) ) {
			closeSearch();
		}
	} );
}() );
