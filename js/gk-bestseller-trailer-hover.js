/**
 * Bestseller-Karten: Preview-Video bei Hover.
 * Chromium/Edge: defaultMuted, playsInline (Property), load()+canplay wenn nötig, play()-Retry.
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

	function videoHasSrc(video) {
		if (video.currentSrc) {
			return true;
		}
		if (video.getAttribute('src')) {
			return true;
		}
		var source = video.querySelector('source');
		return !!(source && source.getAttribute('src'));
	}

	var sections = document.querySelectorAll('.gk-section-bestsellers');
	if (!sections.length) {
		return;
	}

	function revealWrap(wrap) {
		wrap.classList.add('is-trailer-playing');
		// Chromium/Edge: Layout-Read triggert ggf. fehlenden Repaint nach Opacity-Wechsel
		void wrap.offsetHeight;
	}

	function hideWrap(wrap) {
		wrap.classList.remove('is-trailer-playing');
	}

	function prepareVideoForAutoplay(video) {
		video.defaultMuted = true;
		video.muted = true;
		if ('playsInline' in video) {
			video.playsInline = true;
		}
		video.setAttribute('muted', 'muted');
		video.setAttribute('playsinline', '');
		video.setAttribute('webkit-playsinline', '');
	}

	function initSection(section) {
		section.querySelectorAll('.gk-featured-product-image.has-trailer').forEach(function (wrap) {
			var video = wrap.querySelector('video.gk-bestseller-trailer');
			if (!video || !videoHasSrc(video)) {
				return;
			}

			wrap._gkTrailerToken = wrap._gkTrailerToken || 0;

			video.addEventListener('error', function () {
				hideWrap(wrap);
			});

			wrap.addEventListener('mouseenter', function () {
				if (!isVideovorschauEnabled()) {
					return;
				}

				var myToken = ++wrap._gkTrailerToken;
				function stillActive() {
					return myToken === wrap._gkTrailerToken;
				}

				prepareVideoForAutoplay(video);

				function attemptPlay(isRetry) {
					var p = video.play();
					if (p !== undefined && typeof p.then === 'function') {
						p.then(function () {
							if (stillActive()) {
								revealWrap(wrap);
							}
						}).catch(function () {
							if (!stillActive()) {
								return;
							}
							if (!isRetry) {
								try {
									video.load();
								} catch (e) {}
								var onReady = function () {
									video.removeEventListener('canplay', onReady);
									video.removeEventListener('loadeddata', onReady);
									if (stillActive()) {
										attemptPlay(true);
									}
								};
								video.addEventListener('canplay', onReady);
								video.addEventListener('loadeddata', onReady);
								return;
							}
							hideWrap(wrap);
						});
					} else {
						try {
							video.play();
							if (stillActive()) {
								revealWrap(wrap);
							}
						} catch (err) {
							hideWrap(wrap);
						}
					}
				}

				if (video.readyState >= 2) {
					attemptPlay(false);
				} else {
					var onCanStart = function () {
						video.removeEventListener('canplay', onCanStart);
						video.removeEventListener('loadeddata', onCanStart);
						if (stillActive()) {
							attemptPlay(false);
						}
					};
					video.addEventListener('canplay', onCanStart);
					video.addEventListener('loadeddata', onCanStart);
					try {
						video.load();
					} catch (e) {
						attemptPlay(false);
					}
				}
			}, false);

			wrap.addEventListener('mouseleave', function () {
				wrap._gkTrailerToken++;
				hideWrap(wrap);
				video.pause();
				try {
					video.currentTime = 0;
				} catch (e) {}
			});
		});
	}

	sections.forEach(initSection);

	document.body.addEventListener('gk_search_results_updated', function () {
		var searchSection = document.getElementById('gk-search-results-grid') || document.querySelector('.gk-section-shop-results');
		if (searchSection) {
			initSection(searchSection);
		}
	});

	document.addEventListener('gk-videovorschau-change', function (e) {
		if (e.detail && !e.detail.enabled) {
			sections.forEach(function (section) {
				section.querySelectorAll('.gk-featured-product-image.has-trailer.is-trailer-playing').forEach(function (wrap) {
					var v = wrap.querySelector('video.gk-bestseller-trailer');
					if (v) {
						v.pause();
						try {
							v.currentTime = 0;
						} catch (err) {}
					}
					hideWrap(wrap);
				});
			});
		}
	});
})();
