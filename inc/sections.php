<?php
/**
 * Front-Page Sections Configuration
 *
 * Zentrale Definition aller Sections. Header, Nav und andere Teile des Themes
 * können diese Daten nutzen (z.B. Anchor-Links, aktive Section).
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gibt die konfigurierten Sections für die Startseite zurück.
 *
 * @return array Array mit Section-Definitionen: id, slug, label, aria_label
 */
function globalkeys_get_front_page_sections() {
	$sections = array(
		array(
			'id'        => 'section-hero',
			'slug'      => 'hero',
			'label'     => __( 'Hero', 'globalkeys' ),
			'aria_label' => __( 'Willkommensbereich', 'globalkeys' ),
		),
		array(
			'id'        => 'section-featured',
			'slug'      => 'featured',
			'label'     => __( 'Featured Products', 'globalkeys' ),
			'aria_label' => __( 'Empfohlene Produkte', 'globalkeys' ),
		),
		array(
			'id'        => 'section-trust-strip',
			'slug'      => 'trust-strip',
			'label'     => __( 'Trust Strip', 'globalkeys' ),
			'aria_label' => __( 'Vertrauen & Service', 'globalkeys' ),
		),
		array(
			'id'        => 'section-intro',
			'slug'      => 'intro',
			'label'     => __( 'Intro', 'globalkeys' ),
			'aria_label' => __( 'Einführung', 'globalkeys' ),
		),
		array(
			'id'        => 'section-cta',
			'slug'      => 'cta',
			'label'     => __( 'Call to Action', 'globalkeys' ),
			'aria_label' => __( 'Handlungsaufforderung', 'globalkeys' ),
		),
	);

	return apply_filters( 'globalkeys_front_page_sections', $sections );
}

/**
 * Prüft, ob die aktuelle Seite Sections anzeigt (Startseite).
 *
 * @return bool
 */
function globalkeys_has_front_page_sections() {
	return is_front_page() && ( is_home() || is_page() );
}

/**
 * Formatiert eine Zahl für die Hero-Statistik: Tausend = K, Million = M.
 *
 * @param int|float $num Zahl
 * @return string z.B. 12.5K, 2M, 50
 */
function globalkeys_format_stat_number( $num ) {
	$num = (float) $num;
	if ( $num >= 1000000 ) {
		$n = $num / 1000000;
		return ( $n === floor( $n ) ? (int) $n : round( $n, 1 ) ) . 'M';
	}
	if ( $num >= 1000 ) {
		$n = $num / 1000;
		return ( $n === floor( $n ) ? (int) $n : round( $n, 1 ) ) . 'K';
	}
	return (string) (int) $num;
}
