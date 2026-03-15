/**
 * Hero Stats Bar: Beim Runterscrollen nach oben wegrücken, unten fest anzeigen.
 * Seitenlänge bleibt gleich (padding-bottom gleicht aus).
 *
 * @package globalkeys
 */
(function () {
	'use strict';

	var scrollThreshold = 120;
	var ticking = false;

	function updateBarState() {
		if (window.scrollY > scrollThreshold) {
			document.body.classList.add('gk-stats-bar-pinned-bottom');
		} else {
			document.body.classList.remove('gk-stats-bar-pinned-bottom');
		}
		ticking = false;
	}

	function onScroll() {
		if (!ticking) {
			requestAnimationFrame(updateBarState);
			ticking = true;
		}
	}

	function init() {
		if (!document.querySelector('.gk-hero-stats-bar')) return;

		updateBarState();
		window.addEventListener('scroll', onScroll, { passive: true });
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
