<?php
/**
 * Template for displaying product archives (shop + search results).
 *
 * Überschreibt WooCommerce Standard. Produktkarten wie Bestseller-Section:
 * gleiches Design, Trailer-Hover, Steam-Panel. Immer 3 pro Zeile.
 *
 * @package globalkeys
 */

defined( 'ABSPATH' ) || exit;

get_header();

$gk_is_search = get_search_query() !== '';
?>

	<main id="primary" class="site-main site-main--shop<?php echo $gk_is_search ? ' site-main--search-results' : ''; ?>">

		<?php if ( $gk_is_search ) : ?>
			<header class="gk-search-results-header">
				<h1 class="gk-search-results-title">
					<?php
					/* translators: %s: search query */
					printf( esc_html__( 'Suchergebnisse für: %s', 'globalkeys' ), '<span>' . get_search_query() . '</span>' );
					?>
				</h1>
			</header>
		<?php endif; ?>

		<?php do_action( 'woocommerce_before_main_content' ); ?>

		<?php if ( woocommerce_product_loop() ) : ?>

			<?php do_action( 'woocommerce_before_shop_loop' ); ?>

			<section class="gk-section gk-section-bestsellers gk-section-shop-results" role="region" aria-label="<?php esc_attr_e( 'Produkte', 'globalkeys' ); ?>">
				<div class="gk-section-inner gk-section-featured-inner">
					<ul class="gk-featured-products gk-featured-products--shop" aria-label="<?php esc_attr_e( 'Produkte', 'globalkeys' ); ?>">
						<?php
						while ( have_posts() ) {
							the_post();
							$product = wc_get_product( get_the_ID() );
							if ( $product && $product->is_visible() ) {
								$GLOBALS['product'] = $product;
								get_template_part( 'template-parts/product-card', 'bestseller' );
							}
						}
						?>
					</ul>
				</div>
			</section>

			<?php do_action( 'woocommerce_after_shop_loop' ); ?>

		<?php else : ?>

			<?php do_action( 'woocommerce_no_products_found' ); ?>

		<?php endif; ?>

		<?php do_action( 'woocommerce_after_main_content' ); ?>

	</main><!-- #primary -->

<?php
get_footer();
