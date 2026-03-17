/**
 * Header-Scroll: Blur + kompakte Pill. Klare Zustandslogik, Lock damit Animation durchläuft.
 *
 * - Zwei Zustände: 'top' | 'scrolled'. Nur zwei Übergänge.
 * - Nach Wechsel in 'scrolled': 450ms Lock, danach erst wieder 'top' möglich.
 * - Nach Wechsel in 'top': 400ms Lock, danach erst wieder 'scrolled' möglich.
 * - Scroll-Position: eine konsistente Quelle (window.scrollY / documentElement.scrollTop).
 *
 * @package globalkeys
 */
(function () {
	'use strict';

	var SCROLLED_PX = 1;
	var AT_TOP_PX = 2;   /* nur bei echt 0–2px = ganz oben wechseln, sonst Stop kurz vor oben */
	var CONSECUTIVE_TICKS_AT_TOP = 1;   /* scrolled → top: sofort wenn y<=2, keine Wartezeit */
	var CONSECUTIVE_TICKS_SCROLLED = 1; /* top → scrolled: 1 Tick mit y>1 reicht, Animation früher */
	var LOCK_MS_AFTER_SCROLLED = 450;
	var LOCK_MS_AFTER_TOP = 400;   /* nur kurz, damit sofortiges Runterscrollen nicht blockiert wird */
	var POLL_MS = 150;

	var state = 'top';
	var lockedUntil = 0;
	var rafScheduled = false;
	var atTopCounter = 0;
	var scrolledDownCounter = 0;

	function getScrollTop() {
		var a = 0, b = 0, c = 0;
		if (typeof window.scrollY === 'number' && !isNaN(window.scrollY)) a = window.scrollY;
		if (typeof window.pageYOffset === 'number' && !isNaN(window.pageYOffset)) b = window.pageYOffset;
		if (document.documentElement && typeof document.documentElement.scrollTop === 'number') c = document.documentElement.scrollTop;
		return Math.max(0, a, b, c);
	}

	var blurTimeout = null;

	function applyState(newState) {
		if (newState === state) return;
		state = newState;
		if (!document.body) return;
		if (state === 'scrolled') {
			document.body.classList.add('gk-header-scrolled');
			if (blurTimeout) clearTimeout(blurTimeout);
			blurTimeout = setTimeout(function () {
				document.body.classList.add('gk-header-blur');
				blurTimeout = null;
			}, 140);
			lockedUntil = Date.now() + LOCK_MS_AFTER_SCROLLED;
		} else {
			document.body.classList.remove('gk-header-scrolled');
			document.body.classList.remove('gk-header-blur');
			if (blurTimeout) { clearTimeout(blurTimeout); blurTimeout = null; }
			lockedUntil = Date.now() + LOCK_MS_AFTER_TOP;
		}
	}

	function tick() {
		if (!document.body || !document.querySelector('.site-header')) return;
		var now = Date.now();
		var y = getScrollTop();

		if (state === 'top') {
			if (now < lockedUntil) return;
			atTopCounter = 0;
			if (y > SCROLLED_PX) {
				scrolledDownCounter += 1;
				if (scrolledDownCounter >= CONSECUTIVE_TICKS_SCROLLED) {
					scrolledDownCounter = 0;
					applyState('scrolled');
				}
			} else {
				scrolledDownCounter = 0;
			}
			return;
		}

		if (state === 'scrolled') {
			if (now < lockedUntil) return;
			scrolledDownCounter = 0;
			if (y <= AT_TOP_PX) {
				atTopCounter += 1;
				if (atTopCounter >= CONSECUTIVE_TICKS_AT_TOP) {
					atTopCounter = 0;
					applyState('top');
				}
			} else {
				atTopCounter = 0;
			}
		}
	}

	function onScroll() {
		if (!rafScheduled) {
			rafScheduled = true;
			requestAnimationFrame(function () {
				rafScheduled = false;
				tick();
			});
		}
	}

	function init() {
		if (!document.querySelector('.site-header')) return;

		var y = getScrollTop();
		if (y > SCROLLED_PX) {
			state = 'scrolled';
			if (document.body) {
				document.body.classList.add('gk-header-scrolled');
				if (blurTimeout) clearTimeout(blurTimeout);
				blurTimeout = setTimeout(function () {
					if (document.body) document.body.classList.add('gk-header-blur');
					blurTimeout = null;
				}, 140);
			}
			lockedUntil = 0;
		} else {
			state = 'top';
			if (document.body) {
				document.body.classList.remove('gk-header-scrolled');
				document.body.classList.remove('gk-header-blur');
				if (blurTimeout) { clearTimeout(blurTimeout); blurTimeout = null; }
			}
			lockedUntil = 0;
		}

		window.addEventListener('scroll', onScroll, { passive: true });
		window.addEventListener('resize', onScroll, { passive: true });
		document.addEventListener('wheel', function (e) {
			if (e.deltaY > 0 && state === 'top' && document.body && document.querySelector('.site-header')) {
				applyState('scrolled');
			}
		}, { passive: true, capture: true });
		setInterval(tick, POLL_MS);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
