<?php
/**
 * Template part for Hero Section (Produkthero ganz oben)
 *
 * @package globalkeys
 */

$section    = get_query_var( 'gk_section', array( 'id' => 'section-hero', 'aria_label' => __( 'Willkommensbereich', 'globalkeys' ) ) );
$id         = ! empty( $section['id'] ) ? $section['id'] : 'section-hero';
$aria_label = ! empty( $section['aria_label'] ) ? $section['aria_label'] : __( 'Hero', 'globalkeys' );

/* Fallback, wenn kein Produkt oder kein Produktbild */
$hero_bg_default = get_template_directory_uri() . '/Pictures/Elden Ring Key Art.jpeg.webp';
$hero_bg         = $hero_bg_default;

$hero_pid = isset( $section['hero_product_id'] ) ? (int) $section['hero_product_id'] : 0;
$product  = function_exists( 'globalkeys_resolve_hero_product_by_id' ) ? globalkeys_resolve_hero_product_by_id( $hero_pid ) : null;
if ( ! $product && function_exists( 'globalkeys_fallback_hero_product' ) ) {
	$product = globalkeys_fallback_hero_product( array() );
}
if ( $product && function_exists( 'globalkeys_register_hero_product_used' ) ) {
	globalkeys_register_hero_product_used( $product );
}

$product_url  = $product ? $product->get_permalink() : '';
$product_name = $product ? $product->get_name() : __( 'Willkommen bei GlobalKeys', 'globalkeys' );
$price_html   = $product ? $product->get_price_html() : '€0.00';

if ( $product ) {
	$hero_from_meta = function_exists( 'globalkeys_get_product_hero_image_url' ) ? globalkeys_get_product_hero_image_url( $product, 'full' ) : '';
	if ( $hero_from_meta ) {
		$hero_bg = $hero_from_meta;
	} else {
		$hero_img_id = (int) $product->get_image_id();
		if ( $hero_img_id ) {
			$hero_from_product = wp_get_attachment_image_url( $hero_img_id, 'full' );
			if ( $hero_from_product ) {
				$hero_bg = $hero_from_product;
			}
		}
	}
}
$discount_pct = 0;
if ( $product && $product->is_on_sale() && ! $product->is_type( 'variable' ) ) {
	$regular = (float) $product->get_regular_price();
	$sale    = (float) $product->get_price();
	if ( $regular > 0 ) {
		$discount_pct = (int) round( ( ( $regular - $sale ) / $regular ) * 100 );
	}
}

$hero_link_aria = $product_name;
if ( $product ) {
	$hero_link_aria .= ', ' . wp_strip_all_tags( html_entity_decode( $product->get_price_html(), ENT_QUOTES, 'UTF-8' ) );
}
if ( $discount_pct > 0 ) {
	$hero_link_aria .= ', ' . sprintf( __( '-%d%%', 'globalkeys' ), $discount_pct );
}
?>
<section id="<?php echo esc_attr( $id ); ?>" class="gk-section gk-section-hero has-hero-image" role="region" aria-label="<?php echo esc_attr( $aria_label ); ?>" style="background-image: url('<?php echo esc_url( $hero_bg ); ?>');">
	<?php if ( $product_url ) : ?>
		<a href="<?php echo esc_url( $product_url ); ?>" class="gk-hero-fullclick" aria-label="<?php echo esc_attr( $hero_link_aria ); ?>"><span class="screen-reader-text"><?php echo esc_html( $hero_link_aria ); ?></span></a>
	<?php endif; ?>
	<div class="gk-section-inner"></div>
	<div class="gk-hero-product-overlay"<?php echo $product_url ? ' aria-hidden="true"' : ''; ?>>
		<div class="gk-hero-product-block">
			<span class="gk-hero-product-title"><?php echo esc_html( $product_name ); ?></span>
			<div class="gk-hero-product-price-row">
				<?php if ( $discount_pct > 0 ) : ?>
					<span class="gk-hero-product-badge">-<?php echo (int) $discount_pct; ?>%</span>
				<?php endif; ?>
				<span class="gk-hero-product-price"><?php echo $price_html; ?></span>
			</div>
		</div>
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
