<?php
/**
 * Template part: Featured-Game-Carousel für Plattform-Seiten (PC, PlayStation, …)
 *
 * Links: Trailer-Video (PC) oder großes Produktbild ohne Trailer (z. B. PlayStation-Platzhalter).
 * Rechts: Panel wie Hover-Box. Timer = Trailer-Länge oder Fallback (data-timer-fallback-ms).
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gk_platform_slug = get_query_var( 'gk_platform' );
if ( ! is_string( $gk_platform_slug ) || $gk_platform_slug === '' ) {
	return;
}

$products = function_exists( 'globalkeys_get_platform_featured_products' )
	? globalkeys_get_platform_featured_products( $gk_platform_slug, 5 )
	: array();

if ( empty( $products ) ) {
	return;
}
?>
<section class="gk-platform-featured-carousel" role="region" aria-label="<?php esc_attr_e( 'Featured games', 'globalkeys' ); ?>" data-timer-fallback-ms="5000">
	<div class="gk-platform-featured-carousel__inner">
		<div class="gk-platform-featured-carousel__transition-overlay" aria-hidden="true"></div>
		<?php foreach ( $products as $idx => $product ) : ?>
			<?php
			$is_active   = $idx === 0;
			$product_url = $product->get_permalink();
			$name        = $product->get_name();
			$trailer_raw = function_exists( 'globalkeys_get_product_trailer_url' ) ? globalkeys_get_product_trailer_url( $product ) : '';
			$trailer_src = ( $trailer_raw !== '' && function_exists( 'globalkeys_resolve_product_trailer_url' ) )
				? globalkeys_resolve_product_trailer_url( $trailer_raw )
				: '';
			/* Optional: HD-Trailer für Carousel (Meta _gk_product_trailer_carousel_url) */
			$carousel_trailer = $product->get_meta( '_gk_product_trailer_carousel_url' );
			if ( is_string( $carousel_trailer ) && trim( $carousel_trailer ) !== '' ) {
				$resolved = function_exists( 'globalkeys_resolve_product_trailer_url' ) ? globalkeys_resolve_product_trailer_url( trim( $carousel_trailer ) ) : trim( $carousel_trailer );
				if ( $resolved !== '' ) {
					$trailer_src = $resolved;
				}
			}
			$trailer_src = apply_filters( 'globalkeys_carousel_trailer_url', $trailer_src, $product );
			$img_id      = (int) $product->get_image_id();
			$img_url     = $img_id ? wp_get_attachment_image_url( $img_id, 'medium_large' ) : ( function_exists( 'wc_placeholder_img_src' ) ? wc_placeholder_img_src( 'woocommerce_thumbnail' ) : '' );
			if ( ! $img_url && function_exists( 'globalkeys_get_product_hero_image_url' ) ) {
				$img_url = globalkeys_get_product_hero_image_url( $product, 'medium_large' );
			}
			$cover_left_url = $img_id ? wp_get_attachment_image_url( $img_id, 'large' ) : '';
			if ( ! $cover_left_url && function_exists( 'globalkeys_get_product_hero_image_url' ) ) {
				$cover_left_url = globalkeys_get_product_hero_image_url( $product, 'large' );
			}
			if ( ! $cover_left_url && $img_url ) {
				$cover_left_url = $img_url;
			}
			if ( ! $cover_left_url && function_exists( 'wc_placeholder_img_src' ) ) {
				$cover_left_url = wc_placeholder_img_src( 'woocommerce_single' );
			}
			$raw_desc   = $product->get_short_description();
			if ( $raw_desc === '' ) {
				$raw_desc = $product->get_description();
			}
			$excerpt    = $raw_desc !== '' ? wp_trim_words( wp_strip_all_tags( $raw_desc ), 42, '…' ) : '';
			$created    = $product->get_date_created();
			$created_ts = ( $created && method_exists( $created, 'getTimestamp' ) ) ? (int) $created->getTimestamp() : 0;
			$release_ts = function_exists( 'globalkeys_get_product_release_timestamp' ) ? globalkeys_get_product_release_timestamp( $product ) : 0;
			$date_ts    = $release_ts > 0 ? $release_ts : $created_ts;
			$released_display = '';
			if ( $date_ts > 0 ) {
				$date_formatted = wp_date( get_option( 'date_format' ), $date_ts );
				$today_ymd      = wp_date( 'Y-m-d', current_time( 'timestamp' ) );
				$release_ymd    = $release_ts > 0 ? wp_date( 'Y-m-d', $release_ts ) : '';
				$is_not_out_yet = false;
				if ( $release_ts > 0 ) {
					$is_not_out_yet = ( $release_ymd > $today_ymd );
				} elseif ( function_exists( 'globalkeys_is_preorder_product' ) && globalkeys_is_preorder_product( $product ) ) {
					$is_not_out_yet = true;
				}
				if ( $is_not_out_yet ) {
					$released_display = sprintf( __( 'Release: %s', 'globalkeys' ), $date_formatted );
				} else {
					$released_display = sprintf( __( 'Released: %s', 'globalkeys' ), $date_formatted );
				}
			}
			$tags             = get_the_terms( (int) $product->get_id(), 'product_tag' );
			$tags_heading     = __( 'Tags:', 'globalkeys' );
			if ( is_wp_error( $tags ) || empty( $tags ) ) {
				$tags         = get_the_terms( (int) $product->get_id(), 'product_cat' );
				$tags_heading = __( 'Kategorien:', 'globalkeys' );
			}
			$tags = is_array( $tags ) ? array_values( array_filter( $tags, static function ( $t ) {
				return $t && isset( $t->slug ) && $t->slug !== 'uncategorized';
			} ) ) : array();
			$tags_display = array_slice( $tags, 0, 6 );
			$tag_more_count = max( 0, count( $tags ) - 6 );
			$discount_pct = 0;
			if ( $product->is_on_sale() && ! $product->is_type( 'variable' ) ) {
				$regular = (float) $product->get_regular_price();
				$sale    = (float) $product->get_price();
				if ( $regular > 0 ) {
					$discount_pct = (int) round( ( ( $regular - $sale ) / $regular ) * 100 );
				}
			}
			$reg_html  = '';
			$curr_html = '';
			if ( $product->is_on_sale() ) {
				$reg_price = $product->get_regular_price();
				if ( $reg_price ) {
					$reg_html = wc_price( wc_get_price_to_display( $product, array( 'price' => $reg_price ) ) );
				}
			}
			$curr_price = $product->get_price();
			if ( $curr_price !== '' ) {
				$curr_html = wc_price( wc_get_price_to_display( $product, array( 'price' => $curr_price ) ) );
			} else {
				$curr_html = $product->get_price_html();
			}
			?>
			<article class="gk-platform-featured-slide<?php echo $is_active ? ' is-active' : ''; ?>" data-index="<?php echo (int) $idx; ?>" aria-hidden="<?php echo $is_active ? 'false' : 'true'; ?>">
				<a href="<?php echo esc_url( $product_url ); ?>" class="gk-platform-featured-slide__link">
					<div class="gk-platform-featured-slide__trailer-wrap">
						<?php if ( $trailer_src !== '' ) : ?>
							<video class="gk-platform-featured-slide__trailer" muted playsinline preload="<?php echo $idx === 0 ? 'auto' : 'metadata'; ?>" aria-hidden="true"
								<?php echo $idx === 0 ? ' fetchpriority="high" ' : ''; ?>
								src="<?php echo esc_url( $trailer_src ); ?>"></video>
							<span class="gk-platform-featured-slide__trailer-label"><?php esc_html_e( 'Trailervorschau', 'globalkeys' ); ?> | <?php echo esc_html( $name ); ?></span>
						<?php else : ?>
							<img
								class="gk-platform-featured-slide__trailer-fallback"
								src="<?php echo esc_url( $cover_left_url ); ?>"
								alt=""
								decoding="async"
								loading="<?php echo $idx === 0 ? 'eager' : 'lazy'; ?>"
								<?php echo $idx === 0 ? ' fetchpriority="high"' : ''; ?>
							/>
							<span class="gk-platform-featured-slide__trailer-label"><?php esc_html_e( 'Featured', 'globalkeys' ); ?> | <?php echo esc_html( $name ); ?></span>
						<?php endif; ?>
					</div>
					<aside class="gk-platform-featured-slide__panel">
						<?php if ( $img_url ) : ?>
							<div class="gk-platform-featured-slide__panel-img-wrap">
								<img src="<?php echo esc_url( $img_url ); ?>" alt="" class="gk-platform-featured-slide__panel-img" loading="lazy" />
							</div>
						<?php endif; ?>
						<div class="gk-platform-featured-slide__panel-body">
							<h3 class="gk-product-hover-panel__title"><?php echo esc_html( $name ); ?></h3>
							<?php if ( $released_display !== '' ) : ?>
								<p class="gk-product-hover-panel__released"><?php echo esc_html( $released_display ); ?></p>
							<?php endif; ?>
							<?php if ( $excerpt !== '' ) : ?>
								<p class="gk-product-hover-panel__excerpt"><?php echo esc_html( $excerpt ); ?></p>
							<?php endif; ?>
							<?php if ( ! empty( $tags_display ) ) : ?>
								<div class="gk-product-hover-panel__tags">
									<p class="gk-product-hover-panel__tags-heading"><?php echo esc_html( $tags_heading ); ?></p>
									<ul class="gk-product-hover-panel__tag-list">
										<?php foreach ( $tags_display as $term ) : ?>
											<li><span class="gk-product-hover-panel__tag"><?php echo esc_html( $term->name ); ?></span></li>
										<?php endforeach; ?>
										<?php if ( $tag_more_count > 0 ) : ?>
											<li>
												<span class="gk-product-hover-panel__tag" title="<?php echo esc_attr( sprintf( _n( '%d more tag', '%d more tags', $tag_more_count, 'globalkeys' ), $tag_more_count ) ); ?>">
													<?php echo esc_html( sprintf( __( '+%d', 'globalkeys' ), $tag_more_count ) ); ?>
												</span>
											</li>
										<?php endif; ?>
									</ul>
								</div>
							<?php endif; ?>
							<span class="gk-bestseller-price-bar gk-platform-featured-slide__price-bar">
								<?php if ( $discount_pct > 0 ) : ?>
									<span class="gk-bestseller-price-badge">-<?php echo (int) $discount_pct; ?>%</span>
								<?php endif; ?>
								<?php if ( $reg_html ) : ?>
									<span class="gk-bestseller-price-was"><?php echo '<del>' . wp_kses_post( $reg_html ) . '</del>'; ?></span>
								<?php endif; ?>
								<span class="gk-bestseller-price-now"><?php echo wp_kses_post( $curr_html ); ?></span>
							</span>
						</div>
					</aside>
				</a>
			</article>
		<?php endforeach; ?>
	</div>
	<div class="gk-platform-featured-carousel__bar">
		<div class="gk-platform-featured-carousel__timer-wrap">
			<span class="gk-platform-featured-carousel__timer-spacer" aria-hidden="true"></span>
			<span class="gk-platform-featured-carousel__timer-text gk-platform-featured-carousel__timer-text--running"><?php
				/* translators: %s: seconds until next game (e.g. span with number) */
				echo wp_kses( sprintf( __( 'Next game in %s seconds', 'globalkeys' ), '<span class="gk-platform-featured-carousel__timer-value">6</span>' ), array( 'span' => array( 'class' => true ) ) );
			?></span>
			<span class="gk-platform-featured-carousel__timer-text gk-platform-featured-carousel__timer-text--paused" aria-hidden="true"><?php esc_html_e( 'Stopped by Hover', 'globalkeys' ); ?></span>
			<div class="gk-platform-featured-carousel__timer-track">
				<div class="gk-platform-featured-carousel__timer-fill" style="width: 0%;"></div>
			</div>
		</div>
		<label class="gk-platform-featured-carousel__auto-switch">
			<input type="checkbox" class="gk-platform-featured-carousel__auto-switch-input" checked />
			<span><?php esc_html_e( 'Auto switch', 'globalkeys' ); ?></span>
		</label>
	</div>
	<div class="gk-platform-featured-carousel__dots" role="tablist" aria-label="<?php esc_attr_e( 'Slide navigation', 'globalkeys' ); ?>">
		<?php foreach ( $products as $idx => $product ) : ?>
			<button type="button" class="gk-platform-featured-carousel__dot<?php echo $idx === 0 ? ' is-active' : ''; ?>" role="tab" aria-selected="<?php echo $idx === 0 ? 'true' : 'false'; ?>" data-index="<?php echo (int) $idx; ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Slide %d', 'globalkeys' ), $idx + 1 ) ); ?>"></button>
		<?php endforeach; ?>
	</div>
</section>
