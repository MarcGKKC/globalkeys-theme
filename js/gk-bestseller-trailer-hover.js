/**
 * Bestseller-Karten: Preview-Video bei Hover.
 * Nutzt play()-Promise (zuverlässiger als nur „playing“); muted per JS für Autoplay-Policy.
 */
(function () {
	'use strict';

	var section = document.querySelector('.gk-section-bestsellers');
	if (!section) {
		return;
	}

	function revealWrap(wrap) {
		wrap.classList.add('is-trailer-playing');
	}

	function hideWrap(wrap) {
		wrap.classList.remove('is-trailer-playing');
	}

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
})();
