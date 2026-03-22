<?php
/**
 * Full-width CTA bar for Premium / House membership (footer background colour).
 * Below House Rewards. Hidden for logged-in premium customers (not for shop admins).
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( function_exists( 'globalkeys_show_premium_member_cta_bar' ) && ! globalkeys_show_premium_member_cta_bar() ) {
	return;
}

$section    = get_query_var( 'gk_section', array( 'id' => 'section-premium-member-cta', 'aria_label' => __( 'Premium membership', 'globalkeys' ) ) );
$id         = ! empty( $section['id'] ) ? $section['id'] : 'section-premium-member-cta';
$aria_label = ! empty( $section['aria_label'] ) ? $section['aria_label'] : __( 'Premium membership', 'globalkeys' );

$cta_url = function_exists( 'globalkeys_house_member_cta_url' ) ? globalkeys_house_member_cta_url() : esc_url( home_url( '/subscriptions/' ) );

$gk_premium_perks = apply_filters(
	'globalkeys_premium_member_cta_perks',
	array(
		array(
			'icon'  => 'coins',
			'label' => __( '2x Points', 'globalkeys' ),
		),
		array(
			'icon'  => 'percent',
			'label' => __( 'Premium Discounts', 'globalkeys' ),
		),
		array(
			'icon'  => 'bolt',
			'label' => __( 'Update benefits', 'globalkeys' ),
		),
		array(
			'icon'  => 'gift',
			'label' => __( 'Exclusive Offers', 'globalkeys' ),
		),
		array(
			'icon'  => 'verify',
			'label' => __( 'Account Verification', 'globalkeys' ),
		),
	)
);
?>

<section id="<?php echo esc_attr( $id ); ?>" class="gk-section gk-section-premium-member-cta" role="region" aria-label="<?php echo esc_attr( $aria_label ); ?>">
	<div class="gk-section-premium-member-cta-inner">
		<div class="gk-premium-member-cta-top-row">
			<div class="gk-premium-member-cta-top-heading-col">
				<h2 class="gk-premium-member-cta-heading" id="<?php echo esc_attr( $id ); ?>-title">
					<?php esc_html_e( 'Become a Premium member', 'globalkeys' ); ?>
				</h2>
			</div>
			<p class="gk-premium-member-cta-subheading" id="<?php echo esc_attr( $id ); ?>-subheading">
				<span class="gk-premium-member-cta-subheading-text"><?php esc_html_e( 'Benefits of Premium', 'globalkeys' ); ?></span>
				<?php if ( function_exists( 'globalkeys_premium_badge_icon_svg' ) ) : ?>
					<span class="gk-premium-member-cta-subheading-badge" aria-hidden="true"><?php echo globalkeys_premium_badge_icon_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<?php endif; ?>
			</p>
			<div class="gk-premium-member-cta-top-actions">
				<a href="<?php echo esc_url( $cta_url ); ?>" class="gk-premium-member-cta-button">
					<?php esc_html_e( 'Explore Premium', 'globalkeys' ); ?>
				</a>
			</div>
		</div>

		<ul class="gk-premium-member-cta-perks" role="list">
			<?php foreach ( $gk_premium_perks as $perk ) : ?>
				<?php
				if ( ! is_array( $perk ) || empty( $perk['label'] ) ) {
					continue;
				}
				$icon_name = isset( $perk['icon'] ) ? (string) $perk['icon'] : 'crown';
				$icon_html  = function_exists( 'globalkeys_premium_member_cta_icon_svg' ) ? globalkeys_premium_member_cta_icon_svg( $icon_name ) : '';
				if ( $icon_html === '' ) {
					continue;
				}
				?>
				<li class="gk-premium-member-cta-perk">
					<span class="gk-premium-member-cta-icon" aria-hidden="true"><?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="gk-premium-member-cta-perk-label"><?php echo esc_html( $perk['label'] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
