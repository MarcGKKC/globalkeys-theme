<?php
/**
 * Produkthero: Kaufkarte im Referenz-Layout (Status-Leiste, Social Proof, Preiszeile, Aktionen).
 *
 * @package globalkeys
 */

defined( 'ABSPATH' ) || exit;

/**
 * Aktive Kaufkarte: Produkthero + Produktbild (Keyart).
 *
 * @return bool
 */
function globalkeys_single_product_is_purchase_card_active() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return false;
	}
	global $product;
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		$product = wc_get_product( get_queried_object_id() );
	}
	if ( ! $product || ! is_a( $product, 'WC_Product' ) || ! $product->get_image_id() ) {
		return false;
	}
	return function_exists( 'globalkeys_product_has_page_hero' ) && globalkeys_product_has_page_hero( $product );
}

/**
 * @param WC_Product $product Produkt.
 * @return int 0 wenn kein Sale.
 */
function globalkeys_single_product_purchase_max_sale_percent( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) || ! $product->is_on_sale() ) {
		return 0;
	}
	if ( $product->is_type( 'simple' ) ) {
		$r = (float) $product->get_regular_price();
		$s = (float) $product->get_sale_price();
		if ( $r <= 0 || $s <= 0 ) {
			return 0;
		}
		return (int) max( 1, min( 99, round( 100 * ( 1 - $s / $r ) ) ) );
	}
	if ( $product->is_type( 'variable' ) ) {
		$max = 0;
		foreach ( $product->get_children() as $vid ) {
			$v = wc_get_product( (int) $vid );
			if ( ! $v || ! $v->is_on_sale() ) {
				continue;
			}
			$r = (float) $v->get_regular_price();
			$s = (float) $v->get_sale_price();
			if ( $r <= 0 || $s <= 0 ) {
				continue;
			}
			$p = (int) max( 1, min( 99, round( 100 * ( 1 - $s / $r ) ) ) );
			if ( $p > $max ) {
				$max = $p;
			}
		}
		return $max;
	}
	return 0;
}

/**
 * Rabatt-Prozent ausgeben (nur wenn > 0).
 */
function globalkeys_single_product_purchase_print_discount_pct() {
	if ( ! globalkeys_single_product_is_purchase_card_active() ) {
		return;
	}
	global $product;
	$pct = globalkeys_single_product_purchase_max_sale_percent( $product );
	if ( $pct < 1 ) {
		return;
	}
	echo '<span class="gk-bestseller-price-badge gk-purchase-card__discount-badge" aria-hidden="true">-' . (int) $pct . '%</span>';
}

/**
 * Status-Zeile: Plattform, Lager, Digital.
 */
function globalkeys_single_product_purchase_status_bar() {
	if ( ! globalkeys_single_product_is_purchase_card_active() ) {
		return;
	}
	global $product;

	$labels = array(
		'playstation' => 'PlayStation',
		'xbox'        => 'Xbox',
		'nintendo'    => 'Nintendo',
		'steam'       => 'Steam',
	);

	$platform = function_exists( 'globalkeys_get_product_platform_key' ) ? globalkeys_get_product_platform_key( $product ) : null;
	$icon_url = ( $platform && function_exists( 'globalkeys_get_product_platform_icon_url' ) )
		? globalkeys_get_product_platform_icon_url( $platform )
		: '';

	echo '<div class="gk-purchase-card__status" role="group" aria-label="' . esc_attr__( 'Produktstatus', 'globalkeys' ) . '">';

	if ( $icon_url && $platform ) {
		$plabel = isset( $labels[ $platform ] ) ? $labels[ $platform ] : ucfirst( (string) $platform );
		echo '<span class="gk-purchase-card__status-item gk-purchase-card__status-item--platform">';
		echo '<img class="gk-purchase-card__status-platform-icon" src="' . esc_url( $icon_url ) . '" width="22" height="22" alt="" decoding="async" loading="lazy" /> ';
		echo '<span class="gk-purchase-card__status-label">' . esc_html( $plabel ) . '</span>';
		echo '</span>';
	}

	if ( $product->is_in_stock() ) {
		echo '<span class="gk-purchase-card__status-item gk-purchase-card__status-item--ok">';
		echo '<span class="gk-purchase-card__check" aria-hidden="true"></span>';
		echo esc_html__( 'Produkt auf Lager', 'globalkeys' );
		echo '</span>';
	} else {
		echo '<span class="gk-purchase-card__status-item gk-purchase-card__status-item--bad">';
		echo esc_html__( 'Ausverkauft', 'globalkeys' );
		echo '</span>';
	}

	if ( $product->is_virtual() || $product->is_downloadable() ) {
		echo '<span class="gk-purchase-card__status-sep" aria-hidden="true"></span>';
		echo '<span class="gk-purchase-card__status-item gk-purchase-card__status-item--ok">';
		echo '<span class="gk-purchase-card__check" aria-hidden="true"></span>';
		echo esc_html__( 'Digitaler Download', 'globalkeys' );
		echo '</span>';
	}

	echo '</div>';
}

/**
 * Social-Proof-Zeile (Zahl per Filter steuerbar; 0 = ausblenden).
 */
function globalkeys_single_product_purchase_social_row() {
	if ( ! globalkeys_single_product_is_purchase_card_active() ) {
		return;
	}
	global $product;
	$count = (int) apply_filters( 'gk_purchase_card_live_users_count', 209, $product );
	if ( $count < 1 ) {
		return;
	}
	echo '<p class="gk-purchase-card__social" role="status">';
	echo '<span class="gk-purchase-card__social-flame" aria-hidden="true">';
	echo '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 22c4.97 0 8-3.58 8-7.5C20 9.5 17.5 7 15 5.5 14.5 8 12 9 12 9S9.5 8 9 5.5C6.5 7 4 9.5 4 14.5 4 18.42 7.03 22 12 22Z" fill="#ff7a2e"/><path d="M12 19a4 4 0 0 0 4-4c0-2.5-2-3.5-4-6-2 2.5-4 3.5-4 6a4 4 0 0 0 4 4Z" fill="#ffb14a"/></svg>';
	echo '</span>';
	echo '<span class="gk-purchase-card__social-text">';
	echo esc_html(
		sprintf(
			/* translators: %d: number (override with filter gk_purchase_card_live_users_count, use 0 to hide). */
			__( '%d Benutzer auf der Seite', 'globalkeys' ),
			$count
		)
	);
	echo '</span>';
	echo '</p>';
}

/**
 * Preisblock (einfaches Produkt): Icon-Zeile + Rabatt + Woo-Preis.
 */
function globalkeys_single_product_purchase_price_block() {
	if ( ! globalkeys_single_product_is_purchase_card_active() ) {
		return;
	}
	global $product;
	if ( $product->is_type( 'variable' ) ) {
		return;
	}
	echo '<div class="gk-purchase-card__price-row gk-purchase-card__price-row--simple">';
	echo '<div class="gk-purchase-card__price-center-group">';
	echo '<span class="gk-purchase-card__price-tag-icon" aria-hidden="true"></span>';
	echo '<div class="gk-purchase-card__price-stack">';
	globalkeys_single_product_purchase_print_discount_pct();
	woocommerce_template_single_price();
	echo '</div></div></div>';
}

/**
 * Variable: Preiszeile öffnen (vor .single_variation).
 */
function globalkeys_single_product_purchase_var_price_open() {
	if ( ! globalkeys_single_product_is_purchase_card_active() ) {
		return;
	}
	echo '<div class="gk-purchase-card__price-row gk-purchase-card__price-row--variable">';
	echo '<div class="gk-purchase-card__price-center-group">';
	echo '<span class="gk-purchase-card__price-tag-icon" aria-hidden="true"></span>';
	echo '<div class="gk-purchase-card__price-stack">';
	globalkeys_single_product_purchase_print_discount_pct();
}

/**
 * Variable: Preiszeile schließen (nach .single_variation, vor Warenkorb-Zeile).
 */
function globalkeys_single_product_purchase_var_price_close() {
	if ( ! globalkeys_single_product_is_purchase_card_active() ) {
		return;
	}
	echo '</div></div></div>';
}

/**
 * Wunschliste + Primärspalte um Menge/Button.
 */
function globalkeys_single_product_purchase_actions_open() {
	if ( ! globalkeys_single_product_is_purchase_card_active() ) {
		return;
	}
	$favorites_url = home_url( '/favorites/' );
	$heart         = esc_url( get_template_directory_uri() . '/Pictures/heart2-gk.svg' );
	echo '<div class="gk-purchase-card__actions">';
	echo '<a class="gk-purchase-card__wishlist" href="' . esc_url( $favorites_url ) . '" aria-label="' . esc_attr__( 'Favoriten', 'globalkeys' ) . '">';
	echo '<img src="' . $heart . '" width="30" height="30" alt="" decoding="async" loading="lazy" />';
	echo '</a>';
	echo '<div class="gk-purchase-card__actions-primary">';
}

/**
 * Öffnet Cluster Preis + Warenkorb (einfaches Produkt): Block per margin-top:auto ans untere Kartenende.
 */
function globalkeys_single_product_purchase_cta_cluster_open_simple() {
	if ( ! globalkeys_single_product_is_purchase_card_active() ) {
		return;
	}
	global $product;
	if ( ! $product || ! $product->is_type( 'simple' ) || ! $product->is_purchasable() || ! $product->is_in_stock() ) {
		return;
	}
	echo '<div class="gk-purchase-card__cta-cluster">';
}

/**
 * Schließt CTA-Cluster (nur einfach – variable schließt im Theme-variable.php).
 */
function globalkeys_single_product_purchase_cta_cluster_close_simple() {
	if ( ! globalkeys_single_product_is_purchase_card_active() ) {
		return;
	}
	global $product;
	if ( ! $product || ! $product->is_type( 'simple' ) || ! $product->is_purchasable() || ! $product->is_in_stock() ) {
		return;
	}
	echo '</div>';
}

/**
 * Schließt Aktionen-Wrapper.
 */
function globalkeys_single_product_purchase_actions_close() {
	if ( ! globalkeys_single_product_is_purchase_card_active() ) {
		return;
	}
	echo '</div></div>';
}

/**
 * Hooks für Kaufkarten-Layout registrieren.
 */
function globalkeys_single_product_purchase_card_bootstrap() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	if ( ! globalkeys_single_product_is_purchase_card_active() ) {
		return;
	}

	global $product;
	if ( $product->is_type( 'external' ) || $product->is_type( 'grouped' ) ) {
		return;
	}

	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
	remove_action( 'woocommerce_single_product_summary', 'globalkeys_single_product_summary_attributes', 25 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 99 );

	if ( $product && $product->is_type( 'variable' ) ) {
		add_action( 'woocommerce_before_single_variation', 'globalkeys_single_product_purchase_var_price_open', 5 );
		add_action( 'woocommerce_after_single_variation', 'globalkeys_single_product_purchase_var_price_close', 5 );
	} else {
		add_action( 'woocommerce_single_product_summary', 'globalkeys_single_product_purchase_cta_cluster_open_simple', 27 );
		add_action( 'woocommerce_single_product_summary', 'globalkeys_single_product_purchase_price_block', 28 );
		add_action( 'woocommerce_after_add_to_cart_form', 'globalkeys_single_product_purchase_cta_cluster_close_simple', 5 );
	}

	add_action( 'woocommerce_single_product_summary', 'globalkeys_single_product_purchase_status_bar', 4 );
	add_action( 'woocommerce_single_product_summary', 'globalkeys_single_product_purchase_social_row', 5 );

	add_action( 'woocommerce_before_add_to_cart_button', 'globalkeys_single_product_purchase_actions_open', 1 );
	add_action( 'woocommerce_after_add_to_cart_button', 'globalkeys_single_product_purchase_actions_close', 999 );
}
add_action( 'wp', 'globalkeys_single_product_purchase_card_bootstrap', 24 );

add_filter(
	'woocommerce_get_stock_html',
	static function ( $html, $product_obj ) {
		if ( ! globalkeys_single_product_is_purchase_card_active() ) {
			return $html;
		}
		if ( $product_obj && $product_obj->is_type( 'simple' ) ) {
			return '';
		}
		return $html;
	},
	10,
	2
);

/**
 * Body-Klasse für gezieltes Styling.
 *
 * @param string[] $classes Klassen.
 * @return string[]
 */
function globalkeys_single_product_purchase_card_body_class( $classes ) {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return $classes;
	}
	$p = wc_get_product( get_queried_object_id() );
	if ( ! $p || ! $p->get_image_id() || ! function_exists( 'globalkeys_product_has_page_hero' ) || ! globalkeys_product_has_page_hero( $p ) ) {
		return $classes;
	}
	$classes[] = 'gk-purchase-card-ui';
	return $classes;
}
add_filter( 'body_class', 'globalkeys_single_product_purchase_card_body_class' );
