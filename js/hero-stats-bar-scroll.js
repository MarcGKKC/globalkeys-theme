/**
 * Hero Stats Bar: Bleibt an ihrer Position im Seitenfluss (kein Anheften unten beim Scrollen).
 *
 * @package globalkeys
 */
(function () {
	'use strict';

	function init() {
		if (!document.querySelector('.gk-hero-stats-bar')) return;
		// Klasse gk-stats-bar-pinned-bottom wird nicht mehr gesetzt – Bar bleibt stehen
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
