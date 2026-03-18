/**
 * Categories + Test carousel: Pfeile + Pagination-Dots zum Umschalten der Seiten.
 */
(function () {
	'use strict';

	function initCarousel(carousel, slideSelector, prevSelector, nextSelector, sectionSelector, dotSelector) {
		if (!carousel) return;

		var slides = carousel.querySelectorAll(slideSelector);
		var total = slides.length;
		if (total === 0) return;

		var prevBtn = carousel.querySelector(prevSelector);
		var nextBtn = carousel.querySelector(nextSelector);
		var section = carousel.closest(sectionSelector);
		var dots = section ? section.querySelectorAll(dotSelector) : [];

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

	function init() {
		/* Altes 4er-Karussell entfernt; Our Categories nutzt Inline-Script in section-categories.php */
		initCarousel(
			document.querySelector('.gk-categories-carousel'),
			'.gk-categories-slide',
			'.gk-categories-arrow--prev',
			'.gk-categories-arrow--next',
			'.gk-section-categories',
			'.gk-categories-dot'
		);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
