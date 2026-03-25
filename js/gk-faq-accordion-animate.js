/**
 * FAQ: Höhen-Animation. Accordion: ein Eintrag offen.
 * Ende Öffnen: transition aus, height → auto (kein hartes Entfernen aller Styles = weniger Sprung/Flackern).
 */
(function () {
	'use strict';

	var root = document.querySelector('.gk-section-questions-answers');
	if (!root) {
		return;
	}

	var reduceMotion =
		window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	var DURATION_OPEN_MS = 320;
	var DURATION_CLOSE_MS = 300;
	var EASING = 'cubic-bezier(0.4, 0, 0.2, 1)';

	var animByDetails = new WeakMap();

	/* Synchron mit gk-faq-cube-size-sync: kein sync() während Höhen-Animation (min-height-Reset flackert) */
	function bumpLayoutAnim(delta) {
		window.dispatchEvent(
			new CustomEvent('gk-faq-anim', {
				detail: { delta: delta },
			})
		);
	}

	function clearAnimStyles(answer) {
		if (!answer) {
			return;
		}
		answer.style.height = '';
		answer.style.overflow = '';
		answer.style.transition = '';
		answer.style.willChange = '';
	}

	function cancelAnim(details) {
		var answer = details.querySelector('.gk-faq-item__answer');
		if (!answer) {
			return;
		}
		var t = animByDetails.get(details);
		if (t) {
			if (t.timeoutId) {
				window.clearTimeout(t.timeoutId);
			}
			if (t.onEnd) {
				answer.removeEventListener('transitionend', t.onEnd);
			}
			animByDetails.delete(details);
			bumpLayoutAnim(-1);
		}
		clearAnimStyles(answer);
	}

	function measureAnswerTargetHeight(answer) {
		var h = answer.scrollHeight;
		if (h > 1) {
			return Math.ceil(h);
		}
		var inner = answer.querySelector('.gk-faq-item__answer-inner');
		if (!inner) {
			return 0;
		}
		var cs = window.getComputedStyle(answer);
		var pad =
			(parseFloat(cs.paddingTop) || 0) + (parseFloat(cs.paddingBottom) || 0);
		return Math.ceil(inner.scrollHeight + pad);
	}

	function findOpenSibling(except) {
		var all = root.querySelectorAll('.gk-faq-item');
		for (var i = 0; i < all.length; i++) {
			var d = all[i];
			if (d !== except && d.open && !d.hidden) {
				return d;
			}
		}
		return null;
	}

	function openAnimate(details, answer) {
		if (reduceMotion || !answer || !details.open) {
			return;
		}

		cancelAnim(details);
		bumpLayoutAnim(1);

		answer.style.willChange = 'height';
		answer.style.overflow = 'hidden';
		answer.style.transition = 'none';
		answer.style.height = '0px';
		void answer.offsetHeight;

		var target = measureAnswerTargetHeight(answer);
		if (target < 1) {
			clearAnimStyles(answer);
			bumpLayoutAnim(-1);
			return;
		}

		void answer.offsetHeight;
		answer.style.transition =
			'height ' + DURATION_OPEN_MS / 1000 + 's ' + EASING;

		/* Ein rAF: Öffnen läuft nur nach preventDefault + manuellem open (kein natives Vollbild-Frame) */
		window.requestAnimationFrame(function () {
			if (!details.open) {
				var tAb = animByDetails.get(details);
				if (tAb) {
					if (tAb.timeoutId) {
						window.clearTimeout(tAb.timeoutId);
					}
					if (tAb.onEnd) {
						answer.removeEventListener('transitionend', tAb.onEnd);
					}
					animByDetails.delete(details);
				}
				clearAnimStyles(answer);
				bumpLayoutAnim(-1);
				return;
			}
			answer.style.height = target + 'px';
		});

		var done = false;
		function finish() {
			if (done) {
				return;
			}
			done = true;
			var t = animByDetails.get(details);
			if (t && t.timeoutId) {
				window.clearTimeout(t.timeoutId);
			}
			if (t && t.onEnd) {
				answer.removeEventListener('transitionend', t.onEnd);
			}
			animByDetails.delete(details);
			/* Nicht alles auf '' setzen: 1px-Sprung height(px) → computed auto flackert */
			answer.style.transition = 'none';
			void answer.offsetHeight;
			answer.style.height = 'auto';
			answer.style.overflow = '';
			answer.style.willChange = '';
			window.requestAnimationFrame(function () {
				answer.style.removeProperty('transition');
				bumpLayoutAnim(-1);
			});
		}

		function onEnd(ev) {
			if (ev.target !== answer || ev.propertyName !== 'height') {
				return;
			}
			finish();
		}

		animByDetails.set(details, {
			timeoutId: window.setTimeout(finish, DURATION_OPEN_MS + 100),
			onEnd: onEnd,
		});
		answer.addEventListener('transitionend', onEnd);
	}

	function closeAnimate(details, answer, onDone) {
		var cb = typeof onDone === 'function' ? onDone : null;

		if (reduceMotion) {
			cancelAnim(details);
			details.open = false;
			if (cb) {
				cb();
			}
			return;
		}

		cancelAnim(details);

		if (!answer) {
			details.open = false;
			if (cb) {
				cb();
			}
			return;
		}

		var start = Math.ceil(answer.getBoundingClientRect().height);
		if (start < 1) {
			start = measureAnswerTargetHeight(answer);
		}
		if (start < 1) {
			details.open = false;
			clearAnimStyles(answer);
			if (cb) {
				cb();
			}
			return;
		}

		bumpLayoutAnim(1);

		answer.style.overflow = 'hidden';
		answer.style.height = start + 'px';
		void answer.offsetHeight;
		answer.style.transition =
			'height ' + DURATION_CLOSE_MS / 1000 + 's ' + EASING;

		window.requestAnimationFrame(function () {
			window.requestAnimationFrame(function () {
				answer.style.height = '0px';
			});
		});

		var done = false;
		function finish() {
			if (done) {
				return;
			}
			done = true;
			var t = animByDetails.get(details);
			if (t && t.timeoutId) {
				window.clearTimeout(t.timeoutId);
			}
			if (t && t.onEnd) {
				answer.removeEventListener('transitionend', t.onEnd);
			}
			animByDetails.delete(details);
			details.open = false;
			clearAnimStyles(answer);
			bumpLayoutAnim(-1);
			if (cb) {
				cb();
			}
		}

		function onEnd(ev) {
			if (ev.target !== answer || ev.propertyName !== 'height') {
				return;
			}
			finish();
		}

		animByDetails.set(details, {
			timeoutId: window.setTimeout(finish, DURATION_CLOSE_MS + 100),
			onEnd: onEnd,
		});
		answer.addEventListener('transitionend', onEnd);
	}

	if (!reduceMotion) {
		/*
		 * Capture + preventDefault: sonst öffnet die Engine <details> sofort in voller Höhe,
		 * ein Frame lang sichtbar, dann erst toggle/openAnimate — Flackern.
		 */
		root.addEventListener(
			'click',
			function (e) {
				var t = e.target;
				if (!t || !t.closest) {
					return;
				}
				var summary = t.closest('.gk-faq-item__summary');
				if (!summary || !root.contains(summary)) {
					return;
				}
				if (t.closest('a')) {
					return;
				}
				var details = summary.closest('.gk-faq-item');
				if (!details || details.hidden) {
					return;
				}
				var answer = details.querySelector('.gk-faq-item__answer');
				if (!answer) {
					return;
				}

				e.preventDefault();

				if (details.open) {
					closeAnimate(details, answer, null);
					return;
				}

				var other = findOpenSibling(details);
				if (other) {
					var oAns = other.querySelector('.gk-faq-item__answer');
					if (!oAns) {
						other.open = false;
					} else {
						closeAnimate(other, oAns, null);
					}
				}

				details.open = true;
				openAnimate(details, answer);
			},
			true
		);
	}

	/* Klick im Antwortbereich schließt den Block (nicht bei Links/Buttons/Formularfeldern) */
	root.addEventListener('click', function (e) {
		var el = e.target;
		if (!el || !el.closest) {
			return;
		}
		if (el.closest('.gk-faq-item__summary')) {
			return;
		}
		if (
			el.closest(
				'a, button, input, textarea, select, [contenteditable="true"], [role="button"], [role="link"]'
			)
		) {
			return;
		}
		var answer = el.closest('.gk-faq-item__answer');
		if (!answer || !root.contains(answer)) {
			return;
		}
		var details = answer.closest('.gk-faq-item');
		if (!details || details.hidden || !details.open) {
			return;
		}
		if (reduceMotion) {
			details.open = false;
			return;
		}
		closeAnimate(details, answer, null);
	});

	if (reduceMotion) {
		var items = root.querySelectorAll('.gk-faq-item');
		for (var j = 0; j < items.length; j++) {
			(function (details) {
				details.addEventListener('toggle', function () {
					if (!details.open) {
						return;
					}
					var all = root.querySelectorAll('.gk-faq-item');
					for (var k = 0; k < all.length; k++) {
						var d = all[k];
						if (d !== details && d.open && !d.hidden) {
							d.open = false;
						}
					}
				});
			})(items[j]);
		}
	}
})();
