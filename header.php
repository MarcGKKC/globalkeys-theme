<?php
/**
 * The header for our theme
 *
 * @package globalkeys
 */

$cart_url         = class_exists( 'WooCommerce' ) ? wc_get_page_permalink( 'cart' ) : '#';
$account_url      = class_exists( 'WooCommerce' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
$gk_account_login = function_exists( 'is_account_page' ) && is_account_page() && ! is_user_logged_in();
$gk_is_cart_page    = function_exists( 'is_cart' ) && is_cart();
$gk_compact_chrome  = $gk_is_cart_page || ( is_string( get_query_var( 'gk_nav_section' ) ) && get_query_var( 'gk_nav_section' ) === 'support' );
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
		<?php if ( $gk_compact_chrome ) : ?>
		<header id="masthead" class="site-header site-header--compact-bar">
			<div class="site-header-inner site-header-inner--compact-bar">
				<div class="site-branding">
					<?php if ( has_custom_logo() ) : ?>
						<?php the_custom_logo(); ?>
					<?php else : ?>
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo-link site-logo-link--svg" rel="home">
							<img src="<?php echo esc_url( get_template_directory_uri() . '/Pictures/GlobalKeysOriginalLogo-gk.svg' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="site-logo-img" width="180" height="36" />
						</a>
					<?php endif; ?>
				</div>
				<?php
				$gk_compact_secure_svg = get_template_directory_uri() . '/Pictures/' . rawurlencode( 'Design ohne Titel (55).svg' );
				$gk_compact_secure_support = is_string( get_query_var( 'gk_nav_section' ) ) && get_query_var( 'gk_nav_section' ) === 'support';
				?>
				<div class="gk-cart-secure" style="<?php echo esc_attr( '--gk-cart-secure-svg:url(' . esc_url( $gk_compact_secure_svg ) . ')' ); ?>">
					<span class="gk-cart-secure__icon" aria-hidden="true"></span>
					<span class="gk-cart-secure__sep" aria-hidden="true"></span>
					<div class="gk-cart-secure__text">
						<?php if ( $gk_compact_secure_support ) : ?>
							<span class="gk-cart-secure__title"><?php esc_html_e( 'SAFE & SECURE', 'globalkeys' ); ?></span>
							<span class="gk-cart-secure__sub"><?php esc_html_e( '100% secure and 24h support', 'globalkeys' ); ?></span>
						<?php else : ?>
							<span class="gk-cart-secure__title"><?php esc_html_e( 'Sichere Zahlung', 'globalkeys' ); ?></span>
							<span class="gk-cart-secure__sub"><?php esc_html_e( '256-Bit SSL-verschlüsselt', 'globalkeys' ); ?></span>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</header><!-- #masthead -->
		<?php else : ?>
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
			<?php
			$gk_cur_nav = is_string( get_query_var( 'gk_nav_section' ) ) ? get_query_var( 'gk_nav_section' ) : '';
			$gk_is_search_results = is_search() && isset( $_GET['post_type'] ) && $_GET['post_type'] === 'product'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$gk_filters_open_only = $gk_is_search_results && isset( $_GET['gk_filters'] ) && 'open' === sanitize_text_field( wp_unslash( (string) $_GET['gk_filters'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$gk_pt_param                = isset( $_GET['gk_pt'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['gk_pt'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$gk_is_preorder_filter_launch = $gk_filters_open_only && ( 'pre-orders' === $gk_pt_param );
			$gk_trending_nav_active     = ( $gk_cur_nav === 'trending-games' ) || ( $gk_filters_open_only && ! $gk_is_preorder_filter_launch );
			$gk_preorders_nav_active    = ( $gk_cur_nav === 'preorders' ) || $gk_is_preorder_filter_launch;
			?>
			<div class="header-pill-wrapper">
				<nav class="header-nav-above" aria-label="<?php esc_attr_e( 'Hauptmenü', 'globalkeys' ); ?>">
					<?php
					$gk_trending_games_href = home_url( '/trending-games/' );
					if ( function_exists( 'is_front_page' ) && is_front_page() && class_exists( 'WooCommerce' ) ) {
						$gk_trending_games_href = add_query_arg(
							array(
								'post_type'  => 'product',
								'gk_filters' => 'open',
							),
							home_url( '/' )
						);
					}
					$gk_preorders_href = home_url( '/preorders/' );
					if ( function_exists( 'is_front_page' ) && is_front_page() && class_exists( 'WooCommerce' ) ) {
						$gk_preorders_href = add_query_arg(
							array(
								'post_type'  => 'product',
								'gk_filters' => 'open',
								'gk_pt'      => 'pre-orders',
							),
							home_url( '/' )
						);
					}
					?>
					<a href="<?php echo esc_url( $gk_trending_games_href ); ?>" class="<?php echo $gk_trending_nav_active ? 'current' : ''; ?>"><?php esc_html_e( 'Trending Games', 'globalkeys' ); ?></a>
					<a href="<?php echo esc_url( $gk_preorders_href ); ?>" class="<?php echo $gk_preorders_nav_active ? 'current' : ''; ?>"><?php esc_html_e( 'Preorders', 'globalkeys' ); ?></a>
					<a href="<?php echo esc_url( home_url( '/available-soon/' ) ); ?>" class="<?php echo ( $gk_cur_nav === 'available-soon' ) ? 'current' : ''; ?>"><?php esc_html_e( 'Available Soon', 'globalkeys' ); ?></a>
					<a href="<?php echo esc_url( home_url( '/activation/' ) ); ?>" class="<?php echo ( $gk_cur_nav === 'activation' ) ? 'current' : ''; ?>"><?php esc_html_e( 'Activation', 'globalkeys' ); ?></a>
					<a href="<?php echo esc_url( home_url( '/support/' ) ); ?>" class="<?php echo ( $gk_cur_nav === 'support' ) ? 'current' : ''; ?>"><?php esc_html_e( '24/7 Support', 'globalkeys' ); ?></a>
				</nav>
			<?php
			/* Ohne gk_filters=open: Such-Pill auf der Suchseite ausgeklappt. Mit open: Filterleiste (JS), Pill zugeklappt. */
			$gk_search_pill_expanded = $gk_is_search_results && ! $gk_filters_open_only;
			?>
			<div class="header-pill-search-outer<?php echo $gk_search_pill_expanded ? ' is-search-open' : ''; ?>">
				<div class="header-pill-container<?php echo $gk_search_pill_expanded ? ' is-search-open' : ''; ?>">
					<div class="header-pill">
						<?php
						$pictures_uri    = get_template_directory_uri() . '/Pictures/';
						$gk_cur_platform = is_string( get_query_var( 'gk_platform' ) ) ? get_query_var( 'gk_platform' ) : '';
						?>
						<div class="header-pill-platforms">
							<a href="<?php echo esc_url( home_url( '/platform/pc/' ) ); ?>" class="platform-filter<?php echo ( $gk_cur_platform === 'pc' ) ? ' active' : ''; ?>" data-platform="pc" aria-label="PC">
								<img src="<?php echo esc_url( $pictures_uri . 'PC-gk (1).svg' ); ?>" alt="" class="platform-filter-icon" width="30" height="30" aria-hidden="true" />
								<span class="platform-filter-label">PC</span>
							</a>
							<a href="<?php echo esc_url( home_url( '/platform/playstation/' ) ); ?>" class="platform-filter<?php echo ( $gk_cur_platform === 'playstation' ) ? ' active' : ''; ?>" data-platform="playstation" aria-label="PlayStation">
								<img src="<?php echo esc_url( $pictures_uri . 'playstation-logo-gk (1).svg' ); ?>" alt="" class="platform-filter-icon" width="30" height="30" aria-hidden="true" />
								<span class="platform-filter-label">PlayStation</span>
							</a>
							<a href="<?php echo esc_url( home_url( '/platform/xbox/' ) ); ?>" class="platform-filter<?php echo ( $gk_cur_platform === 'xbox' ) ? ' active' : ''; ?>" data-platform="xbox" aria-label="Xbox">
								<img src="<?php echo esc_url( $pictures_uri . 'xbox-logo-gk (1).svg' ); ?>" alt="" class="platform-filter-icon" width="30" height="30" aria-hidden="true" />
								<span class="platform-filter-label">Xbox</span>
							</a>
							<a href="<?php echo esc_url( home_url( '/platform/nintendo/' ) ); ?>" class="platform-filter<?php echo ( $gk_cur_platform === 'nintendo' ) ? ' active' : ''; ?>" data-platform="nintendo" aria-label="<?php esc_attr_e( 'Nintendo', 'globalkeys' ); ?>">
								<img src="<?php echo esc_url( $pictures_uri . 'Switch-gk (1).svg' ); ?>" alt="" class="platform-filter-icon" width="30" height="30" aria-hidden="true" />
								<span class="platform-filter-label"><?php esc_html_e( 'Nintendo', 'globalkeys' ); ?></span>
							</a>
						</div>
						<button type="button" class="header-pill-search-trigger header-pill-search-submit" aria-label="<?php esc_attr_e( 'Suchen öffnen', 'globalkeys' ); ?>" aria-expanded="<?php echo $gk_search_pill_expanded ? 'true' : 'false'; ?>" aria-controls="gk-pill-search-overlay">
							<svg class="search-submit-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<circle cx="11" cy="11" r="8"></circle>
								<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
							</svg>
						</button>
					</div>
					<div id="gk-pill-search-overlay" class="header-pill-search-overlay" role="dialog" aria-label="<?php esc_attr_e( 'Suchen', 'globalkeys' ); ?>" aria-hidden="<?php echo $gk_search_pill_expanded ? 'false' : 'true'; ?>">
						<?php
						/* Suche immer zur Startseite mit post_type=product – garantiert keine 404 */
						$gk_search_action = home_url( '/' );
						?>
						<?php $gk_browse_url = globalkeys_get_browse_all_games_url(); ?>
						<form role="search" method="get" class="header-pill-search-form" action="<?php echo esc_url( $gk_search_action ); ?>">
							<label for="gk-pill-search-input" class="screen-reader-text"><?php esc_html_e( 'Suchen', 'globalkeys' ); ?></label>
							<?php if ( class_exists( 'WooCommerce' ) ) : ?><input type="hidden" name="post_type" value="product" /><?php endif; ?>
							<input type="search" id="gk-pill-search-input" class="header-pill-search-input" placeholder="PSN Cards, Multiplayer, ARC Raiders..." value="<?php echo get_search_query(); ?>" name="s" autocomplete="off" />
							<a href="<?php echo esc_url( $gk_browse_url ); ?>" class="header-pill-browse-link"><?php esc_html_e( 'Browse all Games', 'globalkeys' ); ?></a>
							<button type="submit" class="header-pill-search-submit header-pill-search-submit--visible" aria-label="<?php esc_attr_e( 'Suchen', 'globalkeys' ); ?>">
								<svg class="search-submit-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
									<circle cx="11" cy="11" r="8"></circle>
									<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
								</svg>
							</button>
						</form>
					</div>
				</div>
				<div class="header-pill-search-close-area" aria-hidden="<?php echo $gk_search_pill_expanded ? 'false' : 'true'; ?>">
					<button type="button" class="header-pill-search-close" aria-label="<?php esc_attr_e( 'Suche schließen', 'globalkeys' ); ?>">&times;</button>
				</div>
				<?php
				$gk_search_url = home_url( '/' );
				if ( class_exists( 'WooCommerce' ) ) {
					$gk_search_url = add_query_arg( 'post_type', 'product', home_url( '/' ) );
				}
				?>
				<div id="gk-search-dropdown" class="header-pill-search-dropdown" aria-hidden="true"<?php echo $gk_search_pill_expanded ? ' data-gk-hide-dropdown="1"' : ''; ?> hidden data-base-url="<?php echo esc_url( $gk_search_url ); ?>">
					<ul class="header-pill-search-dropdown-list" role="list">
						<!-- Live-Suche: Liste wird per JS befüllt -->
					</ul>
					<div class="header-pill-search-dropdown-footer">
						<a href="<?php echo esc_url( $gk_search_url ); ?>" class="header-pill-search-dropdown-all" id="gk-search-dropdown-all-link">
							<?php esc_html_e( 'See all results', 'globalkeys' ); ?>
						</a>
					</div>
				</div>
			</div>
			</div>

			<!-- Rechts: Warenkorb + Konto / Sign in (Icons per Mask mit Farbe #04DA8D beim Hover) -->
			<?php
			$cart_icon_url      = esc_url( get_template_directory_uri() . '/Pictures/cart.g.svg' );
			$signin_icon_url    = esc_url( get_template_directory_uri() . '/Pictures/sign-in-button.svg' );
			$favorite_icon_url = esc_url( get_template_directory_uri() . '/Pictures/heart2-gk.svg' );
			$favorites_url     = esc_url( function_exists( 'globalkeys_get_wishlist_url' ) ? globalkeys_get_wishlist_url() : home_url( '/wishlist/' ) );
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
				<a href="<?php echo esc_url( $favorites_url ); ?>" class="header-icon-link header-favorites-link" aria-label="<?php esc_attr_e( 'Wishlist', 'globalkeys' ); ?>">
					<span class="header-icon header-icon-favorite" aria-hidden="true"></span>
				</a>
				<a href="<?php echo esc_url( $cart_url ); ?>" class="header-icon-link header-cart-link" aria-label="<?php esc_attr_e( 'Warenkorb', 'globalkeys' ); ?>">
					<span class="header-cart-icon-wrap">
						<span class="header-icon header-icon-cart" aria-hidden="true"></span>
						<?php if ( class_exists( 'WooCommerce' ) && WC()->cart && WC()->cart->get_cart_contents_count() > 0 ) : ?>
							<span class="header-cart-count"><?php echo absint( WC()->cart->get_cart_contents_count() ); ?></span>
						<?php endif; ?>
					</span>
				</a>
				<?php if ( is_user_logged_in() ) : ?>
					<?php
					$current_user_id  = get_current_user_id();
					$header_avatar    = function_exists( 'globalkeys_get_user_avatar_url' ) ? globalkeys_get_user_avatar_url( $current_user_id, 120 ) : get_avatar_url( $current_user_id, array( 'size' => 120 ) );
					$myaccount_url    = class_exists( 'WooCommerce' ) ? wc_get_page_permalink( 'myaccount' ) : $account_url;
					$orders_url       = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'orders', '', $myaccount_url ) : $myaccount_url;
					$wunschliste_url  = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'wunschliste', '', $myaccount_url ) : $myaccount_url;
					$affiliate_url    = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'affiliate', '', $myaccount_url ) : $myaccount_url;
					$edit_account_url = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'edit-account', '', $myaccount_url ) : $myaccount_url;
					$logout_url       = function_exists( 'wc_logout_url' ) ? wc_logout_url( home_url( '/' ) ) : wp_logout_url( home_url( '/' ) );
					$gk_drawer_current_url = '';
					if ( isset( $_SERVER['HTTP_HOST'] ) && isset( $_SERVER['REQUEST_URI'] ) ) {
						$gk_drawer_current_url = set_url_scheme( ( is_ssl() ? 'https://' : 'http://' ) . wp_unslash( $_SERVER['HTTP_HOST'] ) . wp_unslash( $_SERVER['REQUEST_URI'] ) );
						$gk_drawer_current_url = trailingslashit( strtok( $gk_drawer_current_url, '?' ) );
					}
					$gk_drawer_class = function( $url ) use ( $gk_drawer_current_url, $myaccount_url ) {
						$c = 'gk-account-drawer__item';
						if ( $gk_drawer_current_url && $url ) {
							$norm = trailingslashit( strtok( $url, '?' ) );
							$is_current = ( $norm === $gk_drawer_current_url );
							if ( ! $is_current && $norm !== trailingslashit( strtok( $myaccount_url, '?' ) ) && strpos( $gk_drawer_current_url, $norm ) === 0 ) {
								$is_current = true;
							}
							if ( $is_current ) {
								$c .= ' gk-account-drawer__item--current';
							}
						}
						return $c;
					};
					?>
					<div class="header-account-drawer-wrap" id="gk-account-drawer-wrap">
						<button type="button" class="header-icon-link header-account-link header-account-trigger" id="gk-account-drawer-trigger" aria-label="<?php esc_attr_e( 'Mein Konto', 'globalkeys' ); ?>" aria-expanded="false" aria-controls="gk-account-drawer">
							<span class="header-account-avatar-wrap">
								<img src="<?php echo esc_url( $header_avatar ); ?>" alt="" class="header-account-avatar" width="46" height="46" loading="eager" style="object-position: center bottom;">
							</span>
						</button>
						<div class="gk-account-drawer" id="gk-account-drawer" hidden aria-hidden="true" role="dialog" aria-label="<?php esc_attr_e( 'Konto-Menü', 'globalkeys' ); ?>">
							<nav class="gk-account-drawer__nav">
								<a href="<?php echo esc_url( home_url( '/support/' ) ); ?>" class="<?php echo esc_attr( $gk_drawer_class( home_url( '/support/' ) ) ); ?>"><?php esc_html_e( 'Support 24/7', 'globalkeys' ); ?></a>
								<span class="gk-account-drawer__divider" aria-hidden="true"></span>
								<a href="<?php echo esc_url( $myaccount_url ); ?>" class="<?php echo esc_attr( $gk_drawer_class( $myaccount_url ) ); ?> gk-account-drawer__item--pill"><?php esc_html_e( 'Dashboard', 'globalkeys' ); ?></a>
								<a href="<?php echo esc_url( $orders_url ); ?>" class="<?php echo esc_attr( $gk_drawer_class( $orders_url ) ); ?>"><?php esc_html_e( 'Meine Einkäufe', 'globalkeys' ); ?></a>
								<a href="<?php echo esc_url( $wunschliste_url ); ?>" class="<?php echo esc_attr( $gk_drawer_class( $wunschliste_url ) ); ?>"><?php esc_html_e( 'Wunschliste', 'globalkeys' ); ?></a>
								<a href="<?php echo esc_url( $affiliate_url ); ?>" class="<?php echo esc_attr( $gk_drawer_class( $affiliate_url ) ); ?>"><?php esc_html_e( 'Partnerschaft', 'globalkeys' ); ?></a>
								<a href="<?php echo esc_url( $edit_account_url ); ?>" class="<?php echo esc_attr( $gk_drawer_class( $edit_account_url ) ); ?>"><?php esc_html_e( 'Einstellungen', 'globalkeys' ); ?></a>
								<span class="gk-account-drawer__divider" aria-hidden="true"></span>
								<div class="gk-account-drawer__toggle-row">
									<span class="gk-account-drawer__toggle-label"><?php esc_html_e( 'Videovorschau', 'globalkeys' ); ?></span>
									<label class="gk-account-drawer__switch" aria-label="<?php esc_attr_e( 'Videovorschau ein oder aus', 'globalkeys' ); ?>">
										<input type="checkbox" class="gk-account-drawer__switch-input" id="gk-drawer-videovorschau" name="videovorschau" autocomplete="off">
										<span class="gk-account-drawer__switch-track"></span>
									</label>
								</div>
								<span class="gk-account-drawer__divider" aria-hidden="true"></span>
								<a href="<?php echo esc_url( $logout_url ); ?>" class="gk-account-drawer__item"><?php esc_html_e( 'Abmelden', 'globalkeys' ); ?></a>
							</nav>
						</div>
					</div>
				<?php else : ?>
				<a href="<?php echo esc_url( $account_url ); ?>" class="header-icon-link header-account-link" aria-label="<?php esc_attr_e( 'Anmelden', 'globalkeys' ); ?>">
					<span class="header-icon header-icon-account" aria-hidden="true"></span>
				</a>
				<?php endif; ?>
			</div>
		</div>
	</header><!-- #masthead -->
		<?php endif; ?>
	<?php endif; ?>
