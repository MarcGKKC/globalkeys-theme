<?php
/**
 * The header for our theme
 *
 * @package globalkeys
 */

$cart_url    = class_exists( 'WooCommerce' ) ? wc_get_page_permalink( 'cart' ) : '#';
$account_url = class_exists( 'WooCommerce' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'globalkeys' ); ?></a>

	<header id="masthead" class="site-header site-header--transparent">
		<div class="site-header-inner">
			<!-- Links: Logo -->
			<div class="site-branding">
				<?php if ( has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo-link site-logo-link--svg" rel="home">
						<img src="<?php echo esc_url( get_template_directory_uri() . '/Pictures/GlobalKeysOriginalLogo-gk.svg' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="site-logo-img" width="180" height="36" />
					</a>
				<?php endif; ?>
			</div>

			<!-- Mitte: Pill (Plattformen + Suche) – zentriert -->
			<div class="header-pill-wrapper">
			<div class="header-pill">
				<div class="header-pill-platforms">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>?platform=pc" class="platform-filter" data-platform="pc" aria-label="PC">
						<svg class="platform-filter-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
						<span class="platform-filter-label">PC</span>
					</a>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>?platform=playstation" class="platform-filter" data-platform="playstation" aria-label="PlayStation">
						<svg class="platform-filter-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 2L9 8v12h6V8l-3-6z"/><path d="M6 12l3 2 3-2"/></svg>
						<span class="platform-filter-label">PlayStation</span>
					</a>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>?platform=xbox" class="platform-filter" data-platform="xbox" aria-label="Xbox">
						<svg class="platform-filter-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 4h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/><path d="M9 9h6v6H9z"/></svg>
						<span class="platform-filter-label">Xbox</span>
					</a>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>?platform=nintendo" class="platform-filter" data-platform="nintendo" aria-label="Nintendo">
						<svg class="platform-filter-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="12" y1="4" x2="12" y2="20"/><line x1="2" y1="12" x2="22" y2="12"/></svg>
						<span class="platform-filter-label">Nintendo</span>
					</a>
				</div>
				<form role="search" method="get" class="header-pill-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
					<label for="gk-header-search" class="screen-reader-text"><?php esc_html_e( 'Suchen', 'globalkeys' ); ?></label>
					<input type="search" id="gk-header-search" class="header-pill-search-input" placeholder="<?php esc_attr_e( 'Suchen...', 'globalkeys' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
					<button type="submit" class="header-pill-search-submit" aria-label="<?php esc_attr_e( 'Suchen', 'globalkeys' ); ?>">
						<svg class="search-submit-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<circle cx="11" cy="11" r="8"></circle>
							<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
						</svg>
					</button>
				</form>
			</div>
			</div>

			<!-- Rechts: Warenkorb + Konto / Sign in -->
			<div class="site-header-actions">
				<a href="<?php echo esc_url( $cart_url ); ?>" class="header-icon-link header-cart-link" aria-label="<?php esc_attr_e( 'Warenkorb', 'globalkeys' ); ?>">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/Pictures/cart.g.svg' ); ?>" alt="" class="header-icon header-icon-cart" width="30" height="30" aria-hidden="true" />
					<?php if ( class_exists( 'WooCommerce' ) && WC()->cart && WC()->cart->get_cart_contents_count() > 0 ) : ?>
						<span class="header-cart-count"><?php echo absint( WC()->cart->get_cart_contents_count() ); ?></span>
					<?php endif; ?>
				</a>
				<a href="<?php echo esc_url( $account_url ); ?>" class="header-icon-link header-account-link" aria-label="<?php echo esc_attr( is_user_logged_in() ? __( 'Mein Konto', 'globalkeys' ) : __( 'Anmelden', 'globalkeys' ) ); ?>">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/Pictures/sign-in-button.svg' ); ?>" alt="" class="header-icon header-icon-account" width="30" height="30" aria-hidden="true" />
				</a>
			</div>
		</div>
	</header><!-- #masthead -->
