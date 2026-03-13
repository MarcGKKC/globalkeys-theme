/**
 * Login-Fehlermodal: Schließen und Formular leeren.
 * Anmelden-Button: Aktivierung wenn E-Mail und Passwort ausgefüllt.
 * Registrieren-Button: Aktivierung wenn Gamertag, E-Mail, Passwort und Terms-Checkbox ausgefüllt.
 */
(function () {
	'use strict';

	function updateLoginButtonState() {
		var btn = document.querySelector('.woocommerce-form-login .gk-btn-login');
		var usernameInput = document.getElementById('username');
		var passwordInput = document.getElementById('password');

		if (!btn || !usernameInput || !passwordInput) {
			return;
		}

		var hasUsername = (usernameInput.value || '').trim().length > 0;
		var hasPassword = (passwordInput.value || '').trim().length > 0;

		btn.disabled = !(hasUsername && hasPassword);
	}

	function updateRegisterButtonState() {
		var form = document.querySelector('.woocommerce-form-register');
		if (!form) return;

		var btn = form.querySelector('.gk-btn-login.woocommerce-form-register__submit');
		var gamertagInput = document.getElementById('reg_username');
		var emailInput = document.getElementById('reg_email');
		var passwordInput = document.getElementById('reg_password');
		var termsCheckbox = document.getElementById('gk_agree_terms');

		if (!btn) return;

		var hasGamertag = gamertagInput && (gamertagInput.value || '').trim().length > 0;
		var hasEmail = emailInput && (emailInput.value || '').trim().length > 0;
		var hasPassword = !passwordInput || (passwordInput.value || '').trim().length > 0;
		var hasTerms = !termsCheckbox || termsCheckbox.checked;

		btn.disabled = !(hasGamertag && hasEmail && hasPassword && hasTerms);
	}

	function init() {
		var modal = document.getElementById('gk-login-error-modal');
		var usernameInput = document.getElementById('username');
		var passwordInput = document.getElementById('password');

		// Formularfelder bei jedem Laden leeren (z.B. nach Refresh)
		if (usernameInput) {
			usernameInput.value = '';
		}
		if (passwordInput) {
			passwordInput.value = '';
		}

		// Anmelden-Button: Aktivierung bei Eingabe
		updateLoginButtonState();
		if (usernameInput) {
			usernameInput.addEventListener('input', updateLoginButtonState);
			usernameInput.addEventListener('change', updateLoginButtonState);
		}
		if (passwordInput) {
			passwordInput.addEventListener('input', updateLoginButtonState);
			passwordInput.addEventListener('change', updateLoginButtonState);
		}

		// Registrieren-Button: Aktivierung wenn alle Pflichtfelder ausgefüllt
		var regForm = document.querySelector('.woocommerce-form-register');
		if (regForm) {
			updateRegisterButtonState();
			['reg_username', 'reg_email', 'reg_password', 'gk_agree_terms'].forEach(function (id) {
				var el = document.getElementById(id);
				if (el) {
					el.addEventListener('input', updateRegisterButtonState);
					el.addEventListener('change', updateRegisterButtonState);
				}
			});
		}

		if (!modal) {
			return;
		}

		function closeModal() {
			modal.classList.remove('gk-login-error-modal--visible');
		}

		modal.querySelectorAll('.gk-login-error-modal__close, .gk-login-error-modal__ok, .gk-login-error-modal__backdrop').forEach(function (el) {
			el.addEventListener('click', closeModal);
		});

		modal.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') {
				closeModal();
			}
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
