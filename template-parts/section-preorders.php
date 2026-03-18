<?php
/**
 * Template part for Pre-orders Section
 *
 * 1:1 wie Featured/Bestsellers/Last seen: gleiche Überschrift (mit Hover), gleiche Produktliste.
 * Zeigt Produkte aus der Kategorie "pre-order".
 *
 * @package globalkeys
 */

$section    = get_query_var( 'gk_section', array( 'id' => 'section-preorders', 'aria_label' => __( 'Pre-orders', 'globalkeys' ) ) );
$id         = ! empty( $section['id'] ) ? $section['id'] : 'section-preorders';
$aria_label = ! empty( $section['aria_label'] ) ? $section['aria_label'] : __( 'Pre-orders', 'globalkeys' );

$products = array();
if ( function_exists( 'wc_get_products' ) ) {
	$products = wc_get_products(
		array(
			'status'   => 'publish',
			'limit'    => 8,
			'category' => array( 'pre-order' ),
			'orderby'  => 'menu_order title',
			'order'    => 'ASC',
		)
	);
}
?>

<section id="<?php echo esc_attr( $id ); ?>" class="gk-section gk-section-preorders" role="region" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
	<div class="gk-section-inner gk-section-featured-inner">
		<div class="gk-featured-heading-wrap">
			<h2 id="<?php echo esc_attr( $id ); ?>-title" class="gk-section-title gk-featured-heading">
				<span class="gk-featured-heading-text-wrap">
					<span class="gk-featured-heading-text"><?php esc_html_e( 'Pre-orders', 'globalkeys' ); ?></span>
					<span class="gk-featured-title-underline" aria-hidden="true"></span>
				</span>
				<span class="gk-featured-heading-arrow" aria-hidden="true"></span>
			</h2>
		</div>
		<?php if ( ! empty( $products ) ) : ?>
			<ul class="gk-featured-products" aria-label="<?php echo esc_attr( $aria_label ); ?>">
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
			<p class="gk-section-text gk-featured-empty"><?php esc_html_e( 'Aktuell sind keine Pre-orders hinterlegt.', 'globalkeys' ); ?></p>
		<?php endif; ?>
	</div>
</section>
