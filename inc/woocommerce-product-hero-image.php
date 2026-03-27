<?php
/**
 * Optionales Hero-Key-Art-Bild pro Produkt (Startseite + Produktdetail), unabhängig vom Produktkarten-Bild.
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

	$value             = $post ? get_post_meta( $post->ID, '_gk_hero_image_id', true ) : '';
	$single_hero_value = $post ? get_post_meta( $post->ID, '_gk_single_product_hero_image_id', true ) : '';
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
		woocommerce_wp_text_input(
			array(
				'id'                => '_gk_single_product_hero_image_id',
				'name'              => '_gk_single_product_hero_image_id',
				'label'             => __( 'Produktseiten-Hero (Medien-ID)', 'globalkeys' ),
				'placeholder'       => '',
				'description'       => __( 'Optional: Hero-Bild oben auf der Produktdetailseite. Leer lassen = Startseiten-Hero-Bild als Fallback.', 'globalkeys' ),
				'desc_tip'          => true,
				'type'              => 'number',
				'value'             => $single_hero_value,
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
 * Speichern der Hero-Bild-IDs.
 *
 * @param WC_Product $product Produkt.
 */
function globalkeys_save_product_hero_image_id( $product ) {
	if ( ! is_a( $product, 'WC_Product' ) ) {
		return;
	}

	if ( isset( $_POST['_gk_hero_image_id'] ) ) {
		$raw = sanitize_text_field( wp_unslash( $_POST['_gk_hero_image_id'] ) );
		$id  = absint( $raw );

		if ( $id < 1 ) {
			$product->delete_meta_data( '_gk_hero_image_id' );
		} elseif ( wp_attachment_is_image( $id ) ) {
			$product->update_meta_data( '_gk_hero_image_id', $id );
		}
	}

	if ( isset( $_POST['_gk_single_product_hero_image_id'] ) ) {
		$single_raw = sanitize_text_field( wp_unslash( $_POST['_gk_single_product_hero_image_id'] ) );
		$single_id  = absint( $single_raw );

		if ( $single_id < 1 ) {
			$product->delete_meta_data( '_gk_single_product_hero_image_id' );
		} elseif ( wp_attachment_is_image( $single_id ) ) {
			$product->update_meta_data( '_gk_single_product_hero_image_id', $single_id );
		}
	}
}
add_action( 'woocommerce_admin_process_product_object', 'globalkeys_save_product_hero_image_id', 12, 1 );

/**
 * URL des Produktseiten-Hero-Bilds (Single Product) oder leer.
 *
 * @param WC_Product|int $product Produkt oder Post-ID.
 * @param string           $size    Bildgröße (default full).
 * @return string
 */
function globalkeys_get_single_product_hero_image_url( $product, $size = 'full' ) {
	if ( is_numeric( $product ) ) {
		$product = wc_get_product( (int) $product );
	}
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return '';
	}

	$aid = (int) $product->get_meta( '_gk_single_product_hero_image_id' );
	if ( $aid >= 1 && wp_attachment_is_image( $aid ) ) {
		$url = wp_get_attachment_image_url( $aid, $size );
		if ( $url ) {
			return (string) $url;
		}
	}

	// Theme-Datei-Fallback (Datei nach Pictures/product-page-hero/ legen).
	$slug = (string) $product->get_slug();
	if ( in_array( $slug, array( 'resident-evil-requiem', 'resident-evil-requiem-pc', 'resident-evil-requiem-steam', 'resident-evil-requiem-pc-steam' ), true ) ) {
		$file_rel = '/Pictures/product-page-hero/RER-gk.jpg';
		$file_abs = get_template_directory() . $file_rel;
		if ( file_exists( $file_abs ) ) {
			return get_template_directory_uri() . '/Pictures/product-page-hero/' . rawurlencode( 'RER-gk.jpg' );
		}
	}

	return '';
}

/**
 * Ob für das Produkt ein Produkthero-Hintergrund aktiv ist (Single- oder Startseiten-Fallback).
 *
 * @param WC_Product|null $product Produkt oder globales $product.
 * @return bool
 */
function globalkeys_product_has_page_hero( $product = null ) {
	if ( ! $product ) {
		global $product;
	}
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return false;
	}
	$url = globalkeys_get_single_product_hero_image_url( $product );
	if ( ! $url ) {
		$url = globalkeys_get_product_hero_image_url( $product );
	}
	return $url !== '';
}

/**
 * Prüft, ob die aktuelle Anfrage eine Produktdetailseite ist.
 *
 * @return bool
 */
function globalkeys_is_single_product_request() {
	if ( function_exists( 'is_product' ) && is_product() ) {
		return true;
	}

	$pid = (int) get_queried_object_id();
	if ( $pid < 1 ) {
		return false;
	}

	return 'product' === get_post_type( $pid );
}

/**
 * Hero-Bild auf der Produktdetailseite ausgeben.
 */
function globalkeys_single_product_hero_section() {
	static $rendered = false;

	if ( $rendered ) {
		return;
	}

	if ( ! function_exists( 'wc_get_product' ) || ! globalkeys_is_single_product_request() ) {
		return;
	}
	$pid = (int) get_queried_object_id();
	if ( $pid < 1 ) {
		return;
	}
	$wc_product = wc_get_product( $pid );
	if ( ! $wc_product || ! is_a( $wc_product, 'WC_Product' ) ) {
		return;
	}

	$hero_url = globalkeys_get_single_product_hero_image_url( $wc_product, 'full' );
	if ( ! $hero_url ) {
		$hero_url = globalkeys_get_product_hero_image_url( $wc_product, 'full' );
	}
	if ( ! $hero_url ) {
		return;
	}

	$rendered = true;
	echo '<div class="gk-product-page-hero-root">';
	echo '<section class="gk-single-product-hero has-hero-image" role="img" aria-label="' . esc_attr( $wc_product->get_name() ) . '" style="background-image:url(\'' . esc_url( $hero_url ) . '\');"></section>';
	echo '</div>';
}

/**
 * Body-Klasse, wenn ein Produktseiten-Hero aktiv ist.
 *
 * @param string[] $classes Body-Klassen.
 * @return string[]
 */
function globalkeys_single_product_hero_body_class( $classes ) {
	if ( ! function_exists( 'wc_get_product' ) || ! globalkeys_is_single_product_request() ) {
		return $classes;
	}
	$pid = (int) get_queried_object_id();
	if ( $pid < 1 ) {
		return $classes;
	}
	$wc_product = wc_get_product( $pid );
	if ( ! $wc_product || ! is_a( $wc_product, 'WC_Product' ) ) {
		return $classes;
	}
	$url = globalkeys_get_single_product_hero_image_url( $wc_product, 'full' );
	if ( ! $url ) {
		$url = globalkeys_get_product_hero_image_url( $wc_product, 'full' );
	}
	if ( $url !== '' ) {
		$classes[] = 'gk-has-product-page-hero';
	}
	return $classes;
}
add_filter( 'body_class', 'globalkeys_single_product_hero_body_class' );
add_action( 'woocommerce_before_main_content', 'globalkeys_single_product_hero_section', 15 );
add_action( 'woocommerce_before_single_product', 'globalkeys_single_product_hero_section', 1 );

/**
 * Fallback: Produktseite über theme single.php (ohne woocommerce_before_main_content) – Hero trotzdem einmal ausgeben.
 * Doppel-Ausgabe verhindert static $rendered in globalkeys_single_product_hero_section().
 *
 * @param WP_Query $query Abfrage.
 */
function globalkeys_single_product_hero_loop_start( $query ) {
	if ( ! $query instanceof WP_Query || ! $query->is_main_query() ) {
		return;
	}
	if ( ! function_exists( 'is_product' ) || ! is_singular( 'product' ) ) {
		return;
	}
	globalkeys_single_product_hero_section();
}
add_action( 'loop_start', 'globalkeys_single_product_hero_loop_start', 0 );
