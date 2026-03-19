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
						<span class="gk-footer-logo-green">Gaming. Global</span><span class="gk-footer-logo-white">Keys.</span>
					</p>
					<p class="gk-footer-desc">
						<?php esc_html_e( 'Your trusted source for digital game keys. Fast delivery, secure payments, and the best prices for PC, PlayStation, Xbox, and Nintendo.', 'globalkeys' ); ?>
					</p>
					<div class="gk-footer-newsletter">
						<p class="gk-footer-newsletter-heading"><?php esc_html_e( 'Get launch deals first', 'globalkeys' ); ?></p>
						<p class="gk-footer-newsletter-text"><?php esc_html_e( 'Join early for exclusive launch discounts, giveaways, and new releases.', 'globalkeys' ); ?></p>
						<form class="gk-footer-newsletter-form" action="#" method="post" aria-label="<?php esc_attr_e( 'Newsletter', 'globalkeys' ); ?>">
							<input type="email" name="email" placeholder="<?php esc_attr_e( 'Enter your email', 'globalkeys' ); ?>" class="gk-footer-newsletter-input" />
						</form>
					</div>
				</div>
				<div class="gk-footer-right">
					<div class="gk-footer-community">
						<p class="gk-footer-community-heading"><?php esc_html_e( 'Join our community', 'globalkeys' ); ?></p>
						<div class="gk-footer-social">
							<a href="#" class="gk-footer-social-link" aria-label="Instagram"><span class="gk-footer-social-icon">IG</span></a>
							<a href="#" class="gk-footer-social-link" aria-label="Discord"><img src="<?php echo esc_url( $template_uri . '/Pictures/social-discord.svg' ); ?>" alt="" width="24" height="24" /></a>
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
				<div class="gk-footer-payment-logos" aria-hidden="true">
					<span class="gk-footer-payment-logo" title="Amex">Amex</span>
					<span class="gk-footer-payment-logo" title="Apple Pay">Apple Pay</span>
					<span class="gk-footer-payment-logo" title="Bancontact">Bancontact</span>
					<span class="gk-footer-payment-logo" title="EPS">EPS</span>
					<span class="gk-footer-payment-logo" title="Google Pay">Google Pay</span>
					<span class="gk-footer-payment-logo" title="Klarna">Klarna</span>
					<span class="gk-footer-payment-logo" title="Maestro">Maestro</span>
					<span class="gk-footer-payment-logo" title="Mastercard">Mastercard</span>
					<?php if ( file_exists( get_template_directory() . '/Pictures/PayPal.svg' ) ) : ?>
					<img src="<?php echo esc_url( $template_uri . '/Pictures/PayPal.svg' ); ?>" alt="PayPal" class="gk-footer-payment-logo-img" width="48" height="24" />
					<?php else : ?><span class="gk-footer-payment-logo" title="PayPal">PayPal</span><?php endif; ?>
					<span class="gk-footer-payment-logo" title="Shop Pay">Shop Pay</span>
					<span class="gk-footer-payment-logo" title="UnionPay">UnionPay</span>
					<span class="gk-footer-payment-logo" title="Visa">Visa</span>
				</div>
				<span class="gk-footer-payment-label"><?php esc_html_e( 'PROTECTED WITH 256-BIT SSL', 'globalkeys' ); ?></span>
			</div>
		</div>

		<div class="gk-footer-bottom">
			<div class="gk-footer-bottom-inner">
				<p class="gk-footer-copyright">© <?php echo (int) date( 'Y' ); ?> GlobalKeys — <?php esc_html_e( 'All rights reserved.', 'globalkeys' ); ?></p>
				<div class="gk-footer-trustpilot">
					<p class="gk-footer-trustpilot-text"><?php esc_html_e( 'We are new on Trustpilot. We would love to hear back from you!', 'globalkeys' ); ?></p>
					<?php if ( file_exists( get_template_directory() . '/Pictures/Trustpilot_Logo__2022.svg' ) ) : ?>
					<a href="https://www.trustpilot.com" rel="noopener noreferrer" class="gk-footer-trustpilot-badge" aria-label="Trustpilot">
						<img src="<?php echo esc_url( $template_uri . '/Pictures/Trustpilot_Logo__2022.svg' ); ?>" alt="Trustpilot" width="120" height="32" />
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
