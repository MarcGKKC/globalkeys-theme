/**
 * Produktbewertung: 10er-Blöcke, Zusatzkategorien, Woo-rating-Sync, Submit-Validierung.
 * Modal öffnen/schließen: inc/woocommerce-product-reviews-section.php (Footer-Inline).
 */
(function () {
	'use strict';

	var MODAL_ID = 'gk-review-modal';

	function map10toWooStars(n) {
		var v = parseInt(n, 10);
		if (v < 1 || v > 10) {
			return '';
		}
		return String(Math.min(5, Math.max(1, Math.round(v / 2))));
	}

	function syncWooRatingFromGeneral(generalHidden) {
		var sel = document.getElementById('rating');
		if (!sel || !generalHidden) {
			return;
		}
		var g = parseInt(generalHidden.value, 10);
		if (g >= 1 && g <= 10) {
			sel.value = map10toWooStars(g);
		} else {
			sel.value = '';
		}
		sel.dispatchEvent(new Event('change', { bubbles: true }));
	}

	function setScaleValue(scaleRoot, value) {
		var hidden = scaleRoot.querySelector('input[type="hidden"]');
		var blocks = scaleRoot.querySelectorAll('.gk-review-scale__block');
		var num = parseInt(value, 10) || 0;
		if (hidden) {
			hidden.value = num >= 1 && num <= 10 ? String(num) : '';
		}
		blocks.forEach(function (btn) {
			var dv = parseInt(btn.getAttribute('data-value'), 10);
			var on = num >= 1 && dv <= num;
			btn.classList.toggle('is-selected', on);
			btn.setAttribute('aria-pressed', on ? 'true' : 'false');
		});
		if (hidden && hidden.id === 'gk_rating_general') {
			syncWooRatingFromGeneral(hidden);
		}
	}

	function initScale(scaleRoot) {
		if (!scaleRoot || scaleRoot.getAttribute('data-gk-scale-init') === '1') {
			return;
		}
		scaleRoot.setAttribute('data-gk-scale-init', '1');
		var hidden = scaleRoot.querySelector('input[type="hidden"]');

		scaleRoot.addEventListener('click', function (ev) {
			var btn = ev.target.closest('.gk-review-scale__block');
			if (!btn || !scaleRoot.contains(btn)) {
				return;
			}
			ev.preventDefault();
			var val = parseInt(btn.getAttribute('data-value'), 10);
			setScaleValue(scaleRoot, val);
			validateForm();
		});
	}

	function toggleExtraCats() {
		var panel = document.getElementById('gk-review-extra-cats');
		var btn = document.getElementById('gk-review-extra-cats-toggle');
		if (!panel || !btn) {
			return;
		}
		var open = panel.hasAttribute('hidden');
		if (open) {
			panel.removeAttribute('hidden');
			btn.setAttribute('aria-expanded', 'true');
			btn.classList.add('is-open');
		} else {
			panel.setAttribute('hidden', '');
			btn.setAttribute('aria-expanded', 'false');
			btn.classList.remove('is-open');
			panel.querySelectorAll('[data-gk-review-scale]').forEach(function (sc) {
				setScaleValue(sc, 0);
			});
		}
		validateForm();
	}

	function extraCatsOpen() {
		var panel = document.getElementById('gk-review-extra-cats');
		return panel && !panel.hasAttribute('hidden');
	}

	function canSubmit(form) {
		if (!form) {
			return false;
		}
		var general = form.querySelector('#gk_rating_general');
		var rating = form.querySelector('#rating');
		if (rating) {
			var gv = general ? parseInt(general.value, 10) : 0;
			if (gv < 1 || gv > 10) {
				return false;
			}
			if (!rating.value) {
				return false;
			}
		}

		if (extraCatsOpen()) {
			var extra = document.getElementById('gk-review-extra-cats');
			var scales = extra ? extra.querySelectorAll('[data-gk-review-scale] input[type="hidden"]') : [];
			for (var i = 0; i < scales.length; i++) {
				var x = parseInt(scales[i].value, 10);
				if (x < 1 || x > 10) {
					return false;
				}
			}
		}

		var comment = form.querySelector('#comment');
		if (!comment || !String(comment.value).trim()) {
			return false;
		}

		var author = form.querySelector('#author');
		if (author && author.hasAttribute('required') && !String(author.value).trim()) {
			return false;
		}
		var email = form.querySelector('#email');
		if (email && email.hasAttribute('required') && !String(email.value).trim()) {
			return false;
		}

		return true;
	}

	function validateForm() {
		var modal = document.getElementById(MODAL_ID);
		if (!modal) {
			return;
		}
		var form = modal.querySelector('form#commentform');
		var submitBtn = form ? form.querySelector('#submit') : null;
		if (!form || !submitBtn) {
			return;
		}
		var ok = canSubmit(form);
		submitBtn.disabled = !ok;
		submitBtn.classList.toggle('gk-product-reviews__submit--disabled', !ok);
	}

	function bindForm(form) {
		if (!form || form.getAttribute('data-gk-review-form-init') === '1') {
			return;
		}
		form.setAttribute('data-gk-review-form-init', '1');

		form.addEventListener('input', validateForm);
		form.addEventListener('change', validateForm);

		form.addEventListener('submit', function (ev) {
			if (!canSubmit(form)) {
				ev.preventDefault();
			}
		});

		var toggle = document.getElementById('gk-review-extra-cats-toggle');
		if (toggle) {
			toggle.addEventListener('click', function () {
				toggleExtraCats();
			});
		}

		form.querySelectorAll('[data-gk-review-scale]').forEach(initScale);
		validateForm();
	}

	function initModalForm() {
		var modal = document.getElementById(MODAL_ID);
		if (!modal) {
			return;
		}
		var form = modal.querySelector('form#commentform');
		if (form) {
			bindForm(form);
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initModalForm);
	} else {
		initModalForm();
	}

	/* Nach Portal an body: Form kann später im DOM sein – einmal bei open hooken fehlt; Modal ist schon da. */
	document.addEventListener(
		'click',
		function (ev) {
			if (!ev.target || !ev.target.closest) {
				return;
			}
			if (ev.target.closest('[data-gk-review-modal-open]')) {
				window.setTimeout(validateForm, 0);
			}
		},
		true
	);
})();
