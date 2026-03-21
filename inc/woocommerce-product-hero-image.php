<?php
/**
 * Optionales Hero-Key-Art-Bild pro Produkt (nur Startseiten-Hero), unabhängig vom Produktkarten-Bild.
 *
 * @package globalkeys
 */

defined( 'ABSPATH' ) || exit;

/**
 * URL des Hero-Bildes oder leer.
 *
 * @param WC_Product|int $product Produkt oder Post-ID.
 * @param string           $size    Bildgröße (default full).
 * @return string
 */
function globalkeys_get_product_hero_image_url( $product, $size = 'full' ) {
	if ( is_numeric( $product ) ) {
		$product = wc_get_product( (int) $product );
	}
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return '';
	}
	$aid = (int) $product->get_meta( '_gk_hero_image_id' );
	if ( $aid >= 1 && wp_attachment_is_image( $aid ) ) {
		$url = wp_get_attachment_image_url( $aid, $size );
		if ( $url ) {
			return (string) $url;
		}
	}

	// ELDEN RING NIGHTREIGN: Hero = Key-Art (altes Cover), Karte = separates Bild (siehe inc/woocommerce-product-elden-nightreign.php).
	if ( function_exists( 'globalkeys_get_elden_nightreign_hero_attachment_id' ) ) {
		$hid = globalkeys_get_elden_nightreign_hero_attachment_id( $product );
		if ( $hid >= 1 && wp_attachment_is_image( $hid ) ) {
			$url = wp_get_attachment_image_url( $hid, $size );
			return $url ? (string) $url : '';
		}
	}

	return '';
}

/**
 * Eingabefeld: Medien-ID (Anhang) für Hero-Hintergrund.
 */
function globalkeys_product_hero_image_field() {
	global $post;

	$value = $post ? get_post_meta( $post->ID, '_gk_hero_image_id', true ) : '';
	?>
	<div class="options_group">
		<?php
		woocommerce_wp_text_input(
			array(
				'id'                => '_gk_hero_image_id',
				'name'              => '_gk_hero_image_id',
				'label'             => __( 'Hero-Bild (Medien-ID)', 'globalkeys' ),
				'placeholder'       => '',
				'description'       => __( 'Optional: Breites Key-Art-Bild nur für die Produktheroen auf der Startseite. Leer lassen = normales Produktbild. Die ID findest du in der Mediathek unter „Bearbeiten“ in der URL (post=123) oder in der Dateiliste.', 'globalkeys' ),
				'desc_tip'          => true,
				'type'              => 'number',
				'value'             => $value,
				'custom_attributes' => array(
					'min'  => '0',
					'step' => '1',
				),
			)
		);
		?>
	</div>
	<?php
}
add_action( 'woocommerce_product_options_general_product_data', 'globalkeys_product_hero_image_field', 12 );

/**
 * Speichern der Hero-Bild-ID.
 *
 * @param WC_Product $product Produkt.
 */
function globalkeys_save_product_hero_image_id( $product ) {
	if ( ! isset( $_POST['_gk_hero_image_id'] ) ) {
		return;
	}
	if ( ! is_a( $product, 'WC_Product' ) ) {
		return;
	}

	$raw = sanitize_text_field( wp_unslash( $_POST['_gk_hero_image_id'] ) );
	$id  = absint( $raw );

	if ( $id < 1 ) {
		$product->delete_meta_data( '_gk_hero_image_id' );
		return;
	}

	if ( ! wp_attachment_is_image( $id ) ) {
		return;
	}

	$product->update_meta_data( '_gk_hero_image_id', $id );
}
add_action( 'woocommerce_admin_process_product_object', 'globalkeys_save_product_hero_image_id', 12, 1 );
