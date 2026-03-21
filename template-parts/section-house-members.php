<?php
/**
 * Template part: House Members Section
 * Zeigt Produkte für Members (Tag „house-members“), Design wie Featured/Bestsellers.
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section    = get_query_var( 'gk_section', array( 'id' => 'section-house-members', 'aria_label' => __( 'House Members', 'globalkeys' ) ) );
$id         = ! empty( $section['id'] ) ? $section['id'] : 'section-house-members';
$aria_label = ! empty( $section['aria_label'] ) ? $section['aria_label'] : __( 'House Members', 'globalkeys' );

$products = array();
if ( function_exists( 'wc_get_products' ) ) {
	$args_tag = array(
		'status'  => 'publish',
		'limit'   => 8,
		'tag'     => array( 'house-members' ),
		'orderby' => 'menu_order title',
		'order'   => 'ASC',
	);
	if ( function_exists( 'globalkeys_wc_product_args_exclude_preorders' ) ) {
		$args_tag = globalkeys_wc_product_args_exclude_preorders( $args_tag );
	}
	$products = wc_get_products( $args_tag );
	if ( empty( $products ) ) {
		$args_feat = array(
			'featured' => true,
			'status'   => 'publish',
			'limit'    => 8,
			'orderby'  => 'menu_order title',
			'order'    => 'ASC',
		);
		if ( function_exists( 'globalkeys_wc_product_args_exclude_preorders' ) ) {
			$args_feat = globalkeys_wc_product_args_exclude_preorders( $args_feat );
		}
		$products = wc_get_products( $args_feat );
	}
}
?>

<section id="<?php echo esc_attr( $id ); ?>" class="gk-section gk-section-house-members" role="region" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
	<div class="gk-section-inner gk-section-house-members-inner gk-section-featured-inner">
		<div class="gk-featured-heading-wrap">
			<h2 id="<?php echo esc_attr( $id ); ?>-title" class="gk-section-title gk-featured-heading">
				<span class="gk-featured-heading-text-wrap">
					<span class="gk-featured-heading-text"><?php esc_html_e( 'House Members', 'globalkeys' ); ?></span>
					<span class="gk-featured-title-underline" aria-hidden="true"></span>
				</span>
				<span class="gk-featured-heading-arrow" aria-hidden="true"></span>
			</h2>
		</div>
		<?php if ( ! empty( $products ) ) : ?>
			<ul class="gk-featured-products" aria-label="<?php esc_attr_e( 'House Members Produkte', 'globalkeys' ); ?>">
				<?php foreach ( $products as $product ) : ?>
					<?php if ( ! $product || ! is_a( $product, 'WC_Product' ) ) { continue; } ?>
					<li class="gk-featured-product">
						<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="gk-featured-product-link">
							<span class="gk-featured-product-image">
								<?php echo $product->get_image( 'woocommerce_thumbnail', array( 'alt' => esc_attr( $product->get_name() ) ) ); ?>
							</span>
							<span class="gk-featured-product-title"><?php echo esc_html( $product->get_name() ); ?></span>
							<span class="gk-featured-product-price"><?php echo $product->get_price_html(); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php else : ?>
			<p class="gk-section-text gk-featured-empty"><?php esc_html_e( 'Aktuell sind keine House-Members-Produkte hinterlegt.', 'globalkeys' ); ?></p>
		<?php endif; ?>
	</div>
</section>
