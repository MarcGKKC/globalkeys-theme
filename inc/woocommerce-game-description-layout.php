<?php
/**
 * Game Description: wiederholbares Layout Text → Medienpaar (Bilder oder Videos) → Text … (Produkt-Meta).
 *
 * @package globalkeys
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'GK_GAME_DESC_BLOCKS_META' ) ) {
	define( 'GK_GAME_DESC_BLOCKS_META', '_gk_game_description_blocks' );
}

/**
 * @param WC_Product|null $product Produkt.
 * @return array<int, array<string, mixed>>
 */
function globalkeys_get_game_description_blocks( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return array();
	}
	$raw = get_post_meta( $product->get_id(), GK_GAME_DESC_BLOCKS_META, true );
	if ( ! is_array( $raw ) ) {
		return array();
	}
	return $raw;
}

/**
 * @param array<int, array<string, mixed>> $blocks Blöcke.
 * @return bool
 */
function globalkeys_game_description_blocks_nonempty( $blocks ) {
	foreach ( $blocks as $b ) {
		if ( ! is_array( $b ) || empty( $b['type'] ) ) {
			continue;
		}
		$btitle = isset( $b['title'] ) ? trim( (string) $b['title'] ) : '';
		if ( $btitle !== '' ) {
			return true;
		}
		if ( $b['type'] === 'text' ) {
			$html = isset( $b['html'] ) ? (string) $b['html'] : '';
			if ( trim( wp_strip_all_tags( $html ) ) !== '' ) {
				return true;
			}
		}
		if ( $b['type'] === 'images' ) {
			$i1 = isset( $b['img1'] ) ? (int) $b['img1'] : 0;
			$i2 = isset( $b['img2'] ) ? (int) $b['img2'] : 0;
			if ( $i1 >= 1 && wp_attachment_is_image( $i1 ) ) {
				return true;
			}
			if ( $i2 >= 1 && wp_attachment_is_image( $i2 ) ) {
				return true;
			}
		}
		if ( $b['type'] === 'videos' ) {
			$v1 = isset( $b['vid1'] ) ? (int) $b['vid1'] : 0;
			$v2 = isset( $b['vid2'] ) ? (int) $b['vid2'] : 0;
			if ( $v1 >= 1 && globalkeys_game_desc_is_layout_video_attachment( $v1 ) ) {
				return true;
			}
			if ( $v2 >= 1 && globalkeys_game_desc_is_layout_video_attachment( $v2 ) ) {
				return true;
			}
		}
	}
	return false;
}

/**
 * Prüft, ob eine Anhang-ID ein Video aus der Mediathek ist (z. B. MP4).
 *
 * @param int $attachment_id Anhang-ID.
 * @return bool
 */
function globalkeys_game_desc_is_layout_video_attachment( $attachment_id ) {
	$attachment_id = (int) $attachment_id;
	if ( $attachment_id < 1 ) {
		return false;
	}
	$post = get_post( $attachment_id );
	if ( ! $post || $post->post_type !== 'attachment' ) {
		return false;
	}
	$mime = get_post_mime_type( $attachment_id );
	return is_string( $mime ) && strpos( $mime, 'video/' ) === 0;
}

/**
 * @param WC_Product|null $product Produkt.
 * @return bool
 */
function globalkeys_product_page_has_game_description_layout( $product ) {
	$blocks = globalkeys_get_game_description_blocks( $product );
	return globalkeys_game_description_blocks_nonempty( $blocks );
}

/**
 * Ein großes Galerie-<li> wie in „Game Images“ (Featured), inkl. Lightbox-Daten.
 *
 * @param int         $aid            Anhang-ID.
 * @param WC_Product $product        Produkt.
 * @param int         $lightbox_index Index nur innerhalb dieses Bild-Blocks.
 * @param string      $loading        eager|lazy.
 * @return string
 */
function globalkeys_game_description_layout_featured_gallery_li( $aid, $product, $lightbox_index, $loading = 'lazy' ) {
	$aid = (int) $aid;
	if ( $aid < 1 || ! wp_attachment_is_image( $aid ) ) {
		return '';
	}
	$alt = get_post_meta( $aid, '_wp_attachment_image_alt', true );
	$alt = is_string( $alt ) ? $alt : '';
	$full_url = wp_get_attachment_url( $aid );
	$full_url = is_string( $full_url ) ? $full_url : '';
	$full_url = apply_filters( 'gk_game_images_attachment_full_url', $full_url, $aid, $product );
	$lightbox_thumb_sizes = apply_filters(
		'gk_game_images_lightbox_thumb_sizes',
		array( 'medium_large', 'woocommerce_thumbnail', 'woocommerce_single', 'medium', 'woocommerce_gallery_thumbnail', 'large', 'full', 'thumbnail' ),
		$aid,
		$product
	);
	$thumb_url = '';
	foreach ( $lightbox_thumb_sizes as $size_name ) {
		$thumb_src = wp_get_attachment_image_src( $aid, $size_name );
		if ( is_array( $thumb_src ) && ! empty( $thumb_src[0] ) ) {
			$thumb_url = $thumb_src[0];
			break;
		}
	}
	if ( $thumb_url === '' ) {
		$thumb_url = $full_url;
	}
	$img_size = apply_filters( 'gk_game_description_layout_image_size', 'full', $product );
	$img_html = wp_get_attachment_image(
		$aid,
		$img_size,
		false,
		array(
			'class'    => 'gk-product-page-game-images__gallery-img gk-product-page-game-images__gallery-img--featured',
			'loading'  => ( $loading === 'eager' ) ? 'eager' : 'lazy',
			'decoding' => 'async',
			'alt'      => $alt !== '' ? $alt : esc_attr( $product->get_name() ),
		)
	);
	ob_start();
	$item_class = 'gk-product-page-game-images__gallery-item gk-product-page-game-images__gallery-item--featured';
	echo '<li class="' . esc_attr( $item_class ) . '" role="listitem">';
	if ( is_string( $full_url ) && $full_url !== '' ) {
		$link_label = $alt !== '' ? $alt : $product->get_name();
		$aria_label = __( 'Open image gallery', 'globalkeys' ) . ': ' . $link_label;
		echo '<a class="gk-product-page-game-images__gallery-link" href="' . esc_url( $full_url ) . '"';
		echo ' data-gk-lightbox-full="' . esc_url( $full_url ) . '"';
		echo ' data-gk-lightbox-index="' . (int) $lightbox_index . '"';
		if ( is_string( $thumb_url ) && $thumb_url !== '' ) {
			echo ' data-gk-lightbox-thumb="' . esc_url( $thumb_url ) . '"';
		}
		echo ' aria-label="' . esc_attr( $aria_label ) . '">';
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image().
		echo $img_html;
		echo '</a>';
	} else {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image().
		echo $img_html;
	}
	echo '</li>';
	return ob_get_clean();
}

/**
 * Ein Featured-<li> mit schleifen­fähigem Video (gleiches Raster wie Bildpaar, ohne Lightbox).
 *
 * @param int         $aid     Anhang-ID (Video).
 * @param WC_Product $product Produkt.
 * @return string
 */
function globalkeys_game_description_layout_featured_video_li( $aid, $product ) {
	$aid = (int) $aid;
	if ( ! globalkeys_game_desc_is_layout_video_attachment( $aid ) ) {
		return '';
	}
	$url = wp_get_attachment_url( $aid );
	if ( ! is_string( $url ) || $url === '' ) {
		return '';
	}
	$url = apply_filters( 'gk_game_desc_layout_video_src_url', $url, $aid, $product );
	$title = get_the_title( $aid );
	$title = is_string( $title ) ? trim( $title ) : '';
	if ( $title === '' ) {
		$title = $product->get_name();
	}
	$aria = apply_filters(
		'gk_game_desc_layout_video_aria_label',
		/* translators: %s: video title or product name */
		sprintf( __( 'Produktvideo: %s', 'globalkeys' ), $title ),
		$aid,
		$product
	);
	ob_start();
	$item_class = 'gk-product-page-game-images__gallery-item gk-product-page-game-images__gallery-item--featured';
	echo '<li class="' . esc_attr( $item_class ) . '" role="listitem">';
	echo '<div class="gk-product-page-game-images__gallery-link gk-game-desc-block__video-frame" tabindex="-1">';
	echo '<video class="gk-product-page-game-images__gallery-img gk-product-page-game-images__gallery-img--featured gk-game-desc-block__loop-video"';
	echo ' src="' . esc_url( $url ) . '"';
	echo ' autoplay muted loop playsinline preload="auto"';
	echo ' aria-label="' . esc_attr( $aria ) . '">';
	echo '</video>';
	echo '</div>';
	echo '</li>';
	return ob_get_clean();
}

/**
 * Markup für die Section „Game Description“ (nur Blöcke).
 *
 * @param WC_Product|null $product Produkt.
 */
function globalkeys_render_game_description_blocks( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return;
	}
	$blocks = globalkeys_get_game_description_blocks( $product );
	if ( ! globalkeys_game_description_blocks_nonempty( $blocks ) ) {
		return;
	}
	echo '<div class="gk-game-desc-layout">';
	foreach ( $blocks as $block ) {
		if ( ! is_array( $block ) || empty( $block['type'] ) ) {
			continue;
		}
		$title = isset( $block['title'] ) ? trim( (string) $block['title'] ) : '';
		if ( $block['type'] === 'text' ) {
			$html = isset( $block['html'] ) ? (string) $block['html'] : '';
			if ( $title === '' && trim( wp_strip_all_tags( $html ) ) === '' ) {
				continue;
			}
			echo '<div class="gk-game-desc-block gk-game-desc-block--text">';
			if ( $title !== '' ) {
				echo '<h3 class="gk-game-desc-block__title">' . esc_html( $title ) . '</h3>';
			}
			if ( trim( wp_strip_all_tags( $html ) ) !== '' ) {
				echo '<div class="gk-game-desc-block__body">';
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses_post wie Produktbeschreibung.
				echo wp_kses_post( $html );
				echo '</div>';
			}
			echo '</div>';
		} elseif ( $block['type'] === 'images' ) {
			$i1 = isset( $block['img1'] ) ? (int) $block['img1'] : 0;
			$i2 = isset( $block['img2'] ) ? (int) $block['img2'] : 0;
			$pair = array_filter(
				array( $i1, $i2 ),
				function ( $id ) {
					return $id >= 1 && wp_attachment_is_image( $id );
				}
			);
			if ( empty( $pair ) && $title === '' ) {
				continue;
			}
			echo '<div class="gk-game-desc-block gk-game-desc-block--images">';
			if ( $title !== '' ) {
				echo '<h3 class="gk-game-desc-block__title">' . esc_html( $title ) . '</h3>';
			}
			if ( ! empty( $pair ) ) {
				$items = array();
				$li_i  = 0;
				foreach ( $pair as $aid ) {
					$loading = ( 0 === $li_i ) ? 'eager' : 'lazy';
					$items[] = globalkeys_game_description_layout_featured_gallery_li( (int) $aid, $product, $li_i, $loading );
					++$li_i;
				}
				$items = array_values( array_filter( $items ) );
				if ( ! empty( $items ) ) {
					echo '<div class="gk-game-desc-block__game-images-body" role="group" aria-label="' . esc_attr__( 'Image pair', 'globalkeys' ) . '">';
					if ( count( $items ) === 1 ) {
						echo '<div class="gk-product-page-game-images__grid gk-product-page-game-images__grid--single" role="presentation">';
						echo '<div class="gk-product-page-game-images__column">';
						echo '<ul class="gk-product-page-game-images__gallery gk-product-page-game-images__gallery--featured-cell" role="list">';
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- <li> aus Helper.
						echo implode( '', $items );
						echo '</ul></div></div>';
					} else {
						echo '<div class="gk-product-page-game-images__grid" role="presentation">';
						foreach ( $items as $one_li ) {
							echo '<div class="gk-product-page-game-images__column">';
							echo '<ul class="gk-product-page-game-images__gallery gk-product-page-game-images__gallery--featured-cell" role="list">';
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- <li> aus Helper.
							echo $one_li;
							echo '</ul></div>';
						}
						echo '</div>';
					}
					echo '</div>';
				}
			}
			echo '</div>';
		} elseif ( $block['type'] === 'videos' ) {
			$v1 = isset( $block['vid1'] ) ? (int) $block['vid1'] : 0;
			$v2 = isset( $block['vid2'] ) ? (int) $block['vid2'] : 0;
			$pair = array_filter(
				array( $v1, $v2 ),
				'globalkeys_game_desc_is_layout_video_attachment'
			);
			if ( empty( $pair ) && $title === '' ) {
				continue;
			}
			echo '<div class="gk-game-desc-block gk-game-desc-block--videos">';
			if ( $title !== '' ) {
				echo '<h3 class="gk-game-desc-block__title">' . esc_html( $title ) . '</h3>';
			}
			if ( ! empty( $pair ) ) {
				$items = array();
				foreach ( $pair as $vid ) {
					$items[] = globalkeys_game_description_layout_featured_video_li( (int) $vid, $product );
				}
				$items = array_values( array_filter( $items ) );
				if ( ! empty( $items ) ) {
					echo '<div class="gk-game-desc-block__game-images-body" role="group" aria-label="' . esc_attr__( 'Produktvideos', 'globalkeys' ) . '">';
					if ( count( $items ) === 1 ) {
						echo '<div class="gk-product-page-game-images__grid gk-product-page-game-images__grid--single" role="presentation">';
						echo '<div class="gk-product-page-game-images__column">';
						echo '<ul class="gk-product-page-game-images__gallery gk-product-page-game-images__gallery--featured-cell" role="list">';
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- <li> aus Helper.
						echo implode( '', $items );
						echo '</ul></div></div>';
					} else {
						echo '<div class="gk-product-page-game-images__grid" role="presentation">';
						foreach ( $items as $one_li ) {
							echo '<div class="gk-product-page-game-images__column">';
							echo '<ul class="gk-product-page-game-images__gallery gk-product-page-game-images__gallery--featured-cell" role="list">';
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- <li> aus Helper.
							echo $one_li;
							echo '</ul></div>';
						}
						echo '</div>';
					}
					echo '</div>';
				}
			}
			echo '</div>';
		}
	}
	echo '</div>';
}

/**
 * Metabox auf der Produktbearbeitungsseite.
 */
function globalkeys_game_description_layout_metabox() {
	add_meta_box(
		'gk-game-description-layout',
		__( 'Game Description: Text, Bilder & Videos', 'globalkeys' ),
		'globalkeys_game_description_layout_metabox_render',
		'product',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'globalkeys_game_description_layout_metabox' );

/**
 * @param WP_Post $post Post.
 */
function globalkeys_game_description_layout_metabox_render( $post ) {
	wp_nonce_field( 'gk_game_desc_blocks_save', 'gk_game_desc_blocks_nonce' );
	$blocks = get_post_meta( $post->ID, GK_GAME_DESC_BLOCKS_META, true );
	if ( ! is_array( $blocks ) ) {
		$blocks = array();
	}
	$next_idx = 0;
	foreach ( array_keys( $blocks ) as $k ) {
		$next_idx = max( $next_idx, (int) $k + 1 );
	}
	?>
	<p class="description" style="margin-top:0;">
		<?php esc_html_e( 'Reihenfolge von oben nach unten: Blöcke „Text“, „Zwei Bilder“ oder „Zwei Videos (MP4, Loop)“. Jeder Block kann eine Abschnittsüberschrift haben. Videos laufen stumm in Endlosschleife wie die bisherige Bildgröße. Wenn hier mindestens ein Block mit Inhalt gespeichert ist, wird die WooCommerce-Langbeschreibung in dieser Section nicht mehr angezeigt. Der Filter gk_game_description_section_content_html hat weiterhin Vorrang.', 'globalkeys' ); ?>
	</p>
	<p class="gk-game-desc-qol" style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin:0 0 8px;">
		<button type="button" class="button" id="gk-game-desc-expand-all"><?php esc_html_e( 'Alle Blöcke ausklappen', 'globalkeys' ); ?></button>
		<button type="button" class="button" id="gk-game-desc-collapse-all"><?php esc_html_e( 'Alle Blöcke einklappen', 'globalkeys' ); ?></button>
		<span class="description" style="margin:0;"><?php esc_html_e( 'Ab dem zweiten Block ist der Inhalt standardmäßig zu – oben schnell bearbeiten, per Klick auf ▼ den Rest öffnen.', 'globalkeys' ); ?></span>
	</p>
	<div id="gk-game-desc-rows" style="display:flex;flex-direction:column;gap:12px;margin:12px 0;">
		<?php
		foreach ( $blocks as $idx => $block ) {
			globalkeys_game_description_layout_metabox_row( (int) $idx, $block );
		}
		?>
	</div>
	<p>
		<button type="button" class="button" id="gk-game-desc-add"><?php esc_html_e( 'Block hinzufügen', 'globalkeys' ); ?></button>
	</p>
	<template id="gk-game-desc-row-template">
		<?php globalkeys_game_description_layout_metabox_row( '__IDX__', array( 'type' => 'text', 'title' => '', 'html' => '', 'img1' => 0, 'img2' => 0, 'vid1' => 0, 'vid2' => 0 ) ); ?>
	</template>
	<script>
		window.gkGameDescNextIdx = <?php echo (int) $next_idx; ?>;
	</script>
	<?php
}

/**
 * Eine Metabox-Zeile (HTML).
 *
 * @param int|string     $idx   Index oder Platzhalter __IDX__.
 * @param array<string,mixed> $block Blockdaten.
 */
function globalkeys_game_description_layout_metabox_row( $idx, $block ) {
	$type = isset( $block['type'] ) ? (string) $block['type'] : 'text';
	if ( ! in_array( $type, array( 'text', 'images', 'videos' ), true ) ) {
		$type = 'text';
	}
	$title = isset( $block['title'] ) ? (string) $block['title'] : '';
	$html  = isset( $block['html'] ) ? (string) $block['html'] : '';
	$img1  = isset( $block['img1'] ) ? (int) $block['img1'] : 0;
	$img2  = isset( $block['img2'] ) ? (int) $block['img2'] : 0;
	$vid1  = isset( $block['vid1'] ) ? (int) $block['vid1'] : 0;
	$vid2  = isset( $block['vid2'] ) ? (int) $block['vid2'] : 0;
	$ikey  = ( '__IDX__' === $idx || '__IDX__' === (string) $idx ) ? '__IDX__' : (string) (int) $idx;
	$id1   = 'gk_game_desc_img1_' . $ikey;
	$id2   = 'gk_game_desc_img2_' . $ikey;
	$v_id1 = 'gk_game_desc_vid1_' . $ikey;
	$v_id2 = 'gk_game_desc_vid2_' . $ikey;
	$prev1 = $img1 >= 1 ? wp_get_attachment_image( $img1, 'thumbnail', false, array( 'style' => 'max-width:72px;height:auto;border-radius:6px;' ) ) : '';
	$prev2 = $img2 >= 1 ? wp_get_attachment_image( $img2, 'thumbnail', false, array( 'style' => 'max-width:72px;height:auto;border-radius:6px;' ) ) : '';
	$v_prev1 = '';
	$v_prev2 = '';
	if ( $vid1 >= 1 && globalkeys_game_desc_is_layout_video_attachment( $vid1 ) ) {
		$vu = wp_get_attachment_url( $vid1 );
		if ( is_string( $vu ) && $vu !== '' ) {
			$v_prev1 = '<video muted playsinline preload="metadata" style="max-width:72px;height:auto;border-radius:6px;vertical-align:middle;" src="' . esc_url( $vu ) . '" aria-hidden="true"></video>';
		}
	}
	if ( $vid2 >= 1 && globalkeys_game_desc_is_layout_video_attachment( $vid2 ) ) {
		$vu = wp_get_attachment_url( $vid2 );
		if ( is_string( $vu ) && $vu !== '' ) {
			$v_prev2 = '<video muted playsinline preload="metadata" style="max-width:72px;height:auto;border-radius:6px;vertical-align:middle;" src="' . esc_url( $vu ) . '" aria-hidden="true"></video>';
		}
	}
	$type_labels = array(
		'text'   => __( 'Text', 'globalkeys' ),
		'images' => __( 'Zwei Bilder', 'globalkeys' ),
		'videos' => __( 'Zwei Videos', 'globalkeys' ),
	);
	$type_label    = isset( $type_labels[ $type ] ) ? $type_labels[ $type ] : $type;
	$row_num_label = ( '__IDX__' === $ikey ) ? '…' : (string) ( (int) $ikey + 1 );
	?>
	<div class="gk-game-desc-row postbox" style="padding:0;margin:0;border:1px solid #c3c4c7;">
		<div class="gk-game-desc-row__head" style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;padding:10px 12px;background:#f6f7f7;border-bottom:1px solid #dcdcde;">
			<button type="button" class="button button-small gk-game-desc-toggle-body" aria-expanded="true" title="<?php esc_attr_e( 'Inhalt ein- oder ausklappen', 'globalkeys' ); ?>">
				<span class="gk-game-desc-toggle-icon" aria-hidden="true">▼</span>
			</button>
			<span class="gk-game-desc-row__index" style="font-weight:600;color:#1d2327;min-width:2.5em;">#<?php echo esc_html( $row_num_label ); ?></span>
			<label class="screen-reader-text" for="gk_game_desc_title_<?php echo esc_attr( $ikey ); ?>"><?php esc_html_e( 'Abschnittsüberschrift', 'globalkeys' ); ?></label>
			<input type="text" id="gk_game_desc_title_<?php echo esc_attr( $ikey ); ?>" class="gk-game-desc-row__title large-text" name="gk_gdb[<?php echo esc_attr( $ikey ); ?>][title]" value="<?php echo esc_attr( $title ); ?>" style="flex:1;min-width:12rem;max-width:24rem;" placeholder="<?php esc_attr_e( 'Überschrift (optional)', 'globalkeys' ); ?>" />
			<label class="screen-reader-text" for="gk_game_desc_type_<?php echo esc_attr( $ikey ); ?>"><?php esc_html_e( 'Block-Typ', 'globalkeys' ); ?></label>
			<select id="gk_game_desc_type_<?php echo esc_attr( $ikey ); ?>" class="gk-game-desc-type" name="gk_gdb[<?php echo esc_attr( $ikey ); ?>][type]" style="max-width:14rem;">
				<option value="text" <?php selected( $type, 'text' ); ?>><?php esc_html_e( 'Text', 'globalkeys' ); ?></option>
				<option value="images" <?php selected( $type, 'images' ); ?>><?php esc_html_e( 'Zwei Bilder (groß)', 'globalkeys' ); ?></option>
				<option value="videos" <?php selected( $type, 'videos' ); ?>><?php esc_html_e( 'Zwei Videos (MP4, Loop)', 'globalkeys' ); ?></option>
			</select>
			<span class="gk-game-desc-row__summary" style="flex-basis:100%;font-size:12px;color:#646970;margin:-4px 0 0 0;padding-left:2.25rem;"><?php echo esc_html( $title !== '' ? $title : __( 'Ohne Überschrift', 'globalkeys' ) ); ?> · <?php echo esc_html( $type_label ); ?></span>
			<span style="flex:1"></span>
			<button type="button" class="button button-small gk-game-desc-move-up" title="<?php esc_attr_e( 'Nach oben', 'globalkeys' ); ?>"><?php esc_html_e( '↑', 'globalkeys' ); ?></button>
			<button type="button" class="button button-small gk-game-desc-move-down" title="<?php esc_attr_e( 'Nach unten', 'globalkeys' ); ?>"><?php esc_html_e( '↓', 'globalkeys' ); ?></button>
			<button type="button" class="button button-small gk-game-desc-remove-row" style="color:#b32d2e;border-color:#dba617;"><?php esc_html_e( 'Entfernen', 'globalkeys' ); ?></button>
		</div>
		<div class="gk-game-desc-row__body" style="padding:12px;">
		<div class="gk-game-desc-field gk-game-desc-field--text" style="display:none;">
			<label><strong><?php esc_html_e( 'Text (HTML erlaubt)', 'globalkeys' ); ?></strong></label>
			<textarea name="gk_gdb[<?php echo esc_attr( $ikey ); ?>][html]" rows="6" class="large-text" style="width:100%;margin-top:4px;"><?php echo esc_textarea( $html ); ?></textarea>
		</div>
		<div class="gk-game-desc-field gk-game-desc-field--images" style="display:none;">
			<div style="display:flex;flex-wrap:wrap;gap:16px;align-items:flex-end;margin-top:4px;">
				<div>
					<p style="margin:0 0 4px;"><strong><?php esc_html_e( 'Bild 1', 'globalkeys' ); ?></strong></p>
					<input type="hidden" name="gk_gdb[<?php echo esc_attr( $ikey ); ?>][img1]" id="<?php echo esc_attr( $id1 ); ?>" value="<?php echo $img1 >= 1 ? (int) $img1 : ''; ?>" />
					<span class="gk-game-desc-img-preview"><?php echo $prev1; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<p style="margin:6px 0 0;"><button type="button" class="button gk-game-desc-pick-img" data-input="<?php echo esc_attr( $id1 ); ?>"><?php esc_html_e( 'Aus Mediathek wählen', 'globalkeys' ); ?></button></p>
				</div>
				<div>
					<p style="margin:0 0 4px;"><strong><?php esc_html_e( 'Bild 2', 'globalkeys' ); ?></strong></p>
					<input type="hidden" name="gk_gdb[<?php echo esc_attr( $ikey ); ?>][img2]" id="<?php echo esc_attr( $id2 ); ?>" value="<?php echo $img2 >= 1 ? (int) $img2 : ''; ?>" />
					<span class="gk-game-desc-img-preview"><?php echo $prev2; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<p style="margin:6px 0 0;"><button type="button" class="button gk-game-desc-pick-img" data-input="<?php echo esc_attr( $id2 ); ?>"><?php esc_html_e( 'Aus Mediathek wählen', 'globalkeys' ); ?></button></p>
				</div>
			</div>
		</div>
		<div class="gk-game-desc-field gk-game-desc-field--videos" style="display:none;">
			<p class="description" style="margin:0 0 8px;"><?php esc_html_e( 'Kurze MP4-Dateien aus der Mediathek; auf der Produktseite: stumm, Endlosschleife, gleiche Größe wie das Bildpaar.', 'globalkeys' ); ?></p>
			<div style="display:flex;flex-wrap:wrap;gap:16px;align-items:flex-end;margin-top:4px;">
				<div>
					<p style="margin:0 0 4px;"><strong><?php esc_html_e( 'Video 1', 'globalkeys' ); ?></strong></p>
					<input type="hidden" name="gk_gdb[<?php echo esc_attr( $ikey ); ?>][vid1]" id="<?php echo esc_attr( $v_id1 ); ?>" value="<?php echo $vid1 >= 1 ? (int) $vid1 : ''; ?>" />
					<span class="gk-game-desc-video-preview"><?php echo $v_prev1; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<p style="margin:6px 0 0;"><button type="button" class="button gk-game-desc-pick-video" data-input="<?php echo esc_attr( $v_id1 ); ?>"><?php esc_html_e( 'Video aus Mediathek', 'globalkeys' ); ?></button></p>
				</div>
				<div>
					<p style="margin:0 0 4px;"><strong><?php esc_html_e( 'Video 2', 'globalkeys' ); ?></strong></p>
					<input type="hidden" name="gk_gdb[<?php echo esc_attr( $ikey ); ?>][vid2]" id="<?php echo esc_attr( $v_id2 ); ?>" value="<?php echo $vid2 >= 1 ? (int) $vid2 : ''; ?>" />
					<span class="gk-game-desc-video-preview"><?php echo $v_prev2; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<p style="margin:6px 0 0;"><button type="button" class="button gk-game-desc-pick-video" data-input="<?php echo esc_attr( $v_id2 ); ?>"><?php esc_html_e( 'Video aus Mediathek', 'globalkeys' ); ?></button></p>
				</div>
			</div>
		</div>
		</div>
	</div>
	<?php
}

/**
 * @param int $post_id Post-ID.
 */
function globalkeys_save_game_description_blocks( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( ! isset( $_POST['gk_game_desc_blocks_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gk_game_desc_blocks_nonce'] ) ), 'gk_game_desc_blocks_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( get_post_type( $post_id ) !== 'product' ) {
		return;
	}
	if ( empty( $_POST['gk_gdb'] ) || ! is_array( $_POST['gk_gdb'] ) ) {
		delete_post_meta( $post_id, GK_GAME_DESC_BLOCKS_META );
		return;
	}
	$out = array();
	foreach ( wp_unslash( $_POST['gk_gdb'] ) as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$type  = isset( $row['type'] ) ? sanitize_key( $row['type'] ) : '';
		$title = isset( $row['title'] ) ? sanitize_text_field( $row['title'] ) : '';
		if ( $type === 'text' ) {
			$html = isset( $row['html'] ) ? wp_kses_post( $row['html'] ) : '';
			if ( $title === '' && trim( wp_strip_all_tags( $html ) ) === '' ) {
				continue;
			}
			$out[] = array(
				'type'  => 'text',
				'title' => $title,
				'html'  => $html,
			);
		} elseif ( $type === 'images' ) {
			$i1 = isset( $row['img1'] ) ? absint( $row['img1'] ) : 0;
			$i2 = isset( $row['img2'] ) ? absint( $row['img2'] ) : 0;
			if ( $title === '' && $i1 < 1 && $i2 < 1 ) {
				continue;
			}
			$out[] = array(
				'type'  => 'images',
				'title' => $title,
				'img1'  => $i1,
				'img2'  => $i2,
			);
		} elseif ( $type === 'videos' ) {
			$va = isset( $row['vid1'] ) ? absint( $row['vid1'] ) : 0;
			$vb = isset( $row['vid2'] ) ? absint( $row['vid2'] ) : 0;
			if ( $title === '' && $va < 1 && $vb < 1 ) {
				continue;
			}
			$out[] = array(
				'type'  => 'videos',
				'title' => $title,
				'vid1'  => $va,
				'vid2'  => $vb,
			);
		}
	}
	if ( empty( $out ) ) {
		delete_post_meta( $post_id, GK_GAME_DESC_BLOCKS_META );
	} else {
		update_post_meta( $post_id, GK_GAME_DESC_BLOCKS_META, $out );
	}
}
add_action( 'save_post_product', 'globalkeys_save_game_description_blocks', 10, 1 );

/**
 * Admin-Skripte (Mediathek, neue Zeilen).
 *
 * @param string $hook_suffix Screen.
 */
function globalkeys_game_description_layout_admin_assets( $hook_suffix ) {
	if ( $hook_suffix !== 'post.php' && $hook_suffix !== 'post-new.php' ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || $screen->post_type !== 'product' ) {
		return;
	}
	wp_enqueue_media();
	$path = get_template_directory() . '/js/gk-game-description-layout-admin.js';
	if ( is_readable( $path ) ) {
		wp_enqueue_script(
			'gk-game-description-layout-admin',
			get_template_directory_uri() . '/js/gk-game-description-layout-admin.js',
			array( 'jquery' ),
			(string) filemtime( $path ),
			true
		);
		wp_localize_script(
			'gk-game-description-layout-admin',
			'gkGameDescLayoutAdmin',
			array(
				'noTitle'    => __( 'Ohne Überschrift', 'globalkeys' ),
				'typeText'   => __( 'Text', 'globalkeys' ),
				'typeImages' => __( 'Zwei Bilder', 'globalkeys' ),
				'typeVideos' => __( 'Zwei Videos', 'globalkeys' ),
			)
		);
	}
}
add_action( 'admin_enqueue_scripts', 'globalkeys_game_description_layout_admin_assets' );
