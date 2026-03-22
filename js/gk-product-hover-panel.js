/**
 * Produkt-Hover-Panel: bei Platzmangel nach links spiegeln (Viewport-Kante).
 * Flip basiert auf Karten-Position für zuverlässige Erkennung (auch bei hidden Panel).
 */
(function () {
	'use strict';

	var PANEL_WIDTH = 340;
	var GAP = 12;
	var PAD = 12;

	function updateFlip(li) {
		var panel = li.querySelector('.gk-product-hover-panel');
		if (!panel) {
			return;
		}
		var cardRect = li.getBoundingClientRect();
		var spaceRight = window.innerWidth - cardRect.right - GAP - PAD;
		var spaceLeft = cardRect.left - GAP - PAD;
		panel.classList.remove('gk-product-hover-panel--flip');
		if (spaceRight < PANEL_WIDTH && spaceLeft >= PANEL_WIDTH) {
			panel.classList.add('gk-product-hover-panel--flip');
		} else if (spaceRight < PANEL_WIDTH && spaceLeft < PANEL_WIDTH && spaceLeft > spaceRight) {
			panel.classList.add('gk-product-hover-panel--flip');
		}
	}

	function bindItem(li) {
		li.addEventListener('mouseenter', function () {
			updateFlip(li);
		});
		li.addEventListener('focusin', function () {
			updateFlip(li);
		});
	}

	function initSection(section) {
		section.querySelectorAll('.gk-featured-product').forEach(bindItem);
	}

	var sections = document.querySelectorAll('.gk-section-bestsellers');
	if (sections.length) {
		sections.forEach(initSection);
	}

	document.body.addEventListener('gk_search_results_updated', function () {
		var searchSection = document.getElementById('gk-search-results-grid') || document.querySelector('.gk-section-shop-results');
		if (searchSection) {
			initSection(searchSection);
		}
	});
})();
