<?php
/**
 * Produktdetail: umschließender Container für zuverlässige max-width (auch wenn #primary-Styles überschrieben werden).
 *
 * @package globalkeys
 */

defined( 'ABSPATH' ) || exit;

/**
 * Öffnet Shell vor WooCommerce-Wrapper (Priority 5 < 10 für output_content_wrapper).
 */
function globalkeys_single_product_shell_open() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	echo '<div class="gk-single-product-shell">';
}

/**
 * Schließt Shell nach WooCommerce-Wrapper-Ende.
 */
function globalkeys_single_product_shell_close() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	echo '</div>';
}

add_action( 'woocommerce_before_main_content', 'globalkeys_single_product_shell_open', 5 );
add_action( 'woocommerce_after_main_content', 'globalkeys_single_product_shell_close', 999 );

/**
 * Shop-Sidebar auf der Produktdetailseite abmelden (falls do_action( 'woocommerce_sidebar' ) noch greift).
 */
function globalkeys_single_product_remove_wc_sidebar_callback() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
}
add_action( 'wp', 'globalkeys_single_product_remove_wc_sidebar_callback', 99 );
