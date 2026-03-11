/**
 * Live-Prüfung: Gamertag und E-Mail bereits vergeben?
 */
(function() {
	'use strict';

	var config = typeof globalkeysGamertagCheck !== 'undefined' ? globalkeysGamertagCheck : { ajaxUrl: '', takenMessage: '', emailMessage: '' };

	function setupLiveCheck(inputId, errorId, action, paramName, takenMessage, validate) {
		var input = document.getElementById(inputId);
		var errorEl = document.getElementById(errorId);
		if (!input || !errorEl) return;

		validate = validate || function(val) { return true; };
		var timeout;
		var lastChecked = '';

		function showError(msg) {
			errorEl.textContent = msg;
			errorEl.style.display = 'block';
			errorEl.classList.add('gk-gamertag-error--visible');
			input.setAttribute('aria-invalid', 'true');
		}

		function hideError() {
			errorEl.textContent = '';
			errorEl.style.display = 'none';
			errorEl.classList.remove('gk-gamertag-error--visible');
			input.removeAttribute('aria-invalid');
		}

		function check() {
			var value = (input.value || '').trim();
			if (value === '') {
				hideError();
				return;
			}
			if (!validate(value)) {
				hideError();
				return;
			}
			if (value === lastChecked) return;
			lastChecked = value;

			var formData = new FormData();
			formData.append('action', action);
			formData.append(paramName, value);

			fetch(config.ajaxUrl, {
				method: 'POST',
				body: formData,
				credentials: 'same-origin'
			})
			.then(function(r) { return r.json(); })
			.then(function(data) {
				if (data.taken) {
					showError(takenMessage);
				} else {
					hideError();
				}
			})
			.catch(function() {
				hideError();
			});
		}

		input.addEventListener('input', function() {
			clearTimeout(timeout);
			var value = (input.value || '').trim();
			if (value === '' || !validate(value)) {
				hideError();
				if (value === '') lastChecked = '';
				return;
			}
			timeout = setTimeout(check, 400);
		});

		input.addEventListener('blur', function() {
			clearTimeout(timeout);
			var value = (input.value || '').trim();
			if (value !== '' && validate(value)) {
				check();
			} else {
				hideError();
			}
		});
	}

	setupLiveCheck('reg_username', 'gk-gamertag-error', 'globalkeys_check_gamertag', 'gamertag', config.takenMessage || 'Dieser Gamertag wird bereits verwendet.');
	setupLiveCheck('reg_email', 'gk-email-error', 'globalkeys_check_email', 'email', config.emailMessage || 'Diese E-Mail-Adresse wird bereits verwendet.', function(val) {
		return val.indexOf('@') > 0 && val.indexOf('.') > val.indexOf('@');
	});
})();
