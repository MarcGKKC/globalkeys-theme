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
			'id'        => 'section-hero-product',
			'slug'      => 'hero-product',
			'label'     => __( 'Hero Product', 'globalkeys' ),
			'aria_label' => __( 'Produktbereich', 'globalkeys' ),
		),
		array(
			'id'        => 'section-preorders',
			'slug'      => 'preorders',
			'label'     => __( 'Pre-orders', 'globalkeys' ),
			'aria_label' => __( 'Pre-orders', 'globalkeys' ),
		),
		array(
			'id'        => 'section-trust-strip',
			'slug'      => 'trust-strip',
			'label'     => __( 'Trust Strip', 'globalkeys' ),
			'aria_label' => __( 'Vertrauen & Service', 'globalkeys' ),
		),
		array(
			'id'        => 'section-bestsellers',
			'slug'      => 'bestsellers',
			'label'     => __( 'Bestsellers', 'globalkeys' ),
			'aria_label' => __( 'Bestseller', 'globalkeys' ),
		),
		array(
			'id'        => 'section-categories',
			'slug'      => 'categories',
			'label'     => __( 'Our Categories', 'globalkeys' ),
			'aria_label' => __( 'Kategorien', 'globalkeys' ),
		),
		array(
			'id'        => 'section-gift-cards',
			'slug'      => 'gift-cards',
			'label'     => __( 'Gift Cards', 'globalkeys' ),
			'aria_label' => __( 'Gift Cards', 'globalkeys' ),
		),
		array(
			'id'        => 'section-reward',
			'slug'      => 'reward',
			'label'     => __( 'Reward-System', 'globalkeys' ),
			'aria_label' => __( 'Reward-System', 'globalkeys' ),
		),
		array(
			'id'        => 'section-house-members',
			'slug'      => 'house-members',
			'label'     => __( 'House Members', 'globalkeys' ),
			'aria_label' => __( 'House Members', 'globalkeys' ),
		),
		array(
			'id'        => 'section-recently-viewed',
			'slug'      => 'recently-viewed',
			'label'     => __( 'Last seen', 'globalkeys' ),
			'aria_label' => __( 'Last seen', 'globalkeys' ),
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
		array(
			'id'        => 'section-weekly-deals',
			'slug'      => 'weekly-deals',
			'label'     => __( 'Weekly deals', 'globalkeys' ),
			'aria_label' => __( 'Weekly deals', 'globalkeys' ),
		),
		array(
			'id'        => 'section-budget-games',
			'slug'      => 'budget-games',
			'label'     => __( 'Spiele für jeden Geldbeutel', 'globalkeys' ),
			'aria_label' => __( 'Spiele für jeden Geldbeutel', 'globalkeys' ),
		),
		array(
			'id'        => 'section-reward',
			'slug'      => 'reward',
			'label'     => __( 'Reward-System', 'globalkeys' ),
			'aria_label' => __( 'Reward-System', 'globalkeys' ),
		),
		array(
			'id'        => 'section-house-members',
			'slug'      => 'house-members',
			'label'     => __( 'House Members', 'globalkeys' ),
			'aria_label' => __( 'House Members', 'globalkeys' ),
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
