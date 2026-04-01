/**
 * Produktbewertung: 5 Sterne, Woo-rating-Sync, Submit-Validierung.
 * Modal öffnen/schließen: inc/woocommerce-product-reviews-section.php (Footer-Inline).
 */
(function () {
	'use strict';

	var MODAL_ID = 'gk-review-modal';

	function syncWooRatingFromGeneral(hidden) {
		var sel = document.getElementById('rating');
		if (!sel || !hidden) {
			return;
		}
		var g = parseInt(hidden.value, 10);
		if (g >= 1 && g <= 5) {
			sel.value = String(g);
		} else {
			sel.value = '';
		}
		sel.dispatchEvent(new Event('change', { bubbles: true }));
	}

	function setStarsValue(starsRoot, value) {
		var hidden = starsRoot.querySelector('input[type="hidden"]');
		var stars = starsRoot.querySelectorAll('.gk-review-stars__star');
		var num = parseInt(value, 10) || 0;
		if (hidden) {
			hidden.value = num >= 1 && num <= 5 ? String(num) : '';
		}
		stars.forEach(function (btn) {
			var dv = parseInt(btn.getAttribute('data-value'), 10);
			var on = num >= 1 && dv <= num;
			btn.classList.toggle('is-selected', on);
			btn.setAttribute('aria-pressed', on ? 'true' : 'false');
		});
		if (hidden && hidden.id === 'gk_rating_general') {
			syncWooRatingFromGeneral(hidden);
		}
	}

	function initStars(starsRoot) {
		if (!starsRoot || starsRoot.getAttribute('data-gk-stars-init') === '1') {
			return;
		}
		starsRoot.setAttribute('data-gk-stars-init', '1');

		starsRoot.addEventListener('click', function (ev) {
			var btn = ev.target.closest('.gk-review-stars__star');
			if (!btn || !starsRoot.contains(btn)) {
				return;
			}
			ev.preventDefault();
			var val = parseInt(btn.getAttribute('data-value'), 10);
			setStarsValue(starsRoot, val);
			validateForm();
		});
	}

	function canSubmit(form) {
		if (!form) {
			return false;
		}
		var general = form.querySelector('#gk_rating_general');
		var rating = form.querySelector('#rating');
		if (rating) {
			var gv = general ? parseInt(general.value, 10) : 0;
			if (gv < 1 || gv > 5) {
				return false;
			}
			if (!rating.value) {
				return false;
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

		form.querySelectorAll('[data-gk-review-stars]').forEach(initStars);
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
