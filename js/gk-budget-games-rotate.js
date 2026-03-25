/**
 * „Games for every budget“ section: row order every 10 s; pauses on hover over the cards.
 */
( function () {
	'use strict';

	var SECTION_ID = 'section-budget-games';
	var ROW_SEL = '.gk-budget-games-row';
	var INTERVAL_MS = 10000;

	function rotateRow( row ) {
		var first = row.firstElementChild;
		if ( first && first.parentNode === row ) {
			row.appendChild( first );
		}
	}

	function init() {
		var section = document.getElementById( SECTION_ID );
		if ( ! section ) {
			return;
		}
		var row = section.querySelector( ROW_SEL );
		if ( ! row || row.children.length < 2 ) {
			return;
		}
		if ( window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
			return;
		}
		var timerId = null;
		function startTimer() {
			if ( timerId !== null ) {
				return;
			}
			timerId = window.setInterval( function () {
				rotateRow( row );
			}, INTERVAL_MS );
		}
		function stopTimer() {
			if ( timerId !== null ) {
				window.clearInterval( timerId );
				timerId = null;
			}
		}
		row.addEventListener( 'mouseenter', stopTimer );
		row.addEventListener( 'mouseleave', startTimer );
		startTimer();
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
