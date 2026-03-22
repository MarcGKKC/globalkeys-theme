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

/** Maximale Anzahl Produkte in der Startseiten-Section „House Rewards“. */
define( 'GLOBALKEYS_HOUSE_REWARDS_MAX', 6 );

/**
 * Veröffentlichte Produkt-ID anhand des WooCommerce-Slugs.
 *
 * @param string $slug post_name des Produkts.
 * @return int 0 wenn nicht gefunden.
 */
function globalkeys_get_product_id_by_slug( $slug ) {
	if ( ! is_string( $slug ) || $slug === '' ) {
		return 0;
	}
	// get_page_by_path(post_name) – zuverlässiger als wc_get_products( 'slug' ) in manchen WC-Versionen.
	$post = get_page_by_path( $slug, OBJECT, 'product' );
	if ( ! $post || $post->post_status !== 'publish' ) {
		return 0;
	}
	return (int) $post->ID;
}

/**
 * Erstes Produkt aus Slug-Liste (Reihenfolge = Priorität).
 *
 * @param string[] $slugs Kandidaten-Slugs.
 * @param int      $exclude_id Diese ID überspringen (z. B. bereits großes Hero).
 * @return int
 */
function globalkeys_get_first_product_id_by_slugs( array $slugs, $exclude_id = 0 ) {
	$exclude_id = (int) $exclude_id;
	foreach ( $slugs as $slug ) {
		$id = globalkeys_get_product_id_by_slug( $slug );
		if ( $id > 0 && $id !== $exclude_id ) {
			return $id;
		}
	}
	return 0;
}

/**
 * House-Rewards-Liste auf GLOBALKEYS_HOUSE_REWARDS_MAX auffüllen, wenn Slugs/Customizer-Lücken oder gelöschte IDs weniger liefern.
 *
 * Reihenfolge der übergebenen IDs bleibt; fehlende Slots: Tag „house-members“, dann Featured, dann Beliebtheit.
 *
 * @param int[] $ids Bereits gewählte Produkt-IDs.
 * @return int[]
 */
function globalkeys_house_rewards_fill_curated_ids_to_limit( array $ids ) {
	if ( ! function_exists( 'wc_get_products' ) ) {
		$clean = array_values( array_unique( array_map( 'intval', $ids ) ) );
		return array_slice( array_filter( $clean ), 0, (int) GLOBALKEYS_HOUSE_REWARDS_MAX );
	}

	$max = (int) GLOBALKEYS_HOUSE_REWARDS_MAX;
	$ids = array_values( array_unique( array_map( 'intval', $ids ) ) );
	$ids = array_values( array_filter( $ids ) );

	$published = array();
	if ( ! empty( $ids ) ) {
		$valid = wc_get_products(
			array(
				'status'  => 'publish',
				'limit'   => -1,
				'return'  => 'ids',
				'include' => $ids,
			)
		);
		$valid = is_array( $valid ) ? array_map( 'intval', $valid ) : array();
		foreach ( $ids as $id ) {
			if ( in_array( (int) $id, $valid, true ) ) {
				$published[] = (int) $id;
			}
		}
	}
	$ids = $published;

	if ( count( $ids ) >= $max ) {
		return array_slice( $ids, 0, $max );
	}

	$exclude = $ids;
	$sources = array(
		array(
			'tag'     => array( 'house-members' ),
			'orderby' => 'menu_order',
			'order'   => 'ASC',
		),
		array(
			'featured' => true,
			'orderby'  => 'menu_order',
			'order'    => 'ASC',
		),
		array(
			'orderby' => 'popularity',
			'order'   => 'DESC',
		),
	);

	foreach ( $sources as $base_args ) {
		if ( count( $ids ) >= $max ) {
			break;
		}
		$need = $max - count( $ids );
		$more = wc_get_products(
			array_merge(
				array(
					'status'  => 'publish',
					'limit'   => max( $need * 5, 12 ),
					'return'  => 'ids',
					'exclude' => $exclude,
				),
				$base_args
			)
		);
		if ( ! is_array( $more ) ) {
			continue;
		}
		foreach ( $more as $pid ) {
			$pid = (int) $pid;
			if ( $pid < 1 || in_array( $pid, $ids, true ) ) {
				continue;
			}
			$ids[]     = $pid;
			$exclude[] = $pid;
			if ( count( $ids ) >= $max ) {
				break;
			}
		}
	}

	return array_slice( $ids, 0, $max );
}

/**
 * House Members / House Rewards: bis zu GLOBALKEYS_HOUSE_REWARDS_MAX Produkt-IDs für die Startseiten-Section.
 *
 * Customizer „gk_house_members_grid_product_ids“ (kommagetrennt) hat Vorrang.
 * Sonst: erste Treffer pro Slug-Gruppe (Theme-Standard aus eurem Katalog).
 *
 * @return int[]
 */
function globalkeys_get_house_members_curated_product_ids() {
	$raw = get_theme_mod( 'gk_house_members_grid_product_ids', '' );
	if ( is_string( $raw ) && $raw !== '' ) {
		$parts = preg_split( '/[\s,;]+/', $raw, -1, PREG_SPLIT_NO_EMPTY );
		$ids   = array();
		foreach ( $parts as $p ) {
			$id = absint( $p );
			if ( $id > 0 && ! in_array( $id, $ids, true ) ) {
				$ids[] = $id;
			}
			if ( count( $ids ) >= GLOBALKEYS_HOUSE_REWARDS_MAX ) {
				break;
			}
		}
		if ( ! empty( $ids ) ) {
			return globalkeys_house_rewards_fill_curated_ids_to_limit( $ids );
		}
	}

	$groups = apply_filters(
		'globalkeys_house_members_product_slug_groups',
		array(
			array(
				'elden-ring-nightreign-pc-steam',
				'elden-ring-nightreign-steam',
				'elden-ring-nightreign',
				'elden-ring-nightreign-pc',
			),
			array(
				'arc-raiders-pc-steam',
				'arc-raiders-steam',
				'arc-raiders-pc',
				'arc-raiders',
				'arcraiders-pc-steam',
				'arcraiders-steam',
				'arc-raiders-key',
				'arc-raiders-standard-edition-pc-steam',
			),
			array(
				'resident-evil-requiem-steam',
				'resident-evil-requiem-pc-steam',
				'resident-evil-requiem-pc',
				'resident-evil-requiem',
			),
			array(
				'life-is-strange-reunion-pc-steam',
				'life-is-strange-reunion-steam',
				'life-is-strange-reunion-pc',
				'life-is-strange-reunion',
				'life-is-strange-r-steam',
			),
			array(
				'lego-batman-legacy-of-the-dark-knight-pc-steam',
				'lego-batman-legacy-of-the-dark-knight-steam',
				'lego-batman-lotdk-steam',
				'lego-batman-lotdk',
				'lego-batman-legacy-of-the-dark-knight',
			),
			array(
				'marathon-pc-steam',
				'marathon-steam',
				'marathon-pc',
				'marathon',
			),
		)
	);

	if ( ! is_array( $groups ) ) {
		return array();
	}

	$ids = array();
	foreach ( $groups as $slugs ) {
		if ( count( $ids ) >= GLOBALKEYS_HOUSE_REWARDS_MAX ) {
			break;
		}
		if ( ! is_array( $slugs ) ) {
			$slugs = array( $slugs );
		}
		$id = globalkeys_get_first_product_id_by_slugs( $slugs, 0 );
		if ( $id > 0 && ! in_array( $id, $ids, true ) ) {
			$ids[] = $id;
		}
	}

	return globalkeys_house_rewards_fill_curated_ids_to_limit( $ids );
}

/**
 * Standard: großer Hero oben = Elden Ring Nightreign (Customizer-ID 0).
 *
 * @return int
 */
function globalkeys_get_default_hero_main_product_id() {
	$slugs = apply_filters(
		'globalkeys_default_hero_main_product_slugs',
		array(
			'elden-ring-nightreign-steam',
			'elden-ring-nightreign',
			'elden-ring-nightreign-pc-steam',
			'elden-ring-nightreign-pc',
		)
	);
	return globalkeys_get_first_product_id_by_slugs( $slugs, 0 );
}

/**
 * Standard: Banner-Hero unter Featured = Death Stranding 2 (Customizer-ID 0).
 *
 * @param int $main_hero_id Bereits gewähltes oberes Hero (nicht erneut verwenden).
 * @return int
 */
function globalkeys_get_default_hero_banner_product_id( $main_hero_id = 0 ) {
	$slugs = apply_filters(
		'globalkeys_default_hero_banner_product_slugs',
		array(
			'death-stranding-2-on-the-beach-pc-steam',
			'death-stranding-2-on-the-beach-steam',
			'death-stranding-2-on-the-beach',
			'death-stranding-2-pc-steam',
			'death-stranding-2-steam',
			'death-stranding-2',
		)
	);
	return globalkeys_get_first_product_id_by_slugs( $slugs, $main_hero_id );
}

/**
 * Effektive Hero-Produkt-ID: Customizer oder Theme-Standard per Slug.
 *
 * @param string $which 'main'|'banner'.
 * @return int
 */
function globalkeys_get_effective_hero_product_id( $which ) {
	$which = (string) $which;
	if ( 'main' === $which ) {
		$mod = (int) get_theme_mod( 'gk_hero_main_product_id', 0 );
		if ( $mod > 0 ) {
			return $mod;
		}
		return globalkeys_get_default_hero_main_product_id();
	}
	if ( 'banner' === $which ) {
		$mod = (int) get_theme_mod( 'gk_hero_banner_product_id', 0 );
		if ( $mod > 0 ) {
			return $mod;
		}
		$main = globalkeys_get_effective_hero_product_id( 'main' );
		return globalkeys_get_default_hero_banner_product_id( $main );
	}
	return 0;
}

/**
 * Gibt die konfigurierten Sections für die Startseite zurück.
 *
 * @return array Array mit Section-Definitionen: id, slug, label, aria_label
 */
function globalkeys_get_front_page_sections() {
	$main_hero_id   = globalkeys_get_effective_hero_product_id( 'main' );
	$banner_hero_id = globalkeys_get_effective_hero_product_id( 'banner' );

	$sections = array(
		array(
			'id'               => 'section-hero',
			'slug'             => 'hero',
			'label'            => __( 'Hero', 'globalkeys' ),
			'aria_label'       => __( 'Willkommensbereich', 'globalkeys' ),
			/* 0 im Customizer = Theme-Standard (Elden Ring Nightreign), sonst WooCommerce-Produkt-ID */
			'hero_product_id'  => $main_hero_id,
		),
		array(
			'id'        => 'section-featured',
			'slug'      => 'featured',
			'label'     => __( 'Featured Products', 'globalkeys' ),
			'aria_label' => __( 'Empfohlene Produkte', 'globalkeys' ),
		),
		array(
			'id'               => 'section-hero-product',
			'slug'             => 'hero-product',
			'label'            => __( 'Hero Product', 'globalkeys' ),
			'aria_label'       => __( 'Produktbereich', 'globalkeys' ),
			'hero_product_id'  => $banner_hero_id,
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
			'label'     => __( 'All Categories', 'globalkeys' ),
			'aria_label' => __( 'Kategorien', 'globalkeys' ),
		),
		array(
			'id'        => 'section-preorders',
			'slug'      => 'preorders',
			'label'     => __( 'Pre-orders', 'globalkeys' ),
			'aria_label' => __( 'Pre-orders', 'globalkeys' ),
		),
		array(
			'id'        => 'section-gift-cards',
			'slug'      => 'gift-cards',
			'label'     => __( 'Gift Cards', 'globalkeys' ),
			'aria_label' => __( 'Gift Cards', 'globalkeys' ),
		),
		array(
			'id'        => 'section-trust-strip',
			'slug'      => 'trust-strip',
			'label'     => __( 'Trust Strip', 'globalkeys' ),
			'aria_label' => __( 'Vertrauen & Service', 'globalkeys' ),
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
			'label'     => __( 'Premium Discounts', 'globalkeys' ),
			'aria_label' => __( 'Premium Discounts', 'globalkeys' ),
		),
		array(
			'id'         => 'section-premium-member-cta',
			'slug'       => 'premium-member-cta',
			'label'      => __( 'Premium Member CTA', 'globalkeys' ),
			'aria_label' => __( 'Premium membership', 'globalkeys' ),
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
	);

	return apply_filters( 'globalkeys_front_page_sections', $sections );
}

/**
 * Produkt für ein Produkthero anhand festgelegter ID (Customizer o. Section).
 *
 * @param int $product_id WooCommerce-Produkt-ID, 0 = keins.
 * @return WC_Product|null
 */
function globalkeys_resolve_hero_product_by_id( $product_id ) {
	$product_id = (int) $product_id;
	if ( $product_id < 1 || ! function_exists( 'wc_get_product' ) ) {
		return null;
	}
	$product = wc_get_product( $product_id );
	if ( ! $product || ! is_a( $product, 'WC_Product' ) || ! $product->is_visible() ) {
		return null;
	}
	return $product;
}

/**
 * Fallback-Produkt für Hero, optional bereits genutzte IDs ausschließen (zweites Hero).
 *
 * @param int[] $exclude_ids Produkt-IDs.
 * @return WC_Product|null
 */
function globalkeys_fallback_hero_product( $exclude_ids = array() ) {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return null;
	}
	$exclude_ids = array_values( array_unique( array_filter( array_map( 'intval', (array) $exclude_ids ) ) ) );

	$args_feat = array(
		'featured' => true,
		'status'   => 'publish',
		'limit'    => 12,
		'orderby'  => 'menu_order title',
		'order'    => 'ASC',
		'exclude'  => $exclude_ids,
		'return'   => 'objects',
	);
	if ( function_exists( 'globalkeys_wc_product_args_exclude_preorders' ) ) {
		$args_feat = globalkeys_wc_product_args_exclude_preorders( $args_feat );
	}
	$products = wc_get_products( $args_feat );
	if ( ! empty( $products ) && is_a( $products[0], 'WC_Product' ) ) {
		return $products[0];
	}

	$args_all = array(
		'status'  => 'publish',
		'limit'   => 12,
		'exclude' => $exclude_ids,
		'return'  => 'objects',
	);
	if ( function_exists( 'globalkeys_wc_product_args_exclude_preorders' ) ) {
		$args_all = globalkeys_wc_product_args_exclude_preorders( $args_all );
	}
	$products = wc_get_products( $args_all );
	if ( ! empty( $products ) && is_a( $products[0], 'WC_Product' ) ) {
		return $products[0];
	}
	return null;
}

/**
 * Merkt die ID des bereits gerenderten Hero-Produkts (für Ausschluss im zweiten Hero).
 *
 * @param WC_Product|null $product Produkt.
 */
function globalkeys_register_hero_product_used( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return;
	}
	if ( ! isset( $GLOBALS['gk_hero_used_product_ids'] ) ) {
		$GLOBALS['gk_hero_used_product_ids'] = array();
	}
	$GLOBALS['gk_hero_used_product_ids'][] = (int) $product->get_id();
}

/**
 * IDs, die bereits in einem Produkthero vorkamen.
 *
 * @return int[]
 */
function globalkeys_get_hero_used_product_ids() {
	if ( empty( $GLOBALS['gk_hero_used_product_ids'] ) || ! is_array( $GLOBALS['gk_hero_used_product_ids'] ) ) {
		return array();
	}
	return array_values( array_unique( array_map( 'intval', $GLOBALS['gk_hero_used_product_ids'] ) ) );
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
