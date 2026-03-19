/**
 * Account-Tabs: AJAX-Navigation – kein vollständiger Reload, Scroll bleibt exakt.
 *
 * @package globalkeys
 */
(function () {
	'use strict';

	var contentSelector = '.gk-accdash__content';
	var tabsSelector = '.gk-accdash__tabs';

	function init() {
		var tabs = document.querySelectorAll('.gk-accdash__tab');
		var contentEl = document.querySelector(contentSelector);
		if (!tabs.length || !contentEl) return;

		tabs.forEach(function (tab) {
			tab.addEventListener('click', function (e) {
				if (tab.getAttribute('aria-current') === 'page') return;
				var href = tab.getAttribute('href');
				if (!href || href === '#' || href.indexOf('customer-logout') !== -1) return;

				e.preventDefault();

				fetch(href, { credentials: 'same-origin' })
					.then(function (r) { return r.text(); })
					.then(function (html) {
						var wrap = document.createElement('div');
						wrap.innerHTML = html;
						var newContent = wrap.querySelector(contentSelector);
						if (newContent) {
							contentEl.innerHTML = newContent.innerHTML;
						}

						document.querySelectorAll('.gk-accdash__tab').forEach(function (t) {
							t.classList.remove('is-active');
							t.removeAttribute('aria-current');
							if (t.getAttribute('href') === href) {
								t.classList.add('is-active');
								t.setAttribute('aria-current', 'page');
							}
						});

						history.pushState({ url: href }, '', href);
					})
					.catch(function () {
						window.location.href = href;
					});
			});
		});

		window.addEventListener('popstate', function () {
			window.location.reload();
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
