<?php
/**
 * House Member / Premium: besserer Preis für Mitglieder
 *
 * - Jeder kann das Produkt zum normalen Shop-Preis kaufen.
 * - Optionaler niedrigerer Preis (_gk_house_member_price) für Nutzer mit Zugang (Abo / Profil / Rollen).
 * - Section „House Members“: für alle sichtbar, immer zur Produktseite.
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Meta-Key: Mitgliedspreis (ohne Unterstrich in get_meta = mit Unterstrich in DB). */
define( 'GLOBALKEYS_HOUSE_MEMBER_PRICE_META', '_gk_house_member_price' );

/**
 * Produkt-IDs (Subscription/Mitgliedschaft), die den Mitgliederpreis freischalten (Customizer, kommagetrennt).
 *
 * @return int[]
 */
function globalkeys_house_member_unlock_product_ids() {
	$raw = get_theme_mod( 'gk_house_subscription_product_ids', '' );
	if ( ! is_string( $raw ) || $raw === '' ) {
		return array();
	}
	$parts = preg_split( '/[\s,;]+/', $raw, -1, PREG_SPLIT_NO_EMPTY );
	$ids   = array();
	foreach ( $parts as $p ) {
		$id = absint( $p );
		if ( $id > 0 ) {
			$ids[] = $id;
		}
	}
	return array_values( array_unique( $ids ) );
}

/**
 * URL für „Mitglied werden“ / Abo (Hinweise für Nicht-Mitglieder).
 *
 * @return string
 */
function globalkeys_house_member_cta_url() {
	$url = get_theme_mod( 'gk_house_member_cta_url', '' );
	if ( is_string( $url ) && $url !== '' ) {
		return esc_url( $url );
	}
	return esc_url( home_url( '/subscriptions/' ) );
}

/**
 * Text für das rote „Premium Discount“-Badge auf House-Rewards-Karten (Customizer).
 *
 * @return string
 */
function globalkeys_get_house_rewards_promo_badge_text() {
	$v = get_theme_mod( 'gk_house_rewards_promo_badge', '' );
	return ( is_string( $v ) && $v !== '' ) ? $v : __( 'Premium Discount', 'globalkeys' );
}

/**
 * Text daneben, z. B. Frist (Customizer).
 *
 * @return string
 */
function globalkeys_get_house_rewards_promo_until_text() {
	$v = get_theme_mod( 'gk_house_rewards_promo_until', '' );
	return ( is_string( $v ) && $v !== '' ) ? $v : __( 'Ends April', 'globalkeys' );
}

/**
 * Unter dem Kartentitel in House Rewards: wie Pre-orders (PRE-ORDER + Datum) und rechts daneben Premium Discount + Frist (rot).
 *
 * @param WC_Product $product Produkt.
 */
function globalkeys_render_house_rewards_card_meta_row( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return;
	}
	$premium_badge = globalkeys_get_house_rewards_promo_badge_text();
	$premium_until = globalkeys_get_house_rewards_promo_until_text();
	$is_po         = function_exists( 'globalkeys_is_preorder_product' ) && globalkeys_is_preorder_product( $product );

	echo '<span class="gk-house-rewards-card-meta" aria-hidden="true">';
	if ( $is_po ) {
		echo '<span class="gk-house-rewards-card-meta__preorder">';
		echo '<span class="gk-preorder-meta gk-preorder-meta--in-house-cluster">';
		echo '<span class="gk-preorder-badge">' . esc_html__( 'PRE-ORDER', 'globalkeys' ) . '</span>';
		if ( function_exists( 'globalkeys_format_product_release_date_display' ) ) {
			$date_str = globalkeys_format_product_release_date_display( $product );
			if ( $date_str !== '' ) {
				echo '<span class="gk-preorder-date">' . esc_html( $date_str ) . '</span>';
			}
		}
		echo '</span></span>';
	}
	echo '<span class="gk-house-rewards-card-meta__premium">';
	echo '<span class="gk-house-rewards-card-premium-badge">' . esc_html( $premium_badge ) . '</span>';
	if ( $premium_until !== '' ) {
		echo '<span class="gk-house-rewards-card-premium-until">' . esc_html( $premium_until ) . '</span>';
	}
	echo '</span>';
	echo '</span>';
}

/**
 * Roher Mitgliedspreis aus Meta (simple, variation, variable = Minimum der Variationen).
 *
 * @param WC_Product|null $product Produkt.
 * @return string|null Nicht-leerer Preis-String oder null.
 */
function globalkeys_get_product_house_member_price_raw( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return null;
	}

	if ( $product->is_type( 'variation' ) ) {
		$v = $product->get_meta( GLOBALKEYS_HOUSE_MEMBER_PRICE_META, true );
		return globalkeys_sanitize_member_price_meta( $v );
	}

	if ( $product->is_type( 'simple' ) || $product->is_type( 'external' ) ) {
		$v = $product->get_meta( GLOBALKEYS_HOUSE_MEMBER_PRICE_META, true );
		return globalkeys_sanitize_member_price_meta( $v );
	}

	if ( $product->is_type( 'variable' ) ) {
		$min   = null;
		$child = $product->get_children();
		if ( empty( $child ) ) {
			return null;
		}
		foreach ( $child as $vid ) {
			$var = wc_get_product( $vid );
			if ( ! $var || ! $var->is_type( 'variation' ) ) {
				continue;
			}
			$v = globalkeys_sanitize_member_price_meta( $var->get_meta( GLOBALKEYS_HOUSE_MEMBER_PRICE_META, true ) );
			if ( $v === null ) {
				continue;
			}
			$n = (float) wc_format_decimal( $v );
			if ( $n <= 0 ) {
				continue;
			}
			if ( null === $min || $n < $min ) {
				$min = $n;
			}
		}
		return null === $min ? null : (string) $min;
	}

	return null;
}

/**
 * @param mixed $v Meta-Wert.
 * @return string|null
 */
function globalkeys_sanitize_member_price_meta( $v ) {
	if ( $v === null || $v === '' ) {
		return null;
	}
	if ( ! is_string( $v ) && ! is_numeric( $v ) ) {
		return null;
	}
	$s = wc_format_decimal( $v );
	if ( $s === '' || (float) $s <= 0 ) {
		return null;
	}
	return $s;
}

/**
 * Hat das Produkt einen konfigurierten Mitgliedspreis, der unter dem aktuellen Basispreis liegt?
 *
 * @param WC_Product $product Produkt.
 * @return bool
 */
function globalkeys_product_has_house_member_deal( WC_Product $product ) {
	if ( $product->is_type( 'variable' ) ) {
		foreach ( $product->get_children() as $vid ) {
			$v = wc_get_product( $vid );
			if ( $v && globalkeys_product_has_house_member_deal( $v ) ) {
				return true;
			}
		}
		return false;
	}

	$raw = globalkeys_get_product_house_member_price_raw( $product );
	if ( $raw === null ) {
		return false;
	}
	$member = (float) wc_format_decimal( $raw );
	$base   = globalkeys_get_product_base_catalog_price( $product );
	if ( $base <= 0 || $member >= $base ) {
		return false;
	}
	return true;
}

/**
 * Aktueller Katalog-Basispreis (ein Stück), den Gäste zahlen – ohne unsere Mitglieder-Filter.
 *
 * @param WC_Product $product Produkt.
 * @return float
 */
function globalkeys_get_product_base_catalog_price( WC_Product $product ) {
	remove_filter( 'woocommerce_product_get_price', 'globalkeys_house_member_filter_product_price', 50 );
	remove_filter( 'woocommerce_product_variation_get_price', 'globalkeys_house_member_filter_product_price', 50 );

	$p = (float) wc_format_decimal( $product->get_price() );

	add_filter( 'woocommerce_product_get_price', 'globalkeys_house_member_filter_product_price', 50, 2 );
	add_filter( 'woocommerce_product_variation_get_price', 'globalkeys_house_member_filter_product_price', 50, 2 );

	return $p > 0 ? $p : 0.0;
}

/**
 * Preiskette für House-Rewards-Karten (ein Artikel: simple, external oder eine Variation).
 *
 * @param WC_Product $product Simple, External oder Variation.
 * @return array<int, array{strike: bool, html: string}>|null
 */
function globalkeys_get_house_rewards_price_ladder_segments_for_unit( WC_Product $product ) {
	if ( ! $product->is_type( 'simple' ) && ! $product->is_type( 'external' ) && ! $product->is_type( 'variation' ) ) {
		return null;
	}
	$m_raw = globalkeys_get_product_house_member_price_raw( $product );
	if ( $m_raw === null ) {
		return null;
	}
	$member = (float) wc_format_decimal( $m_raw );
	$public = globalkeys_get_product_base_catalog_price( $product );
	if ( $member <= 0 || $member >= $public ) {
		return null;
	}

	$reg_str = $product->get_regular_price();
	$reg     = ( $reg_str !== '' ) ? (float) wc_format_decimal( $reg_str ) : $public;
	if ( $reg <= 0 ) {
		$reg = $public;
	}

	$segments = array();

	if ( $reg > $member ) {
		$p_arg = ( $reg_str !== '' && (float) wc_format_decimal( $reg_str ) > 0 ) ? $reg_str : wc_format_decimal( $reg );
		$segments[] = array(
			'strike' => true,
			'html'   => wc_price( wc_get_price_to_display( $product, array( 'price' => $p_arg ) ) ),
		);
	}

	if ( $public > $member && $public < $reg ) {
		$segments[] = array(
			'strike' => true,
			'html'   => wc_price( wc_get_price_to_display( $product, array( 'price' => wc_format_decimal( $public ) ) ) ),
		);
	}

	$segments[] = array(
		'strike' => false,
		'html'   => wc_price( wc_get_price_to_display( $product, array( 'price' => wc_format_decimal( $member ) ) ) ),
	);

	$strikes = 0;
	foreach ( $segments as $s ) {
		if ( ! empty( $s['strike'] ) ) {
			$strikes++;
		}
	}
	if ( $strikes < 1 ) {
		return null;
	}

	return $segments;
}

/**
 * Referenz-Artikel für House-Rewards-Preise (simple/external = selbst, variable = günstigste Variation mit gültigem Mitgliedspreis).
 *
 * @param WC_Product $product Produkt.
 * @return WC_Product|null Simple, External oder Variation.
 */
function globalkeys_house_rewards_get_reference_unit_product( WC_Product $product ) {
	if ( $product->is_type( 'simple' ) || $product->is_type( 'external' ) || $product->is_type( 'variation' ) ) {
		return $product;
	}
	if ( ! $product->is_type( 'variable' ) ) {
		return null;
	}
	$best_var = null;
	$best_mem = null;
	foreach ( $product->get_children() as $vid ) {
		$v = wc_get_product( $vid );
		if ( ! $v || ! $v->is_type( 'variation' ) || ! $v->is_purchasable() ) {
			continue;
		}
		$m_raw = globalkeys_get_product_house_member_price_raw( $v );
		if ( $m_raw === null ) {
			continue;
		}
		$m = (float) wc_format_decimal( $m_raw );
		$pub = globalkeys_get_product_base_catalog_price( $v );
		if ( $m <= 0 || $m >= $pub ) {
			continue;
		}
		if ( null === $best_mem || $m < $best_mem ) {
			$best_mem = $m;
			$best_var = $v;
		}
	}
	return $best_var;
}

/**
 * Rabatt-Badge auf House-Rewards-Karten: immer vom regulären UVP zum House-Mitgliederpreis (nicht nur Woo-Sale).
 *
 * @param WC_Product $product Produkt.
 * @return int|null Prozent 1–100 oder null.
 */
function globalkeys_get_house_rewards_discount_pct_vs_regular( WC_Product $product ) {
	$unit = globalkeys_house_rewards_get_reference_unit_product( $product );
	if ( ! $unit ) {
		return null;
	}
	$m_raw = globalkeys_get_product_house_member_price_raw( $unit );
	if ( $m_raw === null ) {
		return null;
	}
	$member = (float) wc_format_decimal( $m_raw );
	$reg_str = $unit->get_regular_price();
	$reg     = ( $reg_str !== '' ) ? (float) wc_format_decimal( $reg_str ) : 0.0;
	if ( $reg <= 0 ) {
		/* Basis-Katalogpreis (Gast), nicht get_price() – sonst Mitgliedspreis, wenn Filter aktiv. */
		$reg = globalkeys_get_product_base_catalog_price( $unit );
	}
	if ( $reg <= 0 || $member <= 0 || $member >= $reg ) {
		return null;
	}
	$pct = (int) round( ( 1 - $member / $reg ) * 100 );
	if ( $pct < 1 ) {
		return null;
	}
	$pct = min( 100, $pct );
	return (int) apply_filters( 'globalkeys_house_rewards_discount_pct_vs_regular', $pct, $product, $unit, $reg, $member );
}

/**
 * Preiskette für House Rewards (variables Produkt: günstigste Variation mit gültigem Mitgliedspreis).
 *
 * @param WC_Product $product Produkt.
 * @return array<int, array{strike: bool, html: string}>|null
 */
function globalkeys_get_house_rewards_price_ladder_segments( WC_Product $product ) {
	$unit = globalkeys_house_rewards_get_reference_unit_product( $product );
	if ( ! $unit ) {
		return null;
	}
	$segments = globalkeys_get_house_rewards_price_ladder_segments_for_unit( $unit );
	if ( empty( $segments ) ) {
		return null;
	}

	return apply_filters( 'globalkeys_house_rewards_price_ladder_segments', $segments, $product );
}

/**
 * Markup: UVP (durchgestrichen), Aktionspreis (durchgestrichen), Mitgliedspreis (weiß) – nur Abstand, keine Pfeile.
 *
 * @param WC_Product $product Produkt.
 * @return string HTML oder leer.
 */
function globalkeys_get_house_rewards_price_ladder_html( WC_Product $product ) {
	$segments = globalkeys_get_house_rewards_price_ladder_segments( $product );
	if ( empty( $segments ) || count( $segments ) < 2 ) {
		return '';
	}
	$was_htmls = array();
	$final_html = '';
	foreach ( $segments as $seg ) {
		if ( ! empty( $seg['strike'] ) ) {
			$was_htmls[] = '<span class="gk-house-rewards-price-ladder__step gk-house-rewards-price-ladder__step--was"><del>' . wp_kses_post( $seg['html'] ) . '</del></span>';
		} else {
			$final_html = '<span class="gk-house-rewards-price-ladder__step gk-house-rewards-price-ladder__step--final">' . wp_kses_post( $seg['html'] ) . '</span>';
		}
	}
	$html = '<span class="gk-house-rewards-price-ladder">';
	$html .= '<span class="gk-house-rewards-price-ladder__was-group">' . implode( '', $was_htmls ) . '</span>';
	$html .= $final_html;
	$html .= '</span>';
	return apply_filters( 'globalkeys_house_rewards_price_ladder_html', $html, $product, $segments );
}

/**
 * Ob die dreistufige House-Rewards-Preisleiter für das Produkt angezeigt werden kann.
 *
 * @param WC_Product $product Produkt.
 * @return bool
 */
function globalkeys_product_has_house_rewards_price_ladder( WC_Product $product ) {
	return globalkeys_get_house_rewards_price_ladder_html( $product ) !== '';
}

/**
 * Ob der Nutzer den House-Mitgliederpreis erhält.
 *
 * @param int|null $user_id 0 = aktueller User.
 * @return bool
 */
function globalkeys_user_has_house_member_access( $user_id = null ) {
	if ( null === $user_id ) {
		$user_id = get_current_user_id();
	}
	$user_id = (int) $user_id;
	if ( $user_id < 1 ) {
		return (bool) apply_filters( 'globalkeys_user_has_house_member_access', false, $user_id );
	}

	if ( user_can( $user_id, 'manage_woocommerce' ) || user_can( $user_id, 'manage_options' ) ) {
		return (bool) apply_filters( 'globalkeys_user_has_house_member_access', true, $user_id );
	}

	$allowed = false;

	if ( get_user_meta( $user_id, 'gk_premium_member', true ) === '1' ) {
		$allowed = true;
	}

	if ( ! $allowed ) {
		$user  = get_userdata( $user_id );
		$roles = $user && ! empty( $user->roles ) ? (array) $user->roles : array();
		$extra = apply_filters( 'globalkeys_house_member_roles', array() );
		if ( is_array( $extra ) && $extra ) {
			foreach ( $extra as $role ) {
				if ( $role && in_array( (string) $role, $roles, true ) ) {
					$allowed = true;
					break;
				}
			}
		}
	}

	$unlock_ids = globalkeys_house_member_unlock_product_ids();
	if ( ! $allowed && ! empty( $unlock_ids ) ) {
		foreach ( $unlock_ids as $pid ) {
			if ( globalkeys_user_unlocked_by_product( $user_id, (int) $pid ) ) {
				$allowed = true;
				break;
			}
		}
	}

	return (bool) apply_filters( 'globalkeys_user_has_house_member_access', $allowed, $user_id );
}

/**
 * Ob die Premium-CTA-Leiste unter „House Rewards“ gerendert werden soll.
 *
 * Shop-Admins sehen die Leiste immer (Vorschau). Echte Premium-Kunden (ohne Admin-Shortcut) nicht.
 *
 * @return bool
 */
function globalkeys_show_premium_member_cta_bar() {
	if ( ! is_user_logged_in() ) {
		return true;
	}
	$user_id = get_current_user_id();
	if ( $user_id < 1 ) {
		return true;
	}
	/* globalkeys_user_has_house_member_access() ist für Admins immer true (Preistest) – CTA trotzdem anzeigen. */
	if ( user_can( $user_id, 'manage_woocommerce' ) || user_can( $user_id, 'manage_options' ) ) {
		return true;
	}
	return ! globalkeys_user_has_house_member_access( $user_id );
}

/**
 * Premium-Badge (Check im Schild) wie Pictures/premium-badge-gk.svg, für currentColor-Färbung z. B. rot.
 *
 * @return string SVG (unescaped für Echo mit phpcs:ignore).
 */
function globalkeys_premium_badge_icon_svg() {
	return '<svg class="gk-premium-member-cta-icon__svg gk-premium-badge-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.5924 3.20027C9.34888 3.4078 9.22711 3.51158 9.09706 3.59874C8.79896 3.79854 8.46417 3.93721 8.1121 4.00672C7.95851 4.03705 7.79903 4.04977 7.48008 4.07522C6.6787 4.13918 6.278 4.17115 5.94371 4.28923C5.17051 4.56233 4.56233 5.17051 4.28923 5.94371C4.17115 6.278 4.13918 6.6787 4.07522 7.48008C4.04977 7.79903 4.03705 7.95851 4.00672 8.1121C3.93721 8.46417 3.79854 8.79896 3.59874 9.09706C3.51158 9.22711 3.40781 9.34887 3.20027 9.5924C2.67883 10.2043 2.4181 10.5102 2.26522 10.8301C1.91159 11.57 1.91159 12.43 2.26522 13.1699C2.41811 13.4898 2.67883 13.7957 3.20027 14.4076C3.40778 14.6511 3.51158 14.7729 3.59874 14.9029C3.79854 15.201 3.93721 15.5358 4.00672 15.8879C4.03705 16.0415 4.04977 16.201 4.07522 16.5199C4.13918 17.3213 4.17115 17.722 4.28923 18.0563C4.56233 18.8295 5.17051 19.4377 5.94371 19.7108C6.278 19.8288 6.6787 19.8608 7.48008 19.9248C7.79903 19.9502 7.95851 19.963 8.1121 19.9933C8.46417 20.0628 8.79896 20.2015 9.09706 20.4013C9.22711 20.4884 9.34887 20.5922 9.5924 20.7997C10.2043 21.3212 10.5102 21.5819 10.8301 21.7348C11.57 22.0884 12.43 22.0884 13.1699 21.7348C13.4898 21.5819 13.7957 21.3212 14.4076 20.7997C14.6511 20.5922 14.7729 20.4884 14.9029 20.4013C15.201 20.2015 15.5358 20.0628 15.8879 19.9933C16.0415 19.963 16.201 19.9502 16.5199 19.9248C17.3213 19.8608 17.722 19.8288 18.0563 19.7108C18.8295 19.4377 19.4377 18.8295 19.7108 18.0563C19.8288 17.722 19.8608 17.3213 19.9248 16.5199C19.9502 16.201 19.963 16.0415 19.9933 15.8879C20.0628 15.5358 20.2015 15.201 20.4013 14.9029C20.4884 14.7729 20.5922 14.6511 20.7997 14.4076C21.3212 13.7957 21.5819 13.4898 21.7348 13.1699C22.0884 12.43 22.0884 11.57 21.7348 10.8301C21.5819 10.5102 21.3212 10.2043 20.7997 9.5924C20.5922 9.34887 20.4884 9.22711 20.4013 9.09706C20.2015 8.79896 20.0628 8.46417 19.9933 8.1121C19.963 7.95851 19.9502 7.79903 19.9248 7.48008C19.8608 6.6787 19.8288 6.278 19.7108 5.94371C19.4377 5.17051 18.8295 4.56233 18.0563 4.28923C17.722 4.17115 17.3213 4.13918 16.5199 4.07522C16.201 4.04977 16.0415 4.03705 15.8879 4.00672C15.5358 3.93721 15.201 3.79854 14.9029 3.59874C14.7729 3.51158 14.6511 3.40781 14.4076 3.20027C13.7957 2.67883 13.4898 2.41811 13.1699 2.26522C12.43 1.91159 11.57 1.91159 10.8301 2.26522C10.5102 2.4181 10.2043 2.67883 9.5924 3.20027ZM16.3735 9.86314C16.6913 9.5453 16.6913 9.03 16.3735 8.71216C16.0557 8.39433 15.5403 8.39433 15.2225 8.71216L10.3723 13.5624L8.77746 11.9676C8.45963 11.6498 7.94432 11.6498 7.62649 11.9676C7.30866 12.2854 7.30866 12.8007 7.62649 13.1186L9.79678 15.2889C10.1146 15.6067 10.6299 15.6067 10.9478 15.2889L16.3735 9.86314Z" fill="currentColor"/></svg>';
}

/**
 * SVG-Icon für die Premium-CTA-Leiste (currentColor).
 *
 * @param string $name coins|percent|bolt|gift|verify|crown.
 * @return string SVG (unescaped für Echo mit phpcs:ignore).
 */
function globalkeys_premium_member_cta_icon_svg( $name ) {
	$s = (string) $name;
	switch ( $s ) {
		case 'coins':
			return '<svg class="gk-premium-member-cta-icon__svg" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><ellipse cx="12" cy="6" rx="8" ry="3"/><path d="M4 6v6c0 1.7 3.6 3 8 3s8-1.3 8-3V6"/><path d="M4 12v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"/></svg>';
		case 'percent':
			return '<svg class="gk-premium-member-cta-icon__svg" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="7.5" cy="7.5" r="2"/><circle cx="16.5" cy="16.5" r="2"/><path d="m5 19 14-14"/></svg>';
		case 'bolt':
			return '<svg class="gk-premium-member-cta-icon__svg" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>';
		case 'gift':
			return '<svg class="gk-premium-member-cta-icon__svg" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="8" width="18" height="4" rx="1"/><path d="M12 8v13"/><path d="M3 12v9a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1v-9"/><path d="M12 8h0a4 4 0 0 0 4-4c0-2-1.5-3-3-3a3 3 0 0 0-3 3 3 3 0 0 0-3-3c-1.5 0-3 1-3 3a4 4 0 0 0 4 4h0"/></svg>';
		case 'verify':
			return function_exists( 'globalkeys_premium_badge_icon_svg' ) ? globalkeys_premium_badge_icon_svg() : '';
		case 'crown':
		default:
			return '<svg class="gk-premium-member-cta-icon__svg" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m2 4 3 12h14l3-12-6 7-4-7-4 7-6-7zM3.5 20h17a1 1 0 0 1 0 2h-17a1 1 0 0 1 0-2z"/></svg>';
	}
}

/**
 * SVG-Icons für FAQ-Kategorie-Kacheln (Questions & Answers, currentColor).
 *
 * @param string $name shop|payment|trust|support.
 * @return string SVG (unescaped für Echo mit phpcs:ignore).
 */
function globalkeys_faq_category_icon_svg( $name ) {
	$s = (string) $name;
	switch ( $s ) {
		case 'payment':
			return '<svg class="gk-faq-category-icon__svg" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>';
		case 'trust':
			return '<svg class="gk-faq-category-icon__svg" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>';
		case 'support':
			return '<svg class="gk-faq-category-icon__svg" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>';
		case 'shop':
		default:
			return '<svg class="gk-faq-category-icon__svg" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>';
	}
}

/**
 * @param int $user_id WP-User-ID.
 * @param int $product_id Woo-Produkt-ID.
 * @return bool
 */
function globalkeys_user_unlocked_by_product( $user_id, $product_id ) {
	$user_id    = (int) $user_id;
	$product_id = (int) $product_id;
	if ( $user_id < 1 || $product_id < 1 ) {
		return false;
	}

	if ( function_exists( 'wcs_user_has_subscription' ) && wcs_user_has_subscription( $user_id, $product_id, 'active' ) ) {
		return true;
	}

	if ( function_exists( 'wc_customer_bought_product' ) && wc_customer_bought_product( '', $user_id, $product_id ) ) {
		return true;
	}

	return (bool) apply_filters( 'globalkeys_user_unlocked_by_product', false, $user_id, $product_id );
}

/**
 * Nur während House-Rewards-Karten: Mitgliederpreis in get_price / Teaser-HTML.
 *
 * @return void
 */
function globalkeys_house_rewards_pricing_context_enter() {
	$GLOBALS['gk_house_rewards_pricing_depth'] = (int) ( $GLOBALS['gk_house_rewards_pricing_depth'] ?? 0 ) + 1;
}

/**
 * @return void
 */
function globalkeys_house_rewards_pricing_context_leave() {
	$d = (int) ( $GLOBALS['gk_house_rewards_pricing_depth'] ?? 0 );
	$GLOBALS['gk_house_rewards_pricing_depth'] = max( 0, $d - 1 );
}

/**
 * @return bool
 */
function globalkeys_is_house_rewards_pricing_context() {
	return ! empty( $GLOBALS['gk_house_rewards_pricing_depth'] );
}

/**
 * WooCommerce: Tiefe während calculate_totals (Warenkorb, AJAX, u. a.).
 *
 * @return void
 */
function globalkeys_house_member_before_calculate_totals() {
	$GLOBALS['gk_wc_calculate_totals_depth'] = (int) ( $GLOBALS['gk_wc_calculate_totals_depth'] ?? 0 ) + 1;
}

/**
 * @return void
 */
function globalkeys_house_member_after_calculate_totals() {
	$d = (int) ( $GLOBALS['gk_wc_calculate_totals_depth'] ?? 0 );
	$GLOBALS['gk_wc_calculate_totals_depth'] = max( 0, $d - 1 );
}

add_action( 'woocommerce_before_calculate_totals', 'globalkeys_house_member_before_calculate_totals', 1 );
add_action( 'woocommerce_after_calculate_totals', 'globalkeys_house_member_after_calculate_totals', 9999 );

/**
 * Wo der Mitgliederpreis-Filter greifen soll: House Rewards, Produktseite, Warenkorb/Kasse, Totals, ggf. Woo-AJAX.
 *
 * @return bool
 */
function globalkeys_house_member_price_filters_applicable_context() {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return false;
	}

	if ( globalkeys_is_house_rewards_pricing_context() ) {
		return true;
	}

	if ( function_exists( 'is_cart' ) && is_cart() ) {
		return true;
	}
	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		return true;
	}
	if ( function_exists( 'is_product' ) && is_product() ) {
		return true;
	}

	if ( ! empty( $GLOBALS['gk_wc_calculate_totals_depth'] ) ) {
		return true;
	}

	/* Hinzufügen/Entfernen per AJAX: Preis wird vor/neben calculate_totals gelesen. */
	if ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() && isset( $_REQUEST['action'] ) ) {
		$action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) );
		if ( in_array( $action, array( 'woocommerce_add_to_cart', 'woocommerce_remove_from_cart' ), true ) ) {
			return true;
		}
	}
	if ( isset( $_REQUEST['wc-ajax'] ) ) {
		$wc_ajax = sanitize_text_field( wp_unslash( $_REQUEST['wc-ajax'] ) );
		if ( in_array( $wc_ajax, array( 'add_to_cart', 'remove_from_cart' ), true ) ) {
			return true;
		}
	}

	return (bool) apply_filters( 'globalkeys_house_member_price_filters_applicable_context', false );
}

/**
 * WooCommerce: Mitglieder zahlen den niedrigeren Meta-Preis, wenn gesetzt.
 *
 * @param string      $price   Preis.
 * @param WC_Product  $product Produkt.
 * @return string
 */
function globalkeys_house_member_filter_product_price( $price, $product ) {
	if ( ! globalkeys_house_member_price_filters_applicable_context() ) {
		return $price;
	}
	if ( ! globalkeys_user_has_house_member_access() ) {
		return $price;
	}
	if ( ! is_a( $product, 'WC_Product' ) ) {
		return $price;
	}
	/* Eltern-Produkt „variable“: Preis je Variation, nicht hier überschreiben. */
	if ( $product->is_type( 'variable' ) ) {
		return $price;
	}
	$raw = globalkeys_get_product_house_member_price_raw( $product );
	if ( $raw === null ) {
		return $price;
	}
	$member = (float) wc_format_decimal( $raw );
	$current = (float) wc_format_decimal( $price );
	if ( $member <= 0 || $member >= $current ) {
		return $price;
	}
	return wc_format_decimal( $member );
}
add_filter( 'woocommerce_product_get_price', 'globalkeys_house_member_filter_product_price', 50, 2 );
add_filter( 'woocommerce_product_variation_get_price', 'globalkeys_house_member_filter_product_price', 50, 2 );

/**
 * Variationspreis-Cache: unterscheidet Gast vs. Mitglied.
 *
 * @param array      $hash    Hash-Bestandteile.
 * @param WC_Product $product Produkt.
 * @return array
 */
function globalkeys_house_member_variation_prices_hash( $hash, $product, $for_display = false ) {
	if ( ! is_array( $hash ) ) {
		$hash = array();
	}
	$member_branch = globalkeys_user_has_house_member_access() && globalkeys_house_member_price_filters_applicable_context();
	$hash[]        = 'gk_hm_' . ( $member_branch ? '1' : '0' );
	return $hash;
}
add_filter( 'woocommerce_get_variation_prices_hash', 'globalkeys_house_member_variation_prices_hash', 50, 3 );

/**
 * Preis-HTML: für Nicht-Mitglieder Hinweis auf Mitgliedspreis.
 *
 * @param string     $html    HTML.
 * @param WC_Product $product Produkt.
 * @return string
 */
function globalkeys_house_member_price_html( $html, $product ) {
	if ( globalkeys_user_has_house_member_access() ) {
		return $html;
	}
	/* Teaser „House-Mitglieder: …“ nur auf House-Rewards-Karten, nicht in Featured/Bestseller/Shop-Loops. */
	if ( ! globalkeys_is_house_rewards_pricing_context() ) {
		return $html;
	}
	if ( ! is_a( $product, 'WC_Product' ) || ! globalkeys_product_has_house_member_deal( $product ) ) {
		return $html;
	}
	$raw = globalkeys_get_product_house_member_price_raw( $product );
	if ( $raw === null ) {
		return $html;
	}
	$url = globalkeys_house_member_cta_url();
	/* translators: %s: formatted member price */
	$line = sprintf( __( 'House-Mitglieder: %s', 'globalkeys' ), wp_kses_post( wc_price( $raw ) ) );
	$more = sprintf(
		/* translators: %s: URL to membership */
		'<a href="%s" class="gk-house-member-price-link">%s</a>',
		esc_url( $url ),
		esc_html__( 'Mehr erfahren', 'globalkeys' )
	);
	return $html . '<div class="gk-house-member-price-teaser"><span class="gk-house-member-price-teaser__price">' . $line . '</span> ' . $more . '</div>';
}
add_filter( 'woocommerce_get_price_html', 'globalkeys_house_member_price_html', 20, 2 );

/**
 * Produktseite: Hinweis auf extra Rabatt für Mitglieder.
 */
function globalkeys_house_member_single_product_notice() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	$product = wc_get_product( get_the_ID() );
	if ( ! $product || ! globalkeys_product_has_house_member_deal( $product ) ) {
		return;
	}
	if ( globalkeys_user_has_house_member_access() ) {
		echo '<div class="woocommerce-message gk-house-member-product-notice" role="status">';
		if ( $product->is_type( 'variable' ) ) {
			esc_html_e( 'Als House-Mitglied gilt für passende Varianten der günstigere Mitgliederpreis (siehe gewählte Variation).', 'globalkeys' );
		} else {
			$raw = globalkeys_get_product_house_member_price_raw( $product );
			if ( $raw !== null ) {
				printf(
					/* translators: %s: member price */
					esc_html__( 'Als House-Mitglied zahlst du %s.', 'globalkeys' ),
					wp_kses_post( wc_price( $raw ) )
				);
			} else {
				esc_html_e( 'House-Mitgliederpreis ist für dieses Produkt aktiv.', 'globalkeys' );
			}
		}
		echo '</div>';
		return;
	}
	$url = globalkeys_house_member_cta_url();
	$raw = globalkeys_get_product_house_member_price_raw( $product );
	echo '<div class="woocommerce-info gk-house-member-product-notice" role="status">';
	echo wp_kses_post(
		sprintf(
			/* translators: 1: member price, 2: URL to membership */
			__( 'Noch günstiger mit House: Mitglieder zahlen %1$s. <a href="%2$s">Zur Mitgliedschaft</a>', 'globalkeys' ),
			wp_kses_post( wc_price( $raw ) ),
			esc_url( $url )
		)
	);
	echo '</div>';
}
add_action( 'woocommerce_before_single_product_summary', 'globalkeys_house_member_single_product_notice', 5 );

/* --- WooCommerce Admin: Mitgliedspreis --- */

/**
 * Einfaches / externes Produkt.
 */
function globalkeys_house_member_product_options_pricing() {
	woocommerce_wp_text_input(
		array(
			'id'            => GLOBALKEYS_HOUSE_MEMBER_PRICE_META,
			'value'         => get_post_meta( get_the_ID(), GLOBALKEYS_HOUSE_MEMBER_PRICE_META, true ),
			'label'         => __( 'House-Mitgliederpreis', 'globalkeys' ) . ' (' . get_woocommerce_currency_symbol() . ')',
			'description'   => __( 'Optional: niedriger als der aktuelle Verkaufspreis. Nur für Nutzer mit House-/Abo-Zugang. Alle anderen kaufen zum normalen Preis.', 'globalkeys' ),
			'data_type'     => 'price',
			'desc_tip'      => true,
			'wrapper_class' => 'show_if_simple show_if_external',
		)
	);
}
add_action( 'woocommerce_product_options_pricing', 'globalkeys_house_member_product_options_pricing', 15 );

/**
 * @param WC_Product $product Produkt.
 */
function globalkeys_house_member_save_product( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_product', $product->get_id() ) ) {
		return;
	}
	if ( ! $product->is_type( 'simple' ) && ! $product->is_type( 'external' ) ) {
		return;
	}
	if ( ! isset( $_POST[ GLOBALKEYS_HOUSE_MEMBER_PRICE_META ] ) ) {
		return;
	}
	$val = wc_clean( wp_unslash( $_POST[ GLOBALKEYS_HOUSE_MEMBER_PRICE_META ] ) );
	if ( $val === '' ) {
		$product->delete_meta_data( GLOBALKEYS_HOUSE_MEMBER_PRICE_META );
	} else {
		$product->update_meta_data( GLOBALKEYS_HOUSE_MEMBER_PRICE_META, wc_format_decimal( $val ) );
	}
}
add_action( 'woocommerce_admin_process_product_object', 'globalkeys_house_member_save_product', 15, 1 );

/**
 * Variation: Mitgliedspreis.
 *
 * @param int     $loop           Schleifenindex.
 * @param array   $variation_data Daten (unused).
 * @param WP_Post $variation      Variation Post.
 */
function globalkeys_house_member_variation_options_pricing( $loop, $variation_data, $variation ) {
	$vid = $variation->ID;
	woocommerce_wp_text_input(
		array(
			'id'            => 'gk_house_member_price_var_' . $loop,
			'name'          => 'variable_gk_house_member_price[' . $loop . ']',
			'value'         => get_post_meta( $vid, GLOBALKEYS_HOUSE_MEMBER_PRICE_META, true ),
			'label'         => __( 'House-Mitgliederpreis', 'globalkeys' ) . ' (' . get_woocommerce_currency_symbol() . ')',
			'description'   => __( 'Optional, nur wenn niedriger als Variations-Verkaufspreis.', 'globalkeys' ),
			'data_type'     => 'price',
			'wrapper_class' => 'form-row form-row-full',
		)
	);
}
add_action( 'woocommerce_variation_options_pricing', 'globalkeys_house_member_variation_options_pricing', 15, 3 );

/**
 * @param int $variation_id Variations-ID.
 * @param int $i            Index.
 */
function globalkeys_house_member_save_variation( $variation_id, $i ) {
	if ( ! current_user_can( 'edit_product', $variation_id ) ) {
		return;
	}
	if ( ! isset( $_POST['variable_gk_house_member_price'][ $i ] ) ) {
		return;
	}
	$val = wc_clean( wp_unslash( $_POST['variable_gk_house_member_price'][ $i ] ) );
	if ( $val === '' ) {
		delete_post_meta( $variation_id, GLOBALKEYS_HOUSE_MEMBER_PRICE_META );
	} else {
		update_post_meta( $variation_id, GLOBALKEYS_HOUSE_MEMBER_PRICE_META, wc_format_decimal( $val ) );
	}
}
add_action( 'woocommerce_save_product_variation', 'globalkeys_house_member_save_variation', 15, 2 );

/* --- Profilfeld: Premium / House --- */

/**
 * @param WP_User $user User.
 */
function globalkeys_house_member_user_profile_field( $user ) {
	if ( ! current_user_can( 'edit_users' ) ) {
		return;
	}
	$val = get_user_meta( $user->ID, 'gk_premium_member', true ) === '1';
	wp_nonce_field( 'gk_premium_member_save', 'gk_premium_member_nonce' );
	?>
	<h2><?php esc_html_e( 'GlobalKeys: House / Premium', 'globalkeys' ); ?></h2>
	<table class="form-table" role="presentation">
		<tr>
			<th><label for="gk_premium_member"><?php esc_html_e( 'Premium / House-Zugang', 'globalkeys' ); ?></label></th>
			<td>
				<label>
					<input type="checkbox" name="gk_premium_member" id="gk_premium_member" value="1" <?php checked( $val ); ?> />
					<?php esc_html_e( 'Nutzer erhält House-Mitgliederpreise auf Produkten mit hinterlegtem Mitgliedspreis (unabhängig von Abo).', 'globalkeys' ); ?>
				</label>
			</td>
		</tr>
	</table>
	<?php
}
add_action( 'show_user_profile', 'globalkeys_house_member_user_profile_field' );
add_action( 'edit_user_profile', 'globalkeys_house_member_user_profile_field' );

/**
 * @param int $user_id User-ID.
 */
function globalkeys_house_member_save_user_profile_field( $user_id ) {
	if ( ! current_user_can( 'edit_users' ) ) {
		return;
	}
	if ( ! isset( $_POST['gk_premium_member_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gk_premium_member_nonce'] ) ), 'gk_premium_member_save' ) ) {
		return;
	}
	$on = ! empty( $_POST['gk_premium_member'] );
	update_user_meta( $user_id, 'gk_premium_member', $on ? '1' : '0' );
}
add_action( 'personal_options_update', 'globalkeys_house_member_save_user_profile_field' );
add_action( 'edit_user_profile_update', 'globalkeys_house_member_save_user_profile_field' );
