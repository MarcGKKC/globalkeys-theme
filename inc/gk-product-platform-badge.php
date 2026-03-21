<?php
/**
 * Plattform-Badge (PlayStation, Xbox, Nintendo, Steam) aus product_cat.
 *
 * @package globalkeys
 */

defined( 'ABSPATH' ) || exit;

/**
 * SVG-Dateien unter /Pictures/ (Theme-Root), Leerzeichen per rawurlencode.
 *
 * @return array<string, string> Plattform-Schlüssel => Dateiname.
 */
function globalkeys_platform_badge_svg_files() {
	return array(
		'playstation' => 'playstation-logo-gk (1).svg',
		'xbox'        => 'xbox-logo-gk (1).svg',
		'nintendo'    => 'Switch-gk (1).svg',
		'steam'       => 'steam-logo.gk (1).svg',
	);
}

/**
 * Öffentliche URL zum Plattform-Icon.
 *
 * @param string $platform playstation|xbox|nintendo|steam.
 * @return string Leer bei unbekannter Plattform.
 */
function globalkeys_get_product_platform_icon_url( $platform ) {
	$map = globalkeys_platform_badge_svg_files();
	if ( ! isset( $map[ $platform ] ) ) {
		return '';
	}
	$dir = trailingslashit( get_template_directory_uri() ) . 'Pictures/';
	return esc_url( $dir . rawurlencode( $map[ $platform ] ) );
}

/**
 * Erkennt Plattform anhand eines product_cat-Terms (Slug + Name).
 *
 * @param WP_Term $term Kategorie.
 * @return string|null playstation|xbox|nintendo|steam.
 */
function globalkeys_term_to_platform_key( $term ) {
	if ( ! $term || ! isset( $term->slug ) ) {
		return null;
	}
	$slug = strtolower( (string) $term->slug );
	$name = strtolower( (string) wp_strip_all_tags( $term->name ) );
	$hay  = $slug . ' ' . $name;

	/* Reihenfolge: spezifisch vor allgemein (z. B. „xbox“ vor „pc“) */
	if ( preg_match( '/\b(playstation|ps[345]|psn|ps vita|psp)\b|(^|-)ps[345](-|$)/', $hay ) ) {
		return 'playstation';
	}
	if ( preg_match( '/\bxbox\b|xbox[-_]?(one|series|360)?/', $hay ) ) {
		return 'xbox';
	}
	if ( preg_match( '/\b(nintendo|switch)\b/', $hay ) ) {
		return 'nintendo';
	}
	if ( preg_match( '/\bsteam\b/', $hay ) ) {
		return 'steam';
	}
	/* Häufig: PC-Keys = Steam */
	if ( preg_match( '/(^|-)pc(-|$)|\bpc games\b|\bwindows\b/', $hay ) && ! preg_match( '/\bxbox\b/', $hay ) ) {
		return 'steam';
	}

	return null;
}

/**
 * Plattform für Badge aus allen Produktkategorien (inkl. Eltern).
 *
 * @param WC_Product|int $product Produkt oder ID.
 * @return string|null playstation|xbox|nintendo|steam.
 */
function globalkeys_get_product_platform_key( $product ) {
	if ( is_numeric( $product ) ) {
		$product = wc_get_product( (int) $product );
	}
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return null;
	}

	$terms = get_the_terms( (int) $product->get_id(), 'product_cat' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return null;
	}

	$matched = array();
	foreach ( $terms as $term ) {
		$key = globalkeys_term_to_platform_key( $term );
		if ( $key ) {
			$matched[ $key ] = true;
		}
		$ancestors = get_ancestors( (int) $term->term_id, 'product_cat' );
		foreach ( $ancestors as $aid ) {
			$ancestor = get_term( (int) $aid, 'product_cat' );
			if ( $ancestor && ! is_wp_error( $ancestor ) ) {
				$key2 = globalkeys_term_to_platform_key( $ancestor );
				if ( $key2 ) {
					$matched[ $key2 ] = true;
				}
			}
		}
	}

	$priority = array( 'playstation', 'xbox', 'nintendo', 'steam' );
	foreach ( $priority as $k ) {
		if ( ! empty( $matched[ $k ] ) ) {
			return $k;
		}
	}

	return null;
}
