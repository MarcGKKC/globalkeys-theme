/**
 * Account-Drawer (Original): Drawer ist fest im Header.
 * Klick auf Avatar öffnet/schließt, Klick außerhalb oder Escape schließt.
 */
(function() {
	'use strict';

	function init() {
		var wrap = document.getElementById( 'gk-account-drawer-wrap' );
		var trigger = document.getElementById( 'gk-account-drawer-trigger' );
		var drawer = document.getElementById( 'gk-account-drawer' );

		if ( ! wrap || ! trigger || ! drawer ) return;

		var overlay = null;

		function open() {
			drawer.removeAttribute( 'hidden' );
			drawer.setAttribute( 'aria-hidden', 'false' );
			trigger.setAttribute( 'aria-expanded', 'true' );
			document.body.classList.add( 'gk-drawer-open' );
			wrap.classList.add( 'gk-drawer-open' );
			overlay = document.createElement( 'div' );
			overlay.id = 'gk-drawer-overlay';
			overlay.className = 'gk-drawer-overlay';
			overlay.setAttribute( 'aria-hidden', 'true' );
			document.body.appendChild( overlay );
			document.body.addEventListener( 'click', onClickOutside );
			document.addEventListener( 'keydown', onKeydown );
		}

		function close() {
			drawer.setAttribute( 'hidden', '' );
			drawer.setAttribute( 'aria-hidden', 'true' );
			trigger.setAttribute( 'aria-expanded', 'false' );
			document.body.classList.remove( 'gk-drawer-open' );
			wrap.classList.remove( 'gk-drawer-open' );
			if ( overlay && overlay.parentNode ) {
				overlay.parentNode.removeChild( overlay );
			}
			overlay = null;
			document.body.removeEventListener( 'click', onClickOutside );
			document.removeEventListener( 'keydown', onKeydown );
		}

		function toggle() {
			if ( drawer.hasAttribute( 'hidden' ) ) {
				open();
			} else {
				close();
			}
		}

		function onClickOutside( e ) {
			if ( wrap.contains( e.target ) ) return;
			close();
		}

		function onKeydown( e ) {
			if ( e.key === 'Escape' ) close();
		}

		trigger.addEventListener( 'click', function( e ) {
			e.preventDefault();
			e.stopPropagation();
			toggle();
		} );

		// Videovorschau-Toggle: Zustand aus localStorage, beim Ändern speichern
		var videovorschauInput = document.getElementById( 'gk-drawer-videovorschau' );
		if ( videovorschauInput ) {
			try {
				videovorschauInput.checked = localStorage.getItem( 'gk_videovorschau' ) === '1';
			} catch ( err ) {}
			videovorschauInput.addEventListener( 'change', function() {
				try {
					localStorage.setItem( 'gk_videovorschau', this.checked ? '1' : '0' );
				} catch ( err ) {}
			} );
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
})();
