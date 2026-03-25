<?php
/**
 * PC-Kollektion: „Our recommendations“ – drei Karten wie „Games for every budget“, mit Produkten.
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gk_pc_recommendations = array();
if ( function_exists( 'globalkeys_get_platform_trending_products' ) ) {
	$gk_pc_recommendations = globalkeys_get_platform_trending_products( 3, 'pc' );
}

$gk_pc_rec_fallback_bg = get_template_directory_uri() . '/Pictures/category-card-bg.svg';
$gk_pc_rec_id          = 'section-pc-recommendations';
?>

<section id="<?php echo esc_attr( $gk_pc_rec_id ); ?>" class="gk-section gk-section-budget-games gk-section-pc-recommendations" role="region" aria-labelledby="<?php echo esc_attr( $gk_pc_rec_id ); ?>-title">
	<div class="gk-section-inner gk-section-budget-inner">
		<div class="gk-featured-heading-wrap">
			<h2 id="<?php echo esc_attr( $gk_pc_rec_id ); ?>-title" class="gk-section-title gk-featured-heading">
				<span class="gk-featured-heading-text-wrap">
					<span class="gk-featured-heading-text"><?php esc_html_e( 'Our recommendations', 'globalkeys' ); ?></span>
					<span class="gk-featured-title-underline" aria-hidden="true"></span>
				</span>
			</h2>
		</div>
	</div>
	<?php if ( ! empty( $gk_pc_recommendations ) ) : ?>
		<div class="gk-test-boxes-wrapper gk-budget-games-wrapper gk-pc-recommendations-wrapper">
			<div class="gk-test-boxes-row gk-budget-games-row gk-pc-recommendations-row">
				<?php foreach ( $gk_pc_recommendations as $gk_rec_product ) : ?>
					<?php
					if ( ! $gk_rec_product || ! is_a( $gk_rec_product, 'WC_Product' ) || ! $gk_rec_product->is_visible() ) {
						continue;
					}
					$gk_rec_img_url = '';
					$gk_rec_img_id  = (int) $gk_rec_product->get_image_id();
					if ( $gk_rec_img_id ) {
						$gk_rec_img_url = wp_get_attachment_image_url( $gk_rec_img_id, 'large' );
					}
					if ( ! $gk_rec_img_url && function_exists( 'globalkeys_get_product_hero_image_url' ) ) {
						$gk_rec_img_url = globalkeys_get_product_hero_image_url( $gk_rec_product, 'large' );
					}
					if ( ! $gk_rec_img_url && function_exists( 'wc_placeholder_img_src' ) ) {
						$gk_rec_img_url = wc_placeholder_img_src( 'woocommerce_single' );
					}
					if ( ! $gk_rec_img_url ) {
						$gk_rec_img_url = $gk_pc_rec_fallback_bg;
					}
					$gk_rec_name = $gk_rec_product->get_name();
					?>
					<a class="gk-test-box gk-test-box--bg gk-pc-recommendation-box" href="<?php echo esc_url( $gk_rec_product->get_permalink() ); ?>" style="--gk-card-bg: url('<?php echo esc_url( $gk_rec_img_url ); ?>');">
						<span class="gk-test-box-label">
							<span class="gk-test-box-label-line" aria-hidden="true"></span>
							<span class="gk-test-box-label-text gk-pc-recommendation-box__title"><?php echo esc_html( $gk_rec_name ); ?></span>
						</span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	<?php else : ?>
		<div class="gk-section-inner gk-section-budget-inner">
			<p class="gk-section-text gk-featured-empty"><?php esc_html_e( 'No recommendations available yet.', 'globalkeys' ); ?></p>
		</div>
	<?php endif; ?>
</section>
