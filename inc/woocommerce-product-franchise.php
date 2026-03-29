<?php
/**
 * Produkt-Franchise: Taxonomie + Section „More about this franchise“ auf der Produktseite.
 *
 * @package globalkeys
 */

defined( 'ABSPATH' ) || exit;

/**
 * Taxonomie-Slug (Produkte).
 */
define( 'GK_PRODUCT_FRANCHISE_TAXONOMY', 'product_franchise' );

/**
 * Registriert die Franchise-Taxonomie für WooCommerce-Produkte.
 */
function globalkeys_register_product_franchise_taxonomy() {
	if ( ! post_type_exists( 'product' ) ) {
		return;
	}

	$labels = array(
		'name'                       => _x( 'Franchises', 'taxonomy general name', 'globalkeys' ),
		'singular_name'              => _x( 'Franchise', 'taxonomy singular name', 'globalkeys' ),
		'search_items'               => __( 'Search franchises', 'globalkeys' ),
		'popular_items'              => __( 'Popular franchises', 'globalkeys' ),
		'all_items'                  => __( 'All franchises', 'globalkeys' ),
		'edit_item'                  => __( 'Edit franchise', 'globalkeys' ),
		'update_item'                => __( 'Update franchise', 'globalkeys' ),
		'add_new_item'               => __( 'Add new franchise', 'globalkeys' ),
		'new_item_name'              => __( 'New franchise name', 'globalkeys' ),
		'separate_items_with_commas' => __( 'Separate franchises with commas', 'globalkeys' ),
		'add_or_remove_items'        => __( 'Add or remove franchises', 'globalkeys' ),
		'choose_from_most_used'      => __( 'Choose from most used franchises', 'globalkeys' ),
		'not_found'                  => __( 'No franchises found.', 'globalkeys' ),
		'menu_name'                  => __( 'Franchises', 'globalkeys' ),
		'back_to_items'              => __( '&larr; Back to franchises', 'globalkeys' ),
	);

	register_taxonomy(
		GK_PRODUCT_FRANCHISE_TAXONOMY,
		array( 'product' ),
		array(
			'labels'            => $labels,
			'hierarchical'      => false,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_quick_edit' => true,
			'show_in_nav_menus' => false,
			'show_tagcloud'     => false,
			'show_in_rest'      => true,
			'rewrite'           => false,
			'query_var'         => false,
		)
	);
}
add_action( 'init', 'globalkeys_register_product_franchise_taxonomy', 11 );

/**
 * Franchise-Begriffe eines Produkts.
 *
 * @param WC_Product|int|null $product Produkt oder Post-ID.
 * @return WP_Term[]
 */
function globalkeys_get_product_franchise_terms( $product ) {
	$id = 0;
	if ( $product instanceof WC_Product ) {
		$id = (int) $product->get_id();
	} elseif ( is_numeric( $product ) ) {
		$id = (int) $product;
	}
	if ( $id <= 0 ) {
		return array();
	}

	$terms = get_the_terms( $id, GK_PRODUCT_FRANCHISE_TAXONOMY );
	if ( ! is_array( $terms ) ) {
		return array();
	}

	return array_values(
		array_filter(
			$terms,
			static function ( $t ) {
				return $t instanceof WP_Term && ! is_wp_error( $t );
			}
		)
	);
}

/**
 * Alle passenden Produkte derselben Franchise(s), inkl. der aktuellen Seite.
 * Das aktuelle Produkt steht zuerst; andere nur bei Katalog-Sichtbarkeit (WooCommerce), das aktuelle immer wenn veröffentlicht.
 *
 * @param WC_Product $product Aktuelles Produkt.
 * @param WP_Term[]  $terms   Franchise-Begriffe.
 * @return WC_Product[]
 */
function globalkeys_get_franchise_related_products( $product, array $terms ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) || empty( $terms ) ) {
		return array();
	}

	$term_ids = array();
	foreach ( $terms as $t ) {
		if ( $t instanceof WP_Term ) {
			$term_ids[] = (int) $t->term_id;
		}
	}
	$term_ids = array_values( array_unique( array_filter( $term_ids ) ) );
	if ( empty( $term_ids ) ) {
		return array();
	}

	$current_id = (int) $product->get_id();
	$limit      = (int) apply_filters( 'gk_franchise_section_products_limit', 48 );
	if ( $limit < 1 ) {
		$limit = 48;
	}

	$tax_query = array( 'relation' => 'OR' );
	foreach ( $term_ids as $tid ) {
		$tax_query[] = array(
			'taxonomy' => GK_PRODUCT_FRANCHISE_TAXONOMY,
			'field'    => 'term_id',
			'terms'    => array( $tid ),
		);
	}

	$q = new WP_Query(
		array(
			'post_type'              => 'product',
			'post_status'            => 'publish',
			'posts_per_page'         => $limit,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => true,
			'tax_query'              => $tax_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		)
	);

	$by_id = array();
	if ( $q->have_posts() ) {
		foreach ( $q->posts as $post ) {
			if ( ! $post instanceof WP_Post ) {
				continue;
			}
			$p = wc_get_product( (int) $post->ID );
			if ( ! $p || $p->get_status() !== 'publish' ) {
				continue;
			}
			$pid = (int) $p->get_id();
			if ( $pid === $current_id || $p->is_visible() ) {
				$by_id[ $pid ] = $p;
			}
		}
	}
	wp_reset_postdata();

	// Eigene Seite: immer Karte, auch wenn das Produkt im Katalog verborgen ist (Nutzer sieht es gerade).
	if ( ! isset( $by_id[ $current_id ] ) && $product->get_status() === 'publish' ) {
		$by_id[ $current_id ] = $product;
	}

	$out = array_values( $by_id );
	usort(
		$out,
		static function ( $a, $b ) use ( $current_id ) {
			$aid = (int) $a->get_id();
			$bid = (int) $b->get_id();
			if ( $aid === $current_id && $bid !== $current_id ) {
				return -1;
			}
			if ( $bid === $current_id && $aid !== $current_id ) {
				return 1;
			}
			return strcasecmp( $a->get_name(), $b->get_name() );
		}
	);

	if ( count( $out ) > $limit ) {
		$out = array_slice( $out, 0, $limit );
	}

	return $out;
}

/**
 * Section unter „System Requirements“: sobald mindestens eine Franchise zugewiesen ist.
 * Alle Spiele der Franchise inkl. aktuellem Produkt (Karte zuerst); ohne sichtbare Treffer nur Platzhaltertext.
 */
function globalkeys_single_product_franchise_section() {
	if ( ! function_exists( 'globalkeys_single_product_is_purchase_card_active' ) || ! globalkeys_single_product_is_purchase_card_active() ) {
		return;
	}
	global $product;
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return;
	}

	$terms = globalkeys_get_product_franchise_terms( $product );
	if ( empty( $terms ) ) {
		return;
	}

	$related = globalkeys_get_franchise_related_products( $product, $terms );

	$heading = apply_filters( 'gk_franchise_section_heading_text', __( 'More about this franchise', 'globalkeys' ), $product, $terms );
	if ( ! is_string( $heading ) || $heading === '' ) {
		return;
	}

	$names = array();
	foreach ( $terms as $t ) {
		if ( $t instanceof WP_Term ) {
			$names[] = $t->name;
		}
	}
	$franchise_label = implode( __( ', ', 'globalkeys' ), $names );

	$heading_id = 'gk-product-page-franchise-heading';
	$heading_id .= '-' . (int) $product->get_id();

	echo '<section class="gk-product-page-franchise" aria-labelledby="' . esc_attr( $heading_id ) . '">';
	echo '<div class="gk-section-inner gk-section-featured-inner">';
	echo '<div class="gk-featured-heading-wrap gk-product-page-franchise__heading-wrap">';
	echo '<h2 id="' . esc_attr( $heading_id ) . '" class="gk-section-title gk-featured-heading">';
	echo '<span class="gk-featured-heading-text-wrap">';
	echo '<span class="gk-featured-heading-text">' . esc_html( $heading ) . '</span>';
	echo '<span class="gk-featured-title-underline" aria-hidden="true"></span>';
	echo '</span>';
	echo '</h2>';
	if ( $franchise_label !== '' ) {
		echo '<p class="gk-product-page-franchise__subline">' . esc_html( $franchise_label ) . '</p>';
	}
	echo '</div>';

	echo '<div class="gk-product-page-franchise__body">';
	if ( ! empty( $related ) ) {
		$list_label = apply_filters( 'gk_franchise_section_list_aria_label', __( 'Games in this franchise', 'globalkeys' ), $product, $terms );
		// Gleiche Karten wie Startseite (Featured/Bestsellers): Wrapper .gk-section-bestsellers für Card-CSS (16:9, Trailer, Raster).
		echo '<div class="gk-section-bestsellers gk-product-page-franchise__bestsellers" role="presentation">';
		echo '<ul class="gk-featured-products" aria-label="' . esc_attr( is_string( $list_label ) ? $list_label : '' ) . '">';
		foreach ( $related as $rel ) {
			if ( ! $rel || ! is_a( $rel, 'WC_Product' ) ) {
				continue;
			}
			set_query_var( 'product', $rel );
			// Aktuelle Produktseite: Karte auch bei „versteckt im Katalog“ (Template prüft sonst is_visible()).
			set_query_var( 'gk_product_card_skip_visibility', (int) $rel->get_id() === (int) $product->get_id() );
			get_template_part( 'template-parts/product-card', 'bestseller' );
		}
		set_query_var( 'gk_product_card_skip_visibility', false );
		echo '</ul>';
		echo '</div>';
	} else {
		$empty_msg = apply_filters(
			'gk_franchise_section_empty_message',
			__( 'Weitere Spiele dieser Franchise sind bald hier verlinkt.', 'globalkeys' ),
			$product,
			$terms
		);
		if ( is_string( $empty_msg ) && $empty_msg !== '' ) {
			echo '<p class="gk-product-page-franchise__empty">' . esc_html( $empty_msg ) . '</p>';
		}
	}
	echo '</div>';

	echo '</div>';
	echo '</section>';

	$GLOBALS['product'] = $product;
}

add_action( 'woocommerce_after_single_product_summary', 'globalkeys_single_product_franchise_section', 10 );
