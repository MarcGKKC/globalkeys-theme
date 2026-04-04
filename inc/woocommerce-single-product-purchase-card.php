<?php
/**
 * Produkthero: Kaufkarte im Referenz-Layout (Status-Leiste, Social Proof, Preiszeile, Aktionen).
 *
 * @package globalkeys
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'globalkeys_get_about_game_sidebar_standard_info_definitions' ) ) {
	$gk_about_sidebar_file = get_template_directory() . '/inc/woocommerce-product-about-sidebar-info.php';
	if ( is_readable( $gk_about_sidebar_file ) ) {
		require_once $gk_about_sidebar_file;
	}
}

/**
 * Bei fehlenden kaufbaren Variationen: select deaktivieren, bleibt aber sichtbar (Info).
 *
 * @param string $html Dropdown-Markup.
 * @return string
 */
function globalkeys_variation_dropdown_select_disabled_html( $html ) {
	return str_replace( '<select ', '<select disabled aria-disabled="true" ', $html, 1 );
}

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

	$chunks = array();

	if ( $icon_url && $platform ) {
		$plabel = isset( $labels[ $platform ] ) ? $labels[ $platform ] : ucfirst( (string) $platform );
		ob_start();
		echo '<span class="gk-purchase-card__status-item gk-purchase-card__status-item--platform">';
		echo '<img class="gk-purchase-card__status-platform-icon" src="' . esc_url( $icon_url ) . '" width="22" height="22" alt="" decoding="async" loading="lazy" /> ';
		echo '<span class="gk-purchase-card__status-label">' . esc_html( $plabel ) . '</span>';
		echo '</span>';
		$chunks[] = ob_get_clean();
	}

	ob_start();
	if ( $product->is_in_stock() ) {
		echo '<span class="gk-purchase-card__status-item gk-purchase-card__status-item--ok">';
		echo '<span class="gk-purchase-card__check" aria-hidden="true"></span>';
		echo esc_html__( 'In Stock', 'globalkeys' );
		echo '</span>';
	} else {
		echo '<span class="gk-purchase-card__status-item gk-purchase-card__status-item--bad">';
		echo esc_html__( 'Out of stock', 'globalkeys' );
		echo '</span>';
	}
	$chunks[] = ob_get_clean();

	$show_digital = (bool) apply_filters( 'gk_purchase_card_show_digital_download_status', true, $product );
	if ( $show_digital ) {
		ob_start();
		echo '<span class="gk-purchase-card__status-item gk-purchase-card__status-item--ok">';
		echo '<span class="gk-purchase-card__check" aria-hidden="true"></span>';
		echo esc_html__( 'Digital Download', 'globalkeys' );
		echo '</span>';
		$chunks[] = ob_get_clean();
	}

	$chunks = array_values( array_filter( $chunks ) );
	if ( empty( $chunks ) ) {
		return;
	}

	echo '<div class="gk-purchase-card__status" role="group" aria-label="' . esc_attr__( 'Product status', 'globalkeys' ) . '">';
	foreach ( $chunks as $i => $html ) {
		if ( $i > 0 ) {
			echo '<span class="gk-purchase-card__status-divider" aria-hidden="true"></span>';
		}
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- gebaute Status-Fragmente, Inhalt bereits escaped.
		echo $html;
	}
	echo '</div>';
}

/**
 * Einfaches Produkt: zwei „Dropdown“-Felder (nur Anzeige), da WooCommerce hier keine Variationen rendert.
 * Wird im CTA-Cluster direkt über der Preiszeile ausgegeben (kein Leerraum durch flex).
 */
function globalkeys_single_product_purchase_simple_selectors_row() {
	if ( ! globalkeys_single_product_is_purchase_card_active() ) {
		return;
	}
	global $product;
	if ( ! $product || ! $product->is_type( 'simple' ) ) {
		return;
	}

	$labels = array(
		'playstation' => 'PlayStation',
		'xbox'        => 'Xbox',
		'nintendo'    => 'Nintendo',
		'steam'       => 'Steam',
	);

	$platform_key = function_exists( 'globalkeys_get_product_platform_key' ) ? globalkeys_get_product_platform_key( $product ) : null;
	$platform_display = ( $platform_key && isset( $labels[ $platform_key ] ) )
		? $labels[ $platform_key ]
		: ( $platform_key ? ucfirst( (string) $platform_key ) : __( 'PC', 'globalkeys' ) );

	$platform_display = apply_filters( 'gk_purchase_card_simple_platform_display', $platform_display, $product );
	$edition_display  = apply_filters( 'gk_purchase_card_simple_edition_label', __( 'Standard Edition', 'globalkeys' ), $product );

	$rows = apply_filters(
		'gk_purchase_card_simple_selector_rows',
		array(
			array(
				'label' => __( 'Plattform', 'globalkeys' ),
				'value' => $platform_display,
			),
			array(
				'label' => __( 'Edition', 'globalkeys' ),
				'value' => $edition_display,
			),
		),
		$product
	);

	if ( empty( $rows ) || ! is_array( $rows ) ) {
		return;
	}

	echo '<div class="gk-purchase-card__pseudo-variations-wrap">';
	echo '<table class="variations variations--pseudo gk-variations-appearance" cellspacing="0" role="presentation"><tbody>';

	foreach ( $rows as $row ) {
		$lab = isset( $row['label'] ) ? (string) $row['label'] : '';
		$val = isset( $row['value'] ) ? (string) $row['value'] : '';
		if ( $lab === '' || $val === '' ) {
			continue;
		}
		$id = 'gk-pseudo-' . sanitize_title( $lab . '-' . $product->get_id() );
		echo '<tr>';
		echo '<th class="label"><label for="' . esc_attr( $id ) . '">' . esc_html( $lab ) . '</label></th>';
		echo '<td class="value">';
		echo '<select id="' . esc_attr( $id ) . '" class="gk-pseudo-variation-select" disabled aria-disabled="true" tabindex="-1" aria-label="' . esc_attr( $lab . ': ' . $val ) . '">';
		echo '<option selected>' . esc_html( $val ) . '</option>';
		echo '</select>';
		echo '</td>';
		echo '</tr>';
	}

	echo '</tbody></table></div>';
}

/**
 * Preisblock (einfaches Produkt): Rabatt + Woo-Preis.
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
	global $product;
	$favorites_url = function_exists( 'globalkeys_get_wishlist_url' ) ? globalkeys_get_wishlist_url() : home_url( '/wishlist/' );
	$wish_pid      = ( $product && is_a( $product, 'WC_Product' ) ) ? (int) $product->get_id() : 0;
	$in_wishlist   = false;
	if ( $wish_pid && is_user_logged_in() && function_exists( 'globalkeys_wishlist_user_has_product' ) ) {
		$in_wishlist = globalkeys_wishlist_user_has_product( get_current_user_id(), $wish_pid );
	}
	$wl_href = $favorites_url;
	if ( $wish_pid > 0 && is_user_logged_in() ) {
		$wl_href = add_query_arg(
			array(
				'gk_wl_add'    => $wish_pid,
				'gk_wl_nonce' => wp_create_nonce( 'gk_wl_add_' . $wish_pid ),
			),
			$favorites_url
		);
	}
	echo '<div class="gk-purchase-card__actions">';
	echo '<a class="gk-purchase-card__wishlist" href="' . esc_url( $wl_href ) . '" data-product-id="' . esc_attr( (string) $wish_pid ) . '" aria-pressed="' . ( $in_wishlist ? 'true' : 'false' ) . '" aria-label="' . esc_attr__( 'Favoriten', 'globalkeys' ) . '">';
	echo '<span class="gk-purchase-card__wishlist-icon" aria-hidden="true"></span>';
	echo '</a>';
	echo '<div class="gk-purchase-card__actions-primary">';
}

/**
 * @param string $value Roher Wert aus „Bezeichnung | Wert“.
 * @return array{0:string,1:bool} Wert ohne Präfix, muted-Flag.
 */
function globalkeys_about_game_sidebar_parse_value_modifiers( $value ) {
	$value = is_string( $value ) ? trim( $value ) : '';
	$muted = false;
	if ( preg_match( '/^\[\[muted\]\]\s*/i', $value ) ) {
		$muted = true;
		$value = trim( preg_replace( '/^\[\[muted\]\]\s*/i', '', $value ) );
	}
	return array( $value, $muted );
}

/**
 * Infobox-Bezeichnung mit abschließendem „:“ (ohne doppelten Doppelpunkt).
 *
 * @param string $label Rohe Bezeichnung.
 * @return string
 */
function globalkeys_about_game_sidebar_label_with_colon( $label ) {
	$label = is_string( $label ) ? trim( $label ) : '';
	if ( $label === '' ) {
		return '';
	}
	$last = substr( $label, -1 );
	if ( ':' === $last ) {
		return $label;
	}
	return $label . ':';
}

/**
 * @param string $raw Textarea-Inhalt (eine Zeile: Bezeichnung | Wert).
 * @return array<int, array{label:string,value:string,muted:bool}>
 */
function globalkeys_parse_about_game_sidebar_rows( $raw ) {
	if ( ! is_string( $raw ) || trim( $raw ) === '' ) {
		return array();
	}
	$lines = preg_split( '/\r\n|\r|\n/', $raw );
	$out   = array();
	foreach ( $lines as $line ) {
		$line = trim( $line );
		if ( $line === '' ) {
			continue;
		}
		$pos = strpos( $line, '|' );
		if ( $pos === false ) {
			continue;
		}
		$label = trim( substr( $line, 0, $pos ) );
		$value = trim( substr( $line, $pos + 1 ) );
		if ( $label === '' ) {
			continue;
		}
		list( $value, $muted ) = globalkeys_about_game_sidebar_parse_value_modifiers( $value );
		if ( $value === '' ) {
			continue;
		}
		$out[] = array(
			'label' => $label,
			'value' => $value,
			'muted' => $muted,
		);
	}
	return $out;
}

/**
 * @param WC_Product|null $product Produkt.
 * @return bool Ob Infobox-Inhalt vorliegt.
 */
function globalkeys_product_about_game_sidebar_has_content( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		$pid = function_exists( 'get_queried_object_id' ) ? (int) get_queried_object_id() : 0;
		if ( ! $pid && function_exists( 'get_the_ID' ) ) {
			$pid = (int) get_the_ID();
		}
		if ( $pid && function_exists( 'wc_get_product' ) ) {
			$product = wc_get_product( $pid );
		}
	}
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return false;
	}
	/**
	 * Infobox komplett abschalten (selten).
	 *
	 * @param bool       $enable  Standard true.
	 * @param WC_Product $product Produkt.
	 */
	return (bool) apply_filters( 'gk_about_game_sidebar_enable', true, $product );
}

/**
 * Infobox rechts neben „About the Game“.
 *
 * @param WC_Product|null $product Produkt.
 */
function globalkeys_render_about_game_sidebar( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		$pid = function_exists( 'get_queried_object_id' ) ? (int) get_queried_object_id() : 0;
		if ( ! $pid && function_exists( 'get_the_ID' ) ) {
			$pid = (int) get_the_ID();
		}
		if ( $pid && function_exists( 'wc_get_product' ) ) {
			$product = wc_get_product( $pid );
		}
	}

	$custom = apply_filters( 'gk_about_game_sidebar_html', '', $product );
	if ( is_string( $custom ) && $custom !== '' ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Filter liefert fertiges Markup; Verantwortung beim Anbieter des Filters.
		echo $custom;
		return;
	}
	if ( ! $product || ! is_a( $product, 'WC_Product' ) || ! globalkeys_product_about_game_sidebar_has_content( $product ) ) {
		return;
	}

	$extra_rows = globalkeys_parse_about_game_sidebar_rows( $product->get_meta( '_gk_about_game_sidebar_rows' ) );
	$extra_rows = apply_filters( 'gk_about_game_sidebar_rows', $extra_rows, $product );
	if ( ! is_array( $extra_rows ) ) {
		$extra_rows = array();
	}

	$standard_rows = function_exists( 'globalkeys_build_about_game_sidebar_standard_display_rows' )
		? globalkeys_build_about_game_sidebar_standard_display_rows( $product )
		: array();

	$combined = array();
	foreach ( $standard_rows as $sr ) {
		$combined[] = array(
			'label'      => $sr['label'],
			'value'      => $sr['value'],
			'muted'      => ! empty( $sr['muted'] ),
			'allow_html' => ! empty( $sr['allow_html'] ),
		);
	}
	foreach ( $extra_rows as $er ) {
		if ( empty( $er['label'] ) || ! isset( $er['value'] ) ) {
			continue;
		}
		$combined[] = array(
			'label'      => $er['label'],
			'value'      => $er['value'],
			'muted'      => ! empty( $er['muted'] ),
			'allow_html' => true,
		);
	}
	$combined_before_filter = $combined;
	$combined               = apply_filters( 'gk_about_game_sidebar_combined_rows', $combined, $product );
	if ( ! is_array( $combined ) || $combined === array() ) {
		$combined = $combined_before_filter;
	}

	$show_score_block = (bool) apply_filters( 'gk_about_game_sidebar_auto_overall_rating', true, $product );
	$rating_data      = ( $show_score_block && function_exists( 'globalkeys_get_product_overall_game_rating_display_data' ) )
		? globalkeys_get_product_overall_game_rating_display_data( $product, 'about_sidebar' )
		: null;

	echo '<aside class="gk-product-page-about-game__sidebar" aria-label="' . esc_attr__( 'Produktinformationen', 'globalkeys' ) . '">';
	if ( is_array( $rating_data ) && function_exists( 'globalkeys_product_print_overall_game_rating_score_block' ) ) {
		$reviews_hit = function_exists( 'globalkeys_product_page_reviews_section_will_render' ) && globalkeys_product_page_reviews_section_will_render( $product );
		$reviews_anchor = 'gk-product-page-reviews-' . (int) $product->get_id();
		if ( $reviews_hit ) {
			echo '<a class="gk-product-page-about-game__sidebar-rating gk-product-page-about-game__sidebar-rating--hit" href="' . esc_url( '#' . $reviews_anchor ) . '">';
		} else {
			echo '<div class="gk-product-page-about-game__sidebar-rating">';
		}
		globalkeys_product_print_overall_game_rating_score_block(
			$rating_data,
			'gk-product-page-about-game__sidebar-hero-score'
		);
		echo '<div class="gk-product-page-about-game__sidebar-rating-text">';
		if ( $rating_data['title'] !== '' ) {
			echo '<p class="gk-product-page-about-game__sidebar-rating-line gk-product-page-about-game__sidebar-rating-line--main">' . esc_html( globalkeys_about_game_sidebar_label_with_colon( $rating_data['title'] ) ) . '</p>';
		}
		if ( $rating_data['subtitle'] !== '' ) {
			echo '<p class="gk-product-page-about-game__sidebar-rating-line gk-product-page-about-game__sidebar-rating-line--sub">';
			if ( $reviews_hit ) {
				echo '<span class="gk-product-page-about-game__sidebar-rating-sub-label">' . esc_html( $rating_data['subtitle'] ) . '</span>';
			} else {
				echo esc_html( $rating_data['subtitle'] );
			}
			echo '</p>';
		}
		echo '</div>';
		if ( $reviews_hit ) {
			echo '</a>';
		} else {
			echo '</div>';
		}
	}

	if ( ! empty( $combined ) ) {
		echo '<dl class="gk-product-page-about-game__sidebar-rows">';
		foreach ( $combined as $row ) {
			if ( empty( $row['label'] ) || ! isset( $row['value'] ) ) {
				continue;
			}
			$muted = ! empty( $row['muted'] );
			echo '<div class="gk-product-page-about-game__sidebar-row">';
			echo '<dt class="gk-product-page-about-game__sidebar-dt">' . esc_html( globalkeys_about_game_sidebar_label_with_colon( $row['label'] ) ) . '</dt>';
			echo '<dd class="gk-product-page-about-game__sidebar-dd' . ( $muted ? ' gk-product-page-about-game__sidebar-dd--muted' : '' ) . '">';
			if ( ! empty( $row['allow_html'] ) ) {
				echo wp_kses_post( $row['value'] );
			} else {
				echo esc_html( $row['value'] );
			}
			echo '</dd>';
			echo '</div>';
		}
		echo '</dl>';
	}

	echo '</aside>';
}

/**
 * Section „About the Game“ auf Produktdetail (unter Keyart + Kaufkarte), nicht in der Kaufkarten-Spalte.
 *
 * Ausgabe über woocommerce_after_single_product_summary (Geschwister von .summary).
 */
function globalkeys_single_product_about_game_section() {
	if ( ! globalkeys_single_product_is_purchase_card_active() ) {
		return;
	}
	global $product;
	$text = apply_filters( 'gk_purchase_card_about_game_heading_text', __( 'About the Game', 'globalkeys' ), $product );
	if ( ! is_string( $text ) || $text === '' ) {
		return;
	}
	$heading_id = 'gk-product-page-about-heading';
	if ( $product && is_a( $product, 'WC_Product' ) ) {
		$heading_id .= '-' . (int) $product->get_id();
	}
	echo '<section class="gk-product-page-about-game" aria-labelledby="' . esc_attr( $heading_id ) . '">';
	echo '<div class="gk-section-inner gk-section-featured-inner">';

	$intro = '';
	if ( $product && is_a( $product, 'WC_Product' ) ) {
		$intro = $product->get_meta( '_gk_about_game_intro' );
	}
	$intro = apply_filters( 'gk_about_game_intro_text', $intro, $product );
	$has_intro = is_string( $intro ) && $intro !== '';

	ob_start();
	globalkeys_render_about_game_sidebar( $product );
	$sidebar_html = trim( (string) ob_get_clean() );

	$tag_terms = array();
	if ( $product && is_a( $product, 'WC_Product' ) ) {
		$raw_tags = get_the_terms( (int) $product->get_id(), 'product_tag' );
		if ( is_array( $raw_tags ) && ! is_wp_error( $raw_tags ) ) {
			$tag_terms = array_values(
				array_filter(
					$raw_tags,
					static function ( $term ) {
						return $term && isset( $term->slug, $term->name ) && $term->slug !== '';
					}
				)
			);
			usort(
				$tag_terms,
				static function ( $a, $b ) {
					return strcasecmp( $a->name, $b->name );
				}
			);
		}
	}
	$tag_terms = apply_filters( 'gk_about_game_product_tags', $tag_terms, $product );

	$layout_classes = array( 'gk-product-page-about-game__layout' );
	if ( $sidebar_html === '' ) {
		$layout_classes[] = 'gk-product-page-about-game__layout--no-sidebar';
	}
	echo '<div class="' . esc_attr( implode( ' ', $layout_classes ) ) . '">';
	echo '<div class="gk-product-page-about-game__main">';
	echo '<div class="gk-featured-heading-wrap gk-product-page-about-game__heading-wrap">';
	echo '<h2 id="' . esc_attr( $heading_id ) . '" class="gk-section-title gk-featured-heading">';
	echo '<span class="gk-featured-heading-text-wrap">';
	echo '<span class="gk-featured-heading-text">' . esc_html( $text ) . '</span>';
	echo '<span class="gk-featured-title-underline" aria-hidden="true"></span>';
	echo '</span>';
	echo '</h2>';
	echo '</div>';
	if ( $has_intro ) {
		echo '<div class="gk-product-page-about-game__intro">';
		echo wp_kses_post( wpautop( $intro ) );
		if ( function_exists( 'globalkeys_product_page_game_description_section_will_render' ) && globalkeys_product_page_game_description_section_will_render( $product ) ) {
			$more_label = apply_filters( 'gk_about_game_intro_more_link_text', __( 'More about the game', 'globalkeys' ), $product );
			if ( is_string( $more_label ) && $more_label !== '' ) {
				$desc_anchor = 'gk-product-page-game-description';
				if ( $product && is_a( $product, 'WC_Product' ) ) {
					$desc_anchor .= '-' . (int) $product->get_id();
				}
				echo '<p class="gk-product-page-about-game__intro-more">';
				echo '<a class="gk-product-page-about-game__intro-more-link" href="#' . esc_attr( $desc_anchor ) . '">' . esc_html( $more_label ) . '</a>';
				echo '</p>';
			}
		}
		echo '</div>';
	}
	if ( ! empty( $tag_terms ) ) {
		$tags_label = apply_filters( 'gk_about_game_tags_label', __( 'Game-tags:', 'globalkeys' ), $product );
		echo '<div class="gk-product-page-about-game__tags">';
		echo '<div class="gk-product-page-about-game__tags-inner">';
		echo '<p class="gk-product-hover-panel__tags-heading">' . esc_html( is_string( $tags_label ) ? $tags_label : __( 'Game-tags:', 'globalkeys' ) ) . '</p>';
		echo '<ul class="gk-product-hover-panel__tag-list">';
		foreach ( $tag_terms as $term ) {
			$link = get_term_link( $term );
			echo '<li>';
			if ( ! is_wp_error( $link ) ) {
				echo '<a class="gk-product-hover-panel__tag" href="' . esc_url( $link ) . '">' . esc_html( $term->name ) . '</a>';
			} else {
				echo '<span class="gk-product-hover-panel__tag">' . esc_html( $term->name ) . '</span>';
			}
			echo '</li>';
		}
		echo '</ul>';
		echo '</div>';
		echo '</div>';
	}
	echo '</div>';
	if ( $sidebar_html !== '' ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Markup aus globalkeys_render_about_game_sidebar(), bereits escaped.
		echo $sidebar_html;
	}
	echo '</div>';

	echo '</div>';
	echo '</section>';
}

/**
 * Section „Game Trailer“ unter „About the Game“ – nur Haupttrailer, gleiche Überschriften-Optik wie „Game Images“.
 *
 * Inhalt: Standard = oEmbed aus _gk_product_main_trailer_url; ersetzbar per gk_visuals_section_content_html.
 */
function globalkeys_single_product_visuals_section() {
	if ( ! globalkeys_single_product_is_purchase_card_active() ) {
		return;
	}
	global $product;
	$text = apply_filters( 'gk_visuals_section_heading_text', __( 'Game Trailer', 'globalkeys' ), $product );
	if ( ! is_string( $text ) || $text === '' ) {
		return;
	}
	$content     = apply_filters( 'gk_visuals_section_content_html', '', $product );
	$has_default = function_exists( 'globalkeys_product_page_has_main_trailer_embed' ) && globalkeys_product_page_has_main_trailer_embed( $product );
	if ( ( ! is_string( $content ) || $content === '' ) && ! $has_default ) {
		return;
	}
	$heading_id = 'gk-product-page-visuals-heading';
	if ( $product && is_a( $product, 'WC_Product' ) ) {
		$heading_id .= '-' . (int) $product->get_id();
	}
	echo '<section class="gk-product-page-visuals" aria-labelledby="' . esc_attr( $heading_id ) . '">';
	echo '<div class="gk-section-inner gk-section-featured-inner">';
	echo '<div class="gk-featured-heading-wrap gk-product-page-visuals__heading-wrap">';
	echo '<h2 id="' . esc_attr( $heading_id ) . '" class="gk-section-title gk-featured-heading">';
	echo '<span class="gk-featured-heading-text-wrap">';
	echo '<span class="gk-featured-heading-text">' . esc_html( $text ) . '</span>';
	echo '<span class="gk-featured-title-underline" aria-hidden="true"></span>';
	echo '</span>';
	echo '</h2>';
	echo '</div>';

	if ( is_string( $content ) && $content !== '' ) {
		echo '<div class="gk-product-page-visuals__body">';
		echo wp_kses_post( $content );
		echo '</div>';
	} elseif ( function_exists( 'globalkeys_render_product_game_trailer_default' ) ) {
		globalkeys_render_product_game_trailer_default( $product );
	}

	echo '</div>';
	echo '</section>';
}

/**
 * Section „Game Images“ unter „Game Trailer“ – Produktgalerie, gleiche Featured-Heading-Optik.
 */
function globalkeys_single_product_game_images_section() {
	if ( ! globalkeys_single_product_is_purchase_card_active() ) {
		return;
	}
	global $product;
	$text = apply_filters( 'gk_game_images_section_heading_text', __( 'Game Images', 'globalkeys' ), $product );
	if ( ! is_string( $text ) || $text === '' ) {
		return;
	}
	$content     = apply_filters( 'gk_game_images_section_content_html', '', $product );
	$has_default = function_exists( 'globalkeys_product_page_has_game_images' ) && globalkeys_product_page_has_game_images( $product );
	if ( ( ! is_string( $content ) || $content === '' ) && ! $has_default ) {
		return;
	}
	$heading_id = 'gk-product-page-game-images-heading';
	if ( $product && is_a( $product, 'WC_Product' ) ) {
		$heading_id .= '-' . (int) $product->get_id();
	}
	$game_images_count = function_exists( 'globalkeys_get_product_page_game_images_count' )
		? globalkeys_get_product_page_game_images_count( $product )
		: 0;
	echo '<section class="gk-product-page-game-images" aria-labelledby="' . esc_attr( $heading_id ) . '">';
	echo '<div class="gk-section-inner gk-section-featured-inner">';
	echo '<div class="gk-featured-heading-wrap gk-product-page-game-images__heading-wrap">';
	echo '<h2 id="' . esc_attr( $heading_id ) . '" class="gk-section-title gk-featured-heading">';
	echo '<span class="gk-featured-heading-text-wrap">';
	echo '<span class="gk-featured-heading-text">' . esc_html( $text ) . '</span>';
	if ( $game_images_count > 0 ) {
		echo '<span class="gk-product-page-game-images__heading-count">';
		echo '(' . (int) $game_images_count . ')';
		echo '</span>';
	}
	echo '<span class="gk-featured-title-underline" aria-hidden="true"></span>';
	echo '</span>';
	echo '</h2>';
	echo '</div>';

	if ( is_string( $content ) && $content !== '' ) {
		echo '<div class="gk-product-page-game-images__body">';
		echo wp_kses_post( $content );
		echo '</div>';
	} elseif ( function_exists( 'globalkeys_render_product_game_images_default' ) ) {
		globalkeys_render_product_game_images_default( $product );
	}

	echo '</div>';
	echo '</section>';
}

/**
 * Ob die Section „Game Description“ für das Produkt ausgegeben wird (gleiche Logik wie beim Rendern).
 *
 * @param WC_Product|null $product Produkt.
 * @return bool
 */
function globalkeys_product_page_game_description_section_will_render( $product ) {
	if ( ! function_exists( 'globalkeys_single_product_is_purchase_card_active' ) || ! globalkeys_single_product_is_purchase_card_active() ) {
		return false;
	}
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return false;
	}
	$heading = apply_filters( 'gk_game_description_section_heading_text', __( 'Game Description', 'globalkeys' ), $product );
	if ( ! is_string( $heading ) || $heading === '' ) {
		return false;
	}
	$content     = apply_filters( 'gk_game_description_section_content_html', '', $product );
	$has_default = function_exists( 'globalkeys_product_page_has_game_description_text' ) && globalkeys_product_page_has_game_description_text( $product );
	return ( is_string( $content ) && $content !== '' ) || $has_default;
}

/**
 * Ob für „Game Description“ ein Standardtext existiert (Lang- und/oder Kurzbeschreibung).
 *
 * @param WC_Product|null $product Produkt.
 * @return bool
 */
function globalkeys_product_page_has_game_description_text( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return false;
	}
	if ( function_exists( 'globalkeys_product_page_has_game_description_layout' ) && globalkeys_product_page_has_game_description_layout( $product ) ) {
		return true;
	}
	foreach ( array( $product->get_description(), $product->get_short_description() ) as $html ) {
		if ( is_string( $html ) && trim( wp_strip_all_tags( $html ) ) !== '' ) {
			return true;
		}
	}
	return (bool) apply_filters( 'gk_game_description_section_force_show', false, $product );
}

/**
 * Section „Game Description“ unter „Game Images“ – Produkt-Langbeschreibung, sonst Kurzbeschreibung (viele Shops nutzen nur letztere).
 */
function globalkeys_single_product_game_description_section() {
	if ( ! globalkeys_single_product_is_purchase_card_active() ) {
		return;
	}
	global $product;
	$text = apply_filters( 'gk_game_description_section_heading_text', __( 'Game Description', 'globalkeys' ), $product );
	if ( ! is_string( $text ) || $text === '' ) {
		return;
	}
	$content = apply_filters( 'gk_game_description_section_content_html', '', $product );
	$has_default = globalkeys_product_page_has_game_description_text( $product );
	if ( ( ! is_string( $content ) || $content === '' ) && ! $has_default ) {
		return;
	}
	$heading_id = 'gk-product-page-game-description-heading';
	$section_id = 'gk-product-page-game-description';
	if ( $product && is_a( $product, 'WC_Product' ) ) {
		$pid = (int) $product->get_id();
		$heading_id .= '-' . $pid;
		$section_id  .= '-' . $pid;
	}
	echo '<section id="' . esc_attr( $section_id ) . '" class="gk-product-page-game-description" aria-labelledby="' . esc_attr( $heading_id ) . '">';
	echo '<div class="gk-section-inner gk-section-featured-inner">';
	echo '<div class="gk-featured-heading-wrap gk-product-page-game-description__heading-wrap">';
	echo '<h2 id="' . esc_attr( $heading_id ) . '" class="gk-section-title gk-featured-heading">';
	echo '<span class="gk-featured-heading-text-wrap">';
	echo '<span class="gk-featured-heading-text">' . esc_html( $text ) . '</span>';
	echo '<span class="gk-featured-title-underline" aria-hidden="true"></span>';
	echo '</span>';
	echo '</h2>';
	echo '</div>';

	echo '<div class="gk-product-page-game-description__body">';
	if ( is_string( $content ) && $content !== '' ) {
		echo wp_kses_post( $content );
	} elseif ( function_exists( 'globalkeys_render_game_description_blocks' ) && function_exists( 'globalkeys_product_page_has_game_description_layout' ) && globalkeys_product_page_has_game_description_layout( $product ) ) {
		globalkeys_render_game_description_blocks( $product );
	} elseif ( $product && is_a( $product, 'WC_Product' ) && function_exists( 'wc_format_content' ) ) {
		$body = $product->get_description();
		if ( ! is_string( $body ) || trim( wp_strip_all_tags( $body ) ) === '' ) {
			$body = $product->get_short_description();
		}
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wc_format_content() liefert gefiltertes HTML wie in WooCommerce-Templates.
		echo wc_format_content( is_string( $body ) ? $body : '' );
	}
	echo '</div>';

	echo '</div>';
	echo '</section>';
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
	globalkeys_single_product_purchase_simple_selectors_row();
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

	add_action( 'woocommerce_after_single_product_summary', 'globalkeys_single_product_about_game_section', 5 );
	add_action( 'woocommerce_after_single_product_summary', 'globalkeys_single_product_visuals_section', 6 );
	add_action( 'woocommerce_after_single_product_summary', 'globalkeys_single_product_game_images_section', 7 );
	add_action( 'woocommerce_single_product_summary', 'globalkeys_single_product_purchase_status_bar', 5 );

	add_action( 'woocommerce_before_add_to_cart_button', 'globalkeys_single_product_purchase_actions_open', 1 );
	add_action( 'woocommerce_after_add_to_cart_button', 'globalkeys_single_product_purchase_actions_close', 999 );
}

/**
 * WooCommerce-Tab „Beschreibung“ entfernen: gleicher Inhalt erscheint in Section „Game Description“.
 *
 * @param array<string, array<string, mixed>> $tabs Register tabs.
 * @return array<string, array<string, mixed>>
 */
function globalkeys_single_product_remove_product_description_tab( $tabs ) {
	if ( ! function_exists( 'is_product' ) || ! is_product() || ! globalkeys_single_product_is_purchase_card_active() ) {
		return $tabs;
	}
	unset( $tabs['description'] );
	return $tabs;
}

add_action( 'wp', 'globalkeys_single_product_purchase_card_bootstrap', 24 );
add_action( 'woocommerce_after_single_product_summary', 'globalkeys_single_product_game_description_section', 8 );
add_filter( 'woocommerce_product_tabs', 'globalkeys_single_product_remove_product_description_tab', 98 );

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
