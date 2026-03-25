<?php
/**
 * Plattform: „Best with Friends“ – drei zufällige Produktkarten (Bestseller-Kartenlayout).
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gk_platform_bwf = get_query_var( 'gk_platform' );
$gk_platform_bwf = is_string( $gk_platform_bwf ) ? $gk_platform_bwf : '';

$products = function_exists( 'globalkeys_get_best_with_friends_products' )
	? globalkeys_get_best_with_friends_products( 3, $gk_platform_bwf !== '' ? $gk_platform_bwf : null )
	: array();
?>

<section id="section-platform-best-with-friends" class="gk-section gk-section-bestsellers gk-section-platform-best-with-friends" role="region" aria-labelledby="section-platform-best-with-friends-title">
	<div class="gk-section-inner gk-section-featured-inner">
		<div class="gk-featured-heading-wrap">
			<h2 id="section-platform-best-with-friends-title" class="gk-section-title gk-featured-heading">
				<span class="gk-featured-heading-text-wrap">
					<span class="gk-featured-heading-text"><?php esc_html_e( 'Best with Friends', 'globalkeys' ); ?></span>
					<span class="gk-featured-title-underline" aria-hidden="true"></span>
				</span>
				<span class="gk-featured-heading-arrow" aria-hidden="true"></span>
			</h2>
		</div>
		<?php if ( ! empty( $products ) ) : ?>
			<ul class="gk-featured-products" aria-label="<?php esc_attr_e( 'Best with Friends', 'globalkeys' ); ?>">
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
			<p class="gk-section-text gk-featured-empty"><?php esc_html_e( 'Aktuell sind keine passenden Produkte verfügbar.', 'globalkeys' ); ?></p>
		<?php endif; ?>
	</div>
</section>
