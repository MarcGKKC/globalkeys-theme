<?php
/**
 * Custom My Account endpoints: Bibliothek, Reviews, Partnership, Wunschliste, Achievements
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Zusätzliche Query-Vars für My Account registrieren.
 *
 * @param array $vars Bestehende Query-Vars.
 * @return array
 */
function globalkeys_account_query_vars( $vars ) {
	$vars['bibliothek']   = 'bibliothek';
	$vars['reviews']      = 'reviews';
	$vars['affiliate']    = 'affiliate';
	$vars['wunschliste']  = 'wunschliste';
	$vars['achievements'] = 'achievements';
	return $vars;
}
add_filter( 'woocommerce_get_query_vars', 'globalkeys_account_query_vars' );

/**
 * Custom Menu-Items zur Account-Navigation hinzufügen.
 *
 * @param array $items Bestehende Menu-Items.
 * @return array
 */
function globalkeys_account_menu_items( $items ) {
	$custom = array(
		'bibliothek'   => __( 'Bibliothek', 'globalkeys' ),
		'reviews'      => __( 'Reviews', 'globalkeys' ),
		'affiliate'    => __( 'Partnership', 'globalkeys' ),
		'wunschliste'  => __( 'Wunschliste', 'globalkeys' ),
		'achievements' => __( 'Achievements', 'globalkeys' ),
	);

	// Vor customer-logout einfügen.
	$logout = isset( $items['customer-logout'] ) ? $items['customer-logout'] : null;
	if ( null !== $logout ) {
		unset( $items['customer-logout'] );
	}
	$items = array_merge( $items, $custom );
	if ( null !== $logout ) {
		$items['customer-logout'] = $logout;
	}

	return $items;
}
add_filter( 'woocommerce_account_menu_items', 'globalkeys_account_menu_items' );

/**
 * Seitentitel für Custom-Endpoints.
 */
function globalkeys_endpoint_bibliothek_title( $title ) {
	return __( 'Bibliothek', 'globalkeys' );
}
add_filter( 'woocommerce_endpoint_bibliothek_title', 'globalkeys_endpoint_bibliothek_title' );

function globalkeys_endpoint_reviews_title( $title ) {
	return __( 'Reviews', 'globalkeys' );
}
add_filter( 'woocommerce_endpoint_reviews_title', 'globalkeys_endpoint_reviews_title' );

function globalkeys_endpoint_affiliate_title( $title ) {
	return __( 'Partnership', 'globalkeys' );
}
add_filter( 'woocommerce_endpoint_affiliate_title', 'globalkeys_endpoint_affiliate_title' );

function globalkeys_endpoint_wunschliste_title( $title ) {
	return __( 'Wunschliste', 'globalkeys' );
}
add_filter( 'woocommerce_endpoint_wunschliste_title', 'globalkeys_endpoint_wunschliste_title' );

function globalkeys_endpoint_achievements_title( $title ) {
	return __( 'Achievements', 'globalkeys' );
}
add_filter( 'woocommerce_endpoint_achievements_title', 'globalkeys_endpoint_achievements_title' );

/**
 * Inhalt für Endpoint: Bibliothek.
 */
function globalkeys_account_bibliothek_endpoint() {
	wc_get_template(
		'myaccount/bibliothek.php',
		array(),
		'',
		get_template_directory() . '/woocommerce/'
	);
}
add_action( 'woocommerce_account_bibliothek_endpoint', 'globalkeys_account_bibliothek_endpoint' );

/**
 * Inhalt für Endpoint: Reviews.
 */
function globalkeys_account_reviews_endpoint() {
	wc_get_template(
		'myaccount/reviews.php',
		array(),
		'',
		get_template_directory() . '/woocommerce/'
	);
}
add_action( 'woocommerce_account_reviews_endpoint', 'globalkeys_account_reviews_endpoint' );

/**
 * Inhalt für Endpoint: Affiliate.
 */
function globalkeys_account_affiliate_endpoint() {
	wc_get_template(
		'myaccount/affiliate.php',
		array(),
		'',
		get_template_directory() . '/woocommerce/'
	);
}
add_action( 'woocommerce_account_affiliate_endpoint', 'globalkeys_account_affiliate_endpoint' );

/**
 * Inhalt für Endpoint: Wunschliste.
 */
function globalkeys_account_wunschliste_endpoint() {
	wc_get_template(
		'myaccount/wunschliste.php',
		array(),
		'',
		get_template_directory() . '/woocommerce/'
	);
}
add_action( 'woocommerce_account_wunschliste_endpoint', 'globalkeys_account_wunschliste_endpoint' );

/**
 * Inhalt für Endpoint: Achievements.
 */
function globalkeys_account_achievements_endpoint() {
	wc_get_template(
		'myaccount/achievements.php',
		array(),
		'',
		get_template_directory() . '/woocommerce/'
	);
}
add_action( 'woocommerce_account_achievements_endpoint', 'globalkeys_account_achievements_endpoint' );

/**
 * Rewrite-Rules flushen, damit /my-account/bibliothek/ etc. funktionieren.
 * Einmalig beim Theme-Switch oder beim ersten Seitenaufruf.
 */
function globalkeys_flush_account_endpoints() {
	if ( get_option( 'globalkeys_account_endpoints_flushed' ) ) {
		return;
	}
	flush_rewrite_rules();
	update_option( 'globalkeys_account_endpoints_flushed', true );
}
add_action( 'after_switch_theme', 'globalkeys_flush_account_endpoints' );
add_action( 'init', 'globalkeys_flush_account_endpoints', 99 );
