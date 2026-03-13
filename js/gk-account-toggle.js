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
})();
