( function () {
	'use strict';

	var modal = document.getElementById( 'gk-locale-modal' );
	if ( ! modal ) {
		return;
	}
	if ( modal.parentNode !== document.body ) {
		document.body.appendChild( modal );
	}

	var html = document.documentElement;
	var triggers = document.querySelectorAll( '[data-gk-locale-open]' );
	var closers = modal.querySelectorAll( '[data-gk-locale-close]' );
	var scrollInner = modal.querySelector( '.gk-locale-modal__scroll-inner' );
	var lastActive = null;

	function openModal() {
		lastActive = document.activeElement;
		modal.hidden = false;
		modal.classList.remove( 'gk-locale-modal--closed' );
		modal.setAttribute( 'aria-hidden', 'false' );
		html.classList.add( 'gk-locale-modal-is-open' );
		window.requestAnimationFrame( function () {
			var closeBtn = modal.querySelector( '.gk-locale-modal__close' );
			if ( closeBtn ) {
				try {
					closeBtn.focus( { preventScroll: true } );
				} catch ( e ) {
					closeBtn.focus();
				}
			}
		} );
	}

	function closeModal() {
		modal.classList.add( 'gk-locale-modal--closed' );
		modal.setAttribute( 'aria-hidden', 'true' );
		modal.hidden = true;
		html.classList.remove( 'gk-locale-modal-is-open' );
		if ( lastActive && typeof lastActive.focus === 'function' ) {
			lastActive.focus();
		}
	}

	for ( var i = 0; i < triggers.length; i++ ) {
		triggers[ i ].addEventListener( 'click', function ( e ) {
			e.preventDefault();
			openModal();
		} );
	}

	for ( var j = 0; j < closers.length; j++ ) {
		closers[ j ].addEventListener( 'click', function () {
			closeModal();
		} );
	}

	if ( scrollInner ) {
		scrollInner.addEventListener( 'click', function ( e ) {
			if ( e.target === scrollInner ) {
				closeModal();
			}
		} );
	}

	document.addEventListener( 'keydown', function ( e ) {
		if ( e.key === 'Escape' && ! modal.hidden ) {
			closeModal();
		}
	} );
} )();
