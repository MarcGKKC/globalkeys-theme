/**
 * PC Plattform: Featured-Game-Carousel
 * Timer = Trailer-Länge des aktuellen Spiels. Auto-Switch per Video-Ende oder Fallback-Timer.
 * Dots wechseln die Karten per Klick.
 */
(function () {
	'use strict';

	function run() {
		var carousels = document.querySelectorAll('.gk-platform-featured-carousel');
		if (!carousels.length) {
			return;
		}

	var FALLBACK_MS = 5000;
	var MIN_MS = 2000;
	var MAX_MS = 120000;

	function getDurationFromVideo(video, fallbackMs, cb) {
		if (!video) {
			cb(fallbackMs);
			return;
		}
		function apply() {
			var d = video.duration;
			if (d && isFinite(d) && d > 0) {
				var ms = Math.round(d * 1000);
				cb(Math.max(MIN_MS, Math.min(MAX_MS, ms)));
			} else {
				cb(fallbackMs);
			}
		}
		if (video.readyState >= 1) {
			apply();
			return;
		}
		function onMeta() {
			video.removeEventListener('loadedmetadata', onMeta);
			video.removeEventListener('error', onErr);
			apply();
		}
		function onErr() {
			video.removeEventListener('loadedmetadata', onMeta);
			video.removeEventListener('error', onErr);
			cb(fallbackMs);
		}
		video.addEventListener('loadedmetadata', onMeta);
		video.addEventListener('error', onErr);
		setTimeout(function () {
			if (video.readyState < 1) {
				video.removeEventListener('loadedmetadata', onMeta);
				video.removeEventListener('error', onErr);
				cb(fallbackMs);
			}
		}, 5000);
	}

	function syncSlideHeights(slides) {
		if (!slides.length) {
			return;
		}
		var maxH = 0;
		slides.forEach(function (s, i) {
			var link = s.querySelector('.gk-platform-featured-slide__link');
			if (!link) {
				return;
			}
			slides.forEach(function (o, j) {
				o.style.display = i === j ? 'block' : 'none';
				o.style.position = i === j ? 'relative' : 'absolute';
			});
			s.offsetHeight;
			var h = link.offsetHeight;
			if (h > maxH) {
				maxH = h;
			}
		});
		slides.forEach(function (s) {
			s.style.display = '';
			s.style.position = '';
		});
		var minH = maxH > 0 ? maxH : 352;
		var firstLink = slides[0].querySelector('.gk-platform-featured-slide__link');
		var currentMin = firstLink && firstLink.style.minHeight ? parseInt(firstLink.style.minHeight, 10) : 0;
		if (currentMin > 0 && minH < currentMin) {
			minH = currentMin;
		}
		slides.forEach(function (s) {
			var link = s.querySelector('.gk-platform-featured-slide__link');
			if (link) {
				link.style.minHeight = minH + 'px';
			}
		});
	}

	function initCarousel(el) {
		var slides = el.querySelectorAll('.gk-platform-featured-slide');
		var dots = el.querySelectorAll('.gk-platform-featured-carousel__dot');
		var timerFill = el.querySelector('.gk-platform-featured-carousel__timer-fill');
		var timerValue = el.querySelector('.gk-platform-featured-carousel__timer-value');
		var autoInput = el.querySelector('.gk-platform-featured-carousel__auto-switch-input');
		var fallbackMs = parseInt(el.getAttribute('data-timer-fallback-ms'), 10) || FALLBACK_MS;
		var total = slides.length;

		if (total === 0) {
			return;
		}

		var currentIndex = 0;
		var slideToken = 0;
		var boundVideo = null;
		var endedBound = null;
		var rafId = null;
		var backupTimerId = null;
		var autoEnabled = true;
		var timerMs = fallbackMs;
		var pausedAtPercent = 0;

		function updateTimerDisplay(percent) {
			if (timerFill) {
				timerFill.style.width = Math.min(100, Math.max(0, percent * 100)) + '%';
			}
			if (timerValue) {
				var remaining = Math.ceil((timerMs / 1000) * (1 - percent));
				timerValue.textContent = String(Math.max(0, remaining));
			}
		}

		function tick() {
			if (!boundVideo || !autoEnabled) return;
			var d = boundVideo.duration;
			if (d && isFinite(d) && d > 0) {
				var pct = boundVideo.currentTime / d;
				updateTimerDisplay(pct);
				if (pct >= 1) return;
			}
			rafId = requestAnimationFrame(tick);
		}

		function unbindVideoEvents() {
			if (rafId) {
				cancelAnimationFrame(rafId);
				rafId = null;
			}
			if (backupTimerId) {
				clearTimeout(backupTimerId);
				backupTimerId = null;
			}
			if (boundVideo && endedBound) {
				boundVideo.removeEventListener('ended', endedBound);
			}
			boundVideo = null;
			endedBound = null;
		}

		function bindVideoEvents(video) {
			if (!video) return;
			unbindVideoEvents();
			function onEnded() {
				if (!autoEnabled) return;
				showSlide(currentIndex + 1);
			}
			boundVideo = video;
			endedBound = onEnded;
			video.addEventListener('ended', onEnded);
			rafId = requestAnimationFrame(tick);
			backupTimerId = setTimeout(function () {
				backupTimerId = null;
				if (autoEnabled && boundVideo === video) {
					showSlide(currentIndex + 1);
				}
			}, timerMs + 500);
		}

		function ensureVideoPlay(video, resetElapsed, pausedPct) {
			if (!video) return;
			video.currentTime = resetElapsed ? 0 : (pausedPct * (video.duration || 0));
			var played = false;
			var listenersAttached = false;
			function removeListeners() {
				if (!listenersAttached) return;
				listenersAttached = false;
				video.removeEventListener('loadedmetadata', tryPlay);
				video.removeEventListener('loadeddata', tryPlay);
				video.removeEventListener('canplay', tryPlay);
			}
			function tryPlay() {
				if (played) return;
				video.play().then(function () {
					played = true;
					removeListeners();
				}).catch(function () {
					if (!listenersAttached) {
						listenersAttached = true;
						video.addEventListener('loadedmetadata', tryPlay, { once: true });
						video.addEventListener('loadeddata', tryPlay, { once: true });
						video.addEventListener('canplay', tryPlay, { once: true });
					}
				});
			}
			if (video.readyState >= 2) {
				tryPlay();
			} else if (video.readyState >= 1) {
				tryPlay();
			} else {
				video.addEventListener('loadedmetadata', tryPlay, { once: true });
				video.addEventListener('loadeddata', tryPlay, { once: true });
				video.addEventListener('canplay', tryPlay, { once: true });
			}
			setTimeout(function () {
				if (!played && video.readyState >= 1) {
					tryPlay();
				}
			}, 400);
			setTimeout(function () {
				if (!played) {
					tryPlay();
				}
			}, 1000);
		}

		function startTimer(resetElapsed, durationMs) {
			if (durationMs !== undefined) {
				timerMs = durationMs;
			}
			var video = slides[currentIndex].querySelector('video.gk-platform-featured-slide__trailer');
			if (resetElapsed) {
				pausedAtPercent = 0;
				updateTimerDisplay(0);
			} else if (!autoEnabled) {
				updateTimerDisplay(pausedAtPercent);
				if (timerValue) {
					var sec = Math.ceil((timerMs / 1000) * (1 - pausedAtPercent));
					timerValue.textContent = String(Math.max(0, sec));
				}
			}
			if (video) {
				video.loop = !autoEnabled;
			}
			if (autoEnabled) {
				bindVideoEvents(video);
			} else {
				unbindVideoEvents();
			}
			ensureVideoPlay(video, resetElapsed, pausedAtPercent);
		}

		function stopTimer() {
			var video = slides[currentIndex].querySelector('video.gk-platform-featured-slide__trailer');
			unbindVideoEvents();
			if (video) {
				video.loop = true;
				if (video.duration && isFinite(video.duration)) {
					pausedAtPercent = video.currentTime / video.duration;
				}
			}
			updateTimerDisplay(Math.min(1, Math.max(0, pausedAtPercent)));
		}

		var inner = el.querySelector('.gk-platform-featured-carousel__inner');
		var overlay = inner ? inner.querySelector('.gk-platform-featured-carousel__transition-overlay') : null;

		function doSwitchContent(idx, token) {
			slides.forEach(function (s, i) {
				s.classList.toggle('is-active', i === idx);
				s.setAttribute('aria-hidden', i === idx ? 'false' : 'true');
			});
			dots.forEach(function (d, i) {
				d.classList.toggle('is-active', i === idx);
				d.setAttribute('aria-selected', i === idx ? 'true' : 'false');
			});
			var video = slides[idx].querySelector('video.gk-platform-featured-slide__trailer');
			slides.forEach(function (s, i) {
				var v = s.querySelector('video.gk-platform-featured-slide__trailer');
				if (v) {
					if (i === idx) {
						v.preload = 'auto';
						v.currentTime = 0;
						v.loop = !autoEnabled;
						if (v.readyState >= 2) {
							v.play().catch(function () {});
						} else {
							v.addEventListener('loadeddata', function () {
								v.play().catch(function () {});
							}, { once: true });
						}
					} else {
						v.pause();
					}
				}
			});
			getDurationFromVideo(video, fallbackMs, function (durationMs) {
				if (token !== slideToken) {
					return;
				}
				timerMs = durationMs;
				startTimer(true, durationMs);
			});
		}

		function showSlide(idx) {
			idx = ((idx % total) + total) % total;
			var isSwitch = idx !== currentIndex;
			currentIndex = idx;
			slideToken += 1;
			var token = slideToken;

			if (isSwitch && inner && overlay) {
				unbindVideoEvents();
				inner.classList.add('is-transitioning');
				var done = false;
				var DARK_HOLD_MS = 90;
				function finish() {
					if (done) return;
					done = true;
					overlay.removeEventListener('transitionend', onDark);
					clearTimeout(fb);
					clearTimeout(holdTimer);
					if (token !== slideToken) {
						inner.classList.remove('is-transitioning');
						return;
					}
					doSwitchContent(idx, token);
					requestAnimationFrame(function () {
						requestAnimationFrame(function () {
							inner.classList.remove('is-transitioning');
							setTimeout(function () {
								syncSlideHeights(slides);
							}, 50);
						});
					});
				}
				function onDark() {
					overlay.removeEventListener('transitionend', onDark);
					if (done) return;
					holdTimer = setTimeout(finish, DARK_HOLD_MS);
				}
				var holdTimer;
				overlay.addEventListener('transitionend', onDark);
				var fb = setTimeout(function () {
					if (done) return;
					done = true;
					overlay.removeEventListener('transitionend', onDark);
					clearTimeout(holdTimer);
					if (token !== slideToken) {
						inner.classList.remove('is-transitioning');
						return;
					}
					doSwitchContent(idx, token);
					requestAnimationFrame(function () {
						requestAnimationFrame(function () {
							inner.classList.remove('is-transitioning');
							setTimeout(function () {
								syncSlideHeights(slides);
							}, 50);
						});
					});
				}, 250);
			} else {
				doSwitchContent(idx, token);
				setTimeout(function () {
					syncSlideHeights(slides);
				}, 50);
			}
		}


		function updateAutoState() {
			if (autoInput) {
				autoEnabled = autoInput.checked;
				el.classList.toggle('is-auto-off', !autoEnabled);
			}
			if (autoEnabled) {
				startTimer(false);
			} else {
				stopTimer();
			}
		}
		if (autoInput) {
			autoInput.addEventListener('change', updateAutoState);
		}

		/* Dots: direkte Klick-Listener für zuverlässiges Slide-Wechseln */
		dots.forEach(function (dot, i) {
			function handleDotClick(e) {
				e.preventDefault();
				e.stopPropagation();
				showSlide(i);
			}
			dot.addEventListener('click', handleDotClick);
			dot.addEventListener('keydown', function (e) {
				if (e.key === 'Enter' || e.key === ' ') {
					e.preventDefault();
					handleDotClick(e);
				}
			});
		});

		/* Erst Slide 0 aktivieren, dann Timer nach 2 Frames – verhindert Race bei erstem Video */
		showSlide(0);
		var firstVideo = slides[0].querySelector('video.gk-platform-featured-slide__trailer');
		if (firstVideo && firstVideo.readyState === 0) {
			firstVideo.load();
		}
		requestAnimationFrame(function () {
			requestAnimationFrame(function () {
				updateAutoState();
			});
		});
		syncSlideHeights(slides);
		var resizeTimer;
		function onResize() {
			clearTimeout(resizeTimer);
			resizeTimer = setTimeout(function () {
				syncSlideHeights(slides);
			}, 150);
		}
		window.addEventListener('resize', onResize);
		window.addEventListener('load', function () {
			syncSlideHeights(slides);
		});
	}

		carousels.forEach(initCarousel);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', run);
	} else {
		run();
	}
})();
