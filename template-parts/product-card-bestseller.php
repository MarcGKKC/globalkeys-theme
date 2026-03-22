<?php
/**
 * Produktkarte im Bestseller-Stil (Trailer, Hover-Panel, Preis-Badge).
 * Wird von section-bestsellers, section-house-members (House Rewards) und archive-product genutzt.
 *
 * Erwartet: $product (WC_Product) via set_query_var oder global.
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product = isset( $product ) ? $product : get_query_var( 'product' );
if ( ! $product && isset( $GLOBALS['product'] ) ) {
	$product = $GLOBALS['product'];
}
if ( ! $product || ! is_a( $product, 'WC_Product' ) || ! $product->is_visible() ) {
	return;
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

$gk_house_rewards_card = (bool) get_query_var( 'gk_house_rewards_card_context', false );
$gk_house_discount_pct = null;
if ( $gk_house_rewards_card && function_exists( 'globalkeys_get_house_rewards_discount_pct_vs_regular' ) ) {
	$gk_house_discount_pct = globalkeys_get_house_rewards_discount_pct_vs_regular( $product );
}
$card_discount_pct = ( null !== $gk_house_discount_pct && $gk_house_discount_pct > 0 ) ? $gk_house_discount_pct : $sale_pct;

$current_price_html = wc_price( wc_get_price_to_display( $product ) );
$aria_price         = wp_strip_all_tags( html_entity_decode( $current_price_html, ENT_QUOTES, 'UTF-8' ) );
$aria_label_card    = $product->get_name() . ', ' . $aria_price;
if ( $card_discount_pct > 0 ) {
	/* translators: %d: discount percentage */
	$aria_label_card .= ', ' . sprintf( __( '-%d%%', 'globalkeys' ), $card_discount_pct );
}

if ( function_exists( 'globalkeys_is_preorder_product' ) && globalkeys_is_preorder_product( $product ) ) {
	$aria_label_card .= ', ' . __( 'Pre-order', 'globalkeys' );
	if ( function_exists( 'globalkeys_format_product_release_date_display' ) ) {
		$gk_po_date = globalkeys_format_product_release_date_display( $product );
		if ( $gk_po_date !== '' ) {
			$aria_label_card .= ', ' . $gk_po_date;
		}
	}
}
if ( $gk_house_rewards_card && function_exists( 'globalkeys_get_house_rewards_promo_badge_text' ) ) {
	$aria_label_card .= ', ' . globalkeys_get_house_rewards_promo_badge_text();
	$gk_hr_until = function_exists( 'globalkeys_get_house_rewards_promo_until_text' ) ? globalkeys_get_house_rewards_promo_until_text() : '';
	if ( $gk_hr_until !== '' ) {
		$aria_label_card .= ', ' . $gk_hr_until;
	}
}
$gk_house_ladder_html = '';
if ( $gk_house_rewards_card && function_exists( 'globalkeys_get_house_rewards_price_ladder_html' ) ) {
	$gk_house_ladder_html = globalkeys_get_house_rewards_price_ladder_html( $product );
	if ( $gk_house_ladder_html !== '' ) {
		$aria_label_card .= ', ' . wp_strip_all_tags( html_entity_decode( $gk_house_ladder_html, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
	}
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
			$gk_platform_key = function_exists( 'globalkeys_get_product_platform_key' ) ? globalkeys_get_product_platform_key( $product ) : null;
			$gk_platform_url = ( $gk_platform_key && function_exists( 'globalkeys_get_product_platform_icon_url' ) )
				? globalkeys_get_product_platform_icon_url( $gk_platform_key )
				: '';
			if ( $gk_platform_url ) :
				$labels = array(
					'playstation' => __( 'PlayStation', 'globalkeys' ),
					'xbox'        => __( 'Xbox', 'globalkeys' ),
					'nintendo'    => __( 'Nintendo', 'globalkeys' ),
					'steam'       => __( 'Steam', 'globalkeys' ),
				);
				$gk_platform_label = isset( $labels[ $gk_platform_key ] ) ? $labels[ $gk_platform_key ] : $gk_platform_key;
				?>
			<span class="gk-product-platform-badge" title="<?php echo esc_attr( $gk_platform_label ); ?>">
				<img
					class="gk-product-platform-badge__icon"
					src="<?php echo esc_url( $gk_platform_url ); ?>"
					width="32"
					height="32"
					alt=""
					decoding="async"
					loading="lazy"
				/>
			</span>
			<?php endif; ?>
			<?php
			if ( function_exists( 'globalkeys_output_product_card_featured_image' ) ) {
				globalkeys_output_product_card_featured_image( $product, 'globalkeys-product-card' );
			} else {
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
			<?php
			if ( $gk_house_rewards_card && function_exists( 'globalkeys_render_house_rewards_card_meta_row' ) ) {
				globalkeys_render_house_rewards_card_meta_row( $product );
			} elseif ( function_exists( 'globalkeys_render_preorder_badge_row' ) ) {
				globalkeys_render_preorder_badge_row( $product );
			}
			?>
			<?php
			$gk_price_bar_class = 'gk-bestseller-price-bar';
			if ( $gk_house_rewards_card && $gk_house_ladder_html !== '' ) {
				$gk_price_bar_class .= ' gk-bestseller-price-bar--house-ladder';
			}
			?>
			<span class="<?php echo esc_attr( $gk_price_bar_class ); ?>">
				<?php if ( $gk_house_ladder_html !== '' ) : ?>
					<?php if ( $card_discount_pct > 0 ) : ?>
						<span class="gk-bestseller-price-badge" aria-hidden="true"><?php echo esc_html( '-' . $card_discount_pct . '%' ); ?></span>
					<?php endif; ?>
					<?php
					if ( function_exists( 'wc_kses_price' ) ) {
						echo wc_kses_price( $gk_house_ladder_html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					} else {
						echo wp_kses_post( $gk_house_ladder_html );
					}
					?>
				<?php else : ?>
					<?php if ( $card_discount_pct > 0 ) : ?>
						<span class="gk-bestseller-price-badge" aria-hidden="true"><?php echo esc_html( '-' . $card_discount_pct . '%' ); ?></span>
					<?php endif; ?>
					<?php if ( $sale_pct > 0 && '' !== $regular_price_html ) : ?>
						<span class="gk-bestseller-price-was"><?php echo '<del>' . wp_kses_post( $regular_price_html ) . '</del>'; ?></span>
					<?php endif; ?>
					<span class="gk-bestseller-price-now"><?php echo wp_kses_post( $current_price_html ); ?></span>
				<?php endif; ?>
			</span>
		</span>
	</a>
	<?php
	if ( function_exists( 'globalkeys_render_product_hover_panel' ) ) {
		globalkeys_render_product_hover_panel( $product );
	}
	?>
</li>
