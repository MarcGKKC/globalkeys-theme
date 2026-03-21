<?php
/**
 * globalkeys functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package globalkeys
 */

if ( ! defined( '_S_VERSION' ) ) {
	// Replace the version number of the theme on each release.
	define( '_S_VERSION', '1.0.0' );
}

/**
 * Checkout vorübergehend deaktivieren.
 * Auf false setzen, um den Checkout wieder zu aktivieren.
 */
if ( ! defined( 'GLOBKEYS_CHECKOUT_DISABLED' ) ) {
	define( 'GLOBKEYS_CHECKOUT_DISABLED', true );
}

/**
 * Checkout deaktivieren: Von Checkout-Seite abfangen und Meldung anzeigen.
 */
function globalkeys_disable_checkout_if_set() {
	if ( ! GLOBKEYS_CHECKOUT_DISABLED || ! function_exists( 'is_checkout' ) || ! function_exists( 'is_wc_endpoint_url' ) ) {
		return;
	}
	if ( is_checkout() && ! is_wc_endpoint_url( 'order-received' ) ) {
		wp_safe_redirect( wc_get_page_permalink( 'cart' ) );
		wc_add_notice( __( 'Der Checkout ist vorübergehend nicht verfügbar. Wir arbeiten daran und sind bald wieder für dich da.', 'globalkeys' ), 'notice' );
		exit;
	}
}
add_action( 'template_redirect', 'globalkeys_disable_checkout_if_set', 5 );

/**
 * Hinweis auf der Warenkorb-Seite, wenn Checkout deaktiviert ist.
 */
function globalkeys_cart_checkout_disabled_notice() {
	if ( ! GLOBKEYS_CHECKOUT_DISABLED || ! function_exists( 'is_cart' ) || ! is_cart() ) {
		return;
	}
	wc_print_notice( __( 'Checkout ist derzeit deaktiviert. Wir sind in Kürze wieder für dich erreichbar.', 'globalkeys' ), 'notice' );
}
add_action( 'woocommerce_before_cart', 'globalkeys_cart_checkout_disabled_notice', 5 );

/**
 * Bestellung blockieren, falls jemand den Checkout doch erreicht.
 */
function globalkeys_prevent_order_if_checkout_disabled() {
	if ( GLOBKEYS_CHECKOUT_DISABLED && function_exists( 'wc_add_notice' ) ) {
		wc_add_notice( __( 'Der Checkout ist vorübergehend nicht verfügbar.', 'globalkeys' ), 'error' );
	}
}
add_action( 'woocommerce_checkout_process', 'globalkeys_prevent_order_if_checkout_disabled', 1 );

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function globalkeys_setup() {
	/*
		* Make theme available for translation.
		* Translations can be filed in the /languages/ directory.
		* If you're building a theme based on globalkeys, use a find and replace
		* to change 'globalkeys' to the name of your theme in all the template files.
		*/
	load_theme_textdomain( 'globalkeys', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
		* Let WordPress manage the document title.
		* By adding theme support, we declare that this theme does not use a
		* hard-coded <title> tag in the document head, and expect WordPress to
		* provide it for us.
		*/
	add_theme_support( 'title-tag' );

	/*
		* Enable support for Post Thumbnails on posts and pages.
		*
		* @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		*/
		add_theme_support( 'post-thumbnails' );

		/*
		 * Größere Produktkarten-Bilder (Bestseller & ähnliche Raster): weniger Hochskalieren, schärfere Darstellung.
		 * Nach dem ersten Deploy ggf. Plugin „Regenerate Thumbnails“ ausführen, damit alte Uploads diese Größe erhalten.
		 */
		add_image_size( 'globalkeys-product-card', 1536, 1536, false );
		add_image_size( 'globalkeys-search-dropdown', 320, 180, true );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus(
		array(
			'menu-1' => esc_html__( 'Primary', 'globalkeys' ),
		)
	);

	/*
		* Switch default core markup for search form, comment form, and comments
		* to output valid HTML5.
		*/
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Set up the WordPress core custom background feature.
	add_theme_support(
		'custom-background',
		apply_filters(
			'globalkeys_custom_background_args',
			array(
				'default-color' => 'ffffff',
				'default-image' => '',
			)
		)
	);

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );

	/**
	 * Add support for core custom logo.
	 *
	 * @link https://codex.wordpress.org/Theme_Logo
	 */
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);
}
add_action( 'after_setup_theme', 'globalkeys_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function globalkeys_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'globalkeys_content_width', 640 );
}
add_action( 'after_setup_theme', 'globalkeys_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function globalkeys_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Sidebar', 'globalkeys' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Add widgets here.', 'globalkeys' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'globalkeys_widgets_init' );

/**
 * Remove "My account" from primary menu (we use the account icon instead).
 *
 * @param array    $items Menu items.
 * @param stdClass $args  Menu arguments.
 * @return array
 */
function globalkeys_remove_myaccount_menu_item( $items, $args ) {
	$theme_location = '';
	if ( is_object( $args ) && isset( $args->theme_location ) ) {
		$theme_location = $args->theme_location;
	} elseif ( is_array( $args ) && isset( $args['theme_location'] ) ) {
		$theme_location = $args['theme_location'];
	}
	if ( 'menu-1' !== $theme_location ) {
		return $items;
	}
	foreach ( $items as $key => $item ) {
		if ( isset( $item->url ) && ( strpos( $item->url, 'my-account' ) !== false || strpos( $item->url, 'myaccount' ) !== false ) ) {
			unset( $items[ $key ] );
		}
	}
	return $items;
}
add_filter( 'wp_nav_menu_objects', 'globalkeys_remove_myaccount_menu_item', 10, 2 );

/**
 * Add class to "My account" menu item for CSS hiding (fallback if filter doesn't remove it).
 *
 * @param array    $classes Menu item classes.
 * @param WP_Post  $item    Menu item.
 * @return array
 */
function globalkeys_hide_myaccount_menu_class( $classes, $item ) {
	if ( isset( $item->url ) && ( strpos( $item->url, 'my-account' ) !== false || strpos( $item->url, 'myaccount' ) !== false ) ) {
		$classes[] = 'gk-hide-myaccount';
	}
	return $classes;
}
add_filter( 'nav_menu_css_class', 'globalkeys_hide_myaccount_menu_class', 10, 2 );

/**
 * Plattform-Seiten: URL /platform/{slug}/ (z. B. /platform/pc/) → eigene Seite mit Header + leerem Inhalt.
 */
function globalkeys_platform_rewrite_rules() {
	add_rewrite_rule( 'platform/([^/]+)/?$', 'index.php?gk_platform=$matches[1]', 'top' );
}
add_action( 'init', 'globalkeys_platform_rewrite_rules' );

function globalkeys_platform_query_vars( $vars ) {
	$vars[] = 'gk_platform';
	return $vars;
}
add_filter( 'query_vars', 'globalkeys_platform_query_vars' );

function globalkeys_platform_template_include( $template ) {
	$slug = get_query_var( 'gk_platform' );
	if ( $slug !== '' && $slug !== false ) {
		$platform_template = get_template_directory() . '/template-platform.php';
		if ( file_exists( $platform_template ) ) {
			return $platform_template;
		}
	}
	return $template;
}
add_filter( 'template_include', 'globalkeys_platform_template_include', 5 );

function globalkeys_platform_flush_rewrites() {
	globalkeys_platform_rewrite_rules();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'globalkeys_platform_flush_rewrites' );

function globalkeys_platform_maybe_flush_rewrites() {
	if ( get_option( 'globalkeys_platform_rewrite_flushed' ) ) {
		return;
	}
	globalkeys_platform_rewrite_rules();
	flush_rewrite_rules();
	update_option( 'globalkeys_platform_rewrite_flushed', 1 );
}
add_action( 'init', 'globalkeys_platform_maybe_flush_rewrites', 999 );

function globalkeys_platform_body_class( $classes ) {
	if ( get_query_var( 'gk_platform' ) !== '' && get_query_var( 'gk_platform' ) !== false ) {
		$classes[] = 'gk-platform-page';
	}
	return $classes;
}
add_filter( 'body_class', 'globalkeys_platform_body_class' );

/**
 * Nav-Punkte über der Pill: eigene Seiten (Trending Games, Preorders, Available Soon, Activation, Support).
 */
function globalkeys_nav_section_rewrite_rules() {
	add_rewrite_rule( 'trending-games/?$', 'index.php?gk_nav_section=trending-games', 'top' );
	add_rewrite_rule( 'preorders/?$', 'index.php?gk_nav_section=preorders', 'top' );
	add_rewrite_rule( 'available-soon/?$', 'index.php?gk_nav_section=available-soon', 'top' );
	add_rewrite_rule( 'activation/?$', 'index.php?gk_nav_section=activation', 'top' );
	add_rewrite_rule( 'support/?$', 'index.php?gk_nav_section=support', 'top' );
}
add_action( 'init', 'globalkeys_nav_section_rewrite_rules' );

function globalkeys_nav_section_query_vars( $vars ) {
	$vars[] = 'gk_nav_section';
	return $vars;
}
add_filter( 'query_vars', 'globalkeys_nav_section_query_vars' );

function globalkeys_nav_section_template_include( $template ) {
	$slug = get_query_var( 'gk_nav_section' );
	if ( $slug !== '' && $slug !== false ) {
		$nav_template = get_template_directory() . '/template-nav-section.php';
		if ( file_exists( $nav_template ) ) {
			return $nav_template;
		}
	}
	return $template;
}
add_filter( 'template_include', 'globalkeys_nav_section_template_include', 5 );

/**
 * My Account (eingeloggt): Eigenes Template mit gleicher Struktur wie Plattform.
 * Verhindert Header-Bugs durch identischen DOM-Aufbau.
 */
function globalkeys_my_account_template_include( $template ) {
	if ( function_exists( 'is_account_page' ) && is_account_page() && is_user_logged_in() ) {
		$account_template = get_template_directory() . '/template-my-account.php';
		if ( file_exists( $account_template ) ) {
			return $account_template;
		}
	}
	return $template;
}
add_filter( 'template_include', 'globalkeys_my_account_template_include', 6 );

function globalkeys_my_account_body_class( $classes ) {
	if ( function_exists( 'is_account_page' ) && is_account_page() && is_user_logged_in() ) {
		$classes[] = 'gk-account-dashboard-page';
	}
	return $classes;
}
add_filter( 'body_class', 'globalkeys_my_account_body_class' );


function globalkeys_nav_section_body_class( $classes ) {
	if ( get_query_var( 'gk_nav_section' ) !== '' && get_query_var( 'gk_nav_section' ) !== false ) {
		$classes[] = 'gk-nav-section-page';
	}
	return $classes;
}
add_filter( 'body_class', 'globalkeys_nav_section_body_class' );

function globalkeys_search_results_body_class( $classes ) {
	if ( is_search() && isset( $_GET['post_type'] ) && $_GET['post_type'] === 'product' ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$classes[] = 'gk-search-results-page';
	}
	return $classes;
}
add_filter( 'body_class', 'globalkeys_search_results_body_class' );

function globalkeys_nav_section_maybe_flush_rewrites() {
	if ( get_option( 'globalkeys_nav_section_rewrite_flushed' ) ) {
		return;
	}
	globalkeys_nav_section_rewrite_rules();
	flush_rewrite_rules();
	update_option( 'globalkeys_nav_section_rewrite_flushed', 1 );
}
add_action( 'init', 'globalkeys_nav_section_maybe_flush_rewrites', 998 );

/**
 * Admin-Bar auf Login/Register-Seite ausblenden.
 * wp_body_open wird für Login-Seite ans Seitenende verschoben (header.php/footer.php),
 * damit eingespritzte Inhalte (z. B. Store Notice) nicht oben als Balken erscheinen.
 */
function globalkeys_hide_admin_bar_on_account_login() {
	if ( function_exists( 'is_account_page' ) && is_account_page() && ! is_user_logged_in() ) {
		add_filter( 'show_admin_bar', '__return_false' );
	}
}
add_action( 'wp', 'globalkeys_hide_admin_bar_on_account_login', 1 );

/**
 * Registrierung für neue Kunden aktivieren (Online-Shop).
 *
 * Aktiviert WordPress-Registrierung und WooCommerce-Registrierung auf der
 * My-Account-Seite. Läuft einmalig beim ersten Aufruf nach Theme-Update.
 */
function globalkeys_enable_customer_registration() {
	if ( get_option( 'globalkeys_registration_enabled' ) ) {
		if ( get_option( 'globalkeys_registration_v2' ) ) {
			// V3: Gamertag als Benutzername.
			if ( get_option( 'globalkeys_registration_v3' ) ) {
				return;
			}
		}
	}

	// WordPress: Konto-Erstellung erlauben.
	update_option( 'users_can_register', 1 );

	// WooCommerce: Registrierung auf My-Account-Seite.
	if ( class_exists( 'WooCommerce' ) ) {
		update_option( 'woocommerce_enable_myaccount_registration', 'yes' );
		update_option( 'woocommerce_enable_signup_and_login_from_checkout', 'yes' );
		// Passwort direkt beim Registrieren setzen, keine Bestätigungs-E-Mail.
		update_option( 'woocommerce_registration_generate_password', 'no' );
		// Gamertag als Benutzername.
		update_option( 'woocommerce_registration_generate_username', 'no' );
	}

	update_option( 'globalkeys_registration_enabled', 1 );
	update_option( 'globalkeys_registration_v2', 1 );
	update_option( 'globalkeys_registration_v3', 1 );
}
add_action( 'init', 'globalkeys_enable_customer_registration', 1 );

/**
 * WooCommerce-Registrierung: Vorname und Nachname.
 */
require get_template_directory() . '/inc/woocommerce-registration.php';
require get_template_directory() . '/inc/woocommerce-product-trailer.php';
require get_template_directory() . '/inc/woocommerce-product-preorder.php';
require get_template_directory() . '/inc/woocommerce-product-elden-nightreign.php';
require get_template_directory() . '/inc/woocommerce-product-hero-image.php';
require get_template_directory() . '/inc/gk-product-hover-panel.php';
require get_template_directory() . '/inc/gk-product-platform-badge.php';
require get_template_directory() . '/inc/woocommerce-account-endpoints.php';

/**
 * Produktsuche: is_search() für ?post_type=product (Suche oder Browse all).
 * Wichtig für Header (Suche offen) und Script-Loading.
 */
function globalkeys_product_search_is_search( $query ) {
	if ( ! $query->is_main_query() || is_admin() ) {
		return;
	}
	if ( ! isset( $_GET['post_type'] ) || $_GET['post_type'] !== 'product' ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	$query->is_search = true;
	$s = isset( $_GET['s'] ) ? trim( (string) $_GET['s'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( $s !== '' ) {
		$query->set( 's', sanitize_text_field( wp_unslash( $_GET['s'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}
}
add_action( 'pre_get_posts', 'globalkeys_product_search_is_search', 1 );

/**
 * Produktsuche/Browse: Bei ?post_type=product immer search.php laden.
 */
function globalkeys_force_search_template_for_product_search( $template ) {
	if ( ! isset( $_GET['post_type'] ) || $_GET['post_type'] !== 'product' ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return $template;
	}
	$search_template = get_search_template();
	return $search_template ?: $template;
}
add_filter( 'template_include', 'globalkeys_force_search_template_for_product_search', 20 );

/**
 * Produkte suchen (Name/SKU beginnt mit Begriff) – für Suche + Live-Dropdown.
 *
 * @param string $term Suchbegriff.
 * @param int    $limit Max. Anzahl Produkte (0 = alle).
 * @return array{ids: int[], products: array} IDs und Produktdaten für Dropdown.
 */
function globalkeys_search_products_starts_with( $term, $limit = 0 ) {
	global $wpdb;
	$term = trim( (string) $term );
	if ( $term === '' || ! class_exists( 'WooCommerce' ) ) {
		return array( 'ids' => array(), 'products' => array() );
	}
	$like = $wpdb->esc_like( $term ) . '%';

	$title_ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish' AND post_title LIKE %s",
			$like
		)
	);
	$title_ids = array_map( 'intval', (array) $title_ids );

	$sku_rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT p.ID, p.post_type, p.post_parent FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_sku' AND pm.meta_value LIKE %s
			WHERE p.post_type IN ('product', 'product_variation') AND p.post_status = 'publish'",
			$like
		),
		ARRAY_A
	);
	$sku_ids = array();
	foreach ( (array) $sku_rows as $row ) {
		$sku_ids[] = ( 'product_variation' === ( $row['post_type'] ?? '' ) ) ? (int) $row['post_parent'] : (int) $row['ID'];
	}

	$ids = array_unique( array_filter( array_merge( $title_ids, $sku_ids ) ) );
	if ( function_exists( 'globalkeys_get_preorder_list_product_ids' ) ) {
		$pre = globalkeys_get_preorder_list_product_ids();
		if ( ! empty( $pre ) ) {
			$ids = array_values( array_diff( $ids, $pre ) );
		}
	}
	$total = count( $ids );

	$products = array();
	$take     = $limit > 0 ? min( $limit, count( $ids ) ) : count( $ids );
	$ids_slice = array_slice( $ids, 0, $take );
	foreach ( $ids_slice as $pid ) {
		$product = wc_get_product( $pid );
		if ( ! $product || ! $product->is_visible() ) {
			continue;
		}
		$img_url = function_exists( 'globalkeys_get_product_listing_thumbnail_url' )
			? globalkeys_get_product_listing_thumbnail_url( $product, 'globalkeys-search-dropdown' )
			: ( $product->get_image_id() ? wp_get_attachment_image_url( $product->get_image_id(), 'globalkeys-search-dropdown' ) : wc_placeholder_img_src( 'woocommerce_thumbnail' ) );
		if ( ! $img_url ) {
			$img_url = wc_placeholder_img_src( 'woocommerce_thumbnail' );
		}
		$products[] = array(
			'id'    => $product->get_id(),
			'name'  => $product->get_name(),
			'url'   => $product->get_permalink(),
			'price' => wp_strip_all_tags( wc_price( $product->get_price() ) ),
			'image' => $img_url,
		);
	}
	return array( 'ids' => $ids, 'products' => $products, 'total' => $total );
}

/**
 * AJAX: Live-Produktsuche für Header-Dropdown.
 * Bei leerem Suchbegriff: 5 zufällige Produkte anzeigen.
 */
function globalkeys_ajax_search_products() {
	check_ajax_referer( 'gk_search_products', 'nonce' );
	$term = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';
	$term = trim( $term );
	if ( $term === '' && class_exists( 'WooCommerce' ) ) {
		$rand_args = array(
			'status'  => 'publish',
			'limit'   => 5,
			'orderby' => 'rand',
		);
		if ( function_exists( 'globalkeys_wc_product_args_exclude_preorders' ) ) {
			$rand_args = globalkeys_wc_product_args_exclude_preorders( $rand_args );
		}
		$products = wc_get_products( $rand_args );
		$list = array();
		foreach ( $products as $product ) {
			if ( ! $product || ! $product->is_visible() ) {
				continue;
			}
			$img_url = function_exists( 'globalkeys_get_product_listing_thumbnail_url' )
				? globalkeys_get_product_listing_thumbnail_url( $product, 'globalkeys-search-dropdown' )
				: ( $product->get_image_id() ? wp_get_attachment_image_url( $product->get_image_id(), 'globalkeys-search-dropdown' ) : wc_placeholder_img_src( 'woocommerce_thumbnail' ) );
			$list[] = array(
				'id'    => $product->get_id(),
				'name'  => $product->get_name(),
				'url'   => $product->get_permalink(),
				'price' => wp_strip_all_tags( wc_price( $product->get_price() ) ),
				'image' => $img_url ?: wc_placeholder_img_src( 'woocommerce_thumbnail' ),
			);
		}
		$total = function_exists( 'wc_get_products' ) ? count( wc_get_products( array( 'status' => 'publish', 'limit' => -1, 'return' => 'ids' ) ) ) : 0;
		wp_send_json_success( array( 'ids' => array(), 'products' => $list, 'total' => $total ) );
		return;
	}
	$result = globalkeys_search_products_starts_with( $term, 5 );
	wp_send_json_success( $result );
}
add_action( 'wp_ajax_gk_search_products', 'globalkeys_ajax_search_products' );
add_action( 'wp_ajax_nopriv_gk_search_products', 'globalkeys_ajax_search_products' );

/**
 * AJAX: Produktkarten-HTML für Live-Update auf der Suchseite.
 */
function globalkeys_ajax_search_results_html() {
	check_ajax_referer( 'gk_search_products', 'nonce' );
	$term = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';
	if ( $term === '' || ! class_exists( 'WooCommerce' ) ) {
		wp_send_json_success( array( 'html' => '', 'noResults' => true, 'total' => 0 ) );
	}
	$result  = globalkeys_search_products_starts_with( $term, 0 );
	$ids     = isset( $result['ids'] ) ? $result['ids'] : array();
	$total   = isset( $result['total'] ) ? $result['total'] : 0;
	$no_res  = empty( $ids );
	$html    = '';
	if ( ! $no_res ) {
		$query = new WP_Query(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'post__in'       => $ids,
				'orderby'        => 'post_title',
				'order'          => 'ASC',
				'posts_per_page' => -1,
			)
		);
		ob_start();
		while ( $query->have_posts() ) {
			$query->the_post();
			$product = wc_get_product( get_the_ID() );
			if ( $product && $product->is_visible() ) {
				$GLOBALS['product'] = $product;
				get_template_part( 'template-parts/product-card', 'bestseller' );
			}
		}
		$html = ob_get_clean();
		wp_reset_postdata();
	}
	wp_send_json_success( array( 'html' => $html, 'noResults' => $no_res, 'total' => $total ) );
}
add_action( 'wp_ajax_gk_search_results_html', 'globalkeys_ajax_search_results_html' );
add_action( 'wp_ajax_nopriv_gk_search_results_html', 'globalkeys_ajax_search_results_html' );

/**
 * Produktdaten + Karten-HTML für sofortige clientseitige Filterung auf der Suchseite.
 *
 * @return array{index: array, cards: array}|null
 */
function globalkeys_get_search_products_data_for_js() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return null;
	}
	$ids = wc_get_products( array(
		'status'  => 'publish',
		'limit'   => -1,
		'return'  => 'ids',
		'type'    => array( 'simple', 'variable' ),
	) );
	if ( empty( $ids ) ) {
		return array( 'index' => array(), 'cards' => array(), 'dropdown' => array() );
	}
	$pre_ids = function_exists( 'globalkeys_get_preorder_list_product_ids' ) ? globalkeys_get_preorder_list_product_ids() : array();
	$index      = array();
	$cards      = array();
	$id_to_name = array();
	$dropdown   = array();
	$prices     = array();
	foreach ( $ids as $pid ) {
		if ( ! empty( $pre_ids ) && in_array( (int) $pid, $pre_ids, true ) ) {
			continue;
		}
		$product = wc_get_product( $pid );
		if ( ! $product || ! $product->is_visible() ) {
			continue;
		}
		$name = $product->get_name();
		$sku  = $product->get_sku();
		$id_to_name[ (int) $pid ] = mb_strtolower( $name, 'UTF-8' );
		$index[] = array(
			'id' => (int) $pid,
			'n'  => mb_strtolower( $name, 'UTF-8' ),
			's'  => mb_strtolower( (string) $sku, 'UTF-8' ),
		);
		$img_url = function_exists( 'globalkeys_get_product_listing_thumbnail_url' )
			? globalkeys_get_product_listing_thumbnail_url( $product, 'globalkeys-search-dropdown' )
			: ( $product->get_image_id() ? wp_get_attachment_image_url( $product->get_image_id(), 'globalkeys-search-dropdown' ) : ( function_exists( 'wc_placeholder_img_src' ) ? wc_placeholder_img_src( 'woocommerce_thumbnail' ) : '' ) );
		$price_num = (float) $product->get_price();
		$dropdown[ (int) $pid ] = array(
			'id'    => (int) $pid,
			'name'  => $name,
			'url'   => $product->get_permalink(),
			'price' => wp_strip_all_tags( wc_price( $product->get_price() ) ),
			'image' => $img_url ?: ( function_exists( 'wc_placeholder_img_src' ) ? wc_placeholder_img_src( 'woocommerce_thumbnail' ) : '' ),
		);
		$prices[ (int) $pid ] = $price_num;
		ob_start();
		$GLOBALS['product'] = $product;
		get_template_part( 'template-parts/product-card', 'bestseller' );
		$cards[ (int) $pid ] = ob_get_clean();
		if ( $product->is_type( 'variable' ) ) {
			foreach ( $product->get_children() as $var_id ) {
				$var = wc_get_product( $var_id );
				if ( ! $var ) {
					continue;
				}
				$var_sku = $var->get_sku();
				if ( $var_sku !== '' && $var_sku !== null ) {
					$index[] = array(
						'id' => (int) $pid,
						'n'  => '',
						's'  => mb_strtolower( (string) $var_sku, 'UTF-8' ),
					);
				}
			}
		}
	}
	return array( 'index' => $index, 'cards' => $cards, 'names' => $id_to_name, 'dropdown' => $dropdown, 'prices' => $prices );
}

require get_template_directory() . '/inc/profile-avatar.php';
require get_template_directory() . '/inc/email-verification.php';

/**
 * HTTPS erzwingen für Account- und Verifizierungsseiten.
 * Bitdefender und andere Security-Tools blockieren Login-Formulare über HTTP
 * („PasswordStealer“-Warnung). Mit HTTPS verschwindet die Meldung.
 */
function globalkeys_force_https_account_pages() {
	if ( is_admin() ) {
		return;
	}
	$site_url = get_option( 'siteurl' );
	if ( strpos( $site_url, 'https://' ) !== 0 ) {
		return;
	}
	$is_https = ( ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' )
		|| ( ! empty( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' );
	if ( ! $is_https ) {
		$is_account = function_exists( 'is_account_page' ) && is_account_page();
		$is_verify  = get_query_var( 'gk_verify' );
		if ( $is_account || $is_verify ) {
			$redirect = 'https://' . ( isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '' ) . ( isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '' );
			wp_safe_redirect( $redirect, 301 );
			exit;
		}
	}
}
add_action( 'template_redirect', 'globalkeys_force_https_account_pages', 1 );

/**
 * Security-Header für Login/Account-Seiten – reduziert False-Positives bei AV-Scannern.
 */
function globalkeys_account_security_headers() {
	$is_account = function_exists( 'is_account_page' ) && is_account_page() && ! is_user_logged_in();
	$is_verify  = get_query_var( 'gk_verify' );
	if ( $is_account || $is_verify ) {
		header( 'X-Content-Type-Options: nosniff' );
		header( 'X-Frame-Options: SAMEORIGIN' );
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	}
}
add_action( 'send_headers', 'globalkeys_account_security_headers' );

/**
 * Bei Login-Fehler: Redirect + Transient (gleicher Ansatz wie Register-Block).
 */
function globalkeys_login_failed_redirect() {
	if ( ! function_exists( 'wc_get_notices' ) || ! function_exists( 'wc_clear_notices' ) ) {
		return;
	}
	$notices = wc_get_notices( 'error' );
	$msg    = __( 'Ungültige E-Mail oder Passwort.', 'globalkeys' );
	if ( ! empty( $notices ) ) {
		$last = end( $notices );
		$msg  = isset( $last['notice'] ) ? wp_strip_all_tags( $last['notice'] ) : $msg;
	}
	set_transient( 'gk_login_error', $msg, 60 );
	if ( ! empty( $_POST['username'] ) ) {
		set_transient( 'gk_login_keep_username', sanitize_text_field( wp_unslash( $_POST['username'] ) ), 60 );
	}
	wc_clear_notices();
	wp_safe_redirect( wc_get_page_permalink( 'myaccount' ) );
	exit;
}
add_action( 'woocommerce_login_failed', 'globalkeys_login_failed_redirect', 5 );

/**
 * Nach Logout (Drawer, Dashboard, Pill) immer auf die Startseite leiten.
 */
add_filter( 'woocommerce_logout_redirect', function( $redirect ) {
	return home_url( '/' );
} );

/**
 * Währung immer rechts vom Preis (Euro, USD etc.) – site-weit.
 */
add_filter( 'pre_option_woocommerce_currency_pos', function( $value ) {
	return 'right_space';
} );

/**
 * Zuletzt angesehene Produkte: Cookie setzen beim Aufruf einer Produktseite.
 */
function globalkeys_track_recently_viewed_product() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	$product_id = get_the_ID();
	if ( ! $product_id ) {
		return;
	}
	$cookie_name = 'gk_recently_viewed';
	$max         = 12;
	$ids         = array();
	if ( ! empty( $_COOKIE[ $cookie_name ] ) ) {
		$ids = array_map( 'absint', array_filter( explode( ',', sanitize_text_field( wp_unslash( $_COOKIE[ $cookie_name ] ) ) ) ) );
	}
	$ids = array_diff( $ids, array( $product_id ) );
	array_unshift( $ids, $product_id );
	$ids = array_slice( array_unique( $ids ), 0, $max );
	setcookie( $cookie_name, implode( ',', $ids ), time() + ( 30 * DAY_IN_SECONDS ), '/', '', is_ssl(), true );
}
add_action( 'template_redirect', 'globalkeys_track_recently_viewed_product', 20 );

/**
 * Bei Registrierungs-Fehler: Redirect mit Transient für Custom-Modal.
 * Bei reinen Passwort-Fehlern: Gamertag und E-Mail beibehalten (nur Passwort leeren).
 */
function globalkeys_register_failed_redirect() {
	if ( ! function_exists( 'wc_get_notices' ) || ! function_exists( 'wc_clear_notices' ) ) {
		return;
	}
	if ( ! is_account_page() || is_user_logged_in() ) {
		return;
	}
	// Nur nach Registrierungs-POST mit Fehlern
	if ( empty( $_POST['register'] ) || empty( $_POST['email'] ) ) {
		return;
	}
	$notices = wc_get_notices( 'error' );
	if ( empty( $notices ) ) {
		return;
	}
	$last    = end( $notices );
	$msg     = isset( $last['notice'] ) ? wp_strip_all_tags( $last['notice'] ) : __( 'Bei der Registrierung ist ein Fehler aufgetreten.', 'globalkeys' );
	$is_pw   = ( false !== stripos( $msg, 'passwort' ) || false !== stripos( $msg, 'password' ) );
	$is_tag  = ( false !== stripos( $msg, 'Gamertag' ) || false !== stripos( $msg, 'gamertag' ) );
	$is_mail = ( false !== stripos( $msg, 'E-Mail' ) || false !== stripos( $msg, 'E-Mail-Adresse' ) );
	$is_terms = ( false !== stripos( $msg, 'Nutzungsbedingungen' ) || false !== stripos( $msg, 'Datenschutz' ) || false !== stripos( $msg, 'stimme' ) );

	set_transient( 'gk_register_error', $msg, 60 );
	// Passwort/Terms: alles behalten. Gamertag-Fehler: nur E-Mail. E-Mail-Fehler: nur Gamertag.
	$keep_user = $is_pw || $is_mail || $is_terms;
	$keep_mail = $is_pw || $is_tag || $is_terms;
	if ( $keep_user && ! empty( $_POST['username'] ) ) {
		set_transient( 'gk_register_keep_username', sanitize_text_field( wp_unslash( $_POST['username'] ) ), 60 );
	}
	if ( $keep_mail && ! empty( $_POST['email'] ) ) {
		set_transient( 'gk_register_keep_email', sanitize_email( wp_unslash( $_POST['email'] ) ), 60 );
	}
	wc_clear_notices();
	wp_safe_redirect( wc_get_page_permalink( 'myaccount' ) . '#register' );
	exit;
}
add_action( 'template_redirect', 'globalkeys_register_failed_redirect', 5 );

/**
 * Enqueue scripts and styles.
 */
function globalkeys_scripts() {
	$style_version = file_exists( get_stylesheet_directory() . '/style.css' )
		? filemtime( get_stylesheet_directory() . '/style.css' )
		: _S_VERSION;
	wp_enqueue_style( 'globalkeys-style', get_stylesheet_uri(), array(), $style_version );
	wp_style_add_data( 'globalkeys-style', 'rtl', 'replace' );

	// Account-Icon: rechts positionieren, My-account-Text ausblenden (übersteuert Cache/Plugins).
	$account_css = '
		#masthead .header-pill-nav li.gk-hide-myaccount,
		#masthead .header-pill-nav li:has(a[href*="my-account"]),
		#masthead .header-pill-nav li:has(a[href*="myaccount"]) {
			display: none !important;
		}
		.gk-gamertag-error {
			display: block;
			color: #b32d2e;
			font-size: 0.9em;
			font-weight: 600;
			margin-bottom: 0.5em;
			padding: 0.4em 0.6em;
			background: #fef2f2;
			border-left: 3px solid #b32d2e;
			border-radius: 3px;
		}
		.gk-gamertag-row input[aria-invalid="true"],
		.gk-email-row input[aria-invalid="true"] {
			border-color: #b32d2e;
			box-shadow: 0 0 0 1px #b32d2e;
		}
		/* My Account Login: Sidebar und Footer ausblenden (Dashboard zeigt Footer) */
		body.gk-account-login #secondary,
		body.gk-account-login #colophon {
			display: none !important;
		}
		html.gk-account-login-page {
			margin: 0 !important;
			padding: 0 !important;
			background: #1a193f;
			border: none !important;
			outline: none !important;
			box-shadow: none !important;
		}
		html.gk-account-login-page.admin-bar {
			margin-top: 0 !important;
		}
		html.gk-account-login-page.admin-bar #wpadminbar {
			display: none !important;
		}
		html.gk-account-login-page,
		body.gk-account-login {
			overflow: hidden !important;
			height: 100vh;
			margin: 0 !important;
			padding: 0 !important;
		}
		html.gk-account-login-page::-webkit-scrollbar,
		body.gk-account-login::-webkit-scrollbar {
			display: none;
		}
		html.gk-account-login-page,
		body.gk-account-login {
			-ms-overflow-style: none;
			scrollbar-width: none;
		}
		body.gk-account-login .gk-account-logo-header,
		body.gk-account-login .gk-account-logo-header .site-header-inner {
			border: none !important;
			box-shadow: none !important;
		}
		body.gk-account-login .gk-account-logo-header .site-header-inner {
			padding-top: 1rem;
			padding-bottom: 0.75rem;
			padding-left: 1.75rem;
			padding-right: 1.75rem;
			display: flex;
			align-items: flex-start;
			justify-content: space-between;
		}
		body.gk-account-login .skip-link {
			display: none !important;
		}
		body.gk-account-login .gk-account-close {
			position: relative;
			top: auto;
			right: auto;
			left: auto;
			z-index: 10;
			display: flex;
			align-items: center;
			justify-content: center;
			width: 2rem;
			height: 2rem;
			color: #fff;
			text-decoration: none;
			transition: color 0.2s ease;
			flex-shrink: 0;
			cursor: pointer;
		}
		body.gk-account-login .gk-account-close:hover {
			color: #04DA8D;
		}
		body.gk-account-login .gk-account-close svg {
			width: 32px;
			height: 32px;
		}
		body.gk-account-login .woocommerce-store-notice,
		body.gk-account-login p.demo_store {
			display: none !important;
		}
		body.gk-account-login {
			background: #1a193f;
			border: none !important;
			outline: none !important;
		}
		body.gk-account-login #page,
		body.gk-account-login .site-main,
		body.gk-account-login .woocommerce {
			margin: 0 !important;
			padding: 0 !important;
			border: none !important;
			box-shadow: none !important;
		}
		/* My Account Login: 2-Spalten-Layout (Form links, Bild rechts) */
		body.gk-account-login .site-main {
			max-width: none;
		}
		body.gk-account-login .entry-header,
		body.gk-account-login .entry-footer {
			display: none;
		}
		body.gk-account-login .entry-content {
			padding: 0;
			max-width: none;
		}
		body.gk-account-login article {
			max-width: none;
			padding: 0;
		}
		body.gk-account-login .woocommerce {
			max-width: none;
			padding: 0;
		}
		body.gk-account-login .gk-account-split {
			display: grid;
			grid-template-columns: 1fr 1fr;
			position: fixed;
			inset: 0;
			width: 100%;
			height: 100%;
			min-height: 100vh;
			overflow: hidden;
			margin: 0;
			align-items: stretch;
			border: none !important;
			box-shadow: none !important;
			z-index: 0;
		}
		body.gk-account-login .gk-account-form-col {
			background: #1a193f;
			border: none !important;
			box-shadow: none !important;
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 3rem 4rem;
			clip-path: polygon(0 0, 100% 0, 89% 100%, 0 100%);
			position: relative;
			z-index: 2;
			min-height: 100%;
		}
		body.gk-account-login .gk-account-form-col,
		body.gk-account-login .gk-account-image-col {
			align-self: stretch;
		}
		body.gk-account-login .gk-account-blocks {
			width: 100%;
			max-width: 480px;
			margin: 3rem auto 0;
			box-sizing: border-box;
			position: relative;
			z-index: 5;
		}
		body.gk-account-login .gk-account-title {
			color: #fff;
			font-size: 1.75rem;
			font-weight: 700;
			margin: 0 0 1.5rem;
		}
		body.gk-account-login .gk-login-block,
		body.gk-account-login .gk-register-block {
			margin-bottom: 0;
		}
		/* Nur ein Kasten sichtbar: Standard = Login */
		body.gk-account-login .gk-login-block {
			position: relative;
			z-index: 2;
		}
		body.gk-account-login .gk-register-block {
			display: none;
		}
		body.gk-account-login .gk-account-blocks.gk-show-register .gk-login-block {
			display: none;
		}
		body.gk-account-login .gk-account-blocks.gk-show-register .gk-register-block {
			display: block;
			position: relative;
			z-index: 2;
			margin-top: 0.5rem;
		}
		/* Login-Box: Rahmen nur außen, innen keine Borders */
		body.gk-account-login .gk-login-box form,
		body.gk-account-login .gk-login-box form p,
		body.gk-account-login .gk-login-box .woocommerce-form-login,
		body.gk-account-login .gk-login-box input:not(.gk-terms-checkbox-input),
		body.gk-account-login .gk-login-box button {
			border: none !important;
			border-width: 0 !important;
			outline: none !important;
			box-shadow: none !important;
		}
		body.gk-account-login .gk-login-box {
			background: transparent;
			border: none;
			border-radius: 0;
			padding: 0;
			width: 100%;
			box-sizing: border-box;
		}
		body.gk-account-login .gk-login-box-title {
			color: #fff;
			font-size: 1.55rem;
			font-weight: 600;
			margin: 0 0 1.15rem;
			padding: 0;
			text-align: left;
		}
		body.gk-account-login .gk-login-box .woocommerce-form-login {
			margin: 0;
			padding: 0;
			max-width: 100%;
		}
		body.gk-account-login .gk-login-box .form-row,
		body.gk-account-login .gk-login-box .gk-login-row,
		body.gk-account-login .gk-login-box .gk-login-submit-row,
		body.gk-account-login .gk-login-box .gk-login-links-row {
			margin-left: 0 !important;
			margin-right: 0 !important;
			padding-left: 0 !important;
			padding-right: 0 !important;
			width: 100% !important;
			max-width: 100% !important;
		}
		body.gk-account-login .gk-social-placeholders {
			display: flex;
			flex-wrap: wrap;
			gap: 0.5rem;
			margin-bottom: 1.15rem;
			width: 100%;
		}
		body.gk-account-login .gk-social-placeholders > * {
			flex: 1;
			min-width: 0;
			height: 44px;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			background: #0e0d1e;
			border: 1px solid rgba(180, 180, 190, 0.35);
			border-radius: 5px;
			color: #fff;
			text-decoration: none;
			font-size: 0.9rem;
			transition: border-color 0.2s ease, background 0.2s ease;
		}
		body.gk-account-login .gk-social-placeholders > *:hover {
			border-color: rgba(4, 218, 141, 0.5);
			background: rgba(4, 218, 141, 0.08);
		}
		body.gk-account-login .gk-social-placeholders > a img,
		body.gk-account-login .gk-social-placeholders > * img {
			max-height: 22px;
			width: auto;
			vertical-align: middle;
		}
		body.gk-account-login .gk-divider-oder {
			display: flex;
			align-items: center;
			gap: 1rem;
			margin-bottom: 1.15rem;
			width: 100%;
		}
		body.gk-account-login .gk-divider-line {
			flex: 1;
			height: 1px;
			background: rgba(180, 180, 190, 0.35);
		}
		body.gk-account-login .gk-divider-text {
			color: rgba(255, 255, 255, 0.7);
			font-size: 1rem;
			white-space: nowrap;
		}
		body.gk-account-login .gk-divider-register {
			margin-top: 1.25rem;
			margin-bottom: 1.1rem;
		}
		body.gk-account-login .gk-login-box .gk-login-row {
			margin-bottom: 0.4rem;
			width: 100%;
		}
		body.gk-account-login .gk-login-box .gk-login-row label {
			display: block;
			color: rgba(255, 255, 255, 0.65);
			font-size: 1rem;
			margin-bottom: 0.3rem;
		}
		body.gk-account-login .gk-login-box .gk-login-row label .required {
			color: #04DA8D !important;
		}
		body.gk-account-login .gk-login-box .gk-login-row .input-text {
			background: #0e0d1e !important;
			border: 1px solid rgba(180, 180, 190, 0.35) !important;
			outline: none !important;
			box-shadow: none !important;
			color: #fff;
			padding: 1.15rem 1.15rem;
			border-radius: 5px;
			width: 100%;
			font-size: 1.15rem;
			box-sizing: border-box;
			min-height: 52px;
			transition: border-color 0.2s ease;
		}
		body.gk-account-login .gk-login-box .gk-login-row .input-text::placeholder {
			color: rgba(255, 255, 255, 0.25);
			font-size: 1.1rem;
		}
		body.gk-account-login .gk-login-box .gk-login-row .input-text:focus,
		body.gk-account-login .gk-login-box .gk-login-row .input-text:active {
			border: 1px solid rgba(180, 180, 190, 0.35) !important;
			outline: none !important;
			box-shadow: none !important;
		}
		body.gk-account-login .gk-login-box .gk-login-row .input-text:-webkit-autofill,
		body.gk-account-login .gk-login-box .gk-login-row .input-text:-webkit-autofill:hover,
		body.gk-account-login .gk-login-box .gk-login-row .input-text:-webkit-autofill:focus,
		body.gk-account-login .gk-login-box .gk-login-row .input-text:-webkit-autofill:active {
			-webkit-box-shadow: 0 0 0 1000px #0e0d1e inset !important;
			box-shadow: 0 0 0 1000px #0e0d1e inset !important;
			-webkit-text-fill-color: #fff !important;
			caret-color: #fff;
		}
		body.gk-account-login .gk-login-box .gk-login-row .input-text:hover {
			border-color: #04DA8D !important;
			outline: none !important;
			box-shadow: none !important;
		}
		body.gk-account-login .gk-login-box .gk-login-row .input-text:focus {
			border-color: #04DA8D !important;
		}
		body.gk-account-login .gk-login-box .gk-password-input-wrap {
			position: relative;
			display: block;
		}
		body.gk-account-login .gk-login-box .gk-password-input-wrap .input-text {
			padding-right: 3rem !important;
		}
		body.gk-account-login .gk-login-box .gk-password-toggle {
			position: absolute;
			right: 0.85rem;
			top: 50%;
			transform: translateY(-50%);
			background: #0e0d1e;
			border: 1px solid rgba(180, 180, 190, 0.35);
			border-radius: 3px;
			cursor: pointer;
			padding: 0.35rem;
			color: rgba(255,255,255,0.6);
			transition: color 0.2s ease, border-color 0.2s ease;
			z-index: 20;
			pointer-events: auto !important;
			min-width: 36px;
			min-height: 36px;
		}
		body.gk-account-login .gk-login-box .gk-password-toggle:hover {
			color: #04DA8D;
			border-color: #04DA8D;
		}
		body.gk-account-login .gk-login-box .gk-btn-login {
			background: linear-gradient(90deg, #04DA8D 0%, #028a5a 100%) !important;
			color: #fff !important;
			border: none !important;
			outline: none !important;
			box-shadow: none !important;
			padding: 1rem 1.15rem !important;
			font-weight: 600 !important;
			font-size: 1.05rem !important;
			line-height: 1.2 !important;
			border-radius: 5px !important;
			width: 100%;
			box-sizing: border-box;
			cursor: pointer;
			transition: transform 0.2s ease;
		}
		body.gk-account-login .gk-login-box .gk-btn-login:hover:not(:disabled) {
			transform: translateY(-2px);
		}
		body.gk-account-login .gk-login-box .gk-btn-login:disabled {
			background: rgba(4, 218, 141, 0.18) !important;
			border: none !important;
			color: rgba(255, 255, 255, 0.85) !important;
			cursor: not-allowed;
		}
		body.gk-account-login .gk-login-box .gk-btn-login:disabled:hover {
			background: rgba(4, 218, 141, 0.18) !important;
			border: none !important;
		}
		body.gk-account-login .gk-divider-line-only {
			width: 100%;
			height: 1px;
			background: rgba(180, 180, 190, 0.35);
			margin: 1.35rem 0 1.5rem;
		}
		body.gk-account-login .gk-login-block .gk-divider-line-only {
			margin-top: 0.85rem;
		}
		body.gk-account-login .gk-register-block .gk-divider-line-only {
			margin-top: 0.85rem;
		}
		body.gk-account-login .gk-register-block .gk-divider-register {
			margin-top: 1.5rem;
			margin-bottom: 0.5rem;
		}
		body.gk-account-login .gk-register-block .gk-register-btn-row {
			margin: 0.5rem 0 0;
		}
		body.gk-account-login .gk-register-block .gk-terms-checkbox-row {
			margin-top: 0.4rem !important;
		}
		body.gk-account-login .gk-login-box .gk-login-submit-row {
			margin: 0 0 0.4rem;
		}
		body.gk-account-login .gk-login-box .gk-register-btn-row {
			margin: 1rem 0 0;
		}
		body.gk-account-login .gk-login-box .gk-btn-register {
			display: block;
			width: 100%;
			box-sizing: border-box;
			padding: 1rem 1.15rem !important;
			font-weight: 600 !important;
			font-size: 1.05rem !important;
			line-height: 1.2 !important;
			border-radius: 5px !important;
			text-align: center;
			text-decoration: none !important;
			background: linear-gradient(90deg, #04DA8D 0%, #028a5a 100%) !important;
			color: #fff !important;
			border: none;
			cursor: pointer;
			transition: transform 0.2s ease;
		}
		body.gk-account-login .gk-login-box .gk-btn-register:hover {
			transform: translateY(-2px);
			color: #fff !important;
		}
		body.gk-account-login .gk-login-box .gk-login-links-row {
			display: flex;
			justify-content: flex-end;
			align-items: center;
			flex-wrap: wrap;
			gap: 0.5rem;
			margin: 0 0 0;
			font-size: 1rem;
			width: 100%;
		}
		body.gk-account-login .gk-login-box .gk-login-links-row a {
			position: relative;
			color: rgba(255, 255, 255, 0.78);
			text-decoration: none;
			transition: color 0.2s ease;
		}
		body.gk-account-login .gk-login-box .gk-login-links-row a::after {
			content: "";
			position: absolute;
			left: 0;
			bottom: -0.2em;
			width: 100%;
			height: 1px;
			background: #04DA8D;
			border-radius: 7px;
			transform: scaleX(0);
			transform-origin: left;
			transition: transform 0.25s ease;
		}
		body.gk-account-login .gk-login-box .gk-login-links-row a:hover {
			color: #04DA8D;
		}
		body.gk-account-login .gk-login-box .gk-login-links-row a:hover::after {
			transform: scaleX(1);
		}
		body.gk-account-login .gk-login-box .woocommerce-password-strength {
			display: none !important;
		}
		body.gk-account-login .gk-login-box .gk-password-hint {
			display: block;
			margin-top: 0.35rem;
			color: rgba(255, 255, 255, 0.7);
			font-size: 0.95rem;
			opacity: 0;
			max-height: 0;
			overflow: hidden;
			transition: opacity 0.2s ease, max-height 0.2s ease;
		}
		body.gk-account-login .gk-login-box .gk-password-wrap:focus-within .gk-password-hint {
			opacity: 1;
			max-height: 2em;
		}
		/* Terms/Privacy-Checkbox Registrierung */
		body.gk-account-login .gk-terms-checkbox-row {
			margin: 1rem 0 1.25rem !important;
		}
		body.gk-account-login .gk-terms-checkbox-label {
			display: flex;
			flex-direction: row;
			flex-wrap: nowrap;
			align-items: center;
			gap: 0.75rem;
			cursor: pointer;
			color: rgba(255, 255, 255, 0.78);
			font-size: 1rem;
			line-height: 1.4;
			position: relative;
		}
		body.gk-account-login .gk-terms-checkbox-inner {
			display: inline-flex;
			align-items: center;
			position: relative;
			flex-shrink: 0;
		}
		body.gk-account-login .gk-terms-checkbox-input {
			position: absolute;
			left: 0;
			top: 0;
			width: 20px;
			height: 20px;
			margin: 0;
			padding: 0;
			opacity: 0;
			cursor: pointer;
			z-index: 2;
			pointer-events: auto;
		}
		body.gk-account-login .gk-terms-checkbox-box {
			display: block;
			width: 20px;
			height: 20px;
			background: #0e0d1e;
			border: 1px solid transparent;
			border-radius: 3px;
			transition: border-color 0.2s ease;
			flex-shrink: 0;
		}
		body.gk-account-login .gk-terms-checkbox-label:hover .gk-terms-checkbox-box,
		body.gk-account-login .gk-terms-checkbox-inner:hover .gk-terms-checkbox-box,
		body.gk-account-login .gk-terms-checkbox-input:hover + .gk-terms-checkbox-box,
		body.gk-account-login .gk-terms-checkbox-input:focus + .gk-terms-checkbox-box {
			border-color: #04DA8D;
		}
		body.gk-account-login .gk-terms-checkbox-input:checked + .gk-terms-checkbox-box {
			border-color: #04DA8D;
			background-image: url("data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%2304DA8D%22 stroke-width=%222.5%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22%3E%3Cpolyline points=%2220 6 9 17 4 12%22/%3E%3C/svg%3E");
			background-size: 14px;
			background-position: center;
			background-repeat: no-repeat;
		}
		body.gk-account-login .gk-terms-checkbox-text {
			display: inline-block;
			line-height: 20px;
			vertical-align: middle;
			transform: translate(4px, -4px);
		}
		body.gk-account-login .gk-terms-checkbox-text .gk-terms-link {
			color: #04DA8D;
			text-decoration: underline;
			text-underline-offset: 2px;
		}
		body.gk-account-login .gk-terms-checkbox-text .gk-terms-link:hover {
			color: #05f0a0;
		}
		body.gk-account-login .gk-account-image-col {
			background: #202020;
			min-height: 100%;
			position: relative;
			clip-path: polygon(11% 0, 100% 0, 100% 100%, 0 100%);
			margin-left: -5%;
			z-index: 1;
		}
		body.gk-account-login .gk-account-image-col::before {
			content: "";
			position: absolute;
			left: 10.5%;
			top: 0;
			bottom: 0;
			width: 2px;
			background: rgba(255, 255, 255, 0.26);
			transform: skewX(-6.3deg);
			transform-origin: center;
			pointer-events: none;
			z-index: 2;
		}
		body.gk-account-login .gk-account-login-video {
			position: absolute;
			inset: 0;
			width: 100%;
			height: 100%;
			object-fit: cover;
			z-index: 1;
		}
		body.gk-account-login .gk-account-image-placeholder {
			position: absolute;
			inset: 0;
			background: linear-gradient(135deg, #2d2d2d 0%, #1a1a1a 50%, #0d0d0d 100%);
			background-size: cover;
			background-position: center;
			z-index: 0;
		}
		@media (max-width: 768px) {
			body.gk-account-login .gk-account-split {
				grid-template-columns: 1fr;
			}
			body.gk-account-login .gk-account-form-col {
				clip-path: none;
			}
			body.gk-account-login .gk-account-image-col {
				min-height: 200px;
				clip-path: none;
				margin-left: 0;
			}
			body.gk-account-login .gk-account-image-col::before {
				display: none;
			}
		}
		/* Login-Fehlermeldung: Modal-Popup */
		.gk-login-error-modal {
			position: fixed;
			inset: 0;
			z-index: 99999;
			display: flex;
			align-items: center;
			justify-content: center;
			opacity: 0;
			visibility: hidden;
			transition: opacity 0.2s, visibility 0.2s;
		}
		.gk-login-error-modal--visible {
			opacity: 1;
			visibility: visible;
		}
		.gk-login-error-modal__backdrop {
			position: absolute;
			inset: 0;
			background: rgba(0,0,0,0.5);
			cursor: pointer;
		}
		.gk-login-error-modal__box {
			position: relative;
			background: linear-gradient(180deg, #2a264f 0%, #1e1b3d 100%);
			border-radius: 8px;
			padding: 2rem 2.5rem;
			max-width: 360px;
			width: 90%;
			box-shadow: 0 20px 60px rgba(0,0,0,0.5);
			border: 1px solid rgba(255,255,255,0.08);
		}
		.gk-login-error-modal__close {
			position: absolute;
			top: 1rem;
			right: 1rem;
			background: none;
			border: none;
			color: rgba(255,255,255,0.7);
			font-size: 1.5rem;
			line-height: 1;
			cursor: pointer;
			padding: 0.25rem;
		}
		.gk-login-error-modal__close:hover {
			color: #fff;
		}
		.gk-login-error-modal__icon {
			display: flex;
			justify-content: center;
			margin-bottom: 1rem;
		}
		.gk-login-error-modal__icon svg {
			width: 56px;
			height: 56px;
			color: #dc2626;
			filter: drop-shadow(0 0 0 4px rgba(220,38,38,0.25));
		}
		.gk-login-error-modal__title {
			color: #fff;
			font-size: 1.1rem;
			font-weight: 500;
			text-align: center;
			margin: 0 0 1.5rem;
			line-height: 1.4;
		}
		.gk-login-error-modal__ok {
			display: block;
			width: 100%;
			background: linear-gradient(90deg, #04DA8D 0%, #028a5a 100%) !important;
			color: #fff !important;
			border: none !important;
			padding: 1rem 1.15rem !important;
			font-weight: 600 !important;
			font-size: 1.05rem !important;
			border-radius: 5px !important;
			cursor: pointer;
		}
		.gk-login-error-modal__ok:hover {
			opacity: 0.95;
		}
		/* E-Mail-Verifizierungsseite: Clean, Header/Footer aus, Inhalt zentriert */
		body.gk-verify-page #masthead,
		body.gk-verify-page #secondary,
		body.gk-verify-page #colophon {
			display: none !important;
		}
		body.gk-verify-page {
			background: #1a193f;
			min-height: 100vh;
		}
		body.gk-verify-page .gk-verify-logo {
			position: fixed;
			top: 1.1rem;
			left: 1.25rem;
			z-index: 100;
			pointer-events: none;
		}
		body.gk-verify-page .gk-verify-logo-img {
			display: block;
			max-height: 2.5rem;
			width: auto;
			height: auto;
		}
		body.gk-verify-page #page {
			min-height: 100vh;
			display: flex;
			flex-direction: column;
		}
		body.gk-verify-page .site-main.gk-verify-main {
			flex: 1;
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 2rem;
			margin: 0;
			min-height: 0;
		}
		body.gk-verify-page .gk-verify-centering {
			width: 100%;
			max-width: 460px;
		}
		body.gk-verify-page .gk-verify-box {
			width: 100%;
			text-align: center;
		}
		body.gk-verify-page .gk-login-box-title {
			color: #fff;
			font-size: 1.5rem;
			font-weight: 600;
			margin: 0 0 0.75rem;
		}
		body.gk-verify-page .gk-verify-intro {
			color: rgba(255,255,255,0.75);
			font-size: calc(1rem + 0.5px);
			line-height: 1.5;
			margin: 0 0 1.75rem;
		}
		body.gk-verify-page .gk-verify-error {
			color: #dc2626;
			font-size: calc(1rem + 0.5px);
			margin: 0 0 1rem;
			padding: 0.5rem 0;
		}
		body.gk-verify-page .gk-verify-success {
			color: #04DA8D;
			font-size: calc(0.95rem + 0.5px);
			margin: 0 0 1rem;
			padding: 0.5rem 0;
		}
		body.gk-verify-page .gk-verify-email-display {
			color: rgba(255,255,255,0.85);
			font-size: calc(1rem + 1px);
			margin: 0 0 1.5rem;
			font-weight: 500;
		}
		body.gk-verify-page .gk-verify-code-label {
			display: block;
			color: rgba(255,255,255,0.6);
			font-size: calc(0.9rem + 0.5px);
			font-weight: 500;
			margin: 0 0 0.75rem;
			letter-spacing: 0.02em;
		}
		body.gk-verify-page .gk-verify-code-wrap {
			display: inline-flex;
			align-items: center;
			gap: 0.5rem;
			padding: 1.25rem 1.5rem;
			background: rgba(0,0,0,0.2);
			border-radius: 12px;
			border: 1px solid rgba(255,255,255,0.08);
			margin-bottom: 1.75rem;
		}
		body.gk-verify-page .gk-verify-code-inputs {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 0.5rem;
			margin: 0;
			flex-wrap: nowrap;
		}
		body.gk-verify-page .gk-verify-digit {
			width: 3.25rem;
			height: 4rem;
			flex-shrink: 0;
			text-align: center;
			font-size: calc(1.6rem + 0.5px);
			font-weight: 600;
			background: #0e0d1e !important;
			border: 1px solid rgba(180, 180, 190, 0.35) !important;
			border-radius: 8px;
			color: #fff;
			transition: border-color 0.2s, box-shadow 0.2s;
		}
		body.gk-verify-page .gk-verify-digit:hover {
			border-color: rgba(4, 218, 141, 0.5) !important;
		}
		body.gk-verify-page .gk-verify-digit:focus {
			outline: none;
			border-color: #04DA8D !important;
			box-shadow: 0 0 0 2px rgba(4, 218, 141, 0.2);
		}
		body.gk-verify-page .gk-verify-digit::placeholder {
			color: rgba(255,255,255,0.15);
		}
		body.gk-verify-page .gk-verify-digit-sep {
			width: 1rem;
			min-width: 1rem;
			height: 2px;
			background: rgba(180, 180, 190, 0.4);
			margin: 0 0.25rem;
			flex-shrink: 0;
			border-radius: 1px;
		}
		body.gk-verify-page .gk-login-submit-row {
			margin: 0 0 1rem;
		}
		body.gk-verify-page .gk-btn-login {
			display: block;
			width: 100%;
			background: linear-gradient(90deg, #04DA8D 0%, #028a5a 100%) !important;
			color: #fff !important;
			border: none !important;
			padding: 1rem 1.15rem !important;
			font-weight: 600 !important;
			font-size: 1.05rem !important;
			border-radius: 6px !important;
			cursor: pointer;
			transition: transform 0.2s ease;
		}
		body.gk-verify-page .gk-btn-login:hover:not(:disabled) {
			transform: translateY(-2px);
		}
		body.gk-verify-page .gk-btn-login:disabled {
			background: rgba(4, 218, 141, 0.25) !important;
			cursor: not-allowed;
		}
		body.gk-verify-page .gk-verify-resend-wrap {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 0.75rem;
			margin: 0 auto 1rem;
			padding: 0;
			width: fit-content;
			max-width: 100%;
			transform: translateX(-1.3rem);
		}
		body.gk-verify-page .gk-verify-resend-wrap .gk-verify-later-link {
			font-size: calc(1rem + 0.5px);
			transition: color 0.2s, transform 0.2s;
		}
		body.gk-verify-page .gk-verify-resend-wrap .gk-verify-later-link:hover {
			transform: translateY(-2px);
		}
		body.gk-verify-page .gk-verify-resend-sep {
			width: 1px;
			height: 1.1rem;
			background: rgba(255,255,255,0.3);
			flex-shrink: 0;
		}
		body.gk-verify-page .gk-divider-line-only {
			width: 100%;
			max-width: 200px;
			margin: 2.25rem auto 2rem;
			height: 1px;
			background: rgba(255,255,255,0.2);
		}
		body.gk-verify-page .gk-verify-later {
			margin: 0 0 0.25rem;
		}
		body.gk-verify-page .gk-verify-later-link {
			color: #04DA8D;
			text-decoration: none;
			transition: color 0.2s;
		}
		body.gk-verify-page .gk-verify-later .gk-verify-later-link {
			font-size: calc(1rem + 0.5px);
			text-decoration: underline;
			text-underline-offset: 0.2em;
			text-decoration-color: rgba(4, 218, 141, 0.5);
		}
		body.gk-verify-page .gk-verify-later .gk-verify-later-link:hover {
			text-decoration-color: #05f0a0;
		}
		body.gk-verify-page .gk-verify-later-link:hover {
			color: #05f0a0;
		}
		body.gk-verify-page .gk-verify-later-hint {
			color: rgba(255,255,255,0.45);
			font-size: calc(1rem + 0.5px);
			margin: 0;
		}
		/* Menü über der Pill: kleiner Hover in Grün */
		#masthead .header-nav-above a {
			padding: 0.15rem 0.3rem !important;
		}
		#masthead .header-nav-above a:hover {
			color: #fff !important;
			background: rgba(90, 90, 95, 0.5) !important;
		}
		#masthead .header-nav-above a.current,
		#masthead .header-nav-above .current-menu-item a {
			color: #04DA8D !important;
			background: rgba(90, 90, 95, 0.5) !important;
		}
		#masthead .header-nav-above a.current:hover,
		#masthead .header-nav-above .current-menu-item a:hover {
			background: rgba(90, 90, 95, 0.5) !important;
			color: #04DA8D !important;
		}
		/* Pill-Plattform-Filter (PC, PlayStation…): Hover und aktiv in Grau, kein Grün */
		#masthead .header-pill .platform-filter {
			padding: 0.4rem 0.65rem !important;
		}
		#masthead .header-pill .platform-filter:hover {
			background: rgba(90, 90, 95, 0.5) !important;
			color: #fff !important;
		}
		#masthead .header-pill .platform-filter.active {
			color: #fff !important;
		}
		#masthead .header-pill .platform-filter.active[data-platform="pc"] {
			background: #04DA8D !important;
			box-shadow: 0 0 0 2px #04DA8D !important;
		}
		#masthead .header-pill .platform-filter.active[data-platform="playstation"] {
			background: #1740c6 !important;
			box-shadow: 0 0 0 2px #1740c6 !important;
		}
		#masthead .header-pill .platform-filter.active[data-platform="xbox"] {
			background: #397a27 !important;
			box-shadow: 0 0 0 2px #397a27 !important;
		}
		#masthead .header-pill .platform-filter.active[data-platform="nintendo"] {
			background: #ff000b !important;
			box-shadow: 0 0 0 2px #ff000b !important;
		}
	';
	wp_add_inline_style( 'globalkeys-style', $account_css );

	wp_enqueue_script( 'globalkeys-navigation', get_template_directory_uri() . '/js/navigation.js', array(), _S_VERSION, true );
	$gk_pill_search_ver = (string) filemtime( get_template_directory() . '/js/header-pill-search.js' );
	wp_enqueue_script( 'globalkeys-header-pill-search', get_template_directory_uri() . '/js/header-pill-search.js', array(), $gk_pill_search_ver, true );
	$gk_pill_search_vars = array(
		'ajaxUrl'            => admin_url( 'admin-ajax.php' ),
		'nonce'              => wp_create_nonce( 'gk_search_products' ),
		'noResults'          => __( 'Keine Treffer', 'globalkeys' ),
		'seeAllResults'      => __( 'See all %d results', 'globalkeys' ),
		'seeAll'             => __( 'See all results', 'globalkeys' ),
		'resultsCountOne'    => __( '1 result', 'globalkeys' ),
		'resultsCountMany'   => __( '%d results', 'globalkeys' ),
	);
	if ( class_exists( 'WooCommerce' ) ) {
		$gk_pill_search_vars['productsData'] = globalkeys_get_search_products_data_for_js();
	}
	wp_localize_script( 'globalkeys-header-pill-search', 'gkPillSearch', $gk_pill_search_vars );
	$gk_drawer_ver = (string) filemtime( get_template_directory() . '/js/gk-account-drawer.js' );
	wp_enqueue_script( 'globalkeys-account-drawer', get_template_directory_uri() . '/js/gk-account-drawer.js', array(), $gk_drawer_ver, true );
	$gk_header_scroll_ver = (string) filemtime( get_template_directory() . '/js/header-scroll-blur.js' );
	wp_enqueue_script( 'globalkeys-header-scroll-blur', get_template_directory_uri() . '/js/header-scroll-blur.js', array(), $gk_header_scroll_ver, true );

	if ( function_exists( 'globalkeys_has_front_page_sections' ) && globalkeys_has_front_page_sections() ) {
		wp_enqueue_script( 'globalkeys-hero-stats-count', get_template_directory_uri() . '/js/hero-stats-count.js', array(), _S_VERSION, true );
		wp_enqueue_script( 'globalkeys-hero-stats-bar-scroll', get_template_directory_uri() . '/js/hero-stats-bar-scroll.js', array(), _S_VERSION, true );
	}
	/* Bestseller-Trailer: überall wo Front-Sections (inkl. Bestseller) vorkommen – nicht nur strikt is_front_page() */
	$gk_product_search = is_search() && isset( $_GET['post_type'] ) && $_GET['post_type'] === 'product'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$gk_load_bestseller_trailer = is_front_page() || is_shop() || $gk_product_search || ( function_exists( 'globalkeys_has_front_page_sections' ) && globalkeys_has_front_page_sections() );
	if ( $gk_load_bestseller_trailer ) {
		$gk_bestseller_trailer_js = get_template_directory() . '/js/gk-bestseller-trailer-hover.js';
		wp_enqueue_script(
			'globalkeys-bestseller-trailer-hover',
			get_template_directory_uri() . '/js/gk-bestseller-trailer-hover.js',
			array(),
			file_exists( $gk_bestseller_trailer_js ) ? (string) filemtime( $gk_bestseller_trailer_js ) : _S_VERSION,
			true
		);
		$gk_hover_panel_js = get_template_directory() . '/js/gk-product-hover-panel.js';
		wp_enqueue_script(
			'globalkeys-product-hover-panel',
			get_template_directory_uri() . '/js/gk-product-hover-panel.js',
			array(),
			file_exists( $gk_hover_panel_js ) ? (string) filemtime( $gk_hover_panel_js ) : _S_VERSION,
			true
		);
	}

	/* Carousel: immer auf der Startseite laden (Hero-Sections + Test; unabhängig von strikter has_front_page_sections-Bedingung) */
	if ( is_front_page() ) {
		wp_enqueue_script( 'globalkeys-categories-carousel', get_template_directory_uri() . '/js/gk-categories-carousel.js', array(), _S_VERSION, true );
	}

	if ( function_exists( 'is_account_page' ) && is_account_page() && is_user_logged_in() ) {
		$dashboard_css = get_template_directory() . '/assets/css/account-dashboard.css';
		if ( file_exists( $dashboard_css ) ) {
			wp_enqueue_style(
				'globalkeys-account-dashboard',
				get_template_directory_uri() . '/assets/css/account-dashboard.css',
				array( 'globalkeys-style' ),
				filemtime( $dashboard_css )
			);
		}
		$avatar_js = get_template_directory() . '/js/gk-avatar-upload.js';
		wp_enqueue_script(
			'globalkeys-avatar-upload',
			get_template_directory_uri() . '/js/gk-avatar-upload.js',
			array( 'jquery' ),
			file_exists( $avatar_js ) ? filemtime( $avatar_js ) : _S_VERSION,
			true
		);
		wp_localize_script( 'globalkeys-avatar-upload', 'gkAvatarUpload', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'gk_upload_avatar' ),
		) );
		$tabs_scroll_js = get_template_directory() . '/js/gk-account-tabs-preserve-scroll.js';
		wp_enqueue_script(
			'globalkeys-account-tabs-preserve-scroll',
			get_template_directory_uri() . '/js/gk-account-tabs-preserve-scroll.js',
			array(),
			file_exists( $tabs_scroll_js ) ? filemtime( $tabs_scroll_js ) : _S_VERSION,
			true
		);
	}

	if ( function_exists( 'is_account_page' ) && is_account_page() && ! is_user_logged_in() ) {
		$login_no_border = '
			body.gk-account-login .gk-login-box form { margin: 0; padding: 0; }
			body.gk-account-login .gk-login-box .form-row { margin-left: 0 !important; margin-right: 0 !important; padding-left: 0 !important; padding-right: 0 !important; width: 100% !important; }
			body.gk-account-login .gk-login-box button { border: none !important; outline: none !important; box-shadow: none !important; }
		';
		wp_add_inline_style( 'woocommerce-general', $login_no_border );
		wp_enqueue_script( 'globalkeys-account-toggle', get_template_directory_uri() . '/js/gk-account-toggle.js', array(), _S_VERSION, true );
		wp_enqueue_script( 'globalkeys-gamertag-check', get_template_directory_uri() . '/js/gamertag-check.js', array(), _S_VERSION, true );
		wp_enqueue_script( 'globalkeys-login-modal', get_template_directory_uri() . '/js/gk-login-modal.js', array(), _S_VERSION, true );
		$gk_err = get_transient( 'gk_login_error' );
		$gk_reg = get_transient( 'gk_register_error' );
		$gk_err_type = ! empty( $gk_reg ) ? 'register' : ( ! empty( $gk_err ) ? 'login' : '' );
		wp_localize_script( 'globalkeys-login-modal', 'gkAccountError', array( 'type' => $gk_err_type ) );
		wp_localize_script(
			'globalkeys-gamertag-check',
			'globalkeysGamertagCheck',
			array(
				'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
				'takenMessage'  => __( 'Dieser Gamertag wird bereits verwendet.', 'globalkeys' ),
				'emailMessage'  => __( 'Diese E-Mail-Adresse wird bereits verwendet.', 'globalkeys' ),
			)
		);
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'globalkeys_scripts' );

/**
 * Passwort-Bewertung auf der Account-Seite entfernen.
 */
function globalkeys_dequeue_password_strength() {
	if ( function_exists( 'is_account_page' ) && is_account_page() && ! is_user_logged_in() ) {
		wp_dequeue_script( 'wc-password-strength-meter' );
	}
}
add_action( 'wp_enqueue_scripts', 'globalkeys_dequeue_password_strength', 20 );

/**
 * Front-page sections configuration.
 */
require get_template_directory() . '/inc/sections.php';

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}

