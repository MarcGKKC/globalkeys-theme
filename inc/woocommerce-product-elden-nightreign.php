<?php
/**
 * ELDEN RING NIGHTREIGN: Kartenbild (elden-ring-n-p2) vs. Key-Art für Heros (bisheriges Cover).
 *
 * Auf anderem Shop: Konstanten unten anpassen, falls andere Medien-IDs.
 *
 * @package globalkeys
 */

defined( 'ABSPATH' ) || exit;

/** Medien-ID des Key-Art / alten Produktcovers – nur für Hero, wenn kein „Hero-Bild (Medien-ID)“ gesetzt */
if ( ! defined( 'GLOBALKEYS_ELDEN_NIGHTREIGN_HERO_ATTACHMENT_ID' ) ) {
	define( 'GLOBALKEYS_ELDEN_NIGHTREIGN_HERO_ATTACHMENT_ID', 57 );
}

/** Dateiname des neuen Kartenbildes (Mediathek _wp_attached_file enthält diesen Teilstring) + Theme-Fallback */
if ( ! defined( 'GLOBALKEYS_ELDEN_NIGHTREIGN_CARD_FILE_SUBSTRING' ) ) {
	define( 'GLOBALKEYS_ELDEN_NIGHTREIGN_CARD_FILE_SUBSTRING', 'elden-ring-n-p2' );
}

if ( ! defined( 'GLOBALKEYS_ELDEN_NIGHTREIGN_CARD_THEME_FILE' ) ) {
	define( 'GLOBALKEYS_ELDEN_NIGHTREIGN_CARD_THEME_FILE', 'elden-ring-n-p2.jpg' );
}

/**
 * @param WC_Product|int|null $product Produkt oder ID.
 */
function globalkeys_is_elden_ring_nightreign_product( $product ) {
	if ( is_numeric( $product ) ) {
		$product = wc_get_product( (int) $product );
	}
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return false;
	}
	$slug = strtolower( $product->get_slug() );
	$name = strtolower( $product->get_name() );
	if ( strpos( $slug, 'nightreign' ) !== false && strpos( $slug, 'elden' ) !== false ) {
		return true;
	}
	return strpos( $name, 'nightreign' ) !== false
		&& strpos( $name, 'elden' ) !== false
		&& strpos( $name, 'ring' ) !== false;
}

/**
 * @param string $substring z. B. elden-ring-n-p2.
 * @return int Attachment-ID oder 0.
 */
function globalkeys_find_attachment_id_by_file_substring( $substring ) {
	global $wpdb;
	$substring = $wpdb->esc_like( $substring );
	$id        = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id DESC LIMIT 1",
			'%' . $substring . '%'
		)
	);
	return $id > 0 ? $id : 0;
}

/**
 * Key-Art für Heros (altes Cover), wenn Produkt Nightreign ist.
 *
 * @param WC_Product $product Produkt.
 * @return int 0 wenn nicht zutrifft.
 */
function globalkeys_get_elden_nightreign_hero_attachment_id( $product ) {
	if ( ! globalkeys_is_elden_ring_nightreign_product( $product ) ) {
		return 0;
	}
	$hid = (int) GLOBALKEYS_ELDEN_NIGHTREIGN_HERO_ATTACHMENT_ID;
	return $hid > 0 ? $hid : 0;
}

/**
 * Bild-URL für Listen/Suche (Nightreign: Kartenbild aus Mediathek oder Theme-Datei).
 *
 * @param WC_Product $product Produkt.
 * @param string     $size    Bildgröße (nur bei Attachment).
 * @return string
 */
function globalkeys_get_product_listing_thumbnail_url( $product, $size = 'globalkeys-search-dropdown' ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return '';
	}
	if ( globalkeys_is_elden_ring_nightreign_product( $product ) ) {
		$aid = globalkeys_find_attachment_id_by_file_substring( GLOBALKEYS_ELDEN_NIGHTREIGN_CARD_FILE_SUBSTRING );
		if ( $aid ) {
			$url = wp_get_attachment_image_url( $aid, $size );
			if ( $url ) {
				return $url;
			}
		}
		return trailingslashit( get_template_directory_uri() ) . 'Pictures/' . rawurlencode( GLOBALKEYS_ELDEN_NIGHTREIGN_CARD_THEME_FILE );
	}
	$img_id = (int) $product->get_image_id();
	if ( $img_id ) {
		$url = wp_get_attachment_image_url( $img_id, $size );
		return $url ? (string) $url : '';
	}
	return function_exists( 'wc_placeholder_img_src' ) ? (string) wc_placeholder_img_src( 'woocommerce_thumbnail' ) : '';
}

/**
 * Produktkarten-Bild (Bestseller etc.): Nightreign → n-p2, sonst Standard.
 *
 * @param WC_Product $product Produkt.
 * @param string     $size    Bildgröße.
 */
function globalkeys_output_product_card_featured_image( $product, $size = 'globalkeys-product-card' ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		echo function_exists( 'wc_placeholder_img' ) ? wc_placeholder_img( 'woocommerce_thumbnail' ) : '';
		return;
	}

	if ( globalkeys_is_elden_ring_nightreign_product( $product ) ) {
		$aid = globalkeys_find_attachment_id_by_file_substring( GLOBALKEYS_ELDEN_NIGHTREIGN_CARD_FILE_SUBSTRING );
		if ( $aid ) {
			echo wp_get_attachment_image(
				$aid,
				$size,
				false,
				array(
					'alt'      => '',
					'class'    => 'gk-bestseller-product-img',
					'loading'  => 'lazy',
					'decoding' => 'async',
				)
			);
			return;
		}
		$src = trailingslashit( get_template_directory_uri() ) . 'Pictures/' . rawurlencode( GLOBALKEYS_ELDEN_NIGHTREIGN_CARD_THEME_FILE );
		printf(
			'<img src="%s" alt="" class="gk-bestseller-product-img" loading="lazy" decoding="async" width="1536" height="1536" />',
			esc_url( $src )
		);
		return;
	}

	$gk_bestseller_img_id = (int) $product->get_image_id();
	if ( $gk_bestseller_img_id ) {
		echo wp_get_attachment_image(
			$gk_bestseller_img_id,
			$size,
			false,
			array(
				'alt'      => '',
				'class'    => 'gk-bestseller-product-img',
				'loading'  => 'lazy',
				'decoding' => 'async',
			)
		);
	} elseif ( function_exists( 'wc_placeholder_img' ) ) {
		echo wc_placeholder_img( 'woocommerce_thumbnail' );
	}
}

/**
 * WooCommerce-Produktbild (Miniatur) = Kartenbild n-p2, sobald die Datei in der Mediathek existiert.
 *
 * @param mixed  $check      Filter-Check.
 * @param int    $object_id  Post-ID.
 * @param string $meta_key   Meta-Schlüssel.
 * @param bool   $single     Single.
 * @param string $meta_type  meta_type.
 * @return mixed
 */
function globalkeys_filter_elden_nightreign_thumbnail_id( $check, $object_id, $meta_key, $single, $meta_type = 'post' ) {
	if ( 'post' !== $meta_type || '_thumbnail_id' !== $meta_key ) {
		return $check;
	}
	if ( 'product' !== get_post_type( $object_id ) ) {
		return $check;
	}
	if ( ! function_exists( 'wc_get_product' ) ) {
		return $check;
	}
	$product = wc_get_product( $object_id );
	if ( ! $product || ! globalkeys_is_elden_ring_nightreign_product( $product ) ) {
		return $check;
	}
	$new_id = globalkeys_find_attachment_id_by_file_substring( GLOBALKEYS_ELDEN_NIGHTREIGN_CARD_FILE_SUBSTRING );
	if ( $new_id < 1 ) {
		return $check;
	}
	return $single ? (string) $new_id : array( (string) $new_id );
}
add_filter( 'get_post_metadata', 'globalkeys_filter_elden_nightreign_thumbnail_id', 20, 5 );

/**
 * WooCommerce lädt Thumbnails per get_post_meta( $id ) ohne Key – dabei läuft get_post_metadata nicht.
 * Dieser Filter setzt die sichtbare Produktbild-ID auf das Kartenbild (n-p2), sobald es in der Mediathek existiert.
 *
 * @param int|string $image_id Aktuelle Bild-ID.
 * @param WC_Product $product  Produkt.
 * @return int|string
 */
function globalkeys_filter_wc_product_image_id_nightreign( $image_id, $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) || ! globalkeys_is_elden_ring_nightreign_product( $product ) ) {
		return $image_id;
	}
	$n = globalkeys_find_attachment_id_by_file_substring( GLOBALKEYS_ELDEN_NIGHTREIGN_CARD_FILE_SUBSTRING );
	if ( $n > 0 && wp_attachment_is_image( $n ) ) {
		return $n;
	}
	return $image_id;
}
add_filter( 'woocommerce_product_get_image_id', 'globalkeys_filter_wc_product_image_id_nightreign', 10, 2 );

/**
 * Produktseite: ohne Medien-Anhang n-p2 Hauptbild-URL durch Theme-Datei ersetzen (Key-Art bleibt in DB).
 *
 * @param string $html               Galerie-HTML.
 * @param int    $post_thumbnail_id  Nach WC-Filter = effektive Bild-ID.
 * @return string
 */
function globalkeys_nightreign_single_product_main_image_theme_src( $html, $post_thumbnail_id ) {
	global $product;
	if ( ! $product || ! is_a( $product, 'WC_Product' ) || ! globalkeys_is_elden_ring_nightreign_product( $product ) ) {
		return $html;
	}
	if ( globalkeys_find_attachment_id_by_file_substring( GLOBALKEYS_ELDEN_NIGHTREIGN_CARD_FILE_SUBSTRING ) ) {
		return $html;
	}
	$hero_id = (int) GLOBALKEYS_ELDEN_NIGHTREIGN_HERO_ATTACHMENT_ID;
	if ( $hero_id < 1 || (int) $post_thumbnail_id !== $hero_id ) {
		return $html;
	}
	$src  = trailingslashit( get_template_directory_uri() ) . 'Pictures/' . rawurlencode( GLOBALKEYS_ELDEN_NIGHTREIGN_CARD_THEME_FILE );
	$html = preg_replace( '/\bsrc="[^"]*"/', 'src="' . esc_url( $src ) . '"', $html, 1 );
	$html = preg_replace( '/\bsrcset="[^"]*"\s*/', '', $html );
	$html = preg_replace( '/\bsizes="[^"]*"\s*/', '', $html );
	return $html;
}
add_filter( 'woocommerce_single_product_image_thumbnail_html', 'globalkeys_nightreign_single_product_main_image_theme_src', 20, 2 );
