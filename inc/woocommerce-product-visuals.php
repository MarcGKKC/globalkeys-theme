<?php
/**
 * Produktdetail: „Game Trailer“ (Haupttrailer) und „Game Images“ (Galerie) als getrennte Sections.
 *
 * Haupttrailer: Meta _gk_product_main_trailer_url (Vimeo/YouTube). Preview bleibt _gk_product_trailer_url (Sidebar/Karten).
 * Spielbilder: nur WooCommerce-Produktgalerie (ohne Produkt-Hauptbild).
 *
 * @package globalkeys
 */

defined( 'ABSPATH' ) || exit;

/**
 * Anhang-IDs für „Game Images“: nur Produktgalerie (nicht das Produkt-Hauptbild).
 *
 * @param WC_Product|null $product Produkt.
 * @return int[]
 */
function globalkeys_get_product_page_gallery_attachment_ids( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return array();
	}
	$ids = array();
	foreach ( $product->get_gallery_image_ids() as $gid ) {
		$gid = (int) $gid;
		if ( $gid >= 1 && ! in_array( $gid, $ids, true ) ) {
			$ids[] = $gid;
		}
	}
	return apply_filters( 'gk_visuals_section_gallery_attachment_ids', $ids, $product );
}

/**
 * @param WC_Product|null $product Produkt.
 * @return bool Ob mindestens ein Galerie-Bild gerendert werden kann.
 */
function globalkeys_product_page_has_game_images( $product ) {
	foreach ( globalkeys_get_product_page_gallery_attachment_ids( $product ) as $aid ) {
		if ( wp_attachment_is_image( (int) $aid ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Anzahl der darstellbaren Galerie-Bilder für „Game Images“ (ohne Hauptbild).
 *
 * @param WC_Product|null $product Produkt.
 * @return int
 */
function globalkeys_get_product_page_game_images_count( $product ) {
	$n = 0;
	foreach ( globalkeys_get_product_page_gallery_attachment_ids( $product ) as $aid ) {
		if ( wp_attachment_is_image( (int) $aid ) ) {
			++$n;
		}
	}
	return (int) apply_filters( 'gk_game_images_section_image_count', $n, $product );
}

/**
 * @param WC_Product|null $product Produkt.
 * @return bool Ob ein Haupttrailer-oEmbed ausgegeben werden kann.
 */
function globalkeys_product_page_has_main_trailer_embed( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return false;
	}
	if ( ! apply_filters( 'gk_visuals_section_show_trailer', true, $product ) ) {
		return false;
	}
	$url = function_exists( 'globalkeys_get_product_main_trailer_url' )
		? globalkeys_get_product_main_trailer_url( $product )
		: '';
	$url = apply_filters( 'gk_visuals_main_trailer_url', $url, $product );
	if ( ! is_string( $url ) || $url === '' || ! function_exists( 'globalkeys_get_product_trailer_oembed_html' ) ) {
		return false;
	}
	$embed = globalkeys_get_product_trailer_oembed_html( $url );
	return is_string( $embed ) && $embed !== '';
}

/**
 * Standard-Inhalt Section „Game Trailer“: nur eingebetteter Trailer.
 *
 * @param WC_Product|null $product Produkt.
 */
function globalkeys_render_product_game_trailer_default( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return;
	}
	$parts = array();
	if ( apply_filters( 'gk_visuals_section_show_trailer', true, $product ) ) {
		$url = function_exists( 'globalkeys_get_product_main_trailer_url' )
			? globalkeys_get_product_main_trailer_url( $product )
			: '';
		$url = apply_filters( 'gk_visuals_main_trailer_url', $url, $product );
		if ( is_string( $url ) && $url !== '' && function_exists( 'globalkeys_get_product_trailer_oembed_html' ) ) {
			$embed = globalkeys_get_product_trailer_oembed_html( $url );
			if ( $embed !== '' ) {
				ob_start();
				echo '<div class="gk-product-page-visuals__trailer">';
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- oEmbed vom Provider.
				echo $embed;
				echo '</div>';
				$parts[] = ob_get_clean();
			}
		}
	}
	$parts = apply_filters( 'gk_visuals_section_default_parts', $parts, $product );
	if ( empty( $parts ) ) {
		return;
	}
	echo '<div class="gk-product-page-visuals__body">';
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo implode( '', $parts );
	echo '</div>';
}

/**
 * Standard-Inhalt Section „Game Images“: nur Bildraster.
 *
 * @param WC_Product|null $product Produkt.
 */
function globalkeys_render_product_game_images_default( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return;
	}
	$ids           = globalkeys_get_product_page_gallery_attachment_ids( $product );
	$gallery_items = array();
	$gi            = 0;
	foreach ( $ids as $aid ) {
		$aid = (int) $aid;
		if ( ! wp_attachment_is_image( $aid ) ) {
			continue;
		}
		$alt = get_post_meta( $aid, '_wp_attachment_image_alt', true );
		$alt = is_string( $alt ) ? $alt : '';
		$is_first    = ( 0 === $gi );
		$is_featured = ( $gi < 2 );
		$lightbox_i  = $gi;
		$gi++;
		$full_url    = wp_get_attachment_url( $aid );
		$full_url    = is_string( $full_url ) ? $full_url : '';
		$full_url    = apply_filters( 'gk_game_images_attachment_full_url', $full_url, $aid, $product );
		// Lightbox-Leiste: größere Quelle als woocommerce_gallery_thumbnail (~100px), sonst wirkt es auf Retina/Breite verpixelt.
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
		ob_start();
		$item_class = 'gk-product-page-game-images__gallery-item' . ( $is_featured ? ' gk-product-page-game-images__gallery-item--featured' : '' );
		echo '<li class="' . esc_attr( $item_class ) . '" role="listitem">';
		$img_class = 'gk-product-page-game-images__gallery-img' . ( $is_featured ? ' gk-product-page-game-images__gallery-img--featured' : '' );
		$img_html  = wp_get_attachment_image(
			$aid,
			$is_featured ? 'full' : 'large',
			false,
			array(
				'class'   => $img_class,
				'loading' => $is_first ? 'eager' : 'lazy',
				'alt'     => $alt !== '' ? $alt : esc_attr( $product->get_name() ),
			)
		);
		if ( is_string( $full_url ) && $full_url !== '' ) {
			$link_label = $alt !== '' ? $alt : $product->get_name();
			$aria_label = __( 'Open image gallery', 'globalkeys' ) . ': ' . $link_label;
			echo '<a class="gk-product-page-game-images__gallery-link" href="' . esc_url( $full_url ) . '"';
			echo ' data-gk-lightbox-full="' . esc_url( $full_url ) . '"';
			echo ' data-gk-lightbox-index="' . (int) $lightbox_i . '"';
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
		$gallery_items[] = ob_get_clean();
	}
	$gallery_items = apply_filters( 'gk_game_images_section_gallery_items', $gallery_items, $product );
	if ( empty( $gallery_items ) ) {
		return;
	}
	$featured_items = array_slice( $gallery_items, 0, 2 );
	$thumb_items    = array_slice( $gallery_items, 2 );
	$thumb_pairs    = array_values( array_chunk( $thumb_items, 2 ) );
	$pair_count     = count( $thumb_pairs );

	$column_right_has_content = isset( $featured_items[1] );
	for ( $ti = 1; $ti < $pair_count; $ti += 2 ) {
		$column_right_has_content = true;
		break;
	}

	echo '<div class="gk-product-page-game-images__body">';
	if ( ! $column_right_has_content ) {
		echo '<div class="gk-product-page-game-images__grid gk-product-page-game-images__grid--single" role="presentation">';
		echo '<div class="gk-product-page-game-images__column">';
		if ( isset( $featured_items[0] ) ) {
			echo '<ul class="gk-product-page-game-images__gallery gk-product-page-game-images__gallery--featured-cell" role="list">';
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- <li> aus wp_get_attachment_image.
			echo $featured_items[0];
			echo '</ul>';
		}
		foreach ( $thumb_pairs as $pair ) {
			if ( empty( $pair ) ) {
				continue;
			}
			echo '<ul class="gk-product-page-game-images__gallery gk-product-page-game-images__gallery--thumbs-pair" role="list">';
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- <li> aus wp_get_attachment_image.
			echo implode( '', $pair );
			echo '</ul>';
		}
		echo '</div></div>';
	} else {
		echo '<div class="gk-product-page-game-images__grid" role="presentation">';
		for ( $col = 0; $col < 2; $col++ ) {
			echo '<div class="gk-product-page-game-images__column">';
			if ( isset( $featured_items[ $col ] ) ) {
				echo '<ul class="gk-product-page-game-images__gallery gk-product-page-game-images__gallery--featured-cell" role="list">';
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- <li> aus wp_get_attachment_image.
				echo $featured_items[ $col ];
				echo '</ul>';
			}
			for ( $ti = $col; $ti < $pair_count; $ti += 2 ) {
				echo '<ul class="gk-product-page-game-images__gallery gk-product-page-game-images__gallery--thumbs-pair" role="list">';
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- <li> aus wp_get_attachment_image.
				echo implode( '', $thumb_pairs[ $ti ] );
				echo '</ul>';
			}
			echo '</div>';
		}
		echo '</div>';
	}
	echo '</div>';
}

/**
 * @deprecated 2.x Nutze globalkeys_render_product_game_trailer_default() und globalkeys_render_product_game_images_default().
 *
 * @param WC_Product|null $product Produkt.
 */
function globalkeys_render_product_visuals_default( $product ) {
	globalkeys_render_product_game_trailer_default( $product );
	globalkeys_render_product_game_images_default( $product );
}

/**
 * Skript + Texte für Game-Images-Lightbox (nur Produktdetail mit Kaufkarten-Layout).
 */
function globalkeys_enqueue_game_images_lightbox_assets() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	$path = get_template_directory() . '/js/gk-game-images-lightbox.js';
	if ( ! is_readable( $path ) ) {
		return;
	}
	wp_enqueue_script(
		'globalkeys-game-images-lightbox',
		get_template_directory_uri() . '/js/gk-game-images-lightbox.js',
		array(),
		(string) filemtime( $path ),
		true
	);
	wp_localize_script(
		'globalkeys-game-images-lightbox',
		'gkGameImagesLightbox',
		array(
			'i18nClose'          => __( 'Schließen', 'globalkeys' ),
			'i18nFullscreen'     => __( 'Vollbild', 'globalkeys' ),
			'i18nExitFullscreen' => __( 'Vollbild beenden', 'globalkeys' ),
			'i18nDialog'         => __( 'Bildergalerie', 'globalkeys' ),
			'i18nThumbs'         => __( 'Bild auswählen', 'globalkeys' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'globalkeys_enqueue_game_images_lightbox_assets', 30 );
