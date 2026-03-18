<?php
/**
 * Spiele für jeden Geldbeutel – 3 Karten, gleiches Design wie Our Categories, ohne Carousel.
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section    = get_query_var( 'gk_section', array( 'id' => 'section-budget-games', 'aria_label' => __( 'Spiele für jeden Geldbeutel', 'globalkeys' ) ) );
$id         = ! empty( $section['id'] ) ? $section['id'] : 'section-budget-games';
$aria_label = ! empty( $section['aria_label'] ) ? $section['aria_label'] : __( 'Spiele für jeden Geldbeutel', 'globalkeys' );

$gk_budget_cards = array(
	__( 'Spiele unter 20 $', 'globalkeys' ),
	__( 'Spiele unter 10 $', 'globalkeys' ),
	__( 'Spiele unter 5 $', 'globalkeys' ),
);

$gk_budget_bg_url = get_template_directory_uri() . '/Pictures/category-card-bg.svg';
?>

<section id="<?php echo esc_attr( $id ); ?>" class="gk-section gk-section-budget-games" role="region" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
	<div class="gk-section-inner gk-section-budget-inner">
		<div class="gk-featured-heading-wrap">
			<h2 id="<?php echo esc_attr( $id ); ?>-title" class="gk-section-title gk-featured-heading">
				<span class="gk-featured-heading-text-wrap">
					<span class="gk-featured-heading-text"><?php esc_html_e( 'Spiele für jeden Geldbeutel', 'globalkeys' ); ?></span>
					<span class="gk-featured-title-underline" aria-hidden="true"></span>
				</span>
				<span class="gk-featured-heading-arrow" aria-hidden="true"></span>
			</h2>
		</div>
	</div>
	<div class="gk-test-boxes-wrapper gk-budget-games-wrapper">
		<div class="gk-test-boxes-row gk-budget-games-row">
			<?php foreach ( $gk_budget_cards as $card_label ) : ?>
			<div class="gk-test-box gk-test-box--bg" style="--gk-card-bg: url('<?php echo esc_url( $gk_budget_bg_url ); ?>');">
				<span class="gk-test-box-label">
					<span class="gk-test-box-label-line" aria-hidden="true"></span>
					<span class="gk-test-box-label-text"><?php echo esc_html( $card_label ); ?></span>
				</span>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
