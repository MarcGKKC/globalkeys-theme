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
