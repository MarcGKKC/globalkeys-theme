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
			'label' => __( 'System Requirements', 'globalkeys' ),
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
 * Mappt einen Genre-Text (z. B. „Action“) auf einen Browse-Filter-Slug.
 *
 * @param string $segment Einzelner Genre-Teil (ohne Komma).
 * @return string Slug oder leer.
 */
function globalkeys_about_game_genre_segment_to_filter_slug( $segment ) {
	$segment = trim( wp_strip_all_tags( (string) $segment ) );
	if ( $segment === '' ) {
		return '';
	}
	$lower = mb_strtolower( $segment, 'UTF-8' );
	$cats  = function_exists( 'globalkeys_get_filter_categories' ) ? globalkeys_get_filter_categories() : array();
	foreach ( $cats as $slug => $label ) {
		if ( $lower === $slug ) {
			return $slug;
		}
		$label_lower = mb_strtolower( trim( (string) $label ), 'UTF-8' );
		if ( $lower === $label_lower ) {
			return $slug;
		}
	}
	$hyphen = str_replace( array( ' ', '_' ), '-', $lower );
	if ( isset( $cats[ $hyphen ] ) ) {
		return $hyphen;
	}
	if ( preg_match( '/^open\s*-?\s*world$/iu', $segment ) ) {
		return 'open-world';
	}
	if ( preg_match( '/^new\s*-?\s*releases?$/iu', $segment ) ) {
		return 'new-releases';
	}
	return '';
}

/**
 * Genre-Zeile „Action, Adventure“ → verlinkte Fragmente zur Browse-Seite mit gesetztem Kategorie-Filter.
 *
 * @param string $raw Komma-getrennte Genres.
 * @return string HTML (bereits escaped).
 */
function globalkeys_about_game_genre_value_to_linked_html( $raw ) {
	if ( ! is_string( $raw ) || trim( $raw ) === '' ) {
		return '';
	}
	if ( ! function_exists( 'globalkeys_get_browse_all_games_url' ) || ! class_exists( 'WooCommerce' ) ) {
		return esc_html( $raw );
	}
	$parts = preg_split( '/\s*,\s*/u', trim( $raw ) );
	$frag  = array();
	foreach ( $parts as $part ) {
		$part = trim( $part );
		if ( $part === '' ) {
			continue;
		}
		$slug = globalkeys_about_game_genre_segment_to_filter_slug( $part );
		if ( $slug !== '' ) {
			$url  = add_query_arg(
				array(
					'gk_filters'  => 'open',
					'gk_category' => $slug,
				),
				globalkeys_get_browse_all_games_url()
			);
			$frag[] = '<a class="gk-product-page-about-game__sidebar-dd-link" href="' . esc_url( $url ) . '">' . esc_html( $part ) . '</a>';
		} else {
			$frag[] = esc_html( $part );
		}
	}
	return implode( ', ', $frag );
}

/**
 * Zeilen für die Infobox-Tabelle (alle Standardfelder; leer → Platzhalter).
 *
 * @param WC_Product|null $product Produkt.
 * @return array<int, array{label:string,value:string,muted:bool,allow_html:bool,meta?:string}>
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
		$row     = array(
			'label'      => $def['label'],
			'value'      => $display,
			'muted'      => ! empty( $def['muted'] ),
			'allow_html' => ! empty( $def['allow_html'] ),
			'meta'       => $def['meta'],
		);
		if ( '_gk_about_info_genre' === $def['meta'] && $val !== '' && $display !== $placeholder ) {
			$row['value']      = globalkeys_about_game_genre_value_to_linked_html( $val );
			$row['allow_html'] = true;
		}
		if (
			'_gk_about_info_steam_recent' === $def['meta']
			&& $pid > 0
			&& function_exists( 'globalkeys_product_page_system_requirements_section_will_render' )
			&& globalkeys_product_page_system_requirements_section_will_render( $product )
		) {
			$anchor = 'gk-product-page-system-requirements-heading-' . $pid;
			$link_text = ( $val !== '' && $display !== $placeholder )
				? $val
				: apply_filters(
					'gk_about_game_sidebar_system_requirements_link_text',
					__( 'Look at system requirements.', 'globalkeys' ),
					$product
				);
			$row['value']      = '<a class="gk-product-page-about-game__sidebar-dd-link" href="' . esc_url( '#' . $anchor ) . '">' . esc_html( $link_text ) . '</a>';
			$row['allow_html'] = true;
		}
		$rows[] = $row;
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
