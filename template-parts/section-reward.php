<?php
/**
 * Template part: Reward-System Section (Werbung)
 * Links: Bild, Rechts: Text – 50/50 Layout.
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section    = get_query_var( 'gk_section', array( 'id' => 'section-reward', 'aria_label' => __( 'Reward-System', 'globalkeys' ) ) );
$id         = ! empty( $section['id'] ) ? $section['id'] : 'section-reward';
$aria_label = ! empty( $section['aria_label'] ) ? $section['aria_label'] : __( 'Reward-System', 'globalkeys' );

$reward_image_path = get_template_directory() . '/Pictures/';
$reward_image_file = glob( $reward_image_path . '*KATEGORIE*.svg' );
$reward_image      = ! empty( $reward_image_file ) && is_readable( $reward_image_file[0] )
	? get_template_directory_uri() . '/Pictures/' . rawurlencode( basename( $reward_image_file[0] ) )
	: get_template_directory_uri() . '/Pictures/parkbank.jpg';
?>

<section id="<?php echo esc_attr( $id ); ?>" class="gk-section gk-section-reward" role="region" aria-label="<?php echo esc_attr( $aria_label ); ?>">
	<div class="gk-section-reward-inner">
		<div class="gk-section-reward-image">
			<img src="<?php echo esc_url( $reward_image ); ?>" alt="<?php esc_attr_e( 'Reward-System', 'globalkeys' ); ?>" loading="lazy" />
		</div>
		<div class="gk-section-reward-content">
			<h2 id="<?php echo esc_attr( $id ); ?>-title" class="gk-section-reward-title"><?php esc_html_e( 'Earn juicy Rewards', 'globalkeys' ); ?></h2>
			<ul class="gk-section-reward-list">
				<li><?php esc_html_e( 'Collect points with every purchase and level up your rewards.', 'globalkeys' ); ?></li>
				<li><?php esc_html_e( 'Collect achievements and unlock badges as you use this website.', 'globalkeys' ); ?></li>
				<li><?php esc_html_e( 'Redeem points for discounts, free games, or premium content.', 'globalkeys' ); ?></li>
				<li><?php esc_html_e( 'Get early access to sales and member-only offers.', 'globalkeys' ); ?></li>
			</ul>
			<div class="gk-section-reward-buttons">
				<a href="#" class="gk-section-reward-btn gk-section-reward-btn--green"><?php esc_html_e( 'See Rewards', 'globalkeys' ); ?></a>
				<span class="gk-section-reward-buttons-divider" aria-hidden="true"></span>
				<a href="#" class="gk-section-reward-btn gk-section-reward-btn--outline"><?php esc_html_e( 'See Achievements', 'globalkeys' ); ?></a>
			</div>
		</div>
	</div>
</section>
