<?php
/**
 * The header for our theme
 *
 * @package globalkeys
 */

$cart_url        = class_exists( 'WooCommerce' ) ? wc_get_page_permalink( 'cart' ) : '#';
$account_url     = class_exists( 'WooCommerce' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
$gk_account_login = function_exists( 'is_account_page' ) && is_account_page() && ! is_user_logged_in();
?>
<!doctype html>
<html <?php language_attributes(); ?><?php echo $gk_account_login ? ' class="gk-account-login-page"' : ''; ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php
if ( ! $gk_account_login ) {
	wp_body_open();
}
?>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'globalkeys' ); ?></a>

	<?php if ( $gk_account_login ) : ?>
		<header id="masthead" class="site-header site-header--transparent gk-account-logo-header">
			<div class="site-header-inner">
				<div class="site-branding">
					<?php if ( has_custom_logo() ) : ?>
						<?php the_custom_logo(); ?>
					<?php else : ?>
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo-link site-logo-link--svg" rel="home">
							<img src="<?php echo esc_url( get_template_directory_uri() . '/Pictures/GlobalKeysOriginalLogo-gk.svg' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="site-logo-img" width="180" height="36" />
						</a>
					<?php endif; ?>
				</div>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="gk-account-close" id="gk-account-close-btn" aria-label="<?php esc_attr_e( 'Schließen', 'globalkeys' ); ?>">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
				</a>
			</div>
		</header>
	<?php endif; ?>

	<?php if ( ! $gk_account_login ) : ?>
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

			<!-- Mitte: 5 Menüpunkte über der Pill, darunter Pill (Plattformen + Suche) – zentriert -->
			<div class="header-pill-wrapper">
				<nav class="header-nav-above" aria-label="<?php esc_attr_e( 'Hauptmenü', 'globalkeys' ); ?>">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Trending Games', 'globalkeys' ); ?></a>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Preorders', 'globalkeys' ); ?></a>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Available Soon', 'globalkeys' ); ?></a>
					<a href="<?php echo esc_url( home_url( '/activation/' ) ); ?>"><?php esc_html_e( 'Activation', 'globalkeys' ); ?></a>
					<a href="<?php echo esc_url( home_url( '/support/' ) ); ?>"><?php esc_html_e( '24/7 Support', 'globalkeys' ); ?></a>
				</nav>
			<div class="header-pill-search-outer">
				<div class="header-pill-container">
					<div class="header-pill">
						<?php
						$pictures_uri = get_template_directory_uri() . '/Pictures/';
						?>
						<div class="header-pill-platforms">
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>?platform=pc" class="platform-filter" data-platform="pc" aria-label="PC">
								<img src="<?php echo esc_url( $pictures_uri . 'PC-gk (1).svg' ); ?>" alt="" class="platform-filter-icon" width="30" height="30" aria-hidden="true" />
								<span class="platform-filter-label">PC</span>
							</a>
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>?platform=playstation" class="platform-filter" data-platform="playstation" aria-label="PlayStation">
								<img src="<?php echo esc_url( $pictures_uri . 'playstation-logo-gk (1).svg' ); ?>" alt="" class="platform-filter-icon" width="30" height="30" aria-hidden="true" />
								<span class="platform-filter-label">PlayStation</span>
							</a>
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>?platform=xbox" class="platform-filter" data-platform="xbox" aria-label="Xbox">
								<img src="<?php echo esc_url( $pictures_uri . 'xbox-logo-gk (1).svg' ); ?>" alt="" class="platform-filter-icon" width="30" height="30" aria-hidden="true" />
								<span class="platform-filter-label">Xbox</span>
							</a>
<a href="<?php echo esc_url( home_url( '/' ) ); ?>?platform=nintendo" class="platform-filter" data-platform="nintendo" aria-label="<?php esc_attr_e( 'Nintendo', 'globalkeys' ); ?>">
							<img src="<?php echo esc_url( $pictures_uri . 'Switch-gk (1).svg' ); ?>" alt="" class="platform-filter-icon" width="30" height="30" aria-hidden="true" />
							<span class="platform-filter-label"><?php esc_html_e( 'Nintendo', 'globalkeys' ); ?></span>
							</a>
						</div>
						<button type="button" class="header-pill-search-trigger header-pill-search-submit" aria-label="<?php esc_attr_e( 'Suchen öffnen', 'globalkeys' ); ?>" aria-expanded="false" aria-controls="gk-pill-search-overlay">
							<svg class="search-submit-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<circle cx="11" cy="11" r="8"></circle>
								<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
							</svg>
						</button>
					</div>
					<div id="gk-pill-search-overlay" class="header-pill-search-overlay" role="dialog" aria-label="<?php esc_attr_e( 'Suchen', 'globalkeys' ); ?>" hidden>
						<form role="search" method="get" class="header-pill-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
							<label for="gk-pill-search-input" class="screen-reader-text"><?php esc_html_e( 'Suchen', 'globalkeys' ); ?></label>
							<input type="search" id="gk-pill-search-input" class="header-pill-search-input" placeholder="PSN Cards, Multiplayer, ARC Raiders..." value="<?php echo get_search_query(); ?>" name="s" autocomplete="off" />
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="header-pill-browse-link"><?php esc_html_e( 'Browse all Games', 'globalkeys' ); ?></a>
							<button type="submit" class="header-pill-search-submit" aria-label="<?php esc_attr_e( 'Suchen', 'globalkeys' ); ?>">
								<svg class="search-submit-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
									<circle cx="11" cy="11" r="8"></circle>
									<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
								</svg>
							</button>
						</form>
					</div>
				</div>
				<div class="header-pill-search-close-area" aria-hidden="true">
					<button type="button" class="header-pill-search-close" aria-label="<?php esc_attr_e( 'Suche schließen', 'globalkeys' ); ?>">&times;</button>
				</div>
			</div>
			</div>

			<!-- Rechts: Warenkorb + Konto / Sign in (Icons per Mask mit Farbe #04DA8D beim Hover) -->
			<?php
			$cart_icon_url      = esc_url( get_template_directory_uri() . '/Pictures/cart.g.svg' );
			$signin_icon_url    = esc_url( get_template_directory_uri() . '/Pictures/sign-in-button.svg' );
			$favorite_icon_url  = esc_url( get_template_directory_uri() . '/Pictures/heart2-gk.svg' );
			$favorites_url      = esc_url( home_url( '/favorites/' ) );
			?>
			<style>
			.header-icon-cart { -webkit-mask-image: url('<?php echo $cart_icon_url; ?>'); mask-image: url('<?php echo $cart_icon_url; ?>'); }
			.header-icon-account { -webkit-mask-image: url('<?php echo $signin_icon_url; ?>'); mask-image: url('<?php echo $signin_icon_url; ?>'); }
			.header-icon-favorite { -webkit-mask-image: url('<?php echo $favorite_icon_url; ?>'); mask-image: url('<?php echo $favorite_icon_url; ?>'); }
			</style>
			<div class="site-header-actions">
				<a href="<?php echo esc_url( home_url( '/rewards/' ) ); ?>" class="header-rewards-link" aria-label="<?php esc_attr_e( 'Rewards', 'globalkeys' ); ?>">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/Pictures/GlobalKeysOriginalLogo-gk.svg' ); ?>" alt="" class="header-rewards-logo" width="88" height="18">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/Pictures/Rewards-gk.svg' ); ?>" alt="" class="header-rewards-icon" width="88" height="23">
				</a>
				<span class="header-actions-divider" aria-hidden="true"></span>
				<a href="<?php echo esc_url( $favorites_url ); ?>" class="header-icon-link header-favorites-link" aria-label="<?php esc_attr_e( 'Favoriten', 'globalkeys' ); ?>">
					<span class="header-icon header-icon-favorite" aria-hidden="true"></span>
				</a>
				<a href="<?php echo esc_url( $cart_url ); ?>" class="header-icon-link header-cart-link" aria-label="<?php esc_attr_e( 'Warenkorb', 'globalkeys' ); ?>">
					<span class="header-icon header-icon-cart" aria-hidden="true"></span>
					<?php if ( class_exists( 'WooCommerce' ) && WC()->cart && WC()->cart->get_cart_contents_count() > 0 ) : ?>
						<span class="header-cart-count"><?php echo absint( WC()->cart->get_cart_contents_count() ); ?></span>
					<?php endif; ?>
				</a>
				<a href="<?php echo esc_url( $account_url ); ?>" class="header-icon-link header-account-link" aria-label="<?php echo esc_attr( is_user_logged_in() ? __( 'Mein Konto', 'globalkeys' ) : __( 'Anmelden', 'globalkeys' ) ); ?>">
					<span class="header-icon header-icon-account" aria-hidden="true"></span>
				</a>
			</div>
		</div>
	</header><!-- #masthead -->
	<?php endif; ?>
