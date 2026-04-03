<?php
/**
 * Feste Infobox-Zeilen neben „About the Game“ (Metadaten pro Produkt).
 *
 * @package globalkeys
 */

defined( 'ABSPATH' ) || exit;

/**
 * Standard-Zeilen: Meta-Key, sichtbare Bezeichnung, gedämpfter Wert, Feldtyp.
 *
 * @return array<int, array{meta:string,label:string,muted:bool,type:string,max:int,allow_html?:bool}>
 */
function globalkeys_get_about_game_sidebar_standard_info_definitions() {
	$defs = array(
		array(
			'meta'  => '_gk_about_info_country',
			'label' => __( 'Country compatibility', 'globalkeys' ),
			'muted' => false,
			'type'  => 'textarea',
			'max'   => 4000,
		),
		array(
			'meta'  => '_gk_about_info_languages',
			'label' => __( 'Languages', 'globalkeys' ),
			'muted' => false,
			'type'  => 'textarea',
			'max'   => 4000,
		),
		array(
			'meta'  => '_gk_about_info_installation',
			'label' => __( 'Installation', 'globalkeys' ),
			'muted' => false,
			'type'  => 'textarea',
			'max'   => 6000,
		),
		array(
			'meta'  => '_gk_about_info_pegi',
			'label' => __( 'Rating', 'globalkeys' ),
			'muted' => true,
			'type'  => 'text',
			'max'   => 120,
		),
		array(
			'meta'  => '_gk_about_info_developer',
			'label' => __( 'Developer', 'globalkeys' ),
			'muted' => false,
			'type'  => 'text',
			'max'   => 500,
		),
		array(
			'meta'  => '_gk_about_info_publisher',
			'label' => __( 'Publisher', 'globalkeys' ),
			'muted' => false,
			'type'  => 'text',
			'max'   => 500,
		),
		array(
			'meta'  => '_gk_about_info_release_date',
			'label' => __( 'Release date', 'globalkeys' ),
			'muted' => true,
			'type'  => 'text',
			'max'   => 200,
		),
		array(
			'meta'  => '_gk_about_info_genre',
			'label' => __( 'Genre', 'globalkeys' ),
			'muted' => false,
			'type'  => 'text',
			'max'   => 500,
		),
		array(
			'meta'  => '_gk_about_info_steam_recent',
			'label' => __( 'Recent Steam reviews', 'globalkeys' ),
			'muted' => true,
			'type'  => 'text',
			'max'   => 500,
		),
		array(
			'meta'        => '_gk_about_info_steam_all',
			'label'       => __( 'All Steam reviews', 'globalkeys' ),
			'muted'       => false,
			'type'        => 'textarea',
			'max'         => 8000,
			'allow_html'  => true,
		),
	);

	/**
	 * Zeilen anpassen, entfernen oder erweitern (meta, label, muted, type, max, allow_html).
	 *
	 * @param array $defs Definitionen.
	 */
	$filtered = apply_filters( 'gk_about_game_sidebar_standard_info_definitions', $defs );
	if ( ! is_array( $filtered ) || $filtered === array() ) {
		return $defs;
	}
	return $filtered;
}

/**
 * Speichert alle Standard-Infobox-Metafelder aus POST.
 *
 * @param WC_Product $product Produkt.
 */
function globalkeys_save_about_game_sidebar_standard_info( $product ) {
	if ( ! is_a( $product, 'WC_Product' ) ) {
		return;
	}

	foreach ( globalkeys_get_about_game_sidebar_standard_info_definitions() as $def ) {
		$key = $def['meta'];
		if ( ! isset( $_POST[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			continue;
		}
		$raw = wp_unslash( $_POST[ $key ] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! empty( $def['allow_html'] ) ) {
			$clean = wp_kses_post( is_string( $raw ) ? $raw : '' );
		} elseif ( isset( $def['type'] ) && $def['type'] === 'textarea' ) {
			$clean = sanitize_textarea_field( is_string( $raw ) ? $raw : '' );
		} else {
			$clean = sanitize_text_field( is_string( $raw ) ? $raw : '' );
		}

		$max = isset( $def['max'] ) ? (int) $def['max'] : 2000;
		if ( $max > 0 && strlen( $clean ) > $max ) {
			$clean = substr( $clean, 0, $max );
		}

		if ( trim( $clean ) === '' ) {
			$product->delete_meta_data( $key );
		} else {
			$product->update_meta_data( $key, $clean );
		}
	}
}

/**
 * Zeilen für die Infobox-Tabelle (alle Standardfelder; leer → Platzhalter).
 *
 * @param WC_Product|null $product Produkt.
 * @return array<int, array{label:string,value:string,muted:bool,allow_html:bool}>
 */
function globalkeys_build_about_game_sidebar_standard_display_rows( $product ) {
	$rows = array();
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return $rows;
	}

	$placeholder = apply_filters( 'gk_about_game_sidebar_info_empty_placeholder', __( '–', 'globalkeys' ), $product );
	if ( ! is_string( $placeholder ) || $placeholder === '' ) {
		$placeholder = '–';
	}

	$pid = (int) $product->get_id();
	foreach ( globalkeys_get_about_game_sidebar_standard_info_definitions() as $def ) {
		$raw = $product->get_meta( $def['meta'] );
		if ( ( ! is_string( $raw ) || trim( $raw ) === '' ) && $pid > 0 ) {
			$pm = get_post_meta( $pid, $def['meta'], true );
			$raw = is_string( $pm ) ? $pm : '';
		}
		$val     = is_string( $raw ) ? trim( $raw ) : '';
		$display = $val === '' ? $placeholder : $val;
		$rows[]  = array(
			'label'      => $def['label'],
			'value'      => $display,
			'muted'      => ! empty( $def['muted'] ),
			'allow_html' => ! empty( $def['allow_html'] ),
		);
	}

	return $rows;
}

/**
 * Meta für WooCommerce/HPOS und REST zuverlässig registrieren (Lesen/Schreiben im Admin).
 */
function globalkeys_register_about_game_sidebar_product_meta() {
	if ( ! function_exists( 'register_post_meta' ) ) {
		return;
	}

	$auth = static function () {
		return current_user_can( 'edit_products' );
	};

	foreach ( globalkeys_get_about_game_sidebar_standard_info_definitions() as $def ) {
		if ( empty( $def['meta'] ) ) {
			continue;
		}
		register_post_meta(
			'product',
			$def['meta'],
			array(
				'single'         => true,
				'type'           => 'string',
				'show_in_rest'   => true,
				'auth_callback'  => $auth,
			)
		);
	}

	register_post_meta(
		'product',
		'_gk_about_game_sidebar_rows',
		array(
			'single'         => true,
			'type'           => 'string',
			'show_in_rest'   => true,
			'auth_callback'  => $auth,
		)
	);
}
add_action( 'init', 'globalkeys_register_about_game_sidebar_product_meta', 11 );
