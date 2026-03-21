/**
 * Bestseller-Karten: Preview-Video bei Hover.
 * Nutzt play()-Promise (zuverlässiger als nur „playing“); muted per JS für Autoplay-Policy.
 * Videovorschau aus Drawer: wenn aus, nur Produktbild, kein Video.
 */
(function () {
	'use strict';

	function isVideovorschauEnabled() {
		try {
			return localStorage.getItem('gk_videovorschau') !== '0';
		} catch (e) {
			return true;
		}
	}

	var sections = document.querySelectorAll('.gk-section-bestsellers');
	if (!sections.length) {
		return;
	}

	function revealWrap(wrap) {
		wrap.classList.add('is-trailer-playing');
	}

	function hideWrap(wrap) {
		wrap.classList.remove('is-trailer-playing');
	}

	function initSection(section) {
	section.querySelectorAll('.gk-featured-product-image.has-trailer').forEach(function (wrap) {
		var video = wrap.querySelector('video.gk-bestseller-trailer');
		if (!video || !video.getAttribute('src')) {
			return;
		}

		video.addEventListener('error', function () {
			hideWrap(wrap);
		});

		wrap.addEventListener(
			'mouseenter',
			function () {
				if (!isVideovorschauEnabled()) {
					return;
				}
				video.muted = true;
				video.setAttribute('playsinline', '');
				video.setAttribute('webkit-playsinline', '');

				var p = video.play();
				if (p !== undefined && typeof p.then === 'function') {
					p.then(function () {
						revealWrap(wrap);
					}).catch(function () {
						hideWrap(wrap);
					});
				} else {
					video.addEventListener(
						'playing',
						function onPlaying() {
							video.removeEventListener('playing', onPlaying);
							revealWrap(wrap);
						}
					);
					try {
						video.play();
					} catch (err) {
						hideWrap(wrap);
					}
				}
			},
			false
		);

		wrap.addEventListener('mouseleave', function () {
			hideWrap(wrap);
			video.pause();
			try {
				video.currentTime = 0;
			} catch (e) {}
		});
	});
	}

	sections.forEach(initSection);

	document.addEventListener('gk-videovorschau-change', function (e) {
		if (e.detail && !e.detail.enabled) {
			sections.forEach(function (section) {
				section.querySelectorAll('.gk-featured-product-image.has-trailer.is-trailer-playing').forEach(function (wrap) {
				var v = wrap.querySelector('video.gk-bestseller-trailer');
				if (v) {
					v.pause();
					try { v.currentTime = 0; } catch (err) {}
				}
				hideWrap(wrap);
			});
			});
		}
	});
})();
