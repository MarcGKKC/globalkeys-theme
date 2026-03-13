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
		var btn = document.getElementById('gk-register-submit');
		if (!btn) return;

		var gamertagInput = document.getElementById('reg_username');
		var emailInput = document.getElementById('reg_email');
		var passwordInput = document.getElementById('reg_password');
		var termsCheckbox = document.getElementById('gk_agree_terms');

		var hasGamertag = gamertagInput && (gamertagInput.value || '').trim().length > 0;
		var hasEmail = emailInput && (emailInput.value || '').trim().length > 0;
		var hasPassword = !passwordInput || (passwordInput.value || '').trim().length > 0;
		var hasTerms = !termsCheckbox || termsCheckbox.checked;

		var shouldEnable = hasGamertag && hasEmail && hasPassword && hasTerms;
		if (shouldEnable) {
			btn.removeAttribute('disabled');
			btn.disabled = false;
		} else {
			btn.disabled = true;
		}
	}

	function init() {
		var modal = document.getElementById('gk-login-error-modal');
		var usernameInput = document.getElementById('username');
		var passwordInput = document.getElementById('password');

		// Login-Fehler: nur Passwort leeren (Username kommt von PHP/Transient wie beim Register)
		var isRegisterError = (typeof gkAccountError !== 'undefined' && gkAccountError.type === 'register') ||
			(modal && modal.getAttribute('data-gk-error-type') === 'register');

		if (passwordInput) {
			passwordInput.value = '';
		}
		if (usernameInput && isRegisterError) {
			usernameInput.value = '';
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

			// Registrieren-Button: alle 200ms prüfen – garantiert Aktivierung
		updateRegisterButtonState();
		setInterval(updateRegisterButtonState, 200);

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
