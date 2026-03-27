<?php
/**
 * Produktdetail: Karten-Layout (Medien links, Infos rechts), Keyart & Attribute in der Sidebar.
 *
 * @package globalkeys
 */

defined( 'ABSPATH' ) || exit;

/**
 * Standard-Produktgalerie (großes Bild) ausblenden – Darstellung über Keyart/Infospalte.
 */
function globalkeys_single_product_remove_main_gallery() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
}
add_action( 'wp', 'globalkeys_single_product_remove_main_gallery', 20 );

/**
 * Preis ans Ende der Summary (unten rechts per CSS flex).
 */
function globalkeys_single_product_move_price_to_summary_end() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
	add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 99 );
}
add_action( 'wp', 'globalkeys_single_product_move_price_to_summary_end', 20 );

/**
 * Sale-Badge („Sale!“) auf der Produktdetailseite nicht ausgeben.
 */
function globalkeys_single_product_remove_sale_flash() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_show_product_sale_flash', 98 );
}
add_action( 'wp', 'globalkeys_single_product_remove_sale_flash', 20 );

/**
 * Produkttitel (H1) aus der Summary entfernen – Darstellung erfolgt woanders (z. B. Keyart/Hero).
 * Zusätzlich: Theme-Override woocommerce/single-product/title.php (falls Hook erneut registriert wird).
 */
function globalkeys_single_product_remove_title() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
}
add_action( 'wp', 'globalkeys_single_product_remove_title', 20 );
add_action( 'woocommerce_before_single_product', 'globalkeys_single_product_remove_title', 1 );

/**
 * Kleines Key-Art-Bild oben in der Sidebar, falls Produktbild existiert.
 */
function globalkeys_single_product_sidebar_keyart() {
	global $product;
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return;
	}
	$img_id = $product->get_image_id();
	if ( $img_id < 1 ) {
		return;
	}
	echo '<div class="gk-product-sidebar-keyart">';
	echo wp_get_attachment_image(
		$img_id,
		'woocommerce_single',
		false,
		array(
			'class'   => 'gk-product-sidebar-keyart-img',
			'loading' => 'lazy',
			'alt'     => esc_attr( $product->get_name() ),
		)
	);
	echo '</div>';
}
add_action( 'woocommerce_single_product_summary', 'globalkeys_single_product_sidebar_keyart', 1 );

/**
 * Produkttitel in der Summary, wenn ein Produkthero aktiv ist (title.php ist sonst leer).
 *
 * @see woocommerce/single-product/title.php
 */
function globalkeys_single_product_hero_summary_title() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	global $product;
	if ( ! $product || ! is_a( $product, 'WC_Product' ) || ! $product->get_image_id() ) {
		return;
	}
	if ( ! function_exists( 'globalkeys_product_has_page_hero' ) || ! globalkeys_product_has_page_hero( $product ) ) {
		return;
	}
	echo '<h1 class="product_title entry-title gk-purchase-card__title">' . esc_html( $product->get_name() ) . '</h1>';
}
add_action( 'woocommerce_single_product_summary', 'globalkeys_single_product_hero_summary_title', 3 );

/**
 * Öffnet die rechte Inhaltsspalte (Bild links = Keyart, Text rechts).
 */
function globalkeys_single_product_summary_main_open() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	global $product;
	if ( ! $product || ! is_a( $product, 'WC_Product' ) || ! $product->get_image_id() ) {
		return;
	}
	echo '<div class="gk-product-summary-main">';
}

/**
 * Schließt die rechte Inhaltsspalte.
 *
 * @see globalkeys_single_product_summary_main_open
 */
function globalkeys_single_product_summary_main_close() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	global $product;
	if ( ! $product || ! is_a( $product, 'WC_Product' ) || ! $product->get_image_id() ) {
		return;
	}
	echo '</div>';
}
add_action( 'woocommerce_single_product_summary', 'globalkeys_single_product_summary_main_open', 2 );
add_action( 'woocommerce_single_product_summary', 'globalkeys_single_product_summary_main_close', 999999 );

/**
 * Produktattribute & Maße in der Sidebar (Tab „Zusätzliche Informationen“ entfällt dann).
 */
function globalkeys_single_product_summary_attributes() {
	global $product;
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return;
	}
	$show = $product->has_attributes() || $product->has_weight() || $product->has_dimensions();
	if ( ! $show ) {
		return;
	}
	echo '<div class="gk-product-sidebar-attributes">';
	wc_display_product_attributes( $product );
	echo '</div>';
}
add_action( 'woocommerce_single_product_summary', 'globalkeys_single_product_summary_attributes', 25 );

/**
 * Keine doppelte Attribut-Tabelle im Tab-Bereich.
 *
 * @param array $tabs Register der Produkt-Tabs.
 * @return array
 */
function globalkeys_remove_additional_information_tab( $tabs ) {
	if ( isset( $tabs['additional_information'] ) ) {
		unset( $tabs['additional_information'] );
	}
	return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'globalkeys_remove_additional_information_tab', 98 );
