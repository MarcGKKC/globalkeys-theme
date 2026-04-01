<?php
/**
 * Wunschliste: IDs speichern, REST-Toggle, Ausgabe auf der Wishlist-Seite.
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User-Meta-Schlüssel (Array von Produkt-IDs, Reihenfolge = Anzeige).
 */
function globalkeys_wishlist_meta_key() {
	return 'globalkeys_wishlist_product_ids';
}

/**
 * User-Meta: Produkt-ID (int key) => Unix-Zeitstempel wann zur Wunschliste hinzugefügt.
 */
function globalkeys_wishlist_added_meta_key() {
	return 'globalkeys_wishlist_product_added_at';
}

/**
 * @param int $user_id WordPress-User-ID.
 * @return int[] Sichtbare, gültige Produkt-IDs in gespeicherter Reihenfolge.
 */
function globalkeys_wishlist_get_ids( $user_id ) {
	$user_id = (int) $user_id;
	if ( $user_id <= 0 ) {
		return array();
	}
	$raw = get_user_meta( $user_id, globalkeys_wishlist_meta_key(), true );
	if ( ! is_array( $raw ) ) {
		return array();
	}
	$out = array();
	foreach ( $raw as $id ) {
		$id = absint( $id );
		if ( $id > 0 && ! in_array( $id, $out, true ) ) {
			$out[] = $id;
		}
	}
	return $out;
}

/**
 * @param int   $user_id WordPress-User-ID.
 * @param int[] $ids     Produkt-IDs.
 */
function globalkeys_wishlist_save_ids( $user_id, array $ids ) {
	$user_id = (int) $user_id;
	if ( $user_id <= 0 ) {
		return;
	}
	$clean = array();
	foreach ( $ids as $id ) {
		$id = absint( $id );
		if ( $id > 0 && ! in_array( $id, $clean, true ) ) {
			$clean[] = $id;
		}
		if ( count( $clean ) >= 500 ) {
			break;
		}
	}
	update_user_meta( $user_id, globalkeys_wishlist_meta_key(), $clean );
	globalkeys_wishlist_sync_added_timestamps( $user_id, $clean );
}

/**
 * @param int   $user_id WordPress-User-ID.
 * @return array<int, int> Produkt-ID => Unix-Zeitstempel.
 */
function globalkeys_wishlist_get_added_timestamps( $user_id ) {
	$user_id = (int) $user_id;
	if ( $user_id <= 0 ) {
		return array();
	}
	$raw = get_user_meta( $user_id, globalkeys_wishlist_added_meta_key(), true );
	if ( ! is_array( $raw ) ) {
		return array();
	}
	$out = array();
	foreach ( $raw as $k => $v ) {
		$pid = absint( $k );
		if ( $pid > 0 ) {
			$out[ $pid ] = absint( $v );
		}
	}
	return $out;
}

/**
 * Hält Zeitstempel mit der ID-Liste synchron (neue IDs = jetzt, entfernte Keys löschen).
 *
 * @param int   $user_id WordPress-User-ID.
 * @param int[] $ids     Bereinigte Produkt-IDs in Reihenfolge.
 */
function globalkeys_wishlist_sync_added_timestamps( $user_id, array $ids ) {
	$user_id = (int) $user_id;
	if ( $user_id <= 0 ) {
		return;
	}
	$old = globalkeys_wishlist_get_added_timestamps( $user_id );
	$now = time();
	$new = array();
	foreach ( $ids as $pid ) {
		$pid = absint( $pid );
		if ( $pid <= 0 ) {
			continue;
		}
		if ( isset( $old[ $pid ] ) && (int) $old[ $pid ] > 0 ) {
			$new[ $pid ] = (int) $old[ $pid ];
		} else {
			$new[ $pid ] = $now;
		}
	}
	update_user_meta( $user_id, globalkeys_wishlist_added_meta_key(), $new );
}

/**
 * Fehlende Zeitstempel für bestehende Listen nachtragen (einmalig pro Aufruf).
 *
 * @param int $user_id WordPress-User-ID.
 */
function globalkeys_wishlist_ensure_added_timestamps( $user_id ) {
	$ids = globalkeys_wishlist_get_ids( $user_id );
	if ( empty( $ids ) ) {
		return;
	}
	$map     = globalkeys_wishlist_get_added_timestamps( $user_id );
	$now     = time();
	$changed = false;
	foreach ( $ids as $pid ) {
		$pid = absint( $pid );
		if ( $pid <= 0 ) {
			continue;
		}
		if ( ! isset( $map[ $pid ] ) || $map[ $pid ] < 1 ) {
			$map[ $pid ] = $now;
			$changed     = true;
		}
	}
	foreach ( array_keys( $map ) as $pid ) {
		if ( ! in_array( absint( $pid ), $ids, true ) ) {
			unset( $map[ $pid ] );
			$changed = true;
		}
	}
	if ( $changed ) {
		update_user_meta( $user_id, globalkeys_wishlist_added_meta_key(), $map );
	}
}

/**
 * Anzeige-Datum (z. B. 26.1.2026) wann das Produkt zur Wunschliste kam.
 *
 * @param int $user_id    User-ID.
 * @param int $product_id Produkt-ID.
 * @return string Leer wenn unbekannt.
 */
function globalkeys_wishlist_format_product_added_date( $user_id, $product_id ) {
	$map = globalkeys_wishlist_get_added_timestamps( $user_id );
	$pid = absint( $product_id );
	if ( $pid <= 0 || ! isset( $map[ $pid ] ) || $map[ $pid ] < 1 ) {
		return '';
	}
	return wp_date( 'j.n.Y', (int) $map[ $pid ] );
}

/**
 * @param int $product_id WooCommerce-Produkt-ID.
 */
function globalkeys_wishlist_is_valid_product( $product_id ) {
	if ( ! function_exists( 'wc_get_product' ) ) {
		return false;
	}
	$product_id = absint( $product_id );
	if ( $product_id <= 0 ) {
		return false;
	}
	$post = get_post( $product_id );
	if ( ! $post || 'product' !== $post->post_type || 'publish' !== $post->post_status ) {
		return false;
	}
	$product = wc_get_product( $product_id );
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return false;
	}
	if ( ! $product->is_visible() ) {
		return false;
	}
	return true;
}

/**
 * Steam-ähnliche Rezensionszeile für die Wunschliste (Meta, Filter oder Platzhalter).
 *
 * Meta: `_gk_steam_review_verdict`, optional `_gk_steam_review_count`.
 * Filter: `gk_wishlist_steam_review_data` — Array mit keys `label`, `verdict`, optional `count`, oder null.
 * Ohne Meta: stabile Fake-Zeile pro Produkt-ID (bis echte Daten angebunden sind).
 *
 * Filter: `gk_wishlist_steam_review_fake_enabled` (bool, default true) — Fake abschalten.
 *
 * @param WC_Product $product Produkt.
 * @return array{label: string, verdict: string, count: int}|null
 */
function globalkeys_wishlist_get_product_steam_review_row( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return null;
	}
	$filtered = apply_filters( 'gk_wishlist_steam_review_data', null, $product );
	if ( is_array( $filtered ) && ! empty( $filtered['verdict'] ) && is_string( $filtered['verdict'] ) ) {
		$verdict = trim( $filtered['verdict'] );
		if ( $verdict === '' ) {
			return null;
		}
		$label = __( 'Steam Reviews:', 'globalkeys' );
		if ( isset( $filtered['label'] ) && is_string( $filtered['label'] ) && $filtered['label'] !== '' ) {
			$label = $filtered['label'];
		}
		return array(
			'label'   => $label,
			'verdict' => $verdict,
			'count'   => isset( $filtered['count'] ) ? max( 0, (int) $filtered['count'] ) : 0,
		);
	}
	$verdict = $product->get_meta( '_gk_steam_review_verdict' );
	if ( is_string( $verdict ) && trim( $verdict ) !== '' ) {
		return array(
			'label'   => __( 'Steam Reviews:', 'globalkeys' ),
			'verdict' => trim( $verdict ),
			'count'   => max( 0, absint( $product->get_meta( '_gk_steam_review_count' ) ) ),
		);
	}
	if ( ! apply_filters( 'gk_wishlist_steam_review_fake_enabled', true, $product ) ) {
		return null;
	}
	$pid = (int) $product->get_id();
	$rows = array(
		array( 'verdict' => __( 'Sehr positiv', 'globalkeys' ), 'count' => 12847 ),
		array( 'verdict' => __( 'Sehr positiv', 'globalkeys' ), 'count' => 3421 ),
		array( 'verdict' => __( 'Größtenteils positiv', 'globalkeys' ), 'count' => 892 ),
		array( 'verdict' => __( 'Überwältigend positiv', 'globalkeys' ), 'count' => 56102 ),
		array( 'verdict' => __( 'Positiv', 'globalkeys' ), 'count' => 204 ),
		array( 'verdict' => __( 'Ausgewogen', 'globalkeys' ), 'count' => 4412 ),
	);
	$ix  = $pid > 0 ? ( $pid % count( $rows ) ) : 0;
	$row = $rows[ $ix ];
	$cnt = max( 24, (int) $row['count'] + ( $pid % 4999 ) + ( ( $pid * 7 ) % 1200 ) );
	return array(
		'label'   => __( 'Steam Reviews:', 'globalkeys' ),
		'verdict' => $row['verdict'],
		'count'   => $cnt,
	);
}

/**
 * Veröffentlichungsdatum für die Wunschliste (Format kurz z. B. 18.4.2024).
 *
 * Zuerst `_gk_release_date` (wie im Shop), sonst WooCommerce-Produktdatum (`date_created`).
 *
 * @param WC_Product $product Produkt.
 * @return string Leer wenn kein Datum ermittelbar.
 */
function globalkeys_wishlist_format_release_date_display( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return '';
	}
	$ts = 0;
	if ( function_exists( 'globalkeys_get_product_release_timestamp' ) ) {
		$ts = globalkeys_get_product_release_timestamp( $product );
	}
	if ( $ts < 1 ) {
		$created = $product->get_date_created();
		if ( $created && is_a( $created, 'WC_DateTime' ) ) {
			$ts = $created->getTimestamp();
		}
	}
	if ( $ts < 1 ) {
		return '';
	}
	$formatted = wp_date( 'j.n.Y', $ts );
	return apply_filters( 'gk_wishlist_release_date_formatted', $formatted, $product, $ts );
}

/**
 * Unix-Zeit für Sortierung nach Erscheinungsdatum (Meta oder Woo-Datum).
 *
 * @param WC_Product $product Produkt.
 * @return int >= 0
 */
function globalkeys_wishlist_product_release_ts_for_sort( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return 0;
	}
	$ts = 0;
	if ( function_exists( 'globalkeys_get_product_release_timestamp' ) ) {
		$ts = (int) globalkeys_get_product_release_timestamp( $product );
	}
	if ( $ts < 1 ) {
		$created = $product->get_date_created();
		if ( $created && is_a( $created, 'WC_DateTime' ) ) {
			$ts = (int) $created->getTimestamp();
		}
	}
	return max( 0, $ts );
}

/**
 * Produkt anhängen, falls noch nicht auf der Liste (für Link von der Produktseite).
 *
 * @param int $user_id    User-ID.
 * @param int $product_id Produkt-ID.
 */
function globalkeys_wishlist_append_if_missing( $user_id, $product_id ) {
	$user_id    = (int) $user_id;
	$product_id = absint( $product_id );
	if ( $user_id <= 0 || ! globalkeys_wishlist_is_valid_product( $product_id ) ) {
		return;
	}
	$ids = globalkeys_wishlist_get_ids( $user_id );
	if ( in_array( $product_id, $ids, true ) ) {
		return;
	}
	$ids[] = $product_id;
	globalkeys_wishlist_save_ids( $user_id, $ids );
}

/**
 * Eingeloggte Nutzer: ?gk_wl_add=ID&gk_wl_nonce=… auf der Wishlist-Seite auswerten (ohne JS).
 */
function globalkeys_wishlist_handle_url_add() {
	if ( ! is_user_logged_in() || ! function_exists( 'globalkeys_is_wishlist_page' ) || ! globalkeys_is_wishlist_page() ) {
		return;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- verified below with wp_verify_nonce.
	if ( empty( $_GET['gk_wl_add'] ) || empty( $_GET['gk_wl_nonce'] ) ) {
		return;
	}
	$pid   = absint( wp_unslash( $_GET['gk_wl_add'] ) );
	$nonce = isset( $_GET['gk_wl_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['gk_wl_nonce'] ) ) : '';
	if ( $pid <= 0 || ! wp_verify_nonce( $nonce, 'gk_wl_add_' . $pid ) ) {
		wp_safe_redirect( remove_query_arg( array( 'gk_wl_add', 'gk_wl_nonce' ), get_permalink() ) );
		exit;
	}
	globalkeys_wishlist_append_if_missing( get_current_user_id(), $pid );
	wp_safe_redirect( remove_query_arg( array( 'gk_wl_add', 'gk_wl_nonce' ) ) );
	exit;
}
add_action( 'template_redirect', 'globalkeys_wishlist_handle_url_add', 5 );

/**
 * Produkt zur Wunschliste hinzufügen oder entfernen.
 *
 * @param int $user_id    User-ID.
 * @param int $product_id Produkt-ID.
 * @return array{ in_wishlist: bool, ids: int[] }|WP_Error
 */
function globalkeys_wishlist_toggle_product( $user_id, $product_id ) {
	$user_id    = (int) $user_id;
	$product_id = absint( $product_id );
	if ( $user_id <= 0 ) {
		return new WP_Error( 'not_logged_in', __( 'Anmeldung erforderlich.', 'globalkeys' ), array( 'status' => 401 ) );
	}
	if ( ! globalkeys_wishlist_is_valid_product( $product_id ) ) {
		return new WP_Error( 'invalid_product', __( 'Ungültiges Produkt.', 'globalkeys' ), array( 'status' => 400 ) );
	}
	$ids = globalkeys_wishlist_get_ids( $user_id );
	$key = array_search( $product_id, $ids, true );
	if ( false !== $key ) {
		array_splice( $ids, (int) $key, 1 );
		$in = false;
	} else {
		$ids[] = $product_id;
		$in    = true;
	}
	globalkeys_wishlist_save_ids( $user_id, $ids );
	return array(
		'in_wishlist' => $in,
		'ids'         => globalkeys_wishlist_get_ids( $user_id ),
	);
}

/**
 * Prüft, ob Produkt auf der Wunschliste des Users liegt.
 */
function globalkeys_wishlist_user_has_product( $user_id, $product_id ) {
	$ids = globalkeys_wishlist_get_ids( $user_id );
	return in_array( absint( $product_id ), $ids, true );
}

/**
 * Discovery-Links für leere Wunschliste (gleiche Ziele wie leerer Warenkorb-Drawer).
 *
 * @return array<int, array{label: string, url: string}>
 */
function globalkeys_wishlist_get_empty_discovery_links() {
	$trending    = home_url( '/trending-games/' );
	$bestsellers = home_url( '/#section-bestsellers' );
	$preorders   = home_url( '/preorders/' );
	if ( function_exists( 'is_front_page' ) && is_front_page() && class_exists( 'WooCommerce' ) ) {
		$trending = add_query_arg(
			array(
				'post_type'  => 'product',
				'gk_filters' => 'open',
			),
			home_url( '/' )
		);
		$preorders = add_query_arg(
			array(
				'post_type'  => 'product',
				'gk_filters' => 'open',
				'gk_pt'      => 'pre-orders',
			),
			home_url( '/' )
		);
	}
	return array(
		array(
			'label' => __( 'Trending', 'globalkeys' ),
			'url'   => $trending,
		),
		array(
			'label' => __( 'Bestseller', 'globalkeys' ),
			'url'   => $bestsellers,
		),
		array(
			'label' => __( 'Pre-orders', 'globalkeys' ),
			'url'   => $preorders,
		),
	);
}

/**
 * Leerer Zustand der Wunschliste (Optik wie leerer Cart-Drawer, Herz-Icon wie im Header).
 *
 * @param bool $guest_copy Text für Gäste (lokale Liste).
 */
function globalkeys_wishlist_get_empty_state_html( $guest_copy = false ) {
	$links = globalkeys_wishlist_get_empty_discovery_links();
	$heart = esc_url( get_template_directory_uri() . '/Pictures/heart2-gk.svg' );
	$title = __( 'Deine Wunschliste ist noch leer', 'globalkeys' );
	if ( $guest_copy ) {
		$copy = __( 'Tippe auf der Produktseite auf das Herz. Ohne Anmeldung bleibt die Liste nur auf diesem Gerät.', 'globalkeys' );
	} else {
		$copy = __( 'Entdecke Trending, Bestseller und Pre-orders – ein Klick aufs Herz in der Produktkarte genügt.', 'globalkeys' );
	}
	$aria = esc_attr__( 'Leere Wunschliste', 'globalkeys' );

	$link_parts = array();
	foreach ( $links as $item ) {
		if ( empty( $item['url'] ) ) {
			continue;
		}
		$link_parts[] = '<a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['label'] ) . '</a>';
	}
	$links_html = implode( '<span class="gk-wishlist__empty-sep" aria-hidden="true">•</span>', $link_parts );

	$icon_style = '-webkit-mask-image:url(\'' . $heart . '\');mask-image:url(\'' . $heart . '\')';

	return (
		'<section class="gk-wishlist__empty-state" aria-label="' . $aria . '">' .
		'<div class="gk-wishlist__empty-icon" aria-hidden="true"><span class="gk-wishlist__empty-icon-shape" style="' . esc_attr( $icon_style ) . '"></span></div>' .
		'<h2 class="gk-wishlist__empty-title">' . esc_html( $title ) . '</h2>' .
		'<p class="gk-wishlist__empty-copy">' . esc_html( $copy ) . '</p>' .
		( $links_html ? '<div class="gk-wishlist__empty-links">' . $links_html . '</div>' : '' ) .
		'</section>'
	);
}

/**
 * Markup für die Produktliste (horizontale Zeilen, volle Inhaltsbreite).
 *
 * @param int $user_id WordPress-User-ID.
 */
function globalkeys_wishlist_print_products_markup( $user_id ) {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}
	$user_id = (int) $user_id;
	$ids     = globalkeys_wishlist_get_ids( $user_id );
	if ( empty( $ids ) ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Markup nur aus esc_html/esc_url/esc_attr.
		echo globalkeys_wishlist_get_empty_state_html( false );
		return;
	}

	$visible_ids = array();
	foreach ( $ids as $product_id ) {
		$product = wc_get_product( $product_id );
		if ( $product && is_a( $product, 'WC_Product' ) && $product->is_visible() ) {
			$visible_ids[] = (int) $product_id;
		}
	}
	if ( empty( $visible_ids ) ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Markup nur aus esc_html/esc_url/esc_attr.
		echo globalkeys_wishlist_get_empty_state_html( false );
		return;
	}

	globalkeys_wishlist_ensure_added_timestamps( $user_id );
	$GLOBALS['gk_wishlist_row_user_id'] = $user_id;
	echo '<ul class="gk-wishlist__rows" aria-label="' . esc_attr__( 'Wunschliste', 'globalkeys' ) . '">';
	$gk_wl_list_i = 0;
	foreach ( $visible_ids as $product_id ) {
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			continue;
		}
		$GLOBALS['gk_wishlist_row_list_index'] = $gk_wl_list_i;
		++$gk_wl_list_i;
		$GLOBALS['product'] = $product;
		get_template_part( 'template-parts/wishlist-product', 'row' );
	}
	unset( $GLOBALS['gk_wishlist_row_list_index'], $GLOBALS['gk_wishlist_row_user_id'] );
	echo '</ul>';
}

/**
 * REST: Toggle (nur eingeloggt).
 */
function globalkeys_wishlist_rest_toggle( WP_REST_Request $request ) {
	$uid = get_current_user_id();
	$pid = absint( $request->get_param( 'product_id' ) );
	$res = globalkeys_wishlist_toggle_product( $uid, $pid );
	if ( is_wp_error( $res ) ) {
		return $res;
	}
	return new WP_REST_Response( $res, 200 );
}

/**
 * REST: Öffentliche Katalog-Snippets für Gäste (nur publish + sichtbar).
 */
function globalkeys_wishlist_rest_catalog( WP_REST_Request $request ) {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return new WP_REST_Response( array( 'html' => '' ), 200 );
	}
	$ids_param = $request->get_param( 'ids' );
	if ( ! is_string( $ids_param ) || '' === $ids_param ) {
		return new WP_REST_Response( array( 'html' => '' ), 200 );
	}
	$parts = array_map( 'absint', explode( ',', $ids_param ) );
	$parts = array_values( array_unique( array_filter( $parts ) ) );
	$parts = array_slice( $parts, 0, 60 );

	ob_start();
	if ( ! empty( $parts ) ) {
		echo '<ul class="gk-wishlist__rows" aria-label="' . esc_attr__( 'Wunschliste', 'globalkeys' ) . '">';
		$gk_wl_guest_i = 0;
		foreach ( $parts as $product_id ) {
			if ( ! globalkeys_wishlist_is_valid_product( $product_id ) ) {
				continue;
			}
			$product = wc_get_product( $product_id );
			if ( ! $product ) {
				continue;
			}
			$GLOBALS['gk_wishlist_row_list_index'] = $gk_wl_guest_i;
			++$gk_wl_guest_i;
			$GLOBALS['product'] = $product;
			get_template_part( 'template-parts/wishlist-product', 'row' );
		}
		unset( $GLOBALS['gk_wishlist_row_list_index'] );
		echo '</ul>';
	}
	$html = ob_get_clean();
	return new WP_REST_Response( array( 'html' => $html ), 200 );
}

/**
 * REST-Routen registrieren.
 */
function globalkeys_wishlist_register_rest_routes() {
	register_rest_route(
		'globalkeys/v1',
		'/wishlist/toggle',
		array(
			'methods'             => 'POST',
			'callback'            => 'globalkeys_wishlist_rest_toggle',
			'permission_callback' => static function () {
				return is_user_logged_in();
			},
			'args'                => array(
				'product_id' => array(
					'required'          => true,
					'type'              => 'integer',
					'sanitize_callback' => 'absint',
				),
			),
		)
	);

	register_rest_route(
		'globalkeys/v1',
		'/wishlist/catalog',
		array(
			'methods'             => 'GET',
			'callback'            => 'globalkeys_wishlist_rest_catalog',
			'permission_callback' => '__return_true',
			'args'                => array(
				'ids' => array(
					'required'          => false,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'globalkeys_wishlist_register_rest_routes' );
