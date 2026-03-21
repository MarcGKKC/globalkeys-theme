<?php
/**
 * Template part for Recently Viewed Products Section
 *
 * 1:1 wie Featured/Bestsellers: gleiche Überschrift, gleiche Produktliste.
 * Zeigt zuletzt angesehene Produkte (Cookie gk_recently_viewed).
 *
 * @package globalkeys
 */

$section    = get_query_var( 'gk_section', array( 'id' => 'section-recently-viewed', 'aria_label' => __( 'Last seen', 'globalkeys' ) ) );
$id         = ! empty( $section['id'] ) ? $section['id'] : 'section-recently-viewed';
$aria_label = ! empty( $section['aria_label'] ) ? $section['aria_label'] : __( 'Last seen', 'globalkeys' );

$product_ids = array();
if ( ! empty( $_COOKIE['gk_recently_viewed'] ) ) {
	$product_ids = array_map( 'absint', array_filter( explode( ',', sanitize_text_field( wp_unslash( $_COOKIE['gk_recently_viewed'] ) ) ) ) );
}

$products = array();
if ( ! empty( $product_ids ) && function_exists( 'wc_get_products' ) ) {
	$products = wc_get_products(
		array(
			'status'  => 'publish',
			'limit'   => -1,
			'include' => $product_ids,
			'orderby' => 'post__in',
			'return'  => 'objects',
		)
	);
	// Reihenfolge wie im Cookie (neueste zuerst)
	$order = array_flip( $product_ids );
	usort( $products, function( $a, $b ) use ( $order ) {
		$pos_a = isset( $order[ $a->get_id() ] ) ? $order[ $a->get_id() ] : 999;
		$pos_b = isset( $order[ $b->get_id() ] ) ? $order[ $b->get_id() ] : 999;
		return $pos_a - $pos_b;
	} );
	$products = array_slice( $products, 0, 3 );
}
?>

<section id="<?php echo esc_attr( $id ); ?>" class="gk-section gk-section-bestsellers gk-section-recently-viewed" role="region" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
	<div class="gk-section-inner gk-section-featured-inner">
		<div class="gk-featured-heading-wrap">
			<h2 id="<?php echo esc_attr( $id ); ?>-title" class="gk-section-title gk-featured-heading">
				<span class="gk-featured-heading-text-wrap">
					<span class="gk-featured-heading-text"><?php esc_html_e( 'Last seen', 'globalkeys' ); ?></span>
					<span class="gk-featured-title-underline" aria-hidden="true"></span>
				</span>
				<span class="gk-featured-heading-arrow" aria-hidden="true"></span>
			</h2>
		</div>
		<?php if ( ! empty( $products ) ) : ?>
			<ul class="gk-featured-products" aria-label="<?php echo esc_attr( $aria_label ); ?>">
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
			<p class="gk-section-text gk-featured-empty"><?php esc_html_e( 'Du hast noch keine Produkte angesehen.', 'globalkeys' ); ?></p>
		<?php endif; ?>
	</div>
</section>
