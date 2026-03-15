/**
 * Header: Beim Scrollen Frosted-Glass-Blur wie die Pill anzeigen.
 *
 * @package globalkeys
 */
(function () {
	'use strict';

	var scrollThreshold = 1;
	var ticking = false;

	function updateHeaderState() {
		if (window.scrollY > scrollThreshold) {
			document.body.classList.add('gk-header-scrolled');
		} else {
			document.body.classList.remove('gk-header-scrolled');
		}
		ticking = false;
	}

	function onScroll() {
		if (!ticking) {
			requestAnimationFrame(updateHeaderState);
			ticking = true;
		}
	}

	function init() {
		if (!document.querySelector('.site-header')) return;

		updateHeaderState();
		window.addEventListener('scroll', onScroll, { passive: true });
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
