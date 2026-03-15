/**
 * Hero Stats Bar: Zähl-Animation (Count-up wie Kalender) beim Laden/Sichtbarwerden.
 *
 * @package globalkeys
 */
(function () {
	'use strict';

	function formatStatNumber(num) {
		num = Math.floor(num);
		if (num >= 1000000) {
			var n = num / 1000000;
			return (n === Math.floor(n) ? n : Math.round(n * 10) / 10) + 'M';
		}
		if (num >= 1000) {
			var n = num / 1000;
			return (n === Math.floor(n) ? n : Math.round(n * 10) / 10) + 'K';
		}
		return String(num);
	}

	function easeOutQuart(t) {
		return 1 - Math.pow(1 - t, 4);
	}

	function animateValue(el, end, durationMs) {
		var start = 0;
		var startTime = null;

		function step(timestamp) {
			if (!startTime) startTime = timestamp;
			var elapsed = timestamp - startTime;
			var progress = Math.min(elapsed / durationMs, 1);
			var eased = easeOutQuart(progress);
			var current = Math.round(start + (end - start) * eased);
			el.textContent = formatStatNumber(current);
			if (progress < 1) {
				requestAnimationFrame(step);
			} else {
				el.textContent = formatStatNumber(end);
			}
		}

		requestAnimationFrame(step);
	}

	function runCountAnimations() {
		var numbers = document.querySelectorAll('.gk-hero-stat-number[data-end]');
		var duration = 1600;

		numbers.forEach(function (el) {
			var end = parseInt(el.getAttribute('data-end'), 10);
			if (isNaN(end)) return;
			el.textContent = formatStatNumber(0);
			animateValue(el, end, duration);
		});
	}

	function init() {
		var bar = document.querySelector('.gk-hero-stats-bar');
		if (!bar || !bar.querySelector('.gk-hero-stat-number[data-end]')) return;

		if ('IntersectionObserver' in window) {
			var observer = new IntersectionObserver(
				function (entries) {
					entries.forEach(function (entry) {
						if (entry.isIntersecting) {
							runCountAnimations();
							observer.disconnect();
						}
					});
				},
				{ rootMargin: '0px', threshold: 0.2 }
			);
			observer.observe(bar);
		} else {
			runCountAnimations();
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
