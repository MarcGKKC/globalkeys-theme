<?php
/**
 * Support-Nav-Section: Breite wie Community-Karte (.gk-pc-community__frame) via gleiches Grid wie .gk-pc-community__carousel-wrap.
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gk_support_card_before_icon  = get_theme_file_uri( 'Pictures/Design ohne Titel (58).svg' );
$gk_support_card_refunds_icon = get_theme_file_uri( 'Pictures/Design ohne Titel (59).svg' );
$gk_support_card_rewards_icon = get_theme_file_uri( 'Pictures/Design ohne Titel (60).svg' );
?>
<div class="gk-support-stage-outer">
	<div class="gk-section-inner gk-section-featured-inner gk-support-featured">
		<div class="gk-support-box-outer">
			<div class="gk-support-community-carousel-mirror">
			<div class="gk-support-community-arrow-spacer" aria-hidden="true"></div>
			<div class="gk-support-stack">
				<section class="gk-support-hero" aria-labelledby="gk-support-hero-title">
					<h2 id="gk-support-hero-title" class="gk-support-hero__title"><?php esc_html_e( 'How can we help you?', 'globalkeys' ); ?></h2>
					<p class="gk-support-hero__lead"><?php esc_html_e( 'Answers to common questions — and help when you need it.', 'globalkeys' ); ?></p>
				</section>

				<div class="gk-support-panel-outer">
					<div class="gk-support-panel">
						<ul class="gk-support-cards">
							<li class="gk-support-card">
								<div class="gk-support-card__visual">
									<span class="gk-support-card__icon gk-support-card__icon--asset" style="<?php echo esc_attr( sprintf( "--gk-support-card-icon:url('%s')", esc_url( $gk_support_card_before_icon ) ) ); ?>" aria-hidden="true"></span>
								</div>
								<div class="gk-support-card__copy">
									<h3 class="gk-support-card__title"><?php esc_html_e( 'Before you buy', 'globalkeys' ); ?></h3>
									<p class="gk-support-card__text"><?php esc_html_e( 'What to know for a smooth purchase on GlobalKeys.', 'globalkeys' ); ?></p>
								</div>
							</li>
							<li class="gk-support-card">
								<div class="gk-support-card__visual">
									<span class="gk-support-card__icon gk-support-card__icon--asset" style="<?php echo esc_attr( sprintf( "--gk-support-card-icon:url('%s')", esc_url( $gk_support_card_refunds_icon ) ) ); ?>" aria-hidden="true"></span>
								</div>
								<div class="gk-support-card__copy">
									<h3 class="gk-support-card__title"><?php esc_html_e( 'Refunds & Payments', 'globalkeys' ); ?></h3>
									<p class="gk-support-card__text"><?php esc_html_e( 'Refunds, charge issues, and how payments work at checkout.', 'globalkeys' ); ?></p>
								</div>
							</li>
							<li class="gk-support-card">
								<div class="gk-support-card__visual">
									<span class="gk-support-card__icon gk-support-card__icon--asset" style="<?php echo esc_attr( sprintf( "--gk-support-card-icon:url('%s')", esc_url( $gk_support_card_rewards_icon ) ) ); ?>" aria-hidden="true"></span>
								</div>
								<div class="gk-support-card__copy">
									<h3 class="gk-support-card__title"><?php esc_html_e( 'Your Rewards', 'globalkeys' ); ?></h3>
									<p class="gk-support-card__text"><?php esc_html_e( 'Earn perks, redeem rewards, and get the most from your account.', 'globalkeys' ); ?></p>
								</div>
							</li>
						</ul>

						<span id="gk-support-request" class="gk-support-request-anchor" tabindex="-1"></span>
						<a class="gk-support-cta" href="#gk-support-request"><?php esc_html_e( 'Create a support request', 'globalkeys' ); ?></a>
					</div>
				</div>
			</div>
			<div class="gk-support-community-arrow-spacer" aria-hidden="true"></div>
			</div>
		</div>
	</div>
</div>
