<?php
/**
 * Template part for Featured Products Section
 *
 * Zeigt WooCommerce-Featured-Products unter der Hero-Stats-Bar.
 *
 * @package globalkeys
 */

$section    = get_query_var( 'gk_section', array( 'id' => 'section-featured', 'aria_label' => __( 'Empfohlene Produkte', 'globalkeys' ) ) );
$id         = ! empty( $section['id'] ) ? $section['id'] : 'section-featured';
$aria_label = ! empty( $section['aria_label'] ) ? $section['aria_label'] : __( 'Featured Products', 'globalkeys' );

$products = array();
if ( function_exists( 'wc_get_products' ) ) {
	$args_feat = array(
		'featured' => true,
		'status'   => 'publish',
		'limit'    => 6,
		'orderby'  => 'menu_order title',
		'order'    => 'ASC',
	);
	if ( function_exists( 'globalkeys_wc_product_args_exclude_preorders' ) ) {
		$args_feat = globalkeys_wc_product_args_exclude_preorders( $args_feat );
	}
	$products = wc_get_products( $args_feat );
	// Fallback: wenn keine Featured-Produkte, zeigen wir Bestseller
	if ( empty( $products ) ) {
		$args_pop = array(
			'status'  => 'publish',
			'limit'   => 6,
			'orderby' => 'popularity',
			'order'   => 'DESC',
		);
		if ( function_exists( 'globalkeys_wc_product_args_exclude_preorders' ) ) {
			$args_pop = globalkeys_wc_product_args_exclude_preorders( $args_pop );
		}
		$products = wc_get_products( $args_pop );
	}
}
?>

<section id="<?php echo esc_attr( $id ); ?>" class="gk-section gk-section-bestsellers gk-section-featured" role="region" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
	<div class="gk-section-inner gk-section-featured-inner">
		<div class="gk-featured-heading-wrap">
			<h2 id="<?php echo esc_attr( $id ); ?>-title" class="gk-section-title gk-featured-heading">
				<span class="gk-featured-heading-text-wrap">
					<span class="gk-featured-heading-text"><?php esc_html_e( 'Featured', 'globalkeys' ); ?></span>
					<span class="gk-featured-title-underline" aria-hidden="true"></span>
				</span>
				<span class="gk-featured-heading-arrow" aria-hidden="true"></span>
			</h2>
		</div>
		<?php if ( ! empty( $products ) ) : ?>
			<ul class="gk-featured-products" aria-label="<?php esc_attr_e( 'Empfohlene Produkte', 'globalkeys' ); ?>">
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
			<p class="gk-section-text gk-featured-empty"><?php esc_html_e( 'Aktuell sind keine Featured Products hinterlegt.', 'globalkeys' ); ?></p>
		<?php endif; ?>
	</div>
</section>
