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
 * Mögliche Seiten-Slugs für die öffentliche Wishlist (Theme + Shops mit DE-URL).
 *
 * @return string[]
 */
function globalkeys_wishlist_page_slugs() {
	$slugs = array( 'wishlist', 'wunschliste' );
	return array_values( array_unique( array_filter( array_map( 'sanitize_title', apply_filters( 'globalkeys_wishlist_page_slugs', $slugs ) ) ) ) );
}

/**
 * Veröffentlichte Wishlist-Seiten-ID (Option oder erste passende Seite per Slug).
 *
 * @return int
 */
function globalkeys_get_wishlist_page_id() {
	$wid = absint( get_option( 'globalkeys_wishlist_page_id', 0 ) );
	if ( $wid && get_post_status( $wid ) === 'publish' ) {
		return $wid;
	}
	foreach ( globalkeys_wishlist_page_slugs() as $slug ) {
		$existing = get_page_by_path( $slug, OBJECT, 'page' );
		if ( $existing && $existing->post_status === 'publish' ) {
			return (int) $existing->ID;
		}
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
	foreach ( globalkeys_wishlist_page_slugs() as $slug ) {
		if ( is_page( $slug ) ) {
			return true;
		}
	}
	$obj = get_queried_object();
	if ( $obj instanceof WP_Post ) {
		foreach ( globalkeys_wishlist_page_slugs() as $slug ) {
			if ( $obj->post_name === $slug ) {
				return true;
			}
		}
	}
	return false;
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

/**
 * Toolbar-Layout-CSS einmal ausgeben (direkt im Template neben der Toolbar — kein wp_head/body-Klasse nötig).
 *
 * Referenz Breiten (mit style.css + wishlist-page.php abstimmen):
 * – Grid #gk-wishlist-toolbar-row: Suche + Buttons, column-gap .4rem
 * – Produktanzahl + Sort; Suchfeld max-width; Sort min-width 17.5rem
 * – Trennlinie: Abstände nur in style.css (kein margin !important hier)
 */
function globalkeys_wishlist_toolbar_print_layout_css() {
	static $printed = false;
	if ( $printed ) {
		return;
	}
	$printed = true;
	echo '<style id="gk-wishlist-toolbar-layout">';
	echo '.gk-wishlist__toolbar{display:block!important;width:100%!important;max-width:100%!important;box-sizing:border-box!important}';
	echo '#gk-wishlist-toolbar-row{display:grid!important;grid-template-columns:minmax(0,1fr) auto!important;align-items:stretch!important;column-gap:.55rem!important;row-gap:.55rem!important;width:100%!important;max-width:100%!important;box-sizing:border-box!important}';
	echo '#gk-wishlist-toolbar-row .gk-wishlist__search-wrap{min-width:0!important;max-width:100%!important;width:100%!important;justify-self:stretch!important;box-sizing:border-box!important}';
	echo '#gk-wishlist-toolbar-row .gk-wishlist__search-input,#gk-wishlist-toolbar-row input[type=search]{display:block!important;width:100%!important;max-width:none!important;min-width:0!important;padding-left:1.15rem!important;padding-right:1.15rem!important;box-sizing:border-box!important;-webkit-appearance:textfield!important;appearance:textfield!important}';
	echo '#gk-wishlist-toolbar-row .gk-wishlist__toolbar-actions{display:flex!important;flex-flow:row nowrap!important;align-items:stretch!important;gap:.55rem!important;flex-shrink:0!important;width:auto!important;max-width:100%!important;box-sizing:border-box!important}';
	echo '#gk-wishlist-toolbar-row .gk-wishlist__toolbar-actions .gk-wishlist__tb-count{margin:0!important;display:inline-flex!important;align-items:center!important;justify-content:center!important;min-width:13.5rem!important;min-height:3.125rem!important;padding:0 1.35rem!important;box-sizing:border-box!important;border-radius:4px!important;border:1px solid rgba(255,255,255,.12)!important;background:#2b2744!important;color:rgba(255,255,255,.92)!important;font-size:calc(.9375rem + 1px)!important;font-weight:500!important;line-height:1.2!important;white-space:nowrap!important;flex-shrink:0!important}';
	echo '#gk-wishlist-toolbar-row .gk-wishlist__toolbar-actions .gk-wishlist__tb-sort-wrap{position:relative!important;min-width:17.5rem!important;flex-shrink:0!important}';
	echo '#gk-wishlist-toolbar-row .gk-wishlist__toolbar-actions .gk-wishlist__tb-sort-wrap .gk-wishlist__tb-btn--sort{min-width:17.5rem!important;width:100%!important;padding-left:1.5rem!important;padding-right:1.35rem!important;box-sizing:border-box!important}';
	echo '@media screen and (max-width:36rem){#gk-wishlist-toolbar-row{grid-template-columns:minmax(0,1fr)!important;row-gap:.65rem!important}#gk-wishlist-toolbar-row .gk-wishlist__search-wrap{max-width:none!important;justify-self:stretch!important}#gk-wishlist-toolbar-row .gk-wishlist__toolbar-actions{flex-wrap:wrap!important;width:100%!important}#gk-wishlist-toolbar-row .gk-wishlist__toolbar-actions .gk-wishlist__tb-sort-wrap{flex:1 1 100%!important;min-width:0!important}#gk-wishlist-toolbar-row .gk-wishlist__toolbar-actions .gk-wishlist__tb-sort-wrap .gk-wishlist__tb-btn--sort{min-width:0!important}}';
	echo '#gk-wishlist-toolbar-rule.gk-wishlist__toolbar-rule{display:block!important;box-sizing:border-box!important;width:100%!important;height:1px!important;min-height:1px!important;padding:0!important;border:0!important;overflow:hidden!important;background-color:rgba(255,255,255,.2)!important}';
	echo '</style>' . "\n";
}
