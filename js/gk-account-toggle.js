/**
 * Login / Register: Nur ein Kasten sichtbar, per Klick wechseln.
 */
(function() {
	'use strict';

	var blocks = document.querySelector('.gk-account-blocks');
	if (!blocks) return;

	function showView(view) {
		if (view === 'register') {
			blocks.classList.add('gk-show-register');
			history.replaceState(null, '', '#register');
		} else {
			blocks.classList.remove('gk-show-register');
			history.replaceState(null, '', '#login');
		}
	}

	function initFromHash() {
		if (window.location.hash === '#register') {
			blocks.classList.add('gk-show-register');
		} else {
			blocks.classList.remove('gk-show-register');
		}
	}

	// Klicks auf "Noch keinen Account?" und "Bereits Account? Anmelden"
	blocks.addEventListener('click', function(e) {
		var link = e.target.closest('[data-gk-view]');
		if (!link) return;
		e.preventDefault();
		showView(link.getAttribute('data-gk-view'));
	});

	// Beim Laden: Hash auswerten (z.B. Direktlink zu #register)
	initFromHash();

	// Bei Hash-Änderung (z.B. Browser Zurück)
	window.addEventListener('hashchange', initFromHash);

	// Passwort ein-/ausblenden
	var toggle = document.querySelector('.gk-password-toggle');
	if (toggle) {
		var input = document.getElementById(toggle.getAttribute('data-target'));
		var openIcon = toggle.querySelector('.gk-eye-open');
		var closedIcon = toggle.querySelector('.gk-eye-closed');
		if (input && openIcon && closedIcon) {
			toggle.addEventListener('click', function() {
				var isPass = input.type === 'password';
				input.type = isPass ? 'text' : 'password';
				openIcon.style.display = isPass ? 'none' : 'block';
				closedIcon.style.display = isPass ? 'block' : 'none';
			});
		}
	}
})();
