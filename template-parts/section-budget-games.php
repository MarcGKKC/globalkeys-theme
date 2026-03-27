<?php
/**
 * Games for every budget – 3 cards; order rotates via JS every 10 s.
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section    = get_query_var( 'gk_section', array( 'id' => 'section-budget-games', 'aria_label' => __( 'Games for every budget', 'globalkeys' ) ) );
$id         = ! empty( $section['id'] ) ? $section['id'] : 'section-budget-games';
$aria_label = ! empty( $section['aria_label'] ) ? $section['aria_label'] : __( 'Games for every budget', 'globalkeys' );

$gk_budget_browse_url = class_exists( 'WooCommerce' ) ? add_query_arg( 'post_type', 'product', home_url( '/' ) ) : home_url( '/' );
$gk_budget_cards      = array(
	array(
		'label'     => __( 'Spiele unter 20 $', 'globalkeys' ),
		'price_max' => 20,
	),
	array(
		'label'     => __( 'Spiele unter 10 $', 'globalkeys' ),
		'price_max' => 10,
	),
	array(
		'label'     => __( 'Spiele unter 5 $', 'globalkeys' ),
		'price_max' => 5,
	),
);

$gk_budget_bg_url = get_template_directory_uri() . '/Pictures/category-card-bg.svg';
?>

<section id="<?php echo esc_attr( $id ); ?>" class="gk-section gk-section-budget-games" role="region" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
	<div class="gk-section-inner gk-section-budget-inner">
		<div class="gk-featured-heading-wrap">
			<h2 id="<?php echo esc_attr( $id ); ?>-title" class="gk-section-title gk-featured-heading">
				<span class="gk-featured-heading-text-wrap">
					<span class="gk-featured-heading-text"><?php esc_html_e( 'Games for every budget', 'globalkeys' ); ?></span>
					<span class="gk-featured-title-underline" aria-hidden="true"></span>
				</span>
			</h2>
		</div>
	</div>
	<div class="gk-test-boxes-wrapper gk-budget-games-wrapper">
		<div class="gk-test-boxes-row gk-budget-games-row">
			<?php foreach ( $gk_budget_cards as $card ) : ?>
				<?php
				$card_label = isset( $card['label'] ) ? $card['label'] : '';
				$price_max  = isset( $card['price_max'] ) ? (int) $card['price_max'] : 0;
				$card_href  = $gk_budget_browse_url;
				if ( class_exists( 'WooCommerce' ) && $price_max > 0 ) {
					$card_href = add_query_arg(
						array(
							'gk_price_min'   => 0,
							'gk_price_max'   => max( 1, min( 999999, $price_max ) ),
							'gk_budget_from' => 'home',
						),
						$gk_budget_browse_url
					);
				}
				?>
			<a class="gk-test-box gk-test-box--bg" href="<?php echo esc_url( $card_href ); ?>" style="--gk-card-bg: url('<?php echo esc_url( $gk_budget_bg_url ); ?>');">
				<span class="gk-test-box-label">
					<span class="gk-test-box-label-line" aria-hidden="true"></span>
					<span class="gk-test-box-label-text"><?php echo esc_html( $card_label ); ?></span>
				</span>
			</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
