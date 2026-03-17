/**
 * Categories carousel: Pfeile + Pagination-Dots zum Umschalten der Seiten.
 */
(function () {
	'use strict';

	function init() {
		var carousel = document.querySelector('.gk-categories-carousel');
		if (!carousel) return;

		var slides = carousel.querySelectorAll('.gk-categories-slide');
		var total = slides.length;
		if (total === 0) return;

		var prevBtn = carousel.querySelector('.gk-categories-arrow--prev');
		var nextBtn = carousel.querySelector('.gk-categories-arrow--next');
		var section = carousel.closest('.gk-section-categories');
		var dots = section ? section.querySelectorAll('.gk-categories-dot') : [];

		function normalizePage(page) {
			return ((page % total) + total) % total;
		}

		function getCurrent() {
			var cur = parseInt(carousel.getAttribute('data-current'), 10);
			return isNaN(cur) ? 0 : normalizePage(cur);
		}

		function goTo(page) {
			page = normalizePage(page);
			carousel.setAttribute('data-current', String(page));
			slides.forEach(function (slide, i) {
				slide.classList.toggle('is-active', i === page);
			});
			dots.forEach(function (dot, i) {
				dot.classList.toggle('is-active', i === page);
				dot.setAttribute('aria-current', i === page ? 'true' : 'false');
			});
		}

		/* Endlosschleife: letzte → weiter = erste; erste → zurück = letzte */
		if (prevBtn) {
			prevBtn.addEventListener('click', function () {
				goTo(getCurrent() - 1);
			});
		}
		if (nextBtn) {
			nextBtn.addEventListener('click', function () {
				goTo(getCurrent() + 1);
			});
		}
		dots.forEach(function (dot, i) {
			dot.addEventListener('click', function () {
				goTo(i);
			});
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
