/**
 * FAQ rechts: alle Frage-Karten gleiche Mindesthöhe (höchste Fragezeile).
 * Würfel links: Kantenlänge = 2× diese Zeilenhöhe + Listen-Gap (eine Lücke zwischen zwei Karten).
 * Beim Messen kurz --gk-faq-item-uniform-h entfernen, damit die echte Inhaltshöhe je Zeile erfasst wird.
 */
(function () {
	'use strict';

	var faqAnimCount = 0;
	var cubeSyncPending = false;
	var roDebounceTimer = null;
	var RESIZE_SYNC_DEBOUNCE_MS = 100;

	window.addEventListener('gk-faq-anim', function (e) {
		var d = e.detail && typeof e.detail.delta === 'number' ? e.detail.delta : 0;
		faqAnimCount += d;
		if (faqAnimCount < 0) {
			faqAnimCount = 0;
		}
		if (faqAnimCount === 0 && cubeSyncPending) {
			cubeSyncPending = false;
			window.requestAnimationFrame(sync);
		}
	});

	function parseGapPx(el) {
		if (!el) {
			return 0;
		}
		var s = window.getComputedStyle(el);
		var g = parseFloat(s.rowGap);
		if (!isNaN(g)) {
			return g;
		}
		g = parseFloat(s.columnGap);
		if (!isNaN(g)) {
			return g;
		}
		g = parseFloat(s.gap);
		return isNaN(g) ? 0 : g;
	}

	function blockHeight(el) {
		if (!el || el.hidden) {
			return 0;
		}
		var r = el.getBoundingClientRect();
		return r.height > 0 ? r.height : el.offsetHeight;
	}

	/**
	 * Äußere Höhe der Fragezeile wie bei einer zugeklappten Karte — ohne erzwungene min-height auf .gk-faq-item.
	 * Zugeklappt: volles <details>. Offen: Summary + Kartenränder (Antwort zählt nicht).
	 */
	function naturalClosedRowOuterHeight(item) {
		if (!item || item.hidden) {
			return 0;
		}
		var sum = item.querySelector('.gk-faq-item__summary');
		if (!sum) {
			return 0;
		}
		if (!item.open) {
			return blockHeight(item);
		}
		var cs = window.getComputedStyle(item);
		var borders =
			(parseFloat(cs.borderTopWidth) || 0) + (parseFloat(cs.borderBottomWidth) || 0);
		return blockHeight(sum) + borders;
	}

	function maxUniformRowHeight(list) {
		var maxH = 0;
		var items = list.querySelectorAll('.gk-faq-item');
		for (var i = 0; i < items.length; i++) {
			if (items[i].hidden) {
				continue;
			}
			var h = naturalClosedRowOuterHeight(items[i]);
			if (h > maxH) {
				maxH = h;
			}
		}
		return maxH;
	}

	function visibleItemCount(list) {
		var n = 0;
		var items = list.querySelectorAll('.gk-faq-item');
		for (var i = 0; i < items.length; i++) {
			if (!items[i].hidden) {
				n++;
			}
		}
		return n;
	}

	function computeCubeSize(list, uniformRowPx) {
		if (!list || uniformRowPx < 8) {
			return 0;
		}
		if (visibleItemCount(list) === 0) {
			return 0;
		}
		var gap = parseGapPx(list);
		return Math.ceil(2 * uniformRowPx + gap);
	}

	/** Tatsächlicher Abstand von Oberkante Karte i bis Unterkante Karte i+1 (inkl. flex-gap) — vermeidet Rundungsabweichung zur Formel. */
	function measureConsecutiveClosedPairSpanPx(list) {
		var items = list.querySelectorAll('.gk-faq-item');
		var visible = [];
		for (var i = 0; i < items.length; i++) {
			if (!items[i].hidden) {
				visible.push(items[i]);
			}
		}
		if (visible.length < 2) {
			return 0;
		}
		for (var j = 0; j + 1 < visible.length; j++) {
			if (visible[j].open || visible[j + 1].open) {
				continue;
			}
			var ra = visible[j].getBoundingClientRect();
			var rb = visible[j + 1].getBoundingClientRect();
			var span = rb.bottom - ra.top;
			if (span > 8) {
				return Math.ceil(span);
			}
		}
		return 0;
	}

	function sync() {
		if (faqAnimCount > 0) {
			cubeSyncPending = true;
			return;
		}
		cubeSyncPending = false;

		var section = document.querySelector('.gk-section-questions-answers');
		if (!section) {
			return;
		}
		var list = section.querySelector('.gk-faq-list');
		if (!list) {
			section.classList.add('gk-faq-cubes-ready');
			return;
		}

		section.style.removeProperty('--gk-faq-item-uniform-h');
		void list.offsetHeight;

		var maxUniform = maxUniformRowHeight(list);

		if (maxUniform >= 8) {
			section.style.setProperty('--gk-faq-item-uniform-h', maxUniform + 'px');
		}

		void list.offsetHeight;

		var sizePx = measureConsecutiveClosedPairSpanPx(list);
		if (sizePx < 8) {
			sizePx = computeCubeSize(list, maxUniform);
		}
		if (sizePx >= 8) {
			section.style.setProperty('--gk-faq-cube-size', sizePx + 'px');
		}

		/* Erst bei gültiger Messung einblenden — sonst würde der größere CSS-calc-Fallback kurz sichtbar */
		if (maxUniform >= 8 && sizePx >= 8) {
			section.classList.add('gk-faq-cubes-ready');
		}
	}

	function scheduleDebouncedSync() {
		if (roDebounceTimer) {
			window.clearTimeout(roDebounceTimer);
		}
		roDebounceTimer = window.setTimeout(function () {
			roDebounceTimer = null;
			sync();
		}, RESIZE_SYNC_DEBOUNCE_MS);
	}

	function init() {
		/* Sofort messen; Follow-up nach Debounce gleicht Layout nach Fonts/Bildern aus */
		sync();
		scheduleDebouncedSync();

		var section = document.querySelector('.gk-section-questions-answers');
		var list = section ? section.querySelector('.gk-faq-list') : null;
		if (!list) {
			return;
		}

		window.setTimeout(function () {
			var sec = document.querySelector('.gk-section-questions-answers');
			if (sec && !sec.classList.contains('gk-faq-cubes-ready')) {
				sec.classList.add('gk-faq-cubes-ready');
			}
		}, 2500);

		if (typeof ResizeObserver !== 'undefined') {
			var ro = new ResizeObserver(function () {
				scheduleDebouncedSync();
			});
			ro.observe(list);
		}

		window.addEventListener('resize', scheduleDebouncedSync);
		window.addEventListener('load', scheduleDebouncedSync);
		window.addEventListener('gk-faq-layout-changed', function () {
			if (roDebounceTimer) {
				window.clearTimeout(roDebounceTimer);
				roDebounceTimer = null;
			}
			sync();
		});

		var tries = 0;
		function retry() {
			sync();
			if (tries < 12) {
				tries += 1;
				var sectionEl = document.querySelector('.gk-section-questions-answers');
				var listEl = sectionEl && sectionEl.querySelector('.gk-faq-list');
				var hasVisible = listEl && visibleItemCount(listEl) > 0;
				var cubeSet =
					sectionEl &&
					sectionEl.style.getPropertyValue('--gk-faq-cube-size').trim() !== '';
				if (hasVisible && !cubeSet) {
					window.setTimeout(retry, 80);
				}
			}
		}
		window.setTimeout(retry, 50);
		window.setTimeout(retry, 200);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
