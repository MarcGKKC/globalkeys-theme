/**
 * Categories carousel: Pfeile + Pagination-Dots zum Umschalten der Seiten.
 */
(function () {
	'use strict';

	function init() {
		var carousel = document.querySelector('.gk-categories-carousel');
		if (!carousel) return;

		var total = 4;
		var prevBtn = carousel.querySelector('.gk-categories-arrow--prev');
		var nextBtn = carousel.querySelector('.gk-categories-arrow--next');
		var slides = carousel.querySelectorAll('.gk-categories-slide');
		var section = carousel.closest('.gk-section-categories');
		var dots = section ? section.querySelectorAll('.gk-categories-dot') : [];

		function getCurrent() {
			var cur = parseInt(carousel.getAttribute('data-current'), 10);
			return isNaN(cur) ? 0 : Math.max(0, Math.min(total - 1, cur));
		}

		function goTo(page) {
			page = Math.max(0, Math.min(total - 1, page));
			carousel.setAttribute('data-current', String(page));
			slides.forEach(function (slide, i) {
				slide.classList.toggle('is-active', i === page);
			});
			dots.forEach(function (dot, i) {
				dot.classList.toggle('is-active', i === page);
				dot.setAttribute('aria-current', i === page ? 'true' : 'false');
			});
		}

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
