<?php
/**
 * Template part: Trust Strip (wie Referenz: Icon, Titel, Untertitel, Trennlinien)
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section    = get_query_var( 'gk_section', array( 'id' => 'section-trust-strip', 'aria_label' => __( 'Vertrauen & Service', 'globalkeys' ) ) );
$id         = ! empty( $section['id'] ) ? $section['id'] : 'section-trust-strip';
$aria_label = ! empty( $section['aria_label'] ) ? $section['aria_label'] : __( 'Trust Strip', 'globalkeys' );

$games_count = 0;
if ( class_exists( 'WooCommerce' ) && function_exists( 'wc_get_products' ) ) {
	$counts     = wp_count_posts( 'product' );
	$games_count = isset( $counts->publish ) ? (int) $counts->publish : 0;
}
if ( $games_count <= 0 ) {
	$games_count = 3200;
}
?>

<section id="<?php echo esc_attr( $id ); ?>" class="gk-section gk-section-trust-strip" role="region" aria-label="<?php echo esc_attr( $aria_label ); ?>">
	<div class="gk-trust-strip-inner">
		<div class="gk-trust-strip-item">
			<span class="gk-trust-strip-icon gk-trust-strip-icon--img" aria-hidden="true" style="--gk-icon-url: url('<?php echo esc_url( get_template_directory_uri() . '/Pictures/download-gk.svg' ); ?>');">
			</span>
			<div class="gk-trust-strip-text">
				<span class="gk-trust-strip-title"><?php esc_html_e( 'Super fast', 'globalkeys' ); ?></span>
				<span class="gk-trust-strip-subtitle"><?php esc_html_e( 'Instant digital download', 'globalkeys' ); ?></span>
			</div>
		</div>
		<div class="gk-trust-strip-item">
			<span class="gk-trust-strip-icon gk-trust-strip-icon--img" aria-hidden="true" style="--gk-icon-url: url('<?php echo esc_url( get_template_directory_uri() . '/Pictures/safe-gk.svg' ); ?>');">
			</span>
			<div class="gk-trust-strip-text">
				<span class="gk-trust-strip-title"><?php esc_html_e( 'Reliable & secure', 'globalkeys' ); ?></span>
				<span class="gk-trust-strip-subtitle"><?php echo esc_html( sprintf( __( 'Over %s games', 'globalkeys' ), number_format_i18n( $games_count ) ) ); ?></span>
			</div>
		</div>
		<div class="gk-trust-strip-item">
			<span class="gk-trust-strip-icon gk-trust-strip-icon--img" aria-hidden="true" style="--gk-icon-url: url('<?php echo esc_url( get_template_directory_uri() . '/Pictures/support-gk.svg' ); ?>');">
			</span>
			<div class="gk-trust-strip-text">
				<span class="gk-trust-strip-title"><?php esc_html_e( 'Customer service', 'globalkeys' ); ?></span>
				<span class="gk-trust-strip-subtitle"><?php esc_html_e( 'Human support 24/7', 'globalkeys' ); ?></span>
			</div>
		</div>
		<div class="gk-trust-strip-item gk-trust-strip-item--reward">
			<a href="<?php echo esc_url( home_url( '/rewards/' ) ); ?>" class="gk-trust-strip-btn"><?php esc_html_e( 'Check it out', 'globalkeys' ); ?></a>
			<div class="gk-trust-strip-text">
				<span class="gk-trust-strip-title"><?php esc_html_e( 'Reward System', 'globalkeys' ); ?></span>
				<span class="gk-trust-strip-subtitle"><?php esc_html_e( 'Earn points with every purchase', 'globalkeys' ); ?></span>
			</div>
		</div>
	</div>
</section>
