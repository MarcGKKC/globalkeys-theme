<?php
/**
 * Template part: Kleiner Hero-/Produktbereich unter Featured
 *
 * Zeigt ein Hero-/Produktbild mit Produktname, Preis und Rabatt-Badge. Klick führt zum Produkt.
 *
 * @package globalkeys
 */

$section         = get_query_var( 'gk_section', array( 'id' => 'section-hero-product', 'aria_label' => __( 'Produktbereich', 'globalkeys' ) ) );
$id              = ! empty( $section['id'] ) ? $section['id'] : 'section-hero-product';
$aria_label      = ! empty( $section['aria_label'] ) ? $section['aria_label'] : __( 'Produkt Hero', 'globalkeys' );
$hero_product_img = get_template_directory_uri() . '/Pictures/' . rawurlencode( 'Sdlc-gk (1).jpg' );

$product = null;
if ( function_exists( 'wc_get_products' ) ) {
	$products = wc_get_products(
		array(
			'featured' => true,
			'status'   => 'publish',
			'limit'    => 1,
			'orderby'  => 'menu_order title',
			'order'    => 'ASC',
		)
	);
	if ( ! empty( $products ) && is_a( $products[0], 'WC_Product' ) ) {
		$product = $products[0];
	}
}
if ( ! $product && function_exists( 'wc_get_products' ) ) {
	$products = wc_get_products( array( 'status' => 'publish', 'limit' => 1 ) );
	if ( ! empty( $products ) && is_a( $products[0], 'WC_Product' ) ) {
		$product = $products[0];
	}
}

$product_url   = $product ? $product->get_permalink() : '';
$product_name = $product ? $product->get_name() : '';
$price_html   = $product ? $product->get_price_html() : '';
$discount_pct = 0;
if ( $product && $product->is_on_sale() && ! $product->is_type( 'variable' ) ) {
	$regular = (float) $product->get_regular_price();
	$sale    = (float) $product->get_price();
	if ( $regular > 0 ) {
		$discount_pct = (int) round( ( ( $regular - $sale ) / $regular ) * 100 );
	}
}
?>

<section id="<?php echo esc_attr( $id ); ?>" class="gk-section gk-section-hero-product" role="region" aria-label="<?php echo esc_attr( $aria_label ); ?>" style="background-image: url('<?php echo esc_url( $hero_product_img ); ?>');">
	<?php if ( $product_url && $product_name ) : ?>
		<a href="<?php echo esc_url( $product_url ); ?>" class="gk-section-hero-product-inner gk-section-hero-product-link">
			<div class="gk-section-hero-product-content">
				<span class="gk-section-hero-product-title"><?php echo esc_html( $product_name ); ?></span>
				<div class="gk-section-hero-product-price-row">
					<span class="gk-section-hero-product-price"><?php echo $price_html; ?></span>
					<?php if ( $discount_pct > 0 ) : ?>
						<span class="gk-section-hero-product-badge">-<?php echo (int) $discount_pct; ?>%</span>
					<?php endif; ?>
				</div>
			</div>
		</a>
	<?php else : ?>
		<div class="gk-section-hero-product-inner">
			<div class="gk-section-hero-product-content">
				<span class="gk-section-hero-product-title"><?php esc_html_e( 'Produkt', 'globalkeys' ); ?></span>
				<span class="gk-section-hero-product-price"><?php echo $price_html ? $price_html : '—'; ?></span>
			</div>
		</div>
	<?php endif; ?>
</section>
