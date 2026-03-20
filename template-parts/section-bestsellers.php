<?php
/**
 * Template part for Bestsellers Section
 *
 * Design wie Featured, zeigt WooCommerce-Bestseller.
 *
 * @package globalkeys
 */

$section    = get_query_var( 'gk_section', array( 'id' => 'section-bestsellers', 'aria_label' => __( 'Bestseller', 'globalkeys' ) ) );
$id         = ! empty( $section['id'] ) ? $section['id'] : 'section-bestsellers';
$aria_label = ! empty( $section['aria_label'] ) ? $section['aria_label'] : __( 'Bestsellers', 'globalkeys' );

$products = array();
if ( function_exists( 'wc_get_products' ) ) {
	$products = wc_get_products(
		array(
			'status'  => 'publish',
			'limit'   => 9,
			'orderby' => 'popularity',
			'order'   => 'DESC',
		)
	);
}
?>

<section id="<?php echo esc_attr( $id ); ?>" class="gk-section gk-section-bestsellers" role="region" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
	<div class="gk-section-inner gk-section-featured-inner">
		<div class="gk-featured-heading-wrap">
			<h2 id="<?php echo esc_attr( $id ); ?>-title" class="gk-section-title gk-featured-heading">
				<span class="gk-featured-heading-text-wrap">
					<span class="gk-featured-heading-text"><?php esc_html_e( 'Bestsellers', 'globalkeys' ); ?></span>
					<span class="gk-featured-title-underline" aria-hidden="true"></span>
				</span>
				<span class="gk-featured-heading-arrow" aria-hidden="true"></span>
			</h2>
		</div>
		<?php if ( ! empty( $products ) ) : ?>
			<ul class="gk-featured-products" aria-label="<?php esc_attr_e( 'Bestseller', 'globalkeys' ); ?>">
				<?php foreach ( $products as $product ) : ?>
					<?php
					if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
						continue;
					}
					set_query_var( 'product', $product );
					get_template_part( 'template-parts/product-card', 'bestseller' );
					?>
				<?php endforeach; ?>
			</ul>
		<?php else : ?>
			<p class="gk-section-text gk-featured-empty"><?php esc_html_e( 'Aktuell sind keine Bestseller hinterlegt.', 'globalkeys' ); ?></p>
		<?php endif; ?>
	</div>
</section>
