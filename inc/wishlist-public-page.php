<?php
/**
 * Öffentliche Wishlist-Seite (Header-Herz): normales Layout mit Header/Footer/Theme-Hintergrund.
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Veröffentlichte Wishlist-Seiten-ID (Option oder Seite mit Slug wishlist).
 *
 * @return int
 */
function globalkeys_get_wishlist_page_id() {
	$wid = absint( get_option( 'globalkeys_wishlist_page_id', 0 ) );
	if ( $wid && get_post_status( $wid ) === 'publish' ) {
		return $wid;
	}
	$existing = get_page_by_path( 'wishlist', OBJECT, 'page' );
	if ( $existing && $existing->post_status === 'publish' ) {
		return (int) $existing->ID;
	}
	return 0;
}

/**
 * Prüft, ob die aktuelle Ansicht die öffentliche Wishlist-Seite ist.
 */
function globalkeys_is_wishlist_page() {
	if ( ! is_page() ) {
		return false;
	}
	$pid = (int) get_queried_object_id();
	$wid = globalkeys_get_wishlist_page_id();
	if ( $wid && $pid === $wid ) {
		return true;
	}
	// Fallback: Slug (z. B. wenn Option noch nicht gesetzt).
	if ( is_page( 'wishlist' ) ) {
		return true;
	}
	$obj = get_queried_object();
	return $obj instanceof WP_Post && 'wishlist' === $obj->post_name;
}

/**
 * Permalink der Wishlist-Seite (Slug wishlist).
 */
function globalkeys_get_wishlist_url() {
	$wid = globalkeys_get_wishlist_page_id();
	if ( $wid ) {
		return get_permalink( $wid );
	}
	return home_url( '/wishlist/' );
}

/**
 * Anzeige-Gamertag (Meta gamertag, sonst Login, sonst display_name).
 *
 * @param int|null $user_id WordPress-User-ID; Standard: aktueller User.
 * @return string
 */
function globalkeys_get_user_gamertag_for_display( $user_id = null ) {
	$user_id = null === $user_id ? get_current_user_id() : (int) $user_id;
	if ( $user_id <= 0 ) {
		return '';
	}
	$user = get_userdata( $user_id );
	if ( ! $user ) {
		return '';
	}
	$g = get_user_meta( $user_id, 'gamertag', true );
	if ( is_string( $g ) && '' !== trim( $g ) ) {
		return $g;
	}
	if ( ! empty( $user->user_login ) ) {
		return $user->user_login;
	}
	return (string) $user->display_name;
}

/**
 * Legt die Seite einmalig an, wenn ein Admin das Backend besucht und die Seite noch fehlt.
 */
function globalkeys_maybe_bootstrap_wishlist_page() {
	if ( ! is_admin() || ! current_user_can( 'publish_pages' ) ) {
		return;
	}
	if ( get_option( 'globalkeys_wishlist_page_bootstrapped' ) ) {
		return;
	}
	$existing = get_page_by_path( 'wishlist', OBJECT, 'page' );
	if ( $existing && $existing->post_status === 'publish' ) {
		update_option( 'globalkeys_wishlist_page_id', (int) $existing->ID );
		update_option( 'globalkeys_wishlist_page_bootstrapped', '1' );
		return;
	}
	$post_id = wp_insert_post(
		wp_slash(
			array(
				'post_title'   => __( 'Wishlist', 'globalkeys' ),
				'post_name'    => 'wishlist',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '',
				'post_author'  => get_current_user_id() ? get_current_user_id() : 1,
			)
		),
		true
	);
	if ( is_wp_error( $post_id ) || ! $post_id ) {
		return;
	}
	update_option( 'globalkeys_wishlist_page_id', (int) $post_id );
	update_option( 'globalkeys_wishlist_page_bootstrapped', '1' );
}
add_action( 'admin_init', 'globalkeys_maybe_bootstrap_wishlist_page', 5 );

/**
 * Body-Klasse für gezieltes Styling.
 */
function globalkeys_wishlist_page_body_class( $classes ) {
	if ( globalkeys_is_wishlist_page() ) {
		$classes[] = 'gk-wishlist-page';
	}
	return $classes;
}
add_filter( 'body_class', 'globalkeys_wishlist_page_body_class' );
