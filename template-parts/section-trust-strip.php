<?php
/**
 * Template part for Trust Strip (Instant delivery, 24/7 Support, Trustpilot, etc.)
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
	$counts = wp_count_posts( 'product' );
	$games_count = isset( $counts->publish ) ? (int) $counts->publish : 0;
}
if ( $games_count <= 0 ) {
	$games_count = 3200;
}
?>

<section id="<?php echo esc_attr( $id ); ?>" class="gk-section gk-section-trust-strip" role="region" aria-label="<?php echo esc_attr( $aria_label ); ?>">
	<div class="gk-trust-strip-inner">
		<div class="gk-trust-strip-item">
			<span class="gk-trust-strip-icon" aria-hidden="true">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>
			</span>
			<span class="gk-trust-strip-text"><?php esc_html_e( 'Instant Delivery', 'globalkeys' ); ?></span>
		</div>
		<div class="gk-trust-strip-item">
			<span class="gk-trust-strip-icon" aria-hidden="true">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
			</span>
			<span class="gk-trust-strip-text"><?php esc_html_e( '24/7 Support', 'globalkeys' ); ?></span>
		</div>
		<div class="gk-trust-strip-item">
			<span class="gk-trust-strip-icon gk-trust-strip-icon--trustpilot" aria-hidden="true">★</span>
			<span class="gk-trust-strip-text">Trustpilot</span>
		</div>
		<div class="gk-trust-strip-item">
			<span class="gk-trust-strip-icon" aria-hidden="true">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
			</span>
			<span class="gk-trust-strip-text"><?php esc_html_e( 'Neu dabei', 'globalkeys' ); ?></span>
		</div>
		<div class="gk-trust-strip-item">
			<span class="gk-trust-strip-icon" aria-hidden="true">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
			</span>
			<span class="gk-trust-strip-text"><?php echo esc_html( sprintf( __( '%s Games im Angebot', 'globalkeys' ), number_format_i18n( $games_count ) ) ); ?></span>
		</div>
	</div>
</section>
