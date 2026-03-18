<?php
/**
 * Template part for Hero Section (Produkthero ganz oben)
 *
 * @package globalkeys
 */

$section    = get_query_var( 'gk_section', array( 'id' => 'section-hero', 'aria_label' => __( 'Willkommensbereich', 'globalkeys' ) ) );
$id         = ! empty( $section['id'] ) ? $section['id'] : 'section-hero';
$aria_label = ! empty( $section['aria_label'] ) ? $section['aria_label'] : __( 'Hero', 'globalkeys' );

$hero_bg = get_template_directory_uri() . '/Pictures/testbild2-gk.jpg';

$product = null;
if ( function_exists( 'wc_get_products' ) ) {
	$products = wc_get_products( array( 'featured' => true, 'status' => 'publish', 'limit' => 1, 'orderby' => 'menu_order title', 'order' => 'ASC' ) );
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

$product_url  = $product ? $product->get_permalink() : '';
$product_name = __( 'ARC Raiders - Standart Edition', 'globalkeys' );
$price_html   = $product ? $product->get_price_html() : '€0.00';
$discount_pct = 0;
if ( $product && $product->is_on_sale() && ! $product->is_type( 'variable' ) ) {
	$regular = (float) $product->get_regular_price();
	$sale    = (float) $product->get_price();
	if ( $regular > 0 ) {
		$discount_pct = (int) round( ( ( $regular - $sale ) / $regular ) * 100 );
	}
}
?>
<section id="<?php echo esc_attr( $id ); ?>" class="gk-section gk-section-hero has-hero-image" role="region" aria-label="<?php echo esc_attr( $aria_label ); ?>" style="background-image: url('<?php echo esc_url( $hero_bg ); ?>');">
	<div class="gk-section-inner"></div>
	<div class="gk-hero-product-overlay" aria-hidden="true">
		<?php if ( $product_url ) : ?>
			<a href="<?php echo esc_url( $product_url ); ?>" class="gk-hero-product-block gk-hero-product-link">
				<span class="gk-hero-product-title"><?php echo esc_html( $product_name ); ?></span>
				<div class="gk-hero-product-price-row">
					<?php if ( $discount_pct > 0 ) : ?>
						<span class="gk-hero-product-badge">-<?php echo (int) $discount_pct; ?>%</span>
					<?php endif; ?>
					<span class="gk-hero-product-price"><?php echo $price_html; ?></span>
				</div>
			</a>
		<?php else : ?>
			<div class="gk-hero-product-block">
				<span class="gk-hero-product-title"><?php echo esc_html( $product_name ); ?></span>
				<div class="gk-hero-product-price-row">
					<span class="gk-hero-product-price"><?php echo $price_html; ?></span>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>
<?php
$gk_hero_stats = array(
	array( 'number' => 12500,   'label' => __( 'Active Gamers', 'globalkeys' ) ),
	array( 'number' => 1840,    'label' => __( 'Created Accounts', 'globalkeys' ) ),
	array( 'number' => 2100000, 'label' => __( 'Satisfied Customers', 'globalkeys' ) ),
	array( 'number' => 47,      'label' => __( 'Active Partnerships', 'globalkeys' ) ),
	array( 'number' => 3200,    'label' => __( 'Games in Stock', 'globalkeys' ) ),
	array( 'number' => 42500,   'label' => __( 'Rewards Claimed', 'globalkeys' ) ),
);
?>
<div class="gk-hero-stats-bar-wrapper">
	<div class="gk-hero-stats-bar" role="region" aria-label="<?php esc_attr_e( 'Statistics', 'globalkeys' ); ?>">
		<?php foreach ( $gk_hero_stats as $i => $stat ) : ?>
			<?php if ( $i > 0 ) : ?><span class="gk-hero-stat-divider" aria-hidden="true"></span><?php endif; ?>
			<div class="gk-hero-stat">
				<span class="gk-hero-stat-number" data-end="<?php echo (int) $stat['number']; ?>"><?php echo esc_html( globalkeys_format_stat_number( $stat['number'] ) ); ?></span>
				<span class="gk-hero-stat-label"><?php echo esc_html( $stat['label'] ); ?></span>
			</div>
		<?php endforeach; ?>
	</div>
</div>
