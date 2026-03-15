<?php
/**
 * My Account page – Custom layout (Hero, Tabs, 2-col grid)
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 */

defined( 'ABSPATH' ) || exit;

$current_user  = wp_get_current_user();
$customer_id   = get_current_user_id();
$display_cid   = function_exists( 'globalkeys_get_display_customer_id' ) ? globalkeys_get_display_customer_id( $customer_id ) : (string) $customer_id;

// Avatar: custom oder gravatar fallback
$avatar_url  = function_exists( 'globalkeys_get_user_avatar_url' ) ? globalkeys_get_user_avatar_url( $current_user->ID, 290 ) : get_avatar_url( $current_user->ID, array( 'size' => 290 ) );
$gamertag    = get_user_meta( $current_user->ID, 'gamertag', true );
$gamertag    = ! empty( $gamertag ) ? $gamertag : ( ! empty( $current_user->user_login ) ? $current_user->user_login : $current_user->display_name );
$member_since = $current_user->user_registered ? date_i18n( 'M d, Y', strtotime( $current_user->user_registered ) ) : '';

$progress_current = 12;
$progress_max     = 50;
$progress_pct     = $progress_max > 0 ? min( 100, round( ( $progress_current / $progress_max ) * 100 ) ) : 0;

$total_items = 0;
if ( function_exists( 'wc_get_orders' ) ) {
	$orders = wc_get_orders( array( 'customer' => $customer_id, 'limit' => -1, 'return' => 'ids' ) );
	foreach ( $orders as $order_id ) {
		$order = wc_get_order( $order_id );
		if ( $order ) {
			$total_items += $order->get_item_count();
		}
	}
}
?>

<section class="gk-accdash">

	<div class="gk-accdash__outer">

		<!-- HERO -->
		<div class="gk-accdash__hero">
			<div class="gk-accdash__heroTopLeft gk-accdash__heroTopLeft--hidden">
				<?php esc_html_e( 'Customer ID:', 'globalkeys' ); ?> <span><?php echo esc_html( $display_cid ); ?></span>
			</div>

			<div class="gk-accdash__heroCenter">
				<div class="gk-accdash__heroAvatarWrap" role="button" tabindex="0" title="<?php esc_attr_e( 'Profilbild ändern', 'globalkeys' ); ?>">
					<div class="gk-accdash__heroAvatar" aria-hidden="true">
						<?php if ( $avatar_url ) : ?>
							<img class="gk-accdash__heroAvatarImg" src="<?php echo esc_url( $avatar_url ); ?>" alt="" width="145" height="145" style="object-position: center bottom;">
						<?php else : ?>
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M12 12c2.761 0 5-2.239 5-5S14.761 2 12 2 7 4.239 7 7s2.239 5 5 5Z"/>
								<path d="M4 22c0-4.418 3.582-8 8-8s8 3.582 8 8"/>
							</svg>
						<?php endif; ?>
						<span class="gk-accdash__heroAvatarOverlay"><?php esc_html_e( 'Ändern', 'globalkeys' ); ?></span>
					</div>
					<input type="file" id="gk-avatar-upload" name="avatar" accept="image/jpeg,image/png,image/gif,image/webp" class="gk-accdash__heroAvatarInput" aria-label="<?php esc_attr_e( 'Profilbild auswählen', 'globalkeys' ); ?>">
				</div>
				<div class="gk-accdash__nameRow">
					<div class="gk-accdash__gamertag"><?php echo esc_html( $gamertag ); ?></div>
					<div class="gk-accdash__progressBlock">
						<span class="gk-accdash__progressLabel">
							<?php echo (int) $progress_current; ?>/<?php echo (int) $progress_max; ?>
							<span class="gk-accdash__trophy" aria-hidden="true"></span>
						</span>
						<div class="gk-accdash__progressBar">
							<div class="gk-accdash__progressFill" style="width: <?php echo (int) $progress_pct; ?>%;"></div>
						</div>
					</div>
				</div>
				<?php if ( $member_since ) : ?>
					<div class="gk-accdash__meta"><?php printf( esc_html__( 'Registriert seit: %s', 'globalkeys' ), esc_html( $member_since ) ); ?></div>
				<?php endif; ?>
				<div class="gk-accdash__socials" aria-hidden="true">
					<span class="gk-accdash__socialIcon gk-accdash__socialIcon--steam" title="Steam"></span>
					<span class="gk-accdash__socialIcon gk-accdash__socialIcon--ubisoft" title="Ubisoft"></span>
					<span class="gk-accdash__socialIcon gk-accdash__socialIcon--ea" title="EA"></span>
					<span class="gk-accdash__socialIcon gk-accdash__socialIcon--youtube" title="YouTube"></span>
					<span class="gk-accdash__socialIcon gk-accdash__socialIcon--twitch" title="Twitch"></span>
					<span class="gk-accdash__socialIcon gk-accdash__socialIcon--discord" title="Discord"></span>
				</div>
			</div>

		</div>

		<?php
		$menu_items = function_exists( 'wc_get_account_menu_items' ) ? wc_get_account_menu_items() : array();
		$skip      = array( 'edit-account', 'edit-address', 'payment-methods', 'downloads' );
		$menu_items = array_diff_key( $menu_items, array_flip( $skip ) );
		if ( ! empty( $menu_items ) ) :
			?>
		<nav class="gk-accdash__tabs" aria-label="<?php esc_attr_e( 'Account Navigation', 'globalkeys' ); ?>">
			<?php foreach ( $menu_items as $endpoint => $label ) : ?>
				<?php
				$is_active = function_exists( 'wc_is_current_account_menu_item' ) && wc_is_current_account_menu_item( $endpoint );
				$is_logout = 'customer-logout' === $endpoint;
				$url       = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( $endpoint ) : wc_get_page_permalink( 'myaccount' );
				$classes   = 'gk-accdash__tab';
				if ( $is_active ) {
					$classes .= ' is-active';
				}
				if ( $is_logout ) {
					$classes .= ' gk-accdash__tab--danger';
				}
				?>
				<a href="<?php echo esc_url( $url ); ?>" class="<?php echo esc_attr( $classes ); ?>"<?php echo $is_active ? ' aria-current="page"' : ''; ?>>
					<?php echo esc_html( $label ); ?>
				</a>
			<?php endforeach; ?>
		</nav>
		<?php endif; ?>
	</div>
</section>
