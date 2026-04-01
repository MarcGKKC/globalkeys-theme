/**
 * Wunschliste: Kaufkarten-Herz (Toggle), Gast: localStorage; Wishlist-Seite Gast: Karten nachladen.
 */
(function () {
	'use strict';
	var cfg = typeof gkWishlist === 'undefined' ? null : gkWishlist;
	if (!cfg) {
		return;
	}

	var LS_KEY = cfg.lsKey || 'gk_wishlist_product_ids';

	function getWishlistEmptyHtml() {
		if (cfg.isLoggedIn) {
			return (
				cfg.emptyStateHtmlOwner ||
				'<p class="gk-wishlist__empty">' + (cfg.ownerEmpty || '') + '</p>'
			);
		}
		return (
			cfg.emptyStateHtmlGuest ||
			'<p class="gk-wishlist__empty">' + (cfg.guestEmpty || '') + '</p>'
		);
	}

	function readLsIds() {
		try {
			var raw = localStorage.getItem(LS_KEY);
			if (!raw) {
				return [];
			}
			var a = JSON.parse(raw);
			if (!Array.isArray(a)) {
				return [];
			}
			return a
				.map(function (n) {
					return parseInt(n, 10);
				})
				.filter(function (n) {
					return n > 0;
				});
		} catch (e) {
			return [];
		}
	}

	function writeLsIds(ids) {
		localStorage.setItem(LS_KEY, JSON.stringify(ids));
	}

	function toggleLs(productId) {
		var ids = readLsIds();
		var i = ids.indexOf(productId);
		if (i >= 0) {
			ids.splice(i, 1);
			writeLsIds(ids);
			return false;
		}
		ids.push(productId);
		writeLsIds(ids);
		return true;
	}

	function guestHas(productId) {
		return readLsIds().indexOf(productId) >= 0;
	}

	function bindWishlistButtons() {
		/* Eingeloggt: Link enthält ?gk_wl_add=… — Server speichert beim Aufruf der Wishlist (kein JS nötig). */
		if (cfg.isLoggedIn) {
			return;
		}
		document.body.addEventListener('click', function (e) {
			var el = e.target.closest('a.gk-purchase-card__wishlist[data-product-id]');
			if (!el) {
				return;
			}
			var raw = el.getAttribute('data-product-id');
			var pid = raw ? parseInt(raw, 10) : 0;
			if (!pid) {
				return;
			}
			e.preventDefault();
			var nowIn = toggleLs(pid);
			el.setAttribute('aria-pressed', nowIn ? 'true' : 'false');
		});
	}

	function syncGuestAriaProductPage() {
		if (cfg.isWishlistPage || cfg.isLoggedIn || !cfg.productId) {
			return;
		}
		var el = document.querySelector(
			'a.gk-purchase-card__wishlist[data-product-id="' + String(cfg.productId) + '"]'
		);
		if (el) {
			el.setAttribute('aria-pressed', guestHas(cfg.productId) ? 'true' : 'false');
		}
	}

	function bindWishlistRowRemove() {
		if (!cfg.isLoggedIn || !cfg.isWishlistPage || !cfg.nonce) {
			return;
		}
		document.body.addEventListener('click', function (e) {
			var btn = e.target.closest('.gk-wishlist-row__remove-btn');
			if (!btn) {
				return;
			}
			var strip = btn.closest('.gk-wishlist-row__wishlist-strip');
			var row = btn.closest('.gk-wishlist-row');
			var pid = strip ? parseInt(strip.getAttribute('data-product-id'), 10) : 0;
			if (!row || !pid) {
				return;
			}
			e.preventDefault();
			btn.disabled = true;
			fetch(cfg.restUrl + 'wishlist/toggle', {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': cfg.nonce
				},
				body: JSON.stringify({ product_id: pid })
			})
				.then(function (r) {
					if (!r.ok) {
						throw new Error('toggle failed');
					}
					return r.json();
				})
				.then(function () {
					if (row.parentNode) {
						row.parentNode.removeChild(row);
					}
					applyWishlistToolbarSearch();
					updateWishlistProductCount();
					var ul = document.querySelector('#gk-wishlist-products .gk-wishlist__rows');
					if (ul && ul.querySelectorAll('li.gk-wishlist-row').length === 0) {
						var wrap = document.getElementById('gk-wishlist-products');
						if (wrap) {
							wrap.innerHTML = getWishlistEmptyHtml();
							updateWishlistProductCount();
						}
					}
				})
				.catch(function () {
					btn.disabled = false;
				});
		});
	}

	function updateWishlistProductCount() {
		var el = document.getElementById('gk-wl-product-count');
		if (!el) {
			return;
		}
		var ul = document.querySelector('#gk-wishlist-products .gk-wishlist__rows');
		var n = ul ? ul.querySelectorAll('li.gk-wishlist-row').length : 0;
		var labels = cfg.countLabels;
		if (labels && labels.zero && labels.one && labels.many) {
			if (n === 0) {
				el.textContent = labels.zero;
			} else if (n === 1) {
				el.textContent = labels.one;
			} else {
				el.textContent = labels.many.replace('%d', String(n));
			}
		} else {
			el.textContent = String(n);
		}
	}

	function initWishlistProductCountDisplay() {
		if (!cfg.isWishlistPage) {
			return;
		}
		if (!cfg.isLoggedIn && readLsIds().length > 0) {
			return;
		}
		updateWishlistProductCount();
	}

	function loadGuestWishlistGrid() {
		if (!cfg.isWishlistPage || cfg.isLoggedIn) {
			return;
		}
		var wrap = document.getElementById('gk-wishlist-products');
		if (!wrap) {
			return;
		}
		var ids = readLsIds();
		if (ids.length === 0) {
			wrap.innerHTML = getWishlistEmptyHtml();
			updateWishlistProductCount();
			return;
		}
		var url = cfg.restUrl + 'wishlist/catalog?ids=' + encodeURIComponent(ids.join(','));
		fetch(url, { credentials: 'same-origin' })
			.then(function (r) {
				return r.json();
			})
			.then(function (data) {
				if (data && data.html && data.html.replace(/\s/g, '') !== '') {
					wrap.innerHTML = data.html;
					initWishlistGamepicsSlideshow(wrap);
					applyWishlistToolbarSearch();
					updateWishlistProductCount();
				} else {
					wrap.innerHTML = getWishlistEmptyHtml();
					updateWishlistProductCount();
				}
			})
			.catch(function () {
				wrap.innerHTML = getWishlistEmptyHtml();
				updateWishlistProductCount();
			});
	}

	function prefersReducedMotion() {
		return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	}

	/**
	 * Wishlist-Thumbnails: Gamepics nacheinander, nur sichtbares Bild (opacity), Wechsel alle 1,5 s, kein Slide.
	 */
	/**
	 * Toolbar-Suche: Titel (inkl. Treffer ab 2. Titbuchstabe), ab 3 Zeichen zusätzlich SKU/Tag (Prefix).
	 */
	function parseWlSearchPayload(li) {
		var raw = li.getAttribute('data-gk-wl-search');
		if (!raw) {
			return { n: '', s: '', t: [] };
		}
		try {
			var o = JSON.parse(raw);
			return {
				n: typeof o.n === 'string' ? o.n : '',
				s: typeof o.s === 'string' ? o.s : '',
				t: Array.isArray(o.t) ? o.t : []
			};
		} catch (e) {
			return { n: '', s: '', t: [] };
		}
	}

	function wlStartsWith(term, hay) {
		return hay && hay.indexOf(term) === 0;
	}

	/** Nur Titel: normal ab Anfang, zusätzlich Treffer wenn der Begriff ab dem 2. Buchstaben des Titels „beginnt“. */
	function wlTitleMatches(term, name) {
		if (!term || !name) {
			return false;
		}
		if (wlStartsWith(term, name)) {
			return true;
		}
		return name.length >= 2 && wlStartsWith(term, name.substring(1));
	}

	function wlRowMatches(term, data) {
		if (!term) {
			return true;
		}
		if (wlTitleMatches(term, data.n)) {
			return true;
		}
		/* SKU + Tags erst ab 3 Zeichen: „re“ soll z. B. nicht „Realistic“ / kurze SKU-Prefixe triggern. */
		if (term.length < 3) {
			return false;
		}
		if (wlStartsWith(term, data.s)) {
			return true;
		}
		var tags = data.t;
		for (var i = 0; i < tags.length; i++) {
			if (wlStartsWith(term, tags[i])) {
				return true;
			}
		}
		return false;
	}

	var SORT_MODES = {
		order: 1,
		'price-asc': 1,
		'price-desc': 1,
		'release-desc': 1,
		'added-desc': 1,
		'name-asc': 1
	};

	function wlNumAttr(li, name) {
		var v = parseFloat(li.getAttribute(name));
		return isNaN(v) ? 0 : v;
	}

	function wlIntAttr(li, name) {
		var v = parseInt(li.getAttribute(name), 10);
		return isNaN(v) ? 0 : v;
	}

	function wlStrAttr(li, name) {
		var s = li.getAttribute(name);
		return s ? s.toLowerCase() : '';
	}

	function wlCmpOrder(a, b) {
		return wlIntAttr(a, 'data-gk-wl-order') - wlIntAttr(b, 'data-gk-wl-order');
	}

	function wlLocaleCompare(a, b) {
		try {
			return a.localeCompare(b, 'de', { sensitivity: 'base' });
		} catch (e) {
			return a < b ? -1 : a > b ? 1 : 0;
		}
	}

	/**
	 * Sortiert Zeilen in .gk-wishlist__rows anhand data-gk-wl-*; Gäste ohne Zeitstempel: „Hinzugefügt“ nutzt die Listenposition.
	 */
	function applyWishlistSort(mode, labelText) {
		var ul = document.querySelector('#gk-wishlist-products .gk-wishlist__rows');
		if (!ul) {
			return;
		}
		var m = mode && SORT_MODES[mode] ? mode : 'order';
		var rows = Array.prototype.slice.call(ul.querySelectorAll('li.gk-wishlist-row'));

		function tie(a, b) {
			return wlCmpOrder(a, b);
		}

		rows.sort(function (a, b) {
			var c;
			switch (m) {
				case 'order':
					return tie(a, b);
				case 'price-asc':
					c = wlNumAttr(a, 'data-gk-wl-price') - wlNumAttr(b, 'data-gk-wl-price');
					return c !== 0 ? c : tie(a, b);
				case 'price-desc':
					c = wlNumAttr(b, 'data-gk-wl-price') - wlNumAttr(a, 'data-gk-wl-price');
					return c !== 0 ? c : tie(a, b);
				case 'release-desc':
					c = wlIntAttr(b, 'data-gk-wl-release') - wlIntAttr(a, 'data-gk-wl-release');
					return c !== 0 ? c : tie(a, b);
				case 'added-desc': {
					var aa = wlIntAttr(a, 'data-gk-wl-added');
					var bb = wlIntAttr(b, 'data-gk-wl-added');
					if (aa !== bb) {
						return bb - aa;
					}
					return wlIntAttr(b, 'data-gk-wl-order') - wlIntAttr(a, 'data-gk-wl-order');
				}
				case 'name-asc':
					c = wlLocaleCompare(wlStrAttr(a, 'data-gk-wl-title'), wlStrAttr(b, 'data-gk-wl-title'));
					return c !== 0 ? c : tie(a, b);
				default:
					return tie(a, b);
			}
		});

		for (var i = 0; i < rows.length; i++) {
			ul.appendChild(rows[i]);
		}

		var valEl = document.querySelector('.gk-wishlist__tb-sort-value');
		if (valEl && labelText) {
			valEl.textContent = labelText;
		} else if (valEl) {
			var opt = document.querySelector('#gk-wl-sort-menu [data-gk-wl-sort="' + m + '"]');
			if (opt && opt.textContent) {
				valEl.textContent = opt.textContent.trim();
			}
		}

		applyWishlistToolbarSearch();
	}

	function bindWishlistSortMenu() {
		if (!cfg.isWishlistPage) {
			return;
		}
		var wrap = document.getElementById('gk-wl-sort-wrap');
		var menu = document.getElementById('gk-wl-sort-menu');
		var toggle = document.getElementById('gk-wl-sort-toggle');
		if (!wrap || !menu || !toggle) {
			return;
		}

		function setOpen(open) {
			menu.hidden = !open;
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			if (open) {
				wrap.classList.add('is-open');
			} else {
				wrap.classList.remove('is-open');
			}
		}

		function menuIsOpen() {
			return !menu.hidden;
		}

		document.body.addEventListener('click', function (e) {
			var t = e.target;
			if (wrap.contains(t)) {
				if (toggle.contains(t) || t === toggle) {
					e.preventDefault();
					setOpen(!menuIsOpen());
					return;
				}
				var opt = t.closest ? t.closest('[data-gk-wl-sort]') : null;
				if (opt && menu.contains(opt)) {
					e.preventDefault();
					var mode = opt.getAttribute('data-gk-wl-sort');
					var lbl = (opt.textContent || '').trim();
					applyWishlistSort(mode, lbl);
					setOpen(false);
					toggle.focus();
					return;
				}
			}
			if (menuIsOpen()) {
				setOpen(false);
			}
		});

		document.addEventListener('keydown', function (e) {
			if (e.key !== 'Escape' || !menuIsOpen()) {
				return;
			}
			setOpen(false);
			toggle.focus();
		});
	}

	function applyWishlistToolbarSearch() {
		var input = document.getElementById('gk-wishlist-search');
		var ul = document.querySelector('#gk-wishlist-products .gk-wishlist__rows');
		if (!input || !ul) {
			return;
		}
		var term = (input.value || '').trim().toLowerCase();
		var rows = ul.querySelectorAll('li.gk-wishlist-row');
		var visible = 0;
		for (var i = 0; i < rows.length; i++) {
			var ok = wlRowMatches(term, parseWlSearchPayload(rows[i]));
			rows[i].hidden = !ok;
			rows[i].setAttribute('aria-hidden', ok ? 'false' : 'true');
			if (ok) {
				visible++;
			}
		}
		var noEl = document.getElementById('gk-wishlist-search-no-results');
		if (noEl) {
			noEl.hidden = !(term !== '' && visible === 0 && rows.length > 0);
		}
	}

	function bindWishlistToolbarSearch() {
		var input = document.getElementById('gk-wishlist-search');
		if (!input || !cfg.isWishlistPage) {
			return;
		}
		var run = function () {
			applyWishlistToolbarSearch();
		};
		input.addEventListener('input', run);
		input.addEventListener('search', run);
		run();
	}

	function initWishlistGamepicsSlideshow(rootEl) {
		if (!rootEl || !rootEl.querySelectorAll) {
			return;
		}
		var thumbs = rootEl.querySelectorAll('.gk-wishlist-row__thumb--gamepics:not([data-gk-gamepics-init])');
		var STEP_MS = 1500;

		for (var t = 0; t < thumbs.length; t++) {
			(function (thumb) {
				thumb.setAttribute('data-gk-gamepics-init', '1');
				var stack = thumb.querySelector('.gk-wishlist-row__gamepics-hover');
				if (!stack) {
					return;
				}
				var imgs = stack.querySelectorAll('.gk-wishlist-row__gamepic-img');
				if (imgs.length < 2) {
					return;
				}

				var timer = null;
				var idx = 0;

				function setActive(n) {
					for (var j = 0; j < imgs.length; j++) {
						if (j === n) {
							imgs[j].classList.add('is-active');
						} else {
							imgs[j].classList.remove('is-active');
						}
					}
				}

				function tick() {
					idx = (idx + 1) % imgs.length;
					setActive(idx);
				}

				function start() {
					if (prefersReducedMotion()) {
						setActive(0);
						return;
					}
					stop();
					idx = 0;
					setActive(0);
					timer = window.setInterval(tick, STEP_MS);
				}

				function stop() {
					if (timer !== null) {
						window.clearInterval(timer);
						timer = null;
					}
					idx = 0;
					setActive(0);
				}

				thumb.addEventListener('mouseenter', start);
				thumb.addEventListener('mouseleave', stop);
				thumb.addEventListener('focusin', start);
				thumb.addEventListener('focusout', stop);
			})(thumbs[t]);
		}
	}

	document.addEventListener('DOMContentLoaded', function () {
		syncGuestAriaProductPage();
		bindWishlistButtons();
		bindWishlistRowRemove();
		bindWishlistSortMenu();
		bindWishlistToolbarSearch();
		initWishlistProductCountDisplay();
		initWishlistGamepicsSlideshow(document.body);
		loadGuestWishlistGrid();
	});
})();
