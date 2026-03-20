<?php
/**
 * Produkt-Meta: URL zu einem kurzen Preview-Video (WebM/MP4) pro WooCommerce-Produkt.
 *
 * @package globalkeys
 */

defined( 'ABSPATH' ) || exit;

/**
 * Eingabefeld unter „Produktdaten“ → Tab „Allgemein“.
 */
function globalkeys_product_trailer_url_field() {
	global $post;

	$value = $post ? get_post_meta( $post->ID, '_gk_product_trailer_url', true ) : '';
	?>
	<div class="options_group">
		<?php
		woocommerce_wp_text_input(
			array(
				'id'          => '_gk_product_trailer_url',
				'name'        => '_gk_product_trailer_url',
				'label'       => __( 'Preview-Video (URL)', 'globalkeys' ),
				'placeholder' => 'https://… oder /wp-content/themes/.../datei.webm',
				'description' => __( 'Volle URL zur WebM- oder MP4-Datei (z. B. aus der Mediathek oder Theme-Ordner Previews/). Wird z. B. für Hover-Trailer auf Karten genutzt.', 'globalkeys' ),
				'desc_tip'    => true,
				'value'       => $value,
			)
		);
		?>
	</div>
	<?php
}
add_action( 'woocommerce_product_options_general_product_data', 'globalkeys_product_trailer_url_field' );

/**
 * URL speichern.
 *
 * @param WC_Product $product Produktobjekt.
 */
function globalkeys_save_product_trailer_url( $product ) {
	if ( ! isset( $_POST['_gk_product_trailer_url'] ) ) {
		return;
	}

	if ( ! is_a( $product, 'WC_Product' ) ) {
		return;
	}

	$url = trim( wp_unslash( $_POST['_gk_product_trailer_url'] ) );

	if ( $url === '' ) {
		$product->delete_meta_data( '_gk_product_trailer_url' );
		return;
	}

	$safe = esc_url_raw( $url );
	if ( $safe === '' ) {
		$safe = sanitize_text_field( $url );
	}

	$product->update_meta_data( '_gk_product_trailer_url', $safe );
}
add_action( 'woocommerce_admin_process_product_object', 'globalkeys_save_product_trailer_url', 10, 1 );

/**
 * Hilfsfunktion: Trailer-URL eines Produkts (für Templates).
 *
 * @param WC_Product|int $product Produkt oder Post-ID.
 * @return string Leere Zeichenkette oder URL.
 */
function globalkeys_get_product_trailer_url( $product ) {
	if ( is_numeric( $product ) ) {
		$product = wc_get_product( (int) $product );
	}
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return '';
	}
	$url = $product->get_meta( '_gk_product_trailer_url' );
	return is_string( $url ) ? trim( $url ) : '';
}

/**
 * Trailer-URL für HTML src auflösen (/pfad → vollständige URL).
 *
 * @param string $url Rohwert aus Meta.
 * @return string Leer oder gültige URL.
 */
function globalkeys_resolve_product_trailer_url( $url ) {
	$url = trim( (string) $url );
	if ( $url === '' ) {
		return '';
	}

	/*
	 * Häufiger Eingabefehler: http://wp-content/themes/... (ohne Domain – „wp-content“ wird als Host geparst).
	 * Korrigieren zu /wp-content/themes/... auf dieser Site.
	 */
	$parts = wp_parse_url( $url );
	if ( is_array( $parts ) && ! empty( $parts['host'] ) && strtolower( $parts['host'] ) === 'wp-content' && ! empty( $parts['path'] ) ) {
		$path = '/wp-content' . $parts['path'];
		return esc_url( home_url( $path ) );
	}

	if ( preg_match( '#^https?://#i', $url ) ) {
		return esc_url( $url );
	}
	if ( strpos( $url, '/' ) === 0 ) {
		return esc_url( home_url( $url ) );
	}
	return esc_url( $url );
}
