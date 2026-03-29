<?php
/**
 * Produktseite: Section „Similar products“ (Übereinstimmung über product_tag).
 *
 * @package globalkeys
 */

defined( 'ABSPATH' ) || exit;

/**
 * Liefert ähnliche sichtbare Produkte nach Anzahl gemeinsamer Tags (absteigend), max. $limit.
 *
 * @param WC_Product $product Aktuelles Produkt.
 * @param int        $limit   Max. Anzahl (Standard 6).
 * @return WC_Product[]
 */
function globalkeys_get_similar_products_by_tags( $product, $limit = 6 ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return array();
	}

	$limit = (int) apply_filters( 'gk_similar_products_limit', $limit, $product );
	if ( $limit < 1 ) {
		$limit = 6;
	}

	$pool = (int) apply_filters( 'gk_similar_products_query_pool', 80, $product );
	if ( $pool < $limit ) {
		$pool = max( 24, $limit * 4 );
	}

	$pid     = (int) $product->get_id();
	$tag_ids = wp_get_post_terms( $pid, 'product_tag', array( 'fields' => 'ids' ) );
	if ( empty( $tag_ids ) || is_wp_error( $tag_ids ) ) {
		return array();
	}
	$tag_ids = array_values( array_unique( array_filter( array_map( 'intval', $tag_ids ) ) ) );
	if ( empty( $tag_ids ) ) {
		return array();
	}

	$q = new WP_Query(
		array(
			'post_type'              => 'product',
			'post_status'            => 'publish',
			'posts_per_page'         => $pool,
			'post__not_in'           => array( $pid ),
			'orderby'                => 'none',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => true,
			'tax_query'              => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'product_tag',
					'field'    => 'term_id',
					'terms'    => $tag_ids,
					'operator' => 'IN',
				),
			),
		)
	);

	$scored = array();
	if ( $q->have_posts() ) {
		foreach ( $q->posts as $post ) {
			if ( ! $post instanceof WP_Post ) {
				continue;
			}
			$p = wc_get_product( (int) $post->ID );
			if ( ! $p || ! $p->is_visible() ) {
				continue;
			}
			$other = wp_get_post_terms( $p->get_id(), 'product_tag', array( 'fields' => 'ids' ) );
			if ( empty( $other ) || is_wp_error( $other ) ) {
				continue;
			}
			$overlap = count( array_intersect( $tag_ids, array_map( 'intval', $other ) ) );
			if ( $overlap < 1 ) {
				continue;
			}
			$scored[] = array(
				'product' => $p,
				'score'   => $overlap,
			);
		}
	}
	wp_reset_postdata();

	usort(
		$scored,
		static function ( $a, $b ) {
			if ( $a['score'] !== $b['score'] ) {
				return $b['score'] <=> $a['score'];
			}
			return strcasecmp( $a['product']->get_name(), $b['product']->get_name() );
		}
	);

	$out  = array();
	$seen = array();
	foreach ( $scored as $row ) {
		$id = (int) $row['product']->get_id();
		if ( isset( $seen[ $id ] ) ) {
			continue;
		}
		$seen[ $id ] = true;
		$out[]       = $row['product'];
		if ( count( $out ) >= $limit ) {
			break;
		}
	}

	return $out;
}

/**
 * Section unter „Franchise“: ähnliche Produkte nach Tags.
 */
function globalkeys_single_product_similar_products_section() {
	if ( ! function_exists( 'globalkeys_single_product_is_purchase_card_active' ) || ! globalkeys_single_product_is_purchase_card_active() ) {
		return;
	}
	global $product;
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return;
	}

	$items = globalkeys_get_similar_products_by_tags( $product, 6 );
	if ( empty( $items ) ) {
		return;
	}

	$heading = apply_filters( 'gk_similar_products_section_heading_text', __( 'Similar products', 'globalkeys' ), $product );
	if ( ! is_string( $heading ) || $heading === '' ) {
		return;
	}

	$heading_id = 'gk-product-page-similar-heading-' . (int) $product->get_id();

	echo '<section class="gk-product-page-similar" aria-labelledby="' . esc_attr( $heading_id ) . '">';
	echo '<div class="gk-section-inner gk-section-featured-inner">';
	echo '<div class="gk-featured-heading-wrap gk-product-page-similar__heading-wrap">';
	echo '<h2 id="' . esc_attr( $heading_id ) . '" class="gk-section-title gk-featured-heading">';
	echo '<span class="gk-featured-heading-text-wrap">';
	echo '<span class="gk-featured-heading-text">' . esc_html( $heading ) . '</span>';
	echo '<span class="gk-featured-title-underline" aria-hidden="true"></span>';
	echo '</span>';
	echo '</h2>';
	echo '</div>';

	$list_label = apply_filters( 'gk_similar_products_list_aria_label', __( 'Similar products', 'globalkeys' ), $product );
	echo '<div class="gk-product-page-similar__body">';
	echo '<div class="gk-section-bestsellers gk-product-page-similar__bestsellers" role="presentation">';
	echo '<ul class="gk-featured-products" aria-label="' . esc_attr( is_string( $list_label ) ? $list_label : '' ) . '">';
	foreach ( $items as $rel ) {
		if ( ! $rel || ! is_a( $rel, 'WC_Product' ) ) {
			continue;
		}
		set_query_var( 'product', $rel );
		set_query_var( 'gk_product_card_skip_visibility', false );
		get_template_part( 'template-parts/product-card', 'bestseller' );
	}
	echo '</ul>';
	echo '</div>';
	echo '</div>';

	echo '</div>';
	echo '</section>';

	$GLOBALS['product'] = $product;
}

add_action( 'woocommerce_after_single_product_summary', 'globalkeys_single_product_similar_products_section', 11 );
