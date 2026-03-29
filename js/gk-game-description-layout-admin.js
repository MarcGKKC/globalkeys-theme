/**
 * Game Description Layout: Blöcke, Mediathek, einklappbare Zeilen, Reihenfolge.
 */
(function ($) {
	'use strict';

	function typeLabel(val) {
		var L = (typeof window.gkGameDescLayoutAdmin === 'object' && window.gkGameDescLayoutAdmin) ? window.gkGameDescLayoutAdmin : {};
		var map = {
			text: L.typeText || 'Text',
			images: L.typeImages || 'Images',
			videos: L.typeVideos || 'Videos'
		};
		return map[val] || val || '';
	}

	function updateRowSummary($row) {
		var L = (typeof window.gkGameDescLayoutAdmin === 'object' && window.gkGameDescLayoutAdmin) ? window.gkGameDescLayoutAdmin : {};
		var noTitle = L.noTitle || '—';
		var title = ($row.find('.gk-game-desc-row__title').val() || '').trim();
		var t = $row.find('.gk-game-desc-type').val() || 'text';
		var typeHuman = typeLabel(t);
		var titlePart = title !== '' ? title : noTitle;
		$row.find('.gk-game-desc-row__summary').text(titlePart + ' · ' + typeHuman);
	}

	function setRowCollapsed($row, collapsed) {
		var $body = $row.find('.gk-game-desc-row__body');
		var $btn = $row.find('.gk-game-desc-toggle-body');
		var $icon = $row.find('.gk-game-desc-toggle-icon');
		if (collapsed) {
			$body.hide();
			$btn.attr('aria-expanded', 'false');
			$icon.text('▶');
		} else {
			$body.show();
			$btn.attr('aria-expanded', 'true');
			$icon.text('▼');
		}
	}

	function applyDefaultCollapseState() {
		var $rows = $('#gk-game-desc-rows .gk-game-desc-row');
		if ($rows.length <= 1) {
			$rows.each(function () {
				setRowCollapsed($(this), false);
			});
			return;
		}
		$rows.each(function (i) {
			setRowCollapsed($(this), i > 0);
		});
	}

	function reindexAllRows() {
		$('#gk-game-desc-rows .gk-game-desc-row').each(function (i) {
			var $r = $(this);
			$r.find('input[name], select[name], textarea[name]').each(function () {
				var el = this;
				if (el.name) {
					el.name = el.name.replace(/gk_gdb\[[^\]]+\]/, 'gk_gdb[' + i + ']');
				}
			});
			$r.find('[id^="gk_game_desc_img1_"],[id^="gk_game_desc_img2_"],[id^="gk_game_desc_vid1_"],[id^="gk_game_desc_vid2_"]').each(function () {
				var m = this.id.match(/^(gk_game_desc_(?:img1|img2|vid1|vid2)_)(\d+|__IDX__)$/);
				if (m) {
					this.id = m[1] + i;
				}
			});
			$r.find('.gk-game-desc-row__title').attr('id', 'gk_game_desc_title_' + i);
			$r.find('.gk-game-desc-type').attr('id', 'gk_game_desc_type_' + i);
			$r.find('label[for^="gk_game_desc_title_"]').attr('for', 'gk_game_desc_title_' + i);
			$r.find('label[for^="gk_game_desc_type_"]').attr('for', 'gk_game_desc_type_' + i);
			$r.find('.gk-game-desc-pick-img, .gk-game-desc-pick-video').each(function () {
				var $b = $(this);
				var inp = $b.attr('data-input');
				if (typeof inp === 'string' && /^gk_game_desc_(img1|img2|vid1|vid2)_/.test(inp)) {
					$b.attr('data-input', inp.replace(/^(gk_game_desc_(?:img1|img2|vid1|vid2)_)\d+$/, '$1' + i));
				}
			});
			$r.find('.gk-game-desc-row__index').text('#' + (i + 1));
			updateRowSummary($r);
		});
	}

	function rebindAllRows() {
		$('#gk-game-desc-rows .gk-game-desc-row').each(function () {
			bindRow($(this));
		});
	}

	function bindPick($row) {
		$row.find('.gk-game-desc-pick-img').off('click').on('click', function (e) {
			e.preventDefault();
			var inputId = $(this).data('input');
			var $input = $('#' + inputId);
			var frame = wp.media({
				title: 'Bild wählen',
				library: { type: 'image' },
				multiple: false
			});
			frame.on('select', function () {
				var att = frame.state().get('selection').first().toJSON();
				$input.val(att.id);
				var url = (att.sizes && att.sizes.thumbnail && att.sizes.thumbnail.url) ? att.sizes.thumbnail.url : att.url;
				$input.closest('div').find('.gk-game-desc-img-preview').html(
					$('<img>', { src: url, css: { maxWidth: '72px', height: 'auto', borderRadius: '6px' }, alt: '' })
				);
			});
			frame.open();
		});
	}

	function bindPickVideo($row) {
		$row.find('.gk-game-desc-pick-video').off('click').on('click', function (e) {
			e.preventDefault();
			var inputId = $(this).data('input');
			var $input = $('#' + inputId);
			var frame = wp.media({
				title: 'Video wählen',
				library: { type: 'video' },
				multiple: false
			});
			frame.on('select', function () {
				var att = frame.state().get('selection').first().toJSON();
				$input.val(att.id);
				var url = att.url || '';
				$input.closest('div').find('.gk-game-desc-video-preview').html(
					$('<video>', {
						src: url,
						muted: true,
						playsInline: true,
						preload: 'metadata',
						css: { maxWidth: '120px', height: 'auto', borderRadius: '6px', verticalAlign: 'middle' },
						attr: { 'aria-hidden': 'true' }
					})
				);
			});
			frame.open();
		});
	}

	function toggleFields($row) {
		var t = $row.find('.gk-game-desc-type').val();
		$row.find('.gk-game-desc-field--text').toggle(t === 'text');
		$row.find('.gk-game-desc-field--images').toggle(t === 'images');
		$row.find('.gk-game-desc-field--videos').toggle(t === 'videos');
	}

	function bindRow($row) {
		$row.find('.gk-game-desc-type').off('change').on('change', function () {
			toggleFields($row);
			updateRowSummary($row);
		});
		$row.find('.gk-game-desc-row__title').off('input').on('input', function () {
			updateRowSummary($row);
		});
		$row.find('.gk-game-desc-toggle-body').off('click').on('click', function () {
			var expanded = $(this).attr('aria-expanded') === 'true';
			setRowCollapsed($row, expanded);
		});
		$row.find('.gk-game-desc-move-up').off('click').on('click', function () {
			var $p = $row.prev('.gk-game-desc-row');
			if ($p.length) {
				$row.insertBefore($p);
				reindexAllRows();
				rebindAllRows();
			}
		});
		$row.find('.gk-game-desc-move-down').off('click').on('click', function () {
			var $n = $row.next('.gk-game-desc-row');
			if ($n.length) {
				$row.insertAfter($n);
				reindexAllRows();
				rebindAllRows();
			}
		});
		$row.find('.gk-game-desc-remove-row').off('click').on('click', function () {
			$row.remove();
			reindexAllRows();
			rebindAllRows();
			applyDefaultCollapseState();
		});
		toggleFields($row);
		bindPick($row);
		bindPickVideo($row);
		updateRowSummary($row);
	}

	$(function () {
		$('#gk-game-desc-rows .gk-game-desc-row').each(function () {
			bindRow($(this));
		});
		applyDefaultCollapseState();

		$('#gk-game-desc-expand-all').on('click', function () {
			$('#gk-game-desc-rows .gk-game-desc-row').each(function () {
				setRowCollapsed($(this), false);
			});
		});
		$('#gk-game-desc-collapse-all').on('click', function () {
			$('#gk-game-desc-rows .gk-game-desc-row').each(function () {
				setRowCollapsed($(this), true);
			});
		});

		$('#gk-game-desc-add').on('click', function () {
			var tpl = document.getElementById('gk-game-desc-row-template');
			if (!tpl || !tpl.content) {
				return;
			}
			var idx = typeof window.gkGameDescNextIdx === 'number' ? window.gkGameDescNextIdx : 0;
			window.gkGameDescNextIdx = idx + 1;
			var wrap = document.createElement('div');
			wrap.appendChild(document.importNode(tpl.content, true));
			var html = wrap.innerHTML.replace(/__IDX__/g, String(idx));
			$('#gk-game-desc-rows').append(html);
			reindexAllRows();
			rebindAllRows();
			var $last = $('#gk-game-desc-rows .gk-game-desc-row').last();
			setRowCollapsed($last, false);
			try {
				$last[0].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
			} catch (e) {
				$last[0].scrollIntoView(true);
			}
		});
	});
})(jQuery);
