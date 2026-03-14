(function($) {
	'use strict';

	var $wrap, $input, $img;

	function init() {
		$wrap = $('.gk-accdash__heroAvatarWrap');
		if (!$wrap.length) return;

		$input = $wrap.find('input[type="file"]');
		$img = $wrap.find('.gk-accdash__heroAvatarImg');

		$wrap.on('click', function(e) {
			if (!$(e.target).closest('input').length) {
				e.preventDefault();
				$input[0].click();
			}
		});

		$wrap.on('keydown', function(e) {
			if (e.key === 'Enter' || e.key === ' ') {
				e.preventDefault();
				$input[0].click();
			}
		});

		$input.on('change', function() {
			var file = this.files[0];
			if (!file) return;
			if (!file.type.match(/^image\/(jpeg|png|gif|webp)$/)) {
				alert('Nur JPG, PNG, GIF oder WebP erlaubt.');
				return;
			}
			var formData = new FormData();
			formData.append('action', 'gk_upload_profile_avatar');
			formData.append('nonce', gkAvatarUpload.nonce);
			formData.append('avatar', file);

			$wrap.addClass('gk-avatar-uploading');

			$.ajax({
				url: gkAvatarUpload.ajaxUrl,
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function(res) {
					$wrap.removeClass('gk-avatar-uploading');
					if (res.success && res.data && res.data.url) {
						$img.attr('src', res.data.url).attr('style', 'object-position: center bottom;');
						$('.gk-accdash__heroAvatarSmall .gk-accdash__heroAvatarImg').attr('src', res.data.url).attr('style', 'object-position: center bottom;');
					} else {
						alert(res.data && res.data.message ? res.data.message : 'Fehler beim Hochladen.');
					}
				},
				error: function() {
					$wrap.removeClass('gk-avatar-uploading');
					alert('Fehler beim Hochladen.');
				}
			});

			this.value = '';
		});
	}

	$(function() { init(); });

})(jQuery);
