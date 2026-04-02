<?php
/**
 * PC-Kollektion (/platform/pc/): „Top games in 2026“ – Bild links bündig (volle Höhe), rechts dieselben Produktkarten wie Trending/Bestseller (4 Stück).
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gk_platform = get_query_var( 'gk_platform' );
if ( ! is_string( $gk_platform ) || $gk_platform !== 'pc' ) {
	return;
}

$gk_pc_top_games_file = 'Kein Titel (2750 x 1150 px) (960 x 1150 px) (793 x 1150 px) (793 x 1055 px) (1055 x 1055 px) (2055 x 1055 px).svg';
$gk_pc_top_games_url  = trailingslashit( get_template_directory_uri() ) . 'Pictures/' . rawurlencode( $gk_pc_top_games_file );

$gk_top_games_products = function_exists( 'globalkeys_get_platform_trending_products' )
	? globalkeys_get_platform_trending_products( 4, 'pc' )
	: array();
?>

<section class="gk-section gk-section-pc-top-games-test" role="region" aria-labelledby="gk-pc-top-games-2026-title">
	<div class="gk-pc-top-games-test">
		<div class="gk-pc-top-games-test__art-col">
			<img
				class="gk-pc-top-games-test__art-img"
				src="<?php echo esc_url( $gk_pc_top_games_url ); ?>"
				width="2055"
				height="1055"
				alt=""
				decoding="async"
				loading="eager"
				fetchpriority="low"
			/>
		</div>
		<div class="gk-pc-top-games-test__body">
			<div class="gk-pc-top-games-test__body-inner">
				<div class="gk-featured-heading-wrap">
					<h2 id="gk-pc-top-games-2026-title" class="gk-section-title gk-featured-heading">
						<span class="gk-featured-heading-text-wrap">
							<span class="gk-featured-heading-text"><?php esc_html_e( 'Top games in 2026', 'globalkeys' ); ?></span>
							<span class="gk-featured-title-underline" aria-hidden="true"></span>
						</span>
					</h2>
				</div>
				<?php if ( ! empty( $gk_top_games_products ) ) : ?>
					<div class="gk-pc-top-games-test__bestsellers gk-section-bestsellers">
						<ul class="gk-featured-products gk-featured-products--pc-top-games-2026" aria-label="<?php esc_attr_e( 'Top games in 2026', 'globalkeys' ); ?>">
							<?php
							foreach ( $gk_top_games_products as $gk_product ) :
								if ( ! $gk_product || ! is_a( $gk_product, 'WC_Product' ) ) {
									continue;
								}
								set_query_var( 'product', $gk_product );
								get_template_part( 'template-parts/product-card', 'bestseller' );
							endforeach;
							?>
						</ul>
					</div>
				<?php else : ?>
					<p class="gk-pc-top-games-test__empty"><?php esc_html_e( 'No products to show yet.', 'globalkeys' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
