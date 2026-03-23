/**
 * FAQ: Kategorie-Kacheln filtern die Accordion-Einträge; erneuter Klick zeigt wieder alle.
 */
(function () {
	'use strict';

	var root = document.querySelector('.gk-section-questions-answers');
	if (!root) {
		return;
	}

	var tiles = root.querySelectorAll('.gk-faq-category-tile[data-category-filter]');
	var items = root.querySelectorAll('.gk-faq-item[data-category]');
	if (!tiles.length || !items.length) {
		return;
	}

	var activeSlug = null;

	function notifyLayout() {
		window.dispatchEvent(new CustomEvent('gk-faq-layout-changed'));
	}

	function clearFilter() {
		activeSlug = null;
		items.forEach(function (el) {
			el.hidden = false;
		});
		tiles.forEach(function (btn) {
			btn.setAttribute('aria-pressed', 'false');
			btn.classList.remove('is-active');
		});
		notifyLayout();
	}

	function applyFilter(slug) {
		if (!slug) {
			return;
		}
		if (activeSlug === slug) {
			clearFilter();
			return;
		}
		activeSlug = slug;
		items.forEach(function (el) {
			var c = el.getAttribute('data-category');
			var show = c === slug;
			el.hidden = !show;
			if (!show && el.tagName === 'DETAILS') {
				el.removeAttribute('open');
			}
		});
		tiles.forEach(function (btn) {
			var match = btn.getAttribute('data-category-filter') === slug;
			btn.setAttribute('aria-pressed', match ? 'true' : 'false');
			btn.classList.toggle('is-active', match);
		});
		notifyLayout();
	}

	tiles.forEach(function (btn) {
		btn.addEventListener('click', function () {
			var slug = btn.getAttribute('data-category-filter');
			applyFilter(slug);
		});
	});
})();
