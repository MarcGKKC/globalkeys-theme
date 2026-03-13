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

/**
 * Bei Login-Fehler: Redirect mit Transient, damit Formular leer geladen wird und eigene Fehlermeldung als Modal.
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
	wc_clear_notices();
	wp_safe_redirect( wc_get_page_permalink( 'myaccount' ) );
	exit;
}
add_action( 'woocommerce_login_failed', 'globalkeys_login_failed_redirect', 5 );

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
		/* My Account Login/Register: Sidebar und Footer ausblenden */
		body.gk-account-login #secondary,
		body.gk-account-login #colophon {
			display: none !important;
		}
		/* My Account Login: 2-Spalten-Layout (Form links, Bild rechts) */
		body.gk-account-login .site-main {
			padding: 0;
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
			min-height: calc(100vh - 80px);
			overflow: hidden;
		}
		body.gk-account-login .gk-account-form-col {
			background: #1a193f;
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 3rem 4rem;
			clip-path: polygon(0 0, 100% 0, 89% 100%, 0 100%);
			position: relative;
			z-index: 2;
		}
		body.gk-account-login .gk-account-blocks {
			width: 100%;
			max-width: 480px;
			margin: 3rem auto 0;
			box-sizing: border-box;
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
		body.gk-account-login .gk-register-block {
			display: none;
		}
		body.gk-account-login .gk-account-blocks.gk-show-register .gk-login-block {
			display: none;
		}
		body.gk-account-login .gk-account-blocks.gk-show-register .gk-register-block {
			display: block;
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
			gap: 0.5rem;
			margin-bottom: 1.15rem;
			width: 100%;
		}
		body.gk-account-login .gk-social-placeholder {
			flex: 1;
			height: 44px;
			background: #0e0d1e;
			border: 1px solid rgba(180, 180, 190, 0.35);
			border-radius: 5px;
			min-width: 0;
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
			margin-top: 2rem;
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
		body.gk-account-login .gk-login-box .gk-login-row .input-text {
			background: #0e0d1e !important;
			border: 1px solid rgba(180, 180, 190, 0.35) !important;
			outline: none !important;
			box-shadow: none !important;
			color: #fff;
			padding: 1.15rem 1.15rem;
			border-radius: 5px;
			width: 100%;
			font-size: 1.05rem;
			box-sizing: border-box;
			min-height: 52px;
			transition: border-color 0.2s ease;
		}
		body.gk-account-login .gk-login-box .gk-login-row .input-text::placeholder {
			color: rgba(255, 255, 255, 0.7);
			font-size: 1.1rem;
		}
		body.gk-account-login .gk-login-box .gk-login-row .input-text:focus,
		body.gk-account-login .gk-login-box .gk-login-row .input-text:active,
		body.gk-account-login .gk-login-box .gk-login-row .input-text:-webkit-autofill {
			border: 1px solid rgba(180, 180, 190, 0.35) !important;
			outline: none !important;
			box-shadow: none !important;
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
			padding-right: 3rem;
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
		}
		body.gk-account-login .gk-login-box .gk-password-toggle:hover {
			color: #04DA8D;
			border-color: #04DA8D;
		}
		body.gk-account-login .gk-login-box .gk-btn-login {
			background: linear-gradient(90deg, #f59e0b, #dc2626) !important;
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
			background: linear-gradient(90deg, #f59e0b, #dc2626) !important;
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
			min-height: 400px;
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
			width: 34px;
			background: #1a193f;
			transform: skewX(-6.3deg);
			transform-origin: center;
			pointer-events: none;
			z-index: 2;
		}
		body.gk-account-login .gk-account-image-placeholder {
			position: absolute;
			inset: 0;
			background: linear-gradient(135deg, #2d2d2d 0%, #1a1a1a 50%, #0d0d0d 100%);
			background-size: cover;
			background-position: center;
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
			background: linear-gradient(90deg, #f59e0b, #dc2626) !important;
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
	';
	wp_add_inline_style( 'globalkeys-style', $account_css );

	wp_enqueue_script( 'globalkeys-navigation', get_template_directory_uri() . '/js/navigation.js', array(), _S_VERSION, true );
	wp_enqueue_script( 'globalkeys-header-pill-search', get_template_directory_uri() . '/js/header-pill-search.js', array(), _S_VERSION, true );

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

