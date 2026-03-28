/**
 * Game Images: Lightbox mit Thumbnail-Leiste (Klick auf Galerie-Link).
 */
(function () {
	'use strict';

	var MODAL_ID = 'gk-game-images-lightbox';
	var rootSel = '.gk-product-page-game-images__body';
	var linkSel = 'a.gk-product-page-game-images__gallery-link[href]';

	function getI18n(key, fallback) {
		if (typeof window.gkGameImagesLightbox === 'object' && window.gkGameImagesLightbox && window.gkGameImagesLightbox[key]) {
			return String(window.gkGameImagesLightbox[key]);
		}
		return fallback;
	}

	function collectItems(root) {
		var links = Array.prototype.slice.call(root.querySelectorAll(linkSel));
		links.sort(function (a, b) {
			var ia = parseInt(a.getAttribute('data-gk-lightbox-index'), 10);
			var ib = parseInt(b.getAttribute('data-gk-lightbox-index'), 10);
			ia = isNaN(ia) ? 0 : ia;
			ib = isNaN(ib) ? 0 : ib;
			return ia - ib;
		});
		return links.map(function (a) {
			var img = a.querySelector('img');
			var thumb = a.getAttribute('data-gk-lightbox-thumb');
			if (!thumb && img) {
				thumb = img.currentSrc || img.src || '';
			}
			var full = a.getAttribute('data-gk-lightbox-full') || '';
			if (!full) {
				var h = a.getAttribute('href') || '';
				if (h && h !== '#') {
					full = h;
				}
			}
			return {
				full: full,
				thumb: thumb || full,
				alt: img ? img.getAttribute('alt') || '' : '',
			};
		}).filter(function (it) {
			return it.full;
		});
	}

	function ensureModal() {
		var el = document.getElementById(MODAL_ID);
		if (el) {
			return el;
		}
		el = document.createElement('div');
		el.id = MODAL_ID;
		el.className = 'gk-game-images-lightbox';
		el.setAttribute('hidden', '');
		el.innerHTML =
			'<div class="gk-game-images-lightbox__backdrop" tabindex="-1"></div>' +
			'<div class="gk-game-images-lightbox__dialog" role="dialog" aria-modal="true">' +
			'<div class="gk-game-images-lightbox__shell">' +
			'<div class="gk-game-images-lightbox__toolbar">' +
			'<button type="button" class="gk-game-images-lightbox__fs" aria-pressed="false" aria-label="">' +
			'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M7 2H2v5l1.8-1.8L6.5 8 8 6.5 5.2 3.8 7 2zm6 0l1.8 1.8L12 6.5 13.5 8l2.7-2.7L18 7V2h-5zm.5 10L12 13.5l2.7 2.7L13 18h5v-5l-1.8 1.8-2.7-2.8zm-7 0l-2.7 2.7L2 13v5h5l-1.8-1.8L8 13.5 6.5 12z"/></svg>' +
			'</button>' +
			'<button type="button" class="gk-game-images-lightbox__close" aria-label="">' +
			'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' +
			'</button>' +
			'</div>' +
			'<div class="gk-game-images-lightbox__stage">' +
			'<img class="gk-game-images-lightbox__main" src="" alt="" decoding="async" />' +
			'</div>' +
			'<div class="gk-game-images-lightbox__thumbs" role="tablist" aria-label=""></div>' +
			'</div>' +
			'</div>';
		document.body.appendChild(el);
		return el;
	}

	var state = {
		modal: null,
		items: [],
		index: 0,
		previousActive: null,
		onKey: null,
	};

	function setAriaLabels(modal) {
		var dlg = modal.querySelector('.gk-game-images-lightbox__dialog');
		var fs = modal.querySelector('.gk-game-images-lightbox__fs');
		var cl = modal.querySelector('.gk-game-images-lightbox__close');
		var thumbs = modal.querySelector('.gk-game-images-lightbox__thumbs');
		if (dlg) {
			dlg.setAttribute('aria-label', getI18n('i18nDialog', 'Image gallery'));
		}
		if (fs) {
			fs.setAttribute('aria-label', getI18n('i18nFullscreen', 'Fullscreen'));
		}
		if (cl) {
			cl.setAttribute('aria-label', getI18n('i18nClose', 'Close'));
		}
		if (thumbs) {
			thumbs.setAttribute('aria-label', getI18n('i18nThumbs', 'Select image'));
		}
	}

	function renderThumbs(modal) {
		var wrap = modal.querySelector('.gk-game-images-lightbox__thumbs');
		if (!wrap) {
			return;
		}
		wrap.innerHTML = '';
		state.items.forEach(function (it, i) {
			var btn = document.createElement('button');
			btn.type = 'button';
			btn.className = 'gk-game-images-lightbox__thumb';
			btn.setAttribute('role', 'tab');
			btn.setAttribute('aria-selected', i === state.index ? 'true' : 'false');
			btn.setAttribute('data-index', String(i));
			var timg = document.createElement('img');
			timg.src = it.thumb;
			timg.alt = '';
			timg.decoding = 'async';
			timg.loading = 'lazy';
			btn.appendChild(timg);
			wrap.appendChild(btn);
		});
	}

	function updateView(modal) {
		var main = modal.querySelector('.gk-game-images-lightbox__main');
		var it = state.items[state.index];
		if (!main || !it) {
			return;
		}
		main.src = it.full;
		main.alt = it.alt;
		var thumbs = modal.querySelectorAll('.gk-game-images-lightbox__thumb');
		thumbs.forEach(function (btn, i) {
			btn.setAttribute('aria-selected', i === state.index ? 'true' : 'false');
			btn.classList.toggle('is-active', i === state.index);
		});
	}

	function closeModal() {
		var modal = state.modal;
		if (!modal || modal.hasAttribute('hidden')) {
			return;
		}
		modal.setAttribute('hidden', '');
		document.documentElement.classList.remove('gk-game-images-lightbox-open');
		if (state.onKey) {
			document.removeEventListener('keydown', state.onKey);
			state.onKey = null;
		}
		if (state.previousActive && typeof state.previousActive.focus === 'function') {
			try {
				state.previousActive.focus();
			} catch (e) {
				/* ignore */
			}
		}
		state.previousActive = null;
		var shell = modal.querySelector('.gk-game-images-lightbox__shell');
		if (shell && document.fullscreenElement === shell) {
			document.exitFullscreen().catch(function () {});
		}
	}

	function openModal(startIndex) {
		var root = document.querySelector(rootSel);
		if (!root) {
			return;
		}
		var items = collectItems(root);
		if (!items.length) {
			return;
		}
		var modal = ensureModal();
		if (!modal.getAttribute('data-gk-bound')) {
			bindModalEvents(modal);
			modal.setAttribute('data-gk-bound', '1');
		}
		setAriaLabels(modal);
		state.modal = modal;
		state.items = items;
		state.index = Math.max(0, Math.min(startIndex, items.length - 1));
		state.previousActive = document.activeElement;

		renderThumbs(modal);
		updateView(modal);

		modal.removeAttribute('hidden');
		document.documentElement.classList.add('gk-game-images-lightbox-open');

		var closeBtn = modal.querySelector('.gk-game-images-lightbox__close');
		if (closeBtn) {
			closeBtn.focus();
		}

		if (!state.onKey) {
			state.onKey = function (e) {
				if (e.key === 'Escape') {
					e.preventDefault();
					closeModal();
					return;
				}
				if (state.items.length < 2) {
					return;
				}
				if (e.key === 'ArrowRight') {
					e.preventDefault();
					state.index = (state.index + 1) % state.items.length;
					updateView(modal);
				} else if (e.key === 'ArrowLeft') {
					e.preventDefault();
					state.index = (state.index - 1 + state.items.length) % state.items.length;
					updateView(modal);
				}
			};
			document.addEventListener('keydown', state.onKey);
		}
	}

	function bindModalEvents(modal) {
		var backdrop = modal.querySelector('.gk-game-images-lightbox__backdrop');
		if (backdrop) {
			backdrop.addEventListener('click', closeModal);
		}
		var closeBtn = modal.querySelector('.gk-game-images-lightbox__close');
		if (closeBtn) {
			closeBtn.addEventListener('click', closeModal);
		}
		var shell = modal.querySelector('.gk-game-images-lightbox__shell');
		var fsBtn = modal.querySelector('.gk-game-images-lightbox__fs');
		if (fsBtn && shell) {
			fsBtn.addEventListener('click', function () {
				if (document.fullscreenElement === shell) {
					document.exitFullscreen().then(function () {
						fsBtn.setAttribute('aria-pressed', 'false');
						fsBtn.setAttribute('aria-label', getI18n('i18nFullscreen', 'Fullscreen'));
					}).catch(function () {});
				} else {
					shell.requestFullscreen().then(function () {
						fsBtn.setAttribute('aria-pressed', 'true');
						fsBtn.setAttribute('aria-label', getI18n('i18nExitFullscreen', 'Exit fullscreen'));
					}).catch(function () {});
				}
			});
			document.addEventListener('fullscreenchange', function () {
				if (document.fullscreenElement !== shell && fsBtn) {
					fsBtn.setAttribute('aria-pressed', 'false');
					fsBtn.setAttribute('aria-label', getI18n('i18nFullscreen', 'Fullscreen'));
				}
			});
		}
		var thumbsWrap = modal.querySelector('.gk-game-images-lightbox__thumbs');
		if (thumbsWrap) {
			thumbsWrap.addEventListener('click', function (e) {
				var btn = e.target.closest('.gk-game-images-lightbox__thumb');
				if (!btn) {
					return;
				}
				var i = parseInt(btn.getAttribute('data-index'), 10);
				if (!isNaN(i) && i >= 0 && i < state.items.length) {
					state.index = i;
					updateView(modal);
				}
			});
		}
	}

	function onRootClick(e) {
		var a = e.target.closest(linkSel);
		if (!a) {
			return;
		}
		var full = a.getAttribute('data-gk-lightbox-full') || '';
		var href = a.getAttribute('href') || '';
		if (!full && (!href || href === '#')) {
			return;
		}
		var root = a.closest(rootSel);
		if (!root) {
			return;
		}
		e.preventDefault();
		var idx = parseInt(a.getAttribute('data-gk-lightbox-index'), 10);
		if (isNaN(idx)) {
			idx = 0;
		}
		openModal(idx);
	}

	function init() {
		if (!document.querySelector(rootSel)) {
			return;
		}
		document.querySelectorAll(rootSel).forEach(function (root) {
			root.addEventListener('click', onRootClick);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
