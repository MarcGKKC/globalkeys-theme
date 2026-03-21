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
$hero_product_img_default = get_template_directory_uri() . '/Pictures/' . rawurlencode( 'Sdlc-gk (1).jpg' );
$hero_product_img         = $hero_product_img_default;

$hero_pid = isset( $section['hero_product_id'] ) ? (int) $section['hero_product_id'] : 0;
$product  = function_exists( 'globalkeys_resolve_hero_product_by_id' ) ? globalkeys_resolve_hero_product_by_id( $hero_pid ) : null;
if ( ! $product && function_exists( 'globalkeys_fallback_hero_product' ) ) {
	$exclude = function_exists( 'globalkeys_get_hero_used_product_ids' ) ? globalkeys_get_hero_used_product_ids() : array();
	$product = globalkeys_fallback_hero_product( $exclude );
}
if ( $product && function_exists( 'globalkeys_register_hero_product_used' ) ) {
	globalkeys_register_hero_product_used( $product );
}

$product_url   = $product ? $product->get_permalink() : '';
$product_name  = $product ? $product->get_name() : __( 'Produkt', 'globalkeys' );

if ( $product ) {
	$hp_from_meta = function_exists( 'globalkeys_get_product_hero_image_url' ) ? globalkeys_get_product_hero_image_url( $product, 'full' ) : '';
	if ( $hp_from_meta ) {
		$hero_product_img = $hp_from_meta;
	} else {
		$hp_img_id = (int) $product->get_image_id();
		if ( $hp_img_id ) {
			$hp_url = wp_get_attachment_image_url( $hp_img_id, 'full' );
			if ( $hp_url ) {
				$hero_product_img = $hp_url;
			}
		}
	}
}
$price_html    = $product ? $product->get_price_html() : '—';
$discount_pct  = 0;
if ( $product && $product->is_on_sale() && ! $product->is_type( 'variable' ) ) {
	$regular = (float) $product->get_regular_price();
	$sale    = (float) $product->get_price();
	if ( $regular > 0 ) {
		$discount_pct = (int) round( ( ( $regular - $sale ) / $regular ) * 100 );
	}
}
?>

<section id="<?php echo esc_attr( $id ); ?>" class="gk-section gk-section-hero-product" role="region" aria-label="<?php echo esc_attr( $aria_label ); ?>" style="background-image: url('<?php echo esc_url( $hero_product_img ); ?>');">
	<?php if ( $product_url ) : ?>
		<a href="<?php echo esc_url( $product_url ); ?>" class="gk-section-hero-product-inner gk-section-hero-product-link">
			<div class="gk-section-hero-product-content">
				<span class="gk-section-hero-product-title"><?php echo esc_html( $product_name ); ?></span>
				<div class="gk-section-hero-product-price-row">
					<?php if ( $discount_pct > 0 ) : ?>
						<span class="gk-section-hero-product-badge">-<?php echo (int) $discount_pct; ?>%</span>
					<?php endif; ?>
					<span class="gk-section-hero-product-price"><?php echo $price_html; ?></span>
				</div>
			</div>
		</a>
	<?php else : ?>
		<div class="gk-section-hero-product-inner">
			<div class="gk-section-hero-product-content">
				<span class="gk-section-hero-product-title"><?php echo esc_html( $product_name ); ?></span>
				<div class="gk-section-hero-product-price-row">
					<span class="gk-section-hero-product-price"><?php echo $price_html; ?></span>
				</div>
			</div>
		</div>
	<?php endif; ?>
</section>
