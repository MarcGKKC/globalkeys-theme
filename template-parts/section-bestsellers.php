<?php
/**
 * Template part for Bestsellers Section
 *
 * Design wie Featured, zeigt WooCommerce-Bestseller.
 *
 * @package globalkeys
 */

$section    = get_query_var( 'gk_section', array( 'id' => 'section-bestsellers', 'aria_label' => __( 'Bestseller', 'globalkeys' ) ) );
$id         = ! empty( $section['id'] ) ? $section['id'] : 'section-bestsellers';
$aria_label = ! empty( $section['aria_label'] ) ? $section['aria_label'] : __( 'Bestsellers', 'globalkeys' );

$products = array();
if ( function_exists( 'wc_get_products' ) ) {
	$products = wc_get_products(
		array(
			'status'  => 'publish',
			'limit'   => 8,
			'orderby' => 'popularity',
			'order'   => 'DESC',
		)
	);
}
?>

<section id="<?php echo esc_attr( $id ); ?>" class="gk-section gk-section-bestsellers" role="region" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
	<div class="gk-section-inner gk-section-featured-inner">
		<div class="gk-featured-heading-wrap">
			<h2 id="<?php echo esc_attr( $id ); ?>-title" class="gk-section-title gk-featured-heading">
				<span class="gk-featured-heading-text-wrap">
					<span class="gk-featured-heading-text"><?php esc_html_e( 'Bestsellers', 'globalkeys' ); ?></span>
					<span class="gk-featured-title-underline" aria-hidden="true"></span>
				</span>
				<span class="gk-featured-heading-arrow" aria-hidden="true"></span>
			</h2>
		</div>
		<?php if ( ! empty( $products ) ) : ?>
			<ul class="gk-featured-products" aria-label="<?php esc_attr_e( 'Bestseller', 'globalkeys' ); ?>">
				<?php foreach ( $products as $product ) : ?>
					<?php
					if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
						continue;
					}
					$sale_pct           = 0;
					$regular_price_html = '';
					if ( $product->is_on_sale() ) {
						if ( $product->is_type( 'variable' ) ) {
							$reg_raw  = (float) $product->get_variation_regular_price( 'min', true );
							$sale_raw = (float) $product->get_variation_sale_price( 'min', true );
							$reg_key  = $product->get_variation_regular_price( 'min', true );
						} else {
							$reg_raw  = (float) $product->get_regular_price();
							$sale_raw = (float) $product->get_sale_price();
							$reg_key  = $product->get_regular_price();
						}
						if ( $reg_raw > 0 && $sale_raw > 0 && $sale_raw < $reg_raw ) {
							$sale_pct = (int) round( ( 1 - $sale_raw / $reg_raw ) * 100 );
						}
						if ( '' !== $reg_key && $reg_raw > 0 ) {
							$regular_price_html = wc_price( wc_get_price_to_display( $product, array( 'price' => $reg_key ) ) );
						}
					}
					$current_price_html = wc_price( wc_get_price_to_display( $product ) );
					$aria_price         = wp_strip_all_tags( html_entity_decode( $current_price_html, ENT_QUOTES, 'UTF-8' ) );
					$aria_label_card    = $product->get_name() . ', ' . $aria_price;
					if ( $sale_pct > 0 ) {
						/* translators: %d: discount percentage */
						$aria_label_card .= ', ' . sprintf( __( '-%d%%', 'globalkeys' ), $sale_pct );
					}
					$gk_trailer_src = '';
					if ( function_exists( 'globalkeys_get_product_trailer_url' ) && function_exists( 'globalkeys_resolve_product_trailer_url' ) ) {
						$gk_trailer_raw = globalkeys_get_product_trailer_url( $product );
						if ( $gk_trailer_raw !== '' ) {
							$gk_trailer_src = globalkeys_resolve_product_trailer_url( $gk_trailer_raw );
						}
					}
					?>
					<li class="gk-featured-product">
						<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="gk-featured-product-link" aria-label="<?php echo esc_attr( $aria_label_card ); ?>">
							<span class="gk-featured-product-image<?php echo $gk_trailer_src ? ' has-trailer' : ''; ?>">
								<?php
								$gk_bestseller_img_id = $product->get_image_id();
								if ( $gk_bestseller_img_id ) {
									echo wp_get_attachment_image(
										(int) $gk_bestseller_img_id,
										'globalkeys-product-card',
										false,
										array(
											'alt'      => '',
											'class'    => 'gk-bestseller-product-img',
											'loading'  => 'lazy',
											'decoding' => 'async',
										)
									);
								} else {
									echo wc_placeholder_img( 'woocommerce_thumbnail' );
								}
								if ( $gk_trailer_src ) :
									?>
								<video
									class="gk-bestseller-trailer"
									src="<?php echo esc_url( $gk_trailer_src ); ?>"
									muted
									playsinline
									webkit-playsinline
									loop
									preload="metadata"
									tabindex="-1"
									aria-hidden="true"
								></video>
								<?php endif; ?>
							</span>
							<span class="gk-bestseller-meta-row" aria-hidden="true">
								<span class="gk-bestseller-title"><?php echo esc_html( $product->get_name() ); ?></span>
								<span class="gk-bestseller-price-bar">
									<?php if ( $sale_pct > 0 && '' !== $regular_price_html ) : ?>
										<span class="gk-bestseller-price-badge" aria-hidden="true"><?php echo esc_html( '-' . $sale_pct . '%' ); ?></span>
										<span class="gk-bestseller-price-was"><?php echo '<del>' . wp_kses_post( $regular_price_html ) . '</del>'; ?></span>
									<?php endif; ?>
									<span class="gk-bestseller-price-now"><?php echo wp_kses_post( $current_price_html ); ?></span>
								</span>
							</span>
						</a>
						<?php
						if ( function_exists( 'globalkeys_render_product_hover_panel' ) ) {
							globalkeys_render_product_hover_panel( $product );
						}
						?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php else : ?>
			<p class="gk-section-text gk-featured-empty"><?php esc_html_e( 'Aktuell sind keine Bestseller hinterlegt.', 'globalkeys' ); ?></p>
		<?php endif; ?>
	</div>
</section>
