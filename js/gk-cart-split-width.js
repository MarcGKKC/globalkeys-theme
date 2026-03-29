/**
 * Warenkorb: --gk-cart-split-iw auf der Split-Zeile (Rail-Breite ohne container-type).
 */
( function () {
	'use strict';

	function init() {
		var el = document.querySelector( 'body.woocommerce-cart .gk-cart-page-split' );
		if ( ! el ) {
			return;
		}

		function setWidth() {
			var w = el.offsetWidth;
			if ( w > 0 ) {
				el.style.setProperty( '--gk-cart-split-iw', w + 'px' );
			}
		}

		setWidth();
		if ( typeof ResizeObserver !== 'undefined' ) {
			new ResizeObserver( setWidth ).observe( el );
		} else {
			window.addEventListener( 'resize', setWidth );
		}
		window.addEventListener( 'load', setWidth );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
