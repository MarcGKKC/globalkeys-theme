<?php
/**
 * My Account Dashboard – Custom layout with stats and cards
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 */

defined( 'ABSPATH' ) || exit;

$customer_id  = get_current_user_id();
$orders_count = wc_get_customer_order_count( $customer_id );
$total_spent  = wc_get_customer_total_spent( $customer_id );

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

<div class="gk-dashboard">
	<div class="gk-dashboard__header">
		<h2 class="gk-dashboard__title"><?php esc_html_e( 'Dashboard', 'globalkeys' ); ?></h2>
		<p class="gk-dashboard__subtitle"><?php esc_html_e( 'Your activity, purchases and account highlights', 'globalkeys' ); ?></p>
	</div>

	<div class="gk-dashboard__overview">
		<div class="gk-dashboard__stats">
			<div class="gk-dashboard__stat">
				<span class="gk-dashboard__statNum"><?php echo (int) $orders_count; ?></span>
				<span class="gk-dashboard__statLabel"><?php esc_html_e( 'ORDERS', 'globalkeys' ); ?></span>
			</div>
			<div class="gk-dashboard__stat">
				<span class="gk-dashboard__statNum">0</span>
				<span class="gk-dashboard__statLabel"><?php esc_html_e( 'REVIEWS', 'globalkeys' ); ?></span>
			</div>
			<div class="gk-dashboard__stat">
				<span class="gk-dashboard__statNum">0</span>
				<span class="gk-dashboard__statLabel"><?php esc_html_e( 'WISHLIST', 'globalkeys' ); ?></span>
			</div>
			<div class="gk-dashboard__stat">
				<span class="gk-dashboard__statNum"><?php echo (int) $total_items; ?></span>
				<span class="gk-dashboard__statLabel"><?php esc_html_e( 'LIZENZEN', 'globalkeys' ); ?></span>
			</div>
		</div>

		<div class="gk-dashboard__row1">
			<div class="gk-dashboard__card gk-dashboard__card--twocol">
				<div class="gk-dashboard__cardHalf">
					<span class="gk-dashboard__cardIcon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
							<circle cx="12" cy="12" r="3"/>
						</svg>
					</span>
					<span class="gk-dashboard__cardTitle"><?php esc_html_e( 'Total saved', 'globalkeys' ); ?></span>
					<span class="gk-dashboard__cardValue">0,00 <?php echo esc_html( get_woocommerce_currency() ); ?></span>
				</div>
				<div class="gk-dashboard__cardDivider"></div>
				<div class="gk-dashboard__cardHalf">
					<span class="gk-dashboard__cardIcon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/>
							<path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/>
						</svg>
					</span>
					<span class="gk-dashboard__cardTitle"><?php esc_html_e( 'Total spent', 'globalkeys' ); ?></span>
					<span class="gk-dashboard__cardValue"><?php echo wp_kses_post( wc_price( $total_spent ) ); ?></span>
				</div>
			</div>
			<div class="gk-dashboard__card gk-dashboard__card--twocol">
				<div class="gk-dashboard__cardHalf">
					<span class="gk-dashboard__cardIcon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
							<path d="M3 6h18"/>
							<path d="M16 10a4 4 0 0 1-8 0"/>
						</svg>
					</span>
					<span class="gk-dashboard__cardTitle"><?php esc_html_e( 'Bestellungen', 'globalkeys' ); ?></span>
					<a href="<?php echo esc_url( wc_get_endpoint_url( 'orders', '', wc_get_page_permalink( 'myaccount' ) ) ); ?>" class="gk-dashboard__cardBtn"><?php esc_html_e( 'Ansehen', 'globalkeys' ); ?></a>
				</div>
				<div class="gk-dashboard__cardDivider"></div>
				<div class="gk-dashboard__cardHalf">
					<span class="gk-dashboard__cardIcon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M4 4h16v16H4V4Z"/>
							<path d="M4 8h16"/>
							<path d="M4 12h12"/>
							<path d="M4 16h8"/>
						</svg>
					</span>
					<span class="gk-dashboard__cardTitle"><?php esc_html_e( 'Lizenzarchiv', 'globalkeys' ); ?></span>
					<a href="<?php echo esc_url( wc_get_endpoint_url( 'orders', '', wc_get_page_permalink( 'myaccount' ) ) ); ?>" class="gk-dashboard__cardBtn"><?php esc_html_e( 'Ansehen', 'globalkeys' ); ?></a>
				</div>
			</div>
		</div>

		<div class="gk-dashboard__row2">
			<a href="#" class="gk-dashboard__card gk-dashboard__card--link">
				<span class="gk-dashboard__cardIcon gk-dashboard__cardIcon--green" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
					</svg>
				</span>
				<span class="gk-dashboard__cardTitle"><?php esc_html_e( 'Wishlist', 'globalkeys' ); ?></span>
				<span class="gk-dashboard__cardDesc">0 <?php esc_html_e( 'Saved titles', 'globalkeys' ); ?></span>
				<span class="gk-dashboard__cardBtn"><?php esc_html_e( 'Wishlist bearbeiten', 'globalkeys' ); ?></span>
			</a>
			<a href="#" class="gk-dashboard__card gk-dashboard__card--link">
				<span class="gk-dashboard__cardIcon gk-dashboard__cardIcon--green" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
						<path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
					</svg>
				</span>
				<span class="gk-dashboard__cardTitle"><?php esc_html_e( 'Partner-Link', 'globalkeys' ); ?></span>
				<span class="gk-dashboard__cardDesc"><?php esc_html_e( 'Link teilen und belohnt werden.', 'globalkeys' ); ?></span>
				<span class="gk-dashboard__cardBtn"><?php esc_html_e( 'Kopieren', 'globalkeys' ); ?></span>
			</a>
			<div class="gk-dashboard__card">
				<span class="gk-dashboard__cardIcon gk-dashboard__cardIcon--green" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M4 6h16v12H4V6Z"/>
						<path d="m4 7 8 6 8-6"/>
					</svg>
				</span>
				<span class="gk-dashboard__cardTitle"><?php esc_html_e( 'Übersicht', 'globalkeys' ); ?></span>
				<span class="gk-dashboard__cardDesc"><?php esc_html_e( 'Bestellungen & Lizenzen', 'globalkeys' ); ?></span>
				<a href="<?php echo esc_url( wc_get_endpoint_url( 'orders', '', wc_get_page_permalink( 'myaccount' ) ) ); ?>" class="gk-dashboard__cardBtn"><?php esc_html_e( 'Zu den Bestellungen', 'globalkeys' ); ?></a>
			</div>
		</div>
	</div>
</div>

<?php
do_action( 'woocommerce_account_dashboard' );
do_action( 'woocommerce_before_my_account' );
do_action( 'woocommerce_after_my_account' );
