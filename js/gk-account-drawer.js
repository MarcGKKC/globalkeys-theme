/**
 * Account-Drawer: Beim Öffnen in body verschieben, damit kein transform-Vorfahre
 * den Glas-Hintergrund (backdrop-filter) beim Scrollen kaputt macht.
 */
(function() {
	'use strict';

	function init() {
		var wrap = document.getElementById( 'gk-account-drawer-wrap' );
		var trigger = document.getElementById( 'gk-account-drawer-trigger' );
		var drawer = document.getElementById( 'gk-account-drawer' );

		if ( ! wrap || ! trigger || ! drawer ) return;

		var overlay = null;
		var scrollResizeCleanup = null;

		function updateDrawerPosition() {
			var rect = trigger.getBoundingClientRect();
			drawer.style.position = 'fixed';
			drawer.style.top = (rect.bottom + 8) + 'px';
			drawer.style.left = '';
			drawer.style.right = (window.innerWidth - rect.right) + 'px';
		}

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
			overlay.addEventListener( 'click', function() { close(); } );
			document.body.appendChild( overlay );
			document.body.appendChild( drawer );
			drawer.classList.add( 'gk-account-drawer--portal' );
			updateDrawerPosition();
			scrollResizeCleanup = function() {
				window.removeEventListener( 'scroll', updateDrawerPosition, true );
				window.removeEventListener( 'resize', updateDrawerPosition );
				scrollResizeCleanup = null;
			};
			window.addEventListener( 'scroll', updateDrawerPosition, true );
			window.addEventListener( 'resize', updateDrawerPosition );
			document.body.addEventListener( 'click', onClickOutside );
			document.addEventListener( 'keydown', onKeydown );
		}

		function close() {
			drawer.setAttribute( 'hidden', '' );
			drawer.setAttribute( 'aria-hidden', 'true' );
			trigger.setAttribute( 'aria-expanded', 'false' );
			document.body.classList.remove( 'gk-drawer-open' );
			wrap.classList.remove( 'gk-drawer-open' );
			drawer.classList.remove( 'gk-account-drawer--portal' );
			drawer.style.position = '';
			drawer.style.top = '';
			drawer.style.right = '';
			if ( scrollResizeCleanup ) {
				scrollResizeCleanup();
			}
			wrap.appendChild( drawer );
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
			if ( wrap.contains( e.target ) || drawer.contains( e.target ) ) return;
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

		// Videovorschau-Toggle: Standard AN; beim Ändern speichern und Body-Klasse setzen
		var videovorschauInput = document.getElementById( 'gk-drawer-videovorschau' );
		if ( videovorschauInput ) {
			function updateVideovorschauUI( isOn ) {
				try {
					document.body.classList.toggle( 'gk-videovorschau-off', ! isOn );
					if ( typeof window.CustomEvent === 'function' ) {
						document.dispatchEvent( new CustomEvent( 'gk-videovorschau-change', { detail: { enabled: isOn } } ) );
					}
				} catch ( err ) {}
			}
			try {
				var stored = localStorage.getItem( 'gk_videovorschau' );
				videovorschauInput.checked = stored !== '0';
				updateVideovorschauUI( videovorschauInput.checked );
			} catch ( err ) {}
			videovorschauInput.addEventListener( 'change', function() {
				try {
					localStorage.setItem( 'gk_videovorschau', this.checked ? '1' : '0' );
					updateVideovorschauUI( this.checked );
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
