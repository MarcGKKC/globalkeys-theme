/**
 * PC-Kategorie-Raster: Show All / Show Less, Klick außerhalb schließt (Kategorie-Links ausgenommen).
 */
(function () {
	'use strict';

	var section = document.querySelector('.gk-section-pc-category-grid');
	if (!section) {
		return;
	}

	var btn = section.querySelector('.gk-pc-category-grid__toggle');
	if (!btn) {
		return;
	}

	var labels = typeof gkPcCategoryGrid !== 'undefined' && gkPcCategoryGrid ? gkPcCategoryGrid : {};
	var showAll = labels.showAll || 'Show All';
	var showLess = labels.showLess || 'Show Less';

	var listCompact = section.querySelector('.gk-pc-category-grid__list--compact');
	var listFull = section.querySelector('.gk-pc-category-grid__list--full');

	function setExpanded(expanded) {
		section.classList.toggle('is-expanded', expanded);
		btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
		btn.textContent = expanded ? showLess : showAll;
		if (listCompact) {
			listCompact.setAttribute('aria-hidden', expanded ? 'true' : 'false');
		}
		if (listFull) {
			listFull.setAttribute('aria-hidden', expanded ? 'false' : 'true');
		}
	}

	btn.addEventListener('click', function (e) {
		e.stopPropagation();
		setExpanded(!section.classList.contains('is-expanded'));
	});

	document.addEventListener('click', function (e) {
		if (!section.classList.contains('is-expanded')) {
			return;
		}
		var t = e.target;
		if (t.closest && t.closest('.gk-pc-category-grid__link')) {
			return;
		}
		if (t.closest && t.closest('.gk-pc-category-grid__toggle')) {
			return;
		}
		setExpanded(false);
	});
})();
