<?php
/**
 * The template for displaying the footer
 *
 * @package globalkeys
 */

$template_uri = get_template_directory_uri();
$myaccount_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/' );
$orders_url    = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'orders', '', $myaccount_url ) : $myaccount_url;
$privacy_url   = get_privacy_policy_url();
?>

	<footer id="colophon" class="site-footer gk-footer">
		<div class="gk-footer-inner">
			<div class="gk-footer-top">
				<div class="gk-footer-left">
					<p class="gk-footer-logo">
						<span class="gk-footer-logo-green">G</span><span class="gk-footer-logo-white">aming. </span><span class="gk-footer-logo-green">G</span><span class="gk-footer-logo-white">lobalKeys</span><span class="gk-footer-logo-green">.</span>
					</p>
					<p class="gk-footer-desc">
						<?php esc_html_e( 'GlobalKeys offers digital game keys with secure checkout, fast worldwide delivery, and reliable support. We aim to make gaming more accessible with competitive pricing, new releases, and deals across platforms.', 'globalkeys' ); ?>
					</p>
					<p class="gk-footer-desc">
						<?php esc_html_e( 'Every game, DLC, and wallet credit key is sourced directly from publishers—grey-market keys are excluded. GlobalKeys only sells legitimate keys so you get a trusted experience.', 'globalkeys' ); ?>
					</p>
					<div class="gk-footer-locale" role="group" aria-label="<?php esc_attr_e( 'Currency, language and country', 'globalkeys' ); ?>">
						<div class="gk-footer-locale-inner">
							<div class="gk-footer-locale-row">
							<button type="button" class="gk-footer-locale-btn">
								<svg class="gk-footer-locale-icon gk-footer-locale-icon--fill" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 48 48" fill="currentColor" aria-hidden="true">
									<path d="M44,7.1V14a2,2,0,0,1-2,2H35a2,2,0,0,1-2-2.3A2.1,2.1,0,0,1,35.1,12h2.3A18,18,0,0,0,6.1,22.2a2,2,0,0,1-2,1.8h0a2,2,0,0,1-2-2.2A22,22,0,0,1,40,8.9V7a2,2,0,0,1,2.3-2A2.1,2.1,0,0,1,44,7.1Z"/>
									<path d="M4,40.9V34a2,2,0,0,1,2-2h7a2,2,0,0,1,2,2.3A2.1,2.1,0,0,1,12.9,36H10.6A18,18,0,0,0,41.9,25.8a2,2,0,0,1,2-1.8h0a2,2,0,0,1,2,2.2A22,22,0,0,1,8,39.1V41a2,2,0,0,1-2.3,2A2.1,2.1,0,0,1,4,40.9Z"/>
									<path d="M24.7,22c-3.5-.7-3.5-1.3-3.5-1.8s.2-.6.5-.9a3.4,3.4,0,0,1,1.8-.4,6.3,6.3,0,0,1,3.3.9,1.8,1.8,0,0,0,2.7-.5,1.9,1.9,0,0,0-.4-2.8A9.1,9.1,0,0,0,26,15.3V13a2,2,0,0,0-4,0v2.2c-3,.5-5,2.5-5,5.2s3.3,4.9,6.5,5.5,3.3,1.3,3.3,1.8-1.1,1.4-2.5,1.4h0a6.7,6.7,0,0,1-4.1-1.3,2,2,0,0,0-2.8.6,1.8,1.8,0,0,0,.3,2.6A10.9,10.9,0,0,0,22,32.8V35a2,2,0,0,0,4,0V32.8a6.3,6.3,0,0,0,3-1.3,4.9,4.9,0,0,0,2-4h0C31,23.8,27.6,22.6,24.7,22Z"/>
								</svg>
								<?php esc_html_e( 'Currency', 'globalkeys' ); ?>
							</button>
							<button type="button" class="gk-footer-locale-sep" tabindex="-1" aria-hidden="true"></button>
							<button type="button" class="gk-footer-locale-btn">
								<span class="gk-footer-locale-icon gk-footer-locale-icon--lang-mask" aria-hidden="true"></span>
								<?php esc_html_e( 'Language', 'globalkeys' ); ?>
							</button>
							<button type="button" class="gk-footer-locale-sep" tabindex="-1" aria-hidden="true"></button>
							<button type="button" class="gk-footer-locale-btn">
								<svg class="gk-footer-locale-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
								<?php esc_html_e( 'Country', 'globalkeys' ); ?>
							</button>
							</div>
						</div>
					</div>
					<script>
					(function () {
						var row = document.querySelector('.gk-footer-locale-row');
						if (!row) {
							return;
						}
						row.querySelectorAll('.gk-footer-locale-sep').forEach(function (sep) {
							sep.addEventListener('click', function () {
								var next = sep.nextElementSibling;
								if (next && next.classList.contains('gk-footer-locale-btn')) {
									next.click();
								}
							});
						});
					})();
					</script>
				</div>
				<div class="gk-footer-right">
					<div class="gk-footer-community">
						<p class="gk-footer-community-heading"><?php esc_html_e( 'Join our community:', 'globalkeys' ); ?></p>
						<div class="gk-footer-social">
							<a href="#" class="gk-footer-social-link" aria-label="Instagram"><img src="<?php echo esc_url( $template_uri . '/Pictures/Instagram-gk.svg' ); ?>" alt="" width="24" height="24" /></a>
							<a href="#" class="gk-footer-social-link" aria-label="Discord"><img src="<?php echo esc_url( $template_uri . '/Pictures/Discord1.1-gk.svg' ); ?>" alt="" width="24" height="24" /></a>
							<a href="#" class="gk-footer-social-link" aria-label="X"><img src="<?php echo esc_url( $template_uri . '/Pictures/Twitter-gk.svg' ); ?>" alt="" width="24" height="24" /></a>
							<a href="#" class="gk-footer-social-link" aria-label="Twitch"><img src="<?php echo esc_url( $template_uri . '/Pictures/social-twitch.svg' ); ?>" alt="" width="24" height="24" /></a>
							<a href="#" class="gk-footer-social-link" aria-label="TikTok"><img src="<?php echo esc_url( $template_uri . '/Pictures/tiktok-gk.svg' ); ?>" alt="" width="24" height="24" /></a>
						</div>
					</div>
					<nav class="gk-footer-links" aria-label="<?php esc_attr_e( 'Footer', 'globalkeys' ); ?>">
						<div class="gk-footer-col">
							<p class="gk-footer-col-title"><?php esc_html_e( 'Legal & Information', 'globalkeys' ); ?></p>
							<a href="<?php echo esc_url( home_url( '/terms-of-service/' ) ); ?>"><?php esc_html_e( 'Terms of Service', 'globalkeys' ); ?></a>
							<a href="<?php echo esc_url( $privacy_url ?: home_url( '/privacy-policy/' ) ); ?>"><?php esc_html_e( 'Privacy policy', 'globalkeys' ); ?></a>
							<a href="<?php echo esc_url( home_url( '/legal-notice/' ) ); ?>"><?php esc_html_e( 'Legal Notice', 'globalkeys' ); ?></a>
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'GlobalKeys', 'globalkeys' ); ?></a>
							<a href="<?php echo esc_url( home_url( '/business-contact/' ) ); ?>"><?php esc_html_e( 'Business Contact', 'globalkeys' ); ?></a>
						</div>
						<div class="gk-footer-col">
							<p class="gk-footer-col-title"><?php esc_html_e( 'Account Information', 'globalkeys' ); ?></p>
							<a href="<?php echo esc_url( $myaccount_url ); ?>"><?php esc_html_e( 'View Account', 'globalkeys' ); ?></a>
							<a href="<?php echo esc_url( $orders_url ); ?>"><?php esc_html_e( 'View Orders', 'globalkeys' ); ?></a>
							<a href="<?php echo esc_url( home_url( '/redeem/' ) ); ?>"><?php esc_html_e( 'Redeem Gift Card', 'globalkeys' ); ?></a>
							<a href="<?php echo esc_url( home_url( '/subscriptions/' ) ); ?>"><?php esc_html_e( 'Subscriptions', 'globalkeys' ); ?></a>
							<a href="<?php echo esc_url( function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'affiliate', '', $myaccount_url ) : $myaccount_url ); ?>"><?php esc_html_e( 'Affiliate Dashboard', 'globalkeys' ); ?></a>
						</div>
						<div class="gk-footer-col">
							<p class="gk-footer-col-title"><?php esc_html_e( 'Help & Support', 'globalkeys' ); ?></p>
							<a href="<?php echo esc_url( home_url( '/key-activation-guide/' ) ); ?>"><?php esc_html_e( 'Key activation guide', 'globalkeys' ); ?></a>
							<a href="<?php echo esc_url( home_url( '/create-a-ticket/' ) ); ?>"><?php esc_html_e( 'Create a ticket', 'globalkeys' ); ?></a>
							<a href="<?php echo esc_url( home_url( '/knowledge-base/' ) ); ?>"><?php esc_html_e( 'Knowledge Base', 'globalkeys' ); ?></a>
						</div>
					</nav>
				</div>
			</div>
		</div>

		<div class="gk-footer-payment-bar">
			<div class="gk-footer-payment-inner">
				<span class="gk-footer-payment-label"><?php esc_html_e( 'ACCEPTED PAYMENT CHANNELS', 'globalkeys' ); ?></span>
				<div class="gk-footer-payment-logos" role="list" aria-label="<?php esc_attr_e( 'Accepted payment methods', 'globalkeys' ); ?>">
					<?php
					$gk_pictures_dir = get_template_directory() . '/Pictures/';
					$gk_footer_payment_items = array(
						array( 'src' => 'payments/amex.svg', 'alt' => __( 'American Express', 'globalkeys' ), 'mono' => false ),
						array( 'src' => 'payments/applepay.svg', 'alt' => __( 'Apple Pay', 'globalkeys' ), 'mono' => true ),
						array( 'src' => 'payments/googlepay.svg', 'alt' => __( 'Google Pay', 'globalkeys' ), 'mono' => true ),
						array( 'src' => 'payments/ideal.svg', 'alt' => __( 'iDEAL', 'globalkeys' ), 'mono' => false ),
						array( 'src' => 'payments/klarna.svg', 'alt' => __( 'Klarna', 'globalkeys' ), 'mono' => true ),
						array( 'src' => 'payments/maestro.svg', 'alt' => __( 'Maestro', 'globalkeys' ), 'mono' => false ),
						array( 'src' => 'payments/mastercard.svg', 'alt' => __( 'Mastercard', 'globalkeys' ), 'mono' => false ),
						array( 'src' => 'PayPal.svg', 'alt' => __( 'PayPal', 'globalkeys' ), 'mono' => false ),
						array( 'src' => 'payments/unionpay.svg', 'alt' => __( 'UnionPay', 'globalkeys' ), 'mono' => false ),
						array( 'src' => 'payments/visa.svg', 'alt' => __( 'Visa', 'globalkeys' ), 'mono' => false ),
						array( 'src' => 'payments/discover.svg', 'alt' => __( 'Discover', 'globalkeys' ), 'mono' => false ),
					);
					foreach ( $gk_footer_payment_items as $gk_pay ) :
						if ( ! empty( $gk_pay['text'] ) ) :
							?>
					<span class="gk-footer-payment-pill gk-footer-payment-pill--text" role="listitem">
						<span class="gk-footer-payment-logo" title="<?php echo esc_attr( $gk_pay['title'] ); ?>"><?php echo esc_html( $gk_pay['text'] ); ?></span>
					</span>
							<?php
							continue;
						endif;
						$gk_pay_path = $gk_pictures_dir . $gk_pay['src'];
						if ( ! file_exists( $gk_pay_path ) ) {
							continue;
						}
						$gk_pay_classes = 'gk-footer-payment-logo-img';
						if ( ! empty( $gk_pay['mono'] ) ) {
							$gk_pay_classes .= ' gk-footer-payment-logo-img--mono';
						}
						?>
					<span class="gk-footer-payment-pill" role="listitem">
						<img src="<?php echo esc_url( $template_uri . '/Pictures/' . $gk_pay['src'] ); ?>" alt="<?php echo esc_attr( $gk_pay['alt'] ); ?>" width="40" height="24" class="<?php echo esc_attr( $gk_pay_classes ); ?>" loading="lazy" decoding="async" />
					</span>
						<?php
					endforeach;
					?>
				</div>
				<span class="gk-footer-payment-label"><?php esc_html_e( 'PROTECTED WITH 256-BIT SSL', 'globalkeys' ); ?></span>
			</div>
		</div>

		<div class="gk-footer-bottom">
			<div class="gk-footer-bottom-inner">
				<p class="gk-footer-copyright">© <?php echo (int) date( 'Y' ); ?> GlobalKeys — <?php esc_html_e( 'All rights reserved.', 'globalkeys' ); ?></p>
				<div class="gk-footer-trustpilot">
					<div class="gk-footer-trustpilot-copy">
						<p class="gk-footer-trustpilot-line1"><?php esc_html_e( 'We are new on Trustpilot.', 'globalkeys' ); ?></p>
						<p class="gk-footer-trustpilot-line2"><?php esc_html_e( 'We would love to hear back from you!', 'globalkeys' ); ?></p>
					</div>
					<?php
					$gk_tp_logo = 'Trustpilot_Logo_footer-darkbtn.svg';
					if ( ! file_exists( get_template_directory() . '/Pictures/' . $gk_tp_logo ) ) {
						$gk_tp_logo = 'Trustpilot_Logo__2022.svg';
					}
					if ( file_exists( get_template_directory() . '/Pictures/' . $gk_tp_logo ) ) :
						?>
					<a href="https://www.trustpilot.com" rel="noopener noreferrer" class="gk-footer-trustpilot-badge" target="_blank">
						<img src="<?php echo esc_url( $template_uri . '/Pictures/' . $gk_tp_logo ); ?>" alt="<?php echo esc_attr__( 'Trustpilot', 'globalkeys' ); ?>" class="gk-footer-trustpilot-badge-img" width="112" height="28" loading="lazy" decoding="async" />
					</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php
if ( function_exists( 'is_account_page' ) && is_account_page() && ! is_user_logged_in() ) {
	wp_body_open();
}
wp_footer();
?>
</body>
</html>
