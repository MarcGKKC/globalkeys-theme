/**
 * Account-Drawer: Panel an body, Schließen per Intervall-Check der globalen Mausposition.
 */
(function() {
	'use strict';

	var lastMouseX = -1;
	var lastMouseY = -1;

	document.addEventListener( 'mousemove', function( e ) {
		lastMouseX = e.clientX;
		lastMouseY = e.clientY;
	}, true );

	function init() {
		var trigger = document.getElementById( 'gk-account-drawer-trigger' );
		var sourceDrawer = document.getElementById( 'gk-account-drawer' );

		if ( ! trigger || ! sourceDrawer ) {
			return;
		}

		var panel = null;
		var intervalId = null;
		var MARGIN = 24;
		var POLL_MS = 100;

		function getSafeRect() {
			if ( ! panel || ! panel.parentNode ) { return null; }
			var tr = trigger.getBoundingClientRect();
			var pr = panel.getBoundingClientRect();
			return {
				left:   Math.min( tr.left, pr.left ) - MARGIN,
				right:  Math.max( tr.right, pr.right ) + MARGIN,
				top:    Math.min( tr.top, pr.top ) - MARGIN,
				bottom: Math.max( tr.bottom, pr.bottom ) + MARGIN
			};
		}

		function isInside( x, y, r ) {
			if ( ! r ) { return true; }
			if ( x < 0 || y < 0 ) { return true; }
			return x >= r.left && x <= r.right && y >= r.top && y <= r.bottom;
		}

		function poll() {
			if ( ! panel || ! panel.parentNode ) { return; }
			var r = getSafeRect();
			if ( ! isInside( lastMouseX, lastMouseY, r ) ) {
				closeDrawer();
			}
		}

		function openDrawer() {
			document.body.classList.add( 'gk-drawer-open' );
			trigger.setAttribute( 'aria-expanded', 'true' );

			var tr = trigger.getBoundingClientRect();
			lastMouseX = ( tr.left + tr.right ) / 2;
			lastMouseY = ( tr.top + tr.bottom ) / 2;

			panel = document.createElement( 'div' );
			panel.id = 'gk-account-drawer-panel';
			panel.className = 'gk-account-drawer-panel';
			panel.setAttribute( 'role', 'dialog' );
			panel.setAttribute( 'aria-label', 'Konto-Menü' );

			var nav = sourceDrawer.querySelector( '.gk-account-drawer__nav' );
			if ( nav ) {
				panel.appendChild( nav.cloneNode( true ) );
			}

			document.body.appendChild( panel );
			positionPanel();

			intervalId = setInterval( poll, POLL_MS );
			document.body.addEventListener( 'click', closeDrawerOnClickOutside );
			document.addEventListener( 'keydown', closeOnEscape );
			window.addEventListener( 'scroll', positionPanel, true );
			window.addEventListener( 'resize', positionPanel );
		}

		function positionPanel() {
			if ( ! panel || ! panel.parentNode ) { return; }
			var tr = trigger.getBoundingClientRect();
			var left = tr.right - panel.offsetWidth;
			left = Math.max( 8, Math.min( left, window.innerWidth - panel.offsetWidth - 8 ) );
			panel.style.left = left + 'px';
			panel.style.top = ( tr.bottom + 8 ) + 'px';
		}

		function closeDrawer() {
			if ( intervalId ) {
				clearInterval( intervalId );
				intervalId = null;
			}
			document.body.classList.remove( 'gk-drawer-open' );
			trigger.setAttribute( 'aria-expanded', 'false' );
			document.body.removeEventListener( 'click', closeDrawerOnClickOutside );
			document.removeEventListener( 'keydown', closeOnEscape );
			window.removeEventListener( 'scroll', positionPanel, true );
			window.removeEventListener( 'resize', positionPanel );
			if ( panel && panel.parentNode ) {
				panel.parentNode.removeChild( panel );
				panel = null;
			}
		}

		function closeDrawerOnClickOutside( e ) {
			if ( ! panel || ! panel.parentNode ) { return; }
			if ( e.target !== trigger && ! trigger.contains( e.target ) && ! panel.contains( e.target ) ) {
				closeDrawer();
			}
		}

		function closeOnEscape( e ) {
			if ( e.key === 'Escape' ) { closeDrawer(); }
		}

		trigger.addEventListener( 'click', function( e ) {
			e.preventDefault();
			e.stopPropagation();
			if ( panel && panel.parentNode ) {
				closeDrawer();
			} else {
				openDrawer();
			}
		});
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
})();
