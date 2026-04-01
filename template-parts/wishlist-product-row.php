<?php
/**
 * Eine Wunschlisten-Zeile: Bild, Titel, Preis wie Bestseller-Karten; bei Galerie-Bildern Hover mit Gamepics (kein Trailer).
 *
 * Erwartet: $product (WC_Product) global oder Query-Var.
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

$tag_terms = array();
$raw_tags  = get_the_terms( (int) $product->get_id(), 'product_tag' );
if ( is_array( $raw_tags ) && ! is_wp_error( $raw_tags ) ) {
	$tag_terms = array_values(
		array_filter(
			$raw_tags,
			static function ( $term ) {
				return $term && isset( $term->slug, $term->name ) && $term->slug !== '';
			}
		)
	);
	usort(
		$tag_terms,
		static function ( $a, $b ) {
			return strcasecmp( $a->name, $b->name );
		}
	);
}
$tag_terms = apply_filters( 'gk_about_game_product_tags', $tag_terms, $product );

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
$permalink          = $product->get_permalink();

$gk_wl_gamepic_ids = array();
if ( function_exists( 'globalkeys_get_product_page_gallery_attachment_ids' ) ) {
	$gk_wl_gamepic_ids = array_values(
		array_filter(
			array_map( 'intval', globalkeys_get_product_page_gallery_attachment_ids( $product ) ),
			static function ( $id ) {
				return $id >= 1 && wp_attachment_is_image( $id );
			}
		)
	);
	$gk_wl_gamepics_max = (int) apply_filters( 'gk_wishlist_row_gamepics_max', 12, $product );
	if ( $gk_wl_gamepics_max > 0 && count( $gk_wl_gamepic_ids ) > $gk_wl_gamepics_max ) {
		$gk_wl_gamepic_ids = array_slice( $gk_wl_gamepic_ids, 0, $gk_wl_gamepics_max );
	}
}
$gk_wl_has_gamepics = count( $gk_wl_gamepic_ids ) > 0;

$gk_wl_steam_row     = function_exists( 'globalkeys_wishlist_get_product_steam_review_row' ) ? globalkeys_wishlist_get_product_steam_review_row( $product ) : null;
$gk_wl_release_text  = function_exists( 'globalkeys_wishlist_format_release_date_display' ) ? globalkeys_wishlist_format_release_date_display( $product ) : '';
$gk_wl_platform_key  = function_exists( 'globalkeys_get_product_platform_key' ) ? globalkeys_get_product_platform_key( $product ) : null;
$gk_wl_platform_icon = ( $gk_wl_platform_key && function_exists( 'globalkeys_get_product_platform_icon_url' ) )
	? globalkeys_get_product_platform_icon_url( $gk_wl_platform_key )
	: '';
$gk_wl_stage_raw     = $product->get_meta( '_gk_product_stage_label' );
$gk_wl_stage_label   = ( is_string( $gk_wl_stage_raw ) && trim( $gk_wl_stage_raw ) !== '' ) ? trim( $gk_wl_stage_raw ) : '';
$gk_wl_show_aside_foot = $gk_wl_steam_row || $gk_wl_release_text !== '' || $gk_wl_platform_icon !== '' || $gk_wl_stage_label !== '';

$gk_wl_owner_uid = isset( $GLOBALS['gk_wishlist_row_user_id'] ) ? (int) $GLOBALS['gk_wishlist_row_user_id'] : 0;
$gk_wl_show_strip = $gk_wl_owner_uid > 0 && get_current_user_id() === $gk_wl_owner_uid;
$gk_wl_added_date = '';
if ( $gk_wl_show_strip && function_exists( 'globalkeys_wishlist_format_product_added_date' ) ) {
	$gk_wl_added_date = globalkeys_wishlist_format_product_added_date( $gk_wl_owner_uid, (int) $product->get_id() );
}

$gk_wl_search_tags_lower = array();
foreach ( $tag_terms as $gk_wl_st ) {
	if ( $gk_wl_st && isset( $gk_wl_st->name ) ) {
		$gk_wl_search_tags_lower[] = mb_strtolower( (string) $gk_wl_st->name, 'UTF-8' );
	}
}
$gk_wl_search_tags_lower = array_values( array_unique( array_filter( $gk_wl_search_tags_lower ) ) );
$gk_wl_search_payload    = wp_json_encode(
	array(
		'n' => mb_strtolower( wp_strip_all_tags( $product->get_name() ), 'UTF-8' ),
		's' => mb_strtolower( (string) $product->get_sku(), 'UTF-8' ),
		't' => $gk_wl_search_tags_lower,
	),
	JSON_UNESCAPED_UNICODE
);

$gk_wl_list_idx = isset( $GLOBALS['gk_wishlist_row_list_index'] ) ? (int) $GLOBALS['gk_wishlist_row_list_index'] : 0;
$gk_wl_price_sort = (float) wc_get_price_to_display( $product );
$gk_wl_release_ts = function_exists( 'globalkeys_wishlist_product_release_ts_for_sort' )
	? globalkeys_wishlist_product_release_ts_for_sort( $product )
	: 0;
$gk_wl_added_ts = 0;
if ( $gk_wl_owner_uid > 0 && function_exists( 'globalkeys_wishlist_get_added_timestamps' ) ) {
	static $gk_wl_ts_map = null;
	static $gk_wl_ts_uid = null;
	if ( $gk_wl_ts_uid !== $gk_wl_owner_uid ) {
		$gk_wl_ts_map = globalkeys_wishlist_get_added_timestamps( $gk_wl_owner_uid );
		$gk_wl_ts_uid = $gk_wl_owner_uid;
	}
	$gk_wl_pid = (int) $product->get_id();
	if ( is_array( $gk_wl_ts_map ) && isset( $gk_wl_ts_map[ $gk_wl_pid ] ) ) {
		$gk_wl_added_ts = max( 0, (int) $gk_wl_ts_map[ $gk_wl_pid ] );
	}
}
$gk_wl_title_sort = wp_strip_all_tags( $product->get_name() );

$gk_wl_cart_btn_href  = '';
$gk_wl_cart_btn_rel   = '';
$gk_wl_cart_btn_label = '';
if ( class_exists( 'WooCommerce' ) ) {
	if ( $product->is_purchasable() && $product->is_in_stock() ) {
		$gk_wl_cart_btn_rel = 'nofollow';
		// Direkt in den Warenkorb: feste Cart-URL + ?add-to-cart= (zuverlässiger als add_query_arg auf „current“).
		if ( $product->is_type( 'variable' ) || $product->is_type( 'grouped' ) ) {
			$gk_wl_cart_btn_href = $product->get_permalink();
			$gk_wl_cart_btn_rel  = '';
			$gk_wl_cart_btn_label = trim( $product->add_to_cart_text() . ' — ' . wp_strip_all_tags( $product->get_name() ) );
		} elseif ( $product->is_type( 'external' ) ) {
			$gk_wl_cart_btn_href = $product->get_product_url();
			$gk_wl_cart_btn_rel  = 'nofollow noopener';
			$gk_wl_cart_btn_label = sprintf(
				/* translators: %s: product name */
				__( 'Read more about &quot;%s&quot;', 'woocommerce' ),
				wp_strip_all_tags( $product->get_name() )
			);
		} elseif ( $product->is_type( 'simple' ) || $product->is_type( 'subscription' ) ) {
			$cart_url = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '';
			$gk_wl_cart_btn_href = $cart_url
				? add_query_arg( 'add-to-cart', absint( $product->get_id() ), $cart_url )
				: $product->add_to_cart_url();
			$gk_wl_cart_btn_label = sprintf(
				/* translators: %s: product name */
				__( 'Add %s to cart', 'woocommerce' ),
				wp_strip_all_tags( $product->get_name() )
			);
		} else {
			$gk_wl_cart_btn_href = $product->add_to_cart_url();
			$gk_wl_cart_btn_label = sprintf(
				/* translators: %s: product name */
				__( 'Add %s to cart', 'woocommerce' ),
				wp_strip_all_tags( $product->get_name() )
			);
		}
	} else {
		$gk_wl_cart_btn_href = $permalink;
		$gk_wl_cart_btn_rel  = '';
		$gk_wl_cart_btn_label = sprintf(
			/* translators: %s: product name */
			__( 'Read more about &quot;%s&quot;', 'woocommerce' ),
			wp_strip_all_tags( $product->get_name() )
		);
	}
}
?>
<li
	class="gk-wishlist-row"
	data-gk-wl-search="<?php echo esc_attr( $gk_wl_search_payload ); ?>"
	data-gk-wl-order="<?php echo esc_attr( (string) $gk_wl_list_idx ); ?>"
	data-gk-wl-price="<?php echo esc_attr( (string) $gk_wl_price_sort ); ?>"
	data-gk-wl-release="<?php echo esc_attr( (string) $gk_wl_release_ts ); ?>"
	data-gk-wl-added="<?php echo esc_attr( (string) $gk_wl_added_ts ); ?>"
	data-gk-wl-title="<?php echo esc_attr( $gk_wl_title_sort ); ?>"
>
	<div class="gk-wishlist-row__box<?php echo $gk_wl_show_strip ? ' gk-wishlist-row__box--with-strip' : ''; ?>">
		<?php if ( $gk_wl_cart_btn_href !== '' ) : ?>
			<a
				class="gk-wishlist-row__cart-btn gk-wishlist-row__cart-btn--card-top"
				href="<?php echo esc_url( $gk_wl_cart_btn_href ); ?>"
				aria-label="<?php echo esc_attr( $gk_wl_cart_btn_label ); ?>"
				<?php echo $gk_wl_cart_btn_rel !== '' ? ' rel="' . esc_attr( $gk_wl_cart_btn_rel ) . '"' : ''; ?>
			>
				<span class="gk-wishlist-row__cart-btn-icon" aria-hidden="true"></span>
			</a>
		<?php endif; ?>
		<a
			class="gk-wishlist-row__thumb<?php echo $gk_wl_has_gamepics ? ' gk-wishlist-row__thumb--gamepics' : ''; ?>"
			href="<?php echo esc_url( $permalink ); ?>"
		>
			<span class="gk-wishlist-row__thumb-cover">
				<?php
				if ( function_exists( 'globalkeys_output_product_card_featured_image' ) ) {
					globalkeys_output_product_card_featured_image( $product, 'globalkeys-product-card' );
				} else {
					$img_id = $product->get_image_id();
					if ( $img_id ) {
						echo wp_get_attachment_image(
							(int) $img_id,
							'globalkeys-product-card',
							false,
							array(
								'alt'      => '',
								'class'    => 'gk-wishlist-row__img',
								'loading'  => 'lazy',
								'decoding' => 'async',
							)
						);
					} else {
						echo wc_placeholder_img( 'woocommerce_thumbnail' );
					}
				}
				?>
			</span>
			<?php if ( $gk_wl_has_gamepics ) : ?>
				<span class="gk-wishlist-row__gamepics-hover" aria-hidden="true">
					<?php
					$gk_wl_gpic_i = 0;
					foreach ( $gk_wl_gamepic_ids as $gk_wl_gpic_id ) {
						$gk_wl_gpic_class = 'gk-wishlist-row__gamepic-img' . ( 0 === $gk_wl_gpic_i ? ' is-active' : '' );
						echo wp_get_attachment_image(
							$gk_wl_gpic_id,
							'large',
							false,
							array(
								'class'    => $gk_wl_gpic_class,
								'loading'  => 'lazy',
								'decoding' => 'async',
								'alt'      => '',
							)
						);
						++$gk_wl_gpic_i;
					}
					?>
				</span>
			<?php endif; ?>
		</a>
		<div class="gk-wishlist-row__aside">
			<div class="gk-wishlist-row__body">
				<div class="gk-wishlist-row__head">
					<a class="gk-wishlist-row__title" href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
					<?php if ( ! empty( $tag_terms ) ) : ?>
						<div class="gk-wishlist-row__tags">
							<ul class="gk-wishlist-row__tag-list" aria-label="<?php esc_attr_e( 'Produkt-Tags', 'globalkeys' ); ?>">
								<?php foreach ( $tag_terms as $term ) : ?>
									<?php
									$link = get_term_link( $term );
									?>
									<li>
										<?php if ( ! is_wp_error( $link ) ) : ?>
											<a class="gk-wishlist-row__tag" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $term->name ); ?></a>
										<?php else : ?>
											<span class="gk-wishlist-row__tag"><?php echo esc_html( $term->name ); ?></span>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<?php if ( $gk_wl_show_aside_foot ) : ?>
				<div class="gk-wishlist-row__aside-foot">
					<?php if ( $gk_wl_steam_row || $gk_wl_release_text !== '' ) : ?>
						<div class="gk-wishlist-row__meta gk-wishlist-row__meta--aside-foot">
							<?php if ( $gk_wl_steam_row ) : ?>
								<div class="gk-wishlist-row__meta-line gk-wishlist-row__meta-line--steam">
									<span class="gk-wishlist-row__meta-label"><?php echo esc_html( $gk_wl_steam_row['label'] ); ?></span>
									<span class="gk-wishlist-row__meta-value">
										<?php echo esc_html( $gk_wl_steam_row['verdict'] ); ?>
										<?php if ( $gk_wl_steam_row['count'] > 0 ) : ?>
											<span class="gk-wishlist-row__meta-steam-count">(<?php echo esc_html( number_format_i18n( $gk_wl_steam_row['count'] ) ); ?>)</span>
										<?php endif; ?>
									</span>
								</div>
							<?php endif; ?>
							<?php if ( $gk_wl_release_text !== '' ) : ?>
								<div class="gk-wishlist-row__meta-line gk-wishlist-row__meta-line--release">
									<span class="gk-wishlist-row__meta-label"><?php esc_html_e( 'Release date:', 'globalkeys' ); ?></span>
									<span class="gk-wishlist-row__meta-value"><?php echo esc_html( $gk_wl_release_text ); ?></span>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					<?php if ( $gk_wl_stage_label !== '' || $gk_wl_platform_icon !== '' ) : ?>
						<div class="gk-wishlist-row__platform-row">
							<?php if ( $gk_wl_stage_label !== '' ) : ?>
								<span class="gk-wishlist-row__stage"><?php echo esc_html( $gk_wl_stage_label ); ?></span>
							<?php endif; ?>
							<?php if ( $gk_wl_platform_icon !== '' ) : ?>
								<img
									class="gk-wishlist-row__platform-icon"
									src="<?php echo esc_url( $gk_wl_platform_icon ); ?>"
									width="28"
									height="28"
									alt=""
									decoding="async"
									loading="lazy"
								/>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="gk-wishlist-row__price-row">
				<span class="gk-bestseller-price-bar gk-wishlist-row__price-bar">
					<?php if ( $sale_pct > 0 ) : ?>
						<span class="gk-bestseller-price-badge" aria-hidden="true"><?php echo esc_html( '-' . $sale_pct . '%' ); ?></span>
					<?php endif; ?>
					<?php if ( $sale_pct > 0 && '' !== $regular_price_html ) : ?>
						<span class="gk-bestseller-price-was"><?php echo '<del>' . wp_kses_post( $regular_price_html ) . '</del>'; ?></span>
					<?php endif; ?>
					<span class="gk-bestseller-price-now"><?php echo wp_kses_post( $current_price_html ); ?></span>
				</span>
			</div>
		</div>
		<?php if ( $gk_wl_show_strip ) : ?>
			<div class="gk-wishlist-row__wishlist-strip" data-product-id="<?php echo esc_attr( (string) $product->get_id() ); ?>">
				<span class="gk-wishlist-row__added-text">
					<?php
					if ( $gk_wl_added_date !== '' ) {
						printf(
							/* translators: %s: date */
							esc_html__( 'Hinzugefügt am %s', 'globalkeys' ),
							esc_html( $gk_wl_added_date )
						);
					} else {
						esc_html_e( 'Hinzugefügt', 'globalkeys' );
					}
					?>
				</span>
				<button
					type="button"
					class="gk-wishlist-row__remove-btn"
					aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product name */ __( '%s von der Wunschliste entfernen', 'globalkeys' ), wp_strip_all_tags( $product->get_name() ) ) ); ?>"
				>
					<?php esc_html_e( 'entfernen', 'globalkeys' ); ?>
				</button>
			</div>
		<?php endif; ?>
	</div>
</li>
