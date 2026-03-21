<?php
/**
 * Vorbestellungen: Erscheinungsdatum, optional Anzeige ohne Kategorie „pre-order“.
 *
 * @package globalkeys
 */

defined( 'ABSPATH' ) || exit;

/** Produktkategorie-Slug für die Startseiten-Sektion „Pre-orders“ */
if ( ! defined( 'GLOBALKEYS_PREORDER_CATEGORY_SLUG' ) ) {
	define( 'GLOBALKEYS_PREORDER_CATEGORY_SLUG', 'pre-order' );
}

/**
 * Meta: Erscheinungsdatum (YYYY-MM-DD), für Badge-Zeile „VORBESTELLUNG · Datum“.
 */
function globalkeys_register_preorder_product_meta() {
	register_post_meta(
		'product',
		'_gk_release_date',
		array(
			'type'              => 'string',
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => false,
			'auth_callback'     => function () {
				return current_user_can( 'edit_products' );
			},
		)
	);
	register_post_meta(
		'product',
		'_gk_list_as_preorder',
		array(
			'type'              => 'string',
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => false,
			'auth_callback'     => function () {
				return current_user_can( 'edit_products' );
			},
		)
	);
}
add_action( 'init', 'globalkeys_register_preorder_product_meta' );

/**
 * Produkt gilt als Vorbestellung (Badge unter Titel, Auflistung möglich).
 *
 * @param WC_Product|int $product Produkt oder ID.
 */
function globalkeys_is_preorder_product( $product ) {
	if ( is_numeric( $product ) ) {
		$product = wc_get_product( (int) $product );
	}
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return false;
	}
	if ( has_term( GLOBALKEYS_PREORDER_CATEGORY_SLUG, 'product_cat', $product->get_id() ) ) {
		return true;
	}
	$flag = $product->get_meta( '_gk_list_as_preorder' );
	return $flag === 'yes' || $flag === '1' || $flag === 1;
}

/**
 * Unix-Timestamp aus _gk_release_date oder 0.
 *
 * @param WC_Product $product Produkt.
 * @return int
 */
function globalkeys_get_product_release_timestamp( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return 0;
	}
	$raw = $product->get_meta( '_gk_release_date' );
	if ( ! is_string( $raw ) || $raw === '' ) {
		return 0;
	}
	$raw = trim( $raw );
	if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $raw ) ) {
		return 0;
	}
	$ts = strtotime( $raw . ' 12:00:00 ' . wp_timezone_string() );
	return $ts > 0 ? (int) $ts : 0;
}

/**
 * Lokalisiertes Datum (z. B. „27 Mai 2026“).
 *
 * @param WC_Product $product Produkt.
 * @return string Leer wenn kein Datum.
 */
function globalkeys_format_product_release_date_display( $product ) {
	$ts = globalkeys_get_product_release_timestamp( $product );
	if ( $ts < 1 ) {
		return '';
	}
	return wp_date( 'j F Y', $ts );
}

/**
 * Alle veröffentlichten Produkt-IDs, die als Vorbestellung gelten (Kategorie oder Meta).
 * Diese werden überall außer Pre-orders-Sektion und Kategorie-Archiv „pre-order“ ausgeblendet.
 *
 * @return int[]
 */
function globalkeys_get_preorder_list_product_ids() {
	static $cached = null;
	if ( null !== $cached ) {
		return $cached;
	}
	if ( ! function_exists( 'wc_get_products' ) ) {
		$cached = array();
		return $cached;
	}
	$ids_cat = wc_get_products(
		array(
			'status'   => 'publish',
			'limit'    => -1,
			'return'   => 'ids',
			'category' => array( GLOBALKEYS_PREORDER_CATEGORY_SLUG ),
		)
	);
	$ids_cat = is_array( $ids_cat ) ? $ids_cat : array();

	$ids_meta = wc_get_products(
		array(
			'status'     => 'publish',
			'limit'      => -1,
			'return'     => 'ids',
			'meta_key'   => '_gk_list_as_preorder',
			'meta_value' => 'yes',
		)
	);
	$ids_meta = is_array( $ids_meta ) ? $ids_meta : array();

	$cached = array_values( array_unique( array_map( 'intval', array_merge( $ids_cat, $ids_meta ) ) ) );
	return $cached;
}

/**
 * wc_get_products-Argumente: Vorbestell-Produkte per exclude entfernen.
 *
 * @param array $args WooCommerce-Produktabfrage.
 * @return array
 */
function globalkeys_wc_product_args_exclude_preorders( array $args ) {
	$pre = globalkeys_get_preorder_list_product_ids();
	if ( empty( $pre ) ) {
		return $args;
	}
	$ex = isset( $args['exclude'] ) ? array_map( 'intval', (array) $args['exclude'] ) : array();
	$args['exclude'] = array_values( array_unique( array_merge( $ex, $pre ) ) );
	return $args;
}

/**
 * Produkte für die Sektion „Pre-orders“: Kategorie pre-order ODER Meta „In Liste“.
 *
 * @param int $limit Max. Anzahl.
 * @return WC_Product[]
 */
function globalkeys_get_preorder_section_products( $limit = 12 ) {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return array();
	}
	$limit = max( 1, (int) $limit );
	$ids   = globalkeys_get_preorder_list_product_ids();
	if ( empty( $ids ) ) {
		return array();
	}

	$products = wc_get_products(
		array(
			'status'   => 'publish',
			'include'  => $ids,
			'limit'    => $limit,
			'orderby'  => 'menu_order',
			'order'    => 'ASC',
			'return'   => 'objects',
		)
	);

	return is_array( $products ) ? $products : array();
}

/**
 * Shop, andere Produkt-Kategorien/Tags & Produktsuche: Vorbesteller ausblenden.
 * Ausnahme: Archiv der Kategorie „pre-order“ (Slug wie GLOBALKEYS_PREORDER_CATEGORY_SLUG).
 *
 * @param WP_Query $q Hauptabfrage.
 */
function globalkeys_pre_get_posts_hide_preorders_outside_section( $q ) {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}
	$pre_ids = globalkeys_get_preorder_list_product_ids();
	if ( empty( $pre_ids ) ) {
		return;
	}

	if ( function_exists( 'is_product_category' ) && is_product_category() ) {
		$term = get_queried_object();
		if ( $term && ! is_wp_error( $term ) && isset( $term->slug ) && $term->slug === GLOBALKEYS_PREORDER_CATEGORY_SLUG ) {
			return;
		}
	}

	$apply = false;
	if ( function_exists( 'is_shop' ) && is_shop() ) {
		$apply = true;
	}
	if ( function_exists( 'is_product_category' ) && is_product_category() ) {
		$apply = true;
	}
	if ( function_exists( 'is_product_tag' ) && is_product_tag() ) {
		$apply = true;
	}
	if ( $q->is_search() ) {
		$pt = $q->get( 'post_type' );
		if ( $pt === 'product' || ( is_array( $pt ) && in_array( 'product', $pt, true ) ) ) {
			$apply = true;
		}
	}

	if ( ! $apply ) {
		return;
	}

	$not_in = $q->get( 'post__not_in' );
	$not_in = array_merge( array_map( 'intval', (array) $not_in ), $pre_ids );
	$q->set( 'post__not_in', array_values( array_unique( $not_in ) ) );
}
add_action( 'pre_get_posts', 'globalkeys_pre_get_posts_hide_preorders_outside_section', 50 );

/**
 * HTML: Badge „VORBESTELLUNG“ + optionales Datum (nur wenn Vorbestell-Produkt).
 *
 * @param WC_Product $product Produkt.
 */
function globalkeys_render_preorder_badge_row( $product ) {
	if ( ! globalkeys_is_preorder_product( $product ) ) {
		return;
	}
	$date_str = globalkeys_format_product_release_date_display( $product );
	?>
	<span class="gk-preorder-meta" aria-hidden="true">
		<span class="gk-preorder-badge"><?php esc_html_e( 'PRE-ORDER', 'globalkeys' ); ?></span>
		<?php if ( $date_str !== '' ) : ?>
			<span class="gk-preorder-date"><?php echo esc_html( $date_str ); ?></span>
		<?php endif; ?>
	</span>
	<?php
}

/**
 * Produktdaten: Erscheinungsdatum + optional ohne Kategorie listen.
 */
function globalkeys_product_preorder_fields() {
	global $post;
	if ( ! $post || $post->post_type !== 'product' ) {
		return;
	}
	$product = wc_get_product( $post->ID );
	if ( ! $product ) {
		return;
	}

	$date_val = $product->get_meta( '_gk_release_date' );
	$date_val = is_string( $date_val ) ? $date_val : '';
	if ( $date_val !== '' && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date_val ) ) {
		$ts = strtotime( $date_val );
		$date_val = $ts ? gmdate( 'Y-m-d', $ts ) : '';
	}

	$list_val = $product->get_meta( '_gk_list_as_preorder' );
	$checked  = ( $list_val === 'yes' || $list_val === '1' );
	?>
	<div class="options_group">
		<?php
		woocommerce_wp_text_input(
			array(
				'id'          => '_gk_release_date',
				'name'        => '_gk_release_date',
				'label'       => __( 'Erscheinungsdatum (Vorbestellung)', 'globalkeys' ),
				'placeholder' => 'YYYY-MM-DD',
				'description' => __( 'Wird unter dem Produkttitel in der Sektion „Pre-orders“ neben dem Badge angezeigt. Format: JJJJ-MM-TT.', 'globalkeys' ),
				'desc_tip'    => true,
				'type'        => 'date',
				'value'       => $date_val,
			)
		);
		woocommerce_wp_checkbox(
			array(
				'id'          => '_gk_list_as_preorder',
				'name'        => '_gk_list_as_preorder',
				'label'       => __( 'In „Pre-orders“ listen ohne Kategorie', 'globalkeys' ),
				'description' => sprintf(
					/* translators: %s: category slug */
					__( 'Alternativ Produkt der Shop-Kategorie „%s“ zuweisen.', 'globalkeys' ),
					GLOBALKEYS_PREORDER_CATEGORY_SLUG
				),
				'value'       => $checked ? 'yes' : 'no',
				'cbvalue'     => 'yes',
			)
		);
		?>
	</div>
	<?php
}
add_action( 'woocommerce_product_options_general_product_data', 'globalkeys_product_preorder_fields', 15 );

/**
 * Speichern Vorbestellungs-Felder.
 *
 * @param WC_Product $product Produkt.
 */
function globalkeys_save_product_preorder_fields( $product ) {
	if ( ! is_a( $product, 'WC_Product' ) ) {
		return;
	}

	if ( isset( $_POST['_gk_release_date'] ) ) {
		$raw = sanitize_text_field( wp_unslash( $_POST['_gk_release_date'] ) );
		if ( $raw === '' ) {
			$product->delete_meta_data( '_gk_release_date' );
		} elseif ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $raw ) ) {
			$product->update_meta_data( '_gk_release_date', $raw );
		}
	}

	$list = isset( $_POST['_gk_list_as_preorder'] ) && ( $_POST['_gk_list_as_preorder'] === 'yes' );
	if ( $list ) {
		$product->update_meta_data( '_gk_list_as_preorder', 'yes' );
	} else {
		$product->delete_meta_data( '_gk_list_as_preorder' );
	}
}
add_action( 'woocommerce_admin_process_product_object', 'globalkeys_save_product_preorder_fields', 15, 1 );
