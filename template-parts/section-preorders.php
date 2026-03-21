<?php
/**
 * Template part for Pre-orders Section
 *
 * Wie Bestseller-Karten (Trailer, Hover-Panel). Produkte: Shop-Kategorie „pre-order“
 * oder Häkchen „In Pre-orders listen“ + optionales Erscheinungsdatum (Produktdaten).
 *
 * @package globalkeys
 */

$section    = get_query_var( 'gk_section', array( 'id' => 'section-preorders', 'aria_label' => __( 'Pre-orders', 'globalkeys' ) ) );
$id         = ! empty( $section['id'] ) ? $section['id'] : 'section-preorders';
$aria_label = ! empty( $section['aria_label'] ) ? $section['aria_label'] : __( 'Pre-orders', 'globalkeys' );

$products = array();
if ( function_exists( 'globalkeys_get_preorder_section_products' ) ) {
	$products = globalkeys_get_preorder_section_products( 12 );
} elseif ( function_exists( 'wc_get_products' ) ) {
	$products = wc_get_products(
		array(
			'status'   => 'publish',
			'limit'    => 12,
			'category' => array( 'pre-order' ),
			'orderby'  => 'menu_order title',
			'order'    => 'ASC',
		)
	);
}
?>

<section id="<?php echo esc_attr( $id ); ?>" class="gk-section gk-section-bestsellers gk-section-preorders" role="region" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
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
					<?php
					if ( ! $product || ! is_a( $product, 'WC_Product' ) || ! $product->is_visible() ) {
						continue;
					}
					set_query_var( 'product', $product );
					get_template_part( 'template-parts/product-card', 'bestseller' );
					?>
				<?php endforeach; ?>
			</ul>
		<?php else : ?>
			<p class="gk-section-text gk-featured-empty"><?php esc_html_e( 'Aktuell sind keine Pre-orders hinterlegt. Weise Produkte der Kategorie „pre-order“ zu oder aktiviere „In Pre-orders listen“ in den Produktdaten.', 'globalkeys' ); ?></p>
		<?php endif; ?>
	</div>
</section>
