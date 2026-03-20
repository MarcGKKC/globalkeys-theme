/**
 * Produkt-Hover-Panel: bei Platzmangel nach links spiegeln (Viewport-Kante).
 */
(function () {
	'use strict';

	var section = document.querySelector('.gk-section-bestsellers');
	if (!section) {
		return;
	}

	var items = section.querySelectorAll('.gk-featured-product');
	if (!items.length) {
		return;
	}

	function updateFlip(li) {
		var panel = li.querySelector('.gk-product-hover-panel');
		if (!panel) {
			return;
		}
		panel.classList.remove('gk-product-hover-panel--flip');
		// Nach Anzeige messen (Hover-CSS aktiv)
		requestAnimationFrame(function () {
			requestAnimationFrame(function () {
				var r = panel.getBoundingClientRect();
				var pad = 12;
				if (r.right > window.innerWidth - pad) {
					panel.classList.add('gk-product-hover-panel--flip');
				}
				r = panel.getBoundingClientRect();
				if (panel.classList.contains('gk-product-hover-panel--flip') && r.left < pad) {
					panel.classList.remove('gk-product-hover-panel--flip');
				}
			});
		});
	}

	items.forEach(function (li) {
		li.addEventListener('mouseenter', function () {
			updateFlip(li);
		});
		li.addEventListener('focusin', function () {
			updateFlip(li);
		});
	});
})();
