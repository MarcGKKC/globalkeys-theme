/**
 * Login / Register: Nur ein Kasten sichtbar, per Klick wechseln.
 * Passwort ein-/ausblenden (Login + Register).
 */
(function() {
	'use strict';

	var blocks = document.querySelector('.gk-account-blocks');

	// Passwort ein-/ausblenden – global für Inline-onclick (Login + Register)
	window.gkTogglePassword = function(targetId) {
		var input = document.getElementById(targetId);
		if (!input) return;
		var wrap = input.closest('.gk-password-input-wrap');
		if (!wrap) return;
		var toggle = wrap.querySelector('.gk-password-toggle');
		var openIcon = toggle && toggle.querySelector('.gk-eye-open');
		var closedIcon = toggle && toggle.querySelector('.gk-eye-closed');
		if (!toggle || !openIcon || !closedIcon) return;
		var isPass = input.type === 'password';
		input.type = isPass ? 'text' : 'password';
		openIcon.style.display = isPass ? 'block' : 'none';
		closedIcon.style.display = isPass ? 'none' : 'block';
	};

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
})();
