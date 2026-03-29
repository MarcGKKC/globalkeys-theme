<?php
/**
 * Warenkorb: Summary (UVP / Rabatt / Total) + gleiche Raster-Klassen wie Budget-Banner.
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cart = ( class_exists( 'WooCommerce' ) && WC()->cart ) ? WC()->cart : null;

$gk_affiliate_dashboard_url = home_url( '/' );
if ( function_exists( 'wc_get_page_permalink' ) ) {
	$gk_acc_url = wc_get_page_permalink( 'myaccount' );
	if ( $gk_acc_url ) {
		$gk_affiliate_dashboard_url = $gk_acc_url;
	}
}

$gk_summary_official   = 0.0;
$gk_summary_merch      = 0.0;
$gk_summary_discount   = 0.0;
$gk_summary_total      = 0.0;

if ( $cart && ! $cart->is_empty() ) {
	foreach ( $cart->get_cart() as $cart_item ) {
		$product = isset( $cart_item['data'] ) ? $cart_item['data'] : null;
		if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
			continue;
		}
		$qty = (int) $cart_item['quantity'];
		if ( $qty < 1 ) {
			continue;
		}
		$reg = (float) wc_format_decimal( $product->get_regular_price() );
		if ( $reg <= 0 ) {
			$reg = (float) wc_format_decimal( $product->get_price() );
		}
		$gk_summary_official += (float) wc_get_price_to_display( $product, array( 'price' => $reg ) ) * $qty;
		$gk_summary_merch    += (float) $cart_item['line_subtotal'] + (float) $cart_item['line_subtotal_tax'];
	}
	$coupon_amt = (float) $cart->get_discount_total( 'edit' ) + (float) $cart->get_discount_tax();
	$gk_uvp_gap = max( 0.0, $gk_summary_official - $gk_summary_merch );
	// Eine Rabattzeile: Differenz UVP → Korbzeilen + Gutschein (wie Mockup).
	$gk_summary_discount = $gk_uvp_gap + $coupon_amt;
	$gk_summary_total    = (float) $cart->get_total( 'edit' );
}
?>
<div class="gk-cart-summary gk-cart-rail">
			<div class="gk-cart-summary__top">
			<h2 class="gk-cart-summary__title"><?php esc_html_e( 'Summary', 'globalkeys' ); ?></h2>
			<div class="gk-budget-search-banner gk-cart-summary__banner">
				<div class="gk-budget-search-banner-inner gk-cart-summary__inner">
					<?php if ( $cart && ! $cart->is_empty() ) : ?>
						<div class="gk-cart-summary__stack">
							<div class="gk-cart-summary__affiliate">
								<div class="gk-cart-summary__affiliate-label-row">
									<label class="gk-cart-summary__affiliate-label" for="gk-cart-affiliate-code"><?php esc_html_e( 'Affiliate code', 'globalkeys' ); ?></label><a class="gk-cart-summary__affiliate-asterisk" href="<?php echo esc_url( $gk_affiliate_dashboard_url ); ?>" aria-label="<?php esc_attr_e( 'Affiliate program and account dashboard', 'globalkeys' ); ?>">*</a>
								</div>
								<input
									type="text"
									id="gk-cart-affiliate-code"
									class="gk-cart-summary__affiliate-input"
									name="gk_affiliate_code"
									value=""
									autocomplete="off"
									placeholder="<?php esc_attr_e( 'Enter code', 'globalkeys' ); ?>"
								/>
							</div>
							<div class="gk-cart-summary__rows">
								<div class="gk-cart-summary__row gk-cart-summary__row--muted">
									<span class="gk-cart-summary__label"><?php esc_html_e( 'Official price', 'globalkeys' ); ?></span>
									<span class="gk-cart-summary__value"><?php echo wp_kses_post( wc_price( $gk_summary_official ) ); ?></span>
								</div>
								<div class="gk-cart-summary__row gk-cart-summary__row--muted">
									<span class="gk-cart-summary__label"><?php esc_html_e( 'Discount', 'globalkeys' ); ?></span>
									<span class="gk-cart-summary__value"><?php echo wp_kses_post( wc_price( -1 * $gk_summary_discount ) ); ?></span>
								</div>
								<div class="gk-cart-summary__row gk-cart-summary__row--total">
									<span class="gk-cart-summary__label"><?php esc_html_e( 'Total', 'globalkeys' ); ?></span>
									<span class="gk-cart-summary__value"><?php echo wp_kses_post( wc_price( $gk_summary_total ) ); ?></span>
								</div>
							</div>
							<a class="gk-cart-summary__next" href="<?php echo esc_url( wc_get_checkout_url() ); ?>">
								<span class="gk-cart-summary__next-text"><?php esc_html_e( 'Next', 'globalkeys' ); ?></span>
								<span class="gk-cart-summary__next-chevron" aria-hidden="true">
									<svg width="10" height="16" viewBox="0 0 10 16" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">
										<path d="M1.5 1L8 8l-6.5 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
								</span>
							</a>
						</div>
					<?php else : ?>
						<div class="gk-cart-summary__stack gk-cart-summary__stack--empty">
							<p class="gk-cart-summary__empty"><?php esc_html_e( 'Your cart is empty.', 'globalkeys' ); ?></p>
							<a class="gk-cart-summary__next" href="<?php echo esc_url( globalkeys_get_browse_all_games_url() ); ?>">
								<span class="gk-cart-summary__next-text"><?php esc_html_e( 'Continue shopping', 'globalkeys' ); ?></span>
								<span class="gk-cart-summary__next-chevron" aria-hidden="true">
									<svg width="10" height="16" viewBox="0 0 10 16" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">
										<path d="M1.5 1L8 8l-6.5 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
								</span>
							</a>
						</div>
					<?php endif; ?>
				</div>
			</div>
			</div>
			<?php if ( $cart && ! $cart->is_empty() ) : ?>
				<p class="gk-cart-summary__affiliate-footnote">
					<a class="gk-cart-summary__affiliate-footnote-asterisk" href="<?php echo esc_url( $gk_affiliate_dashboard_url ); ?>" aria-label="<?php esc_attr_e( 'Open account dashboard', 'globalkeys' ); ?>">*</a>
					<span class="gk-cart-summary__affiliate-footnote-text"><?php esc_html_e( 'Earn on qualifying orders that use your affiliate code at checkout. Tracking, terms and payouts are in your account.', 'globalkeys' ); ?></span>
				</p>
			<?php endif; ?>
		</div>
