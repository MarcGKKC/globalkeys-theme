<?php
/**
 * Template part: Trending-Section unter dem Carousel auf Plattform-Seiten (PC, PlayStation, …).
 * Plattform-Kollektionen: 6 Produkte (PC: Beliebtheit ohne PS/Xbox; PS/Xbox/Nintendo: Zufall). Homepage unverändert.
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gk_platform_trending = get_query_var( 'gk_platform' );
$gk_platform_trending = is_string( $gk_platform_trending ) ? $gk_platform_trending : '';

$products = function_exists( 'globalkeys_get_platform_trending_products' )
	? globalkeys_get_platform_trending_products( 6, $gk_platform_trending !== '' ? $gk_platform_trending : null )
	: array();
?>

<section id="section-platform-trending" class="gk-section gk-section-bestsellers gk-section-platform-trending" role="region" aria-labelledby="section-platform-trending-title">
	<div class="gk-section-inner gk-section-featured-inner">
		<div class="gk-featured-heading-wrap">
			<h2 id="section-platform-trending-title" class="gk-section-title gk-featured-heading">
				<span class="gk-featured-heading-text-wrap">
					<span class="gk-featured-heading-text"><?php esc_html_e( 'Trending', 'globalkeys' ); ?></span>
					<span class="gk-featured-title-underline" aria-hidden="true"></span>
				</span>
				<span class="gk-featured-heading-arrow" aria-hidden="true"></span>
			</h2>
		</div>
		<?php if ( ! empty( $products ) ) : ?>
			<ul class="gk-featured-products" aria-label="<?php esc_attr_e( 'Trending products', 'globalkeys' ); ?>">
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
			<p class="gk-section-text gk-featured-empty"><?php esc_html_e( 'Aktuell sind keine Trending-Produkte verfügbar.', 'globalkeys' ); ?></p>
		<?php endif; ?>
	</div>
</section>
