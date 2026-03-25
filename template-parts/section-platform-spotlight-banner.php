<?php
/**
 * Plattform: Panorama-Banner unter Trending (PC / PlayStation / Xbox / Nintendo).
 * Festes Key-Art + verknüpftes Produkt; Xbox: Release-Pill über dem Titel.
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gk_spot_platform = get_query_var( 'gk_platform' );
$gk_spot_platform = is_string( $gk_spot_platform ) ? $gk_spot_platform : '';

if ( ! in_array( $gk_spot_platform, array( 'pc', 'playstation', 'xbox', 'nintendo' ), true ) ) {
	return;
}

$product                 = null;
$hero_src                = '';
$spotlight_title         = '';
$spotlight_release_text  = '';

if ( $gk_spot_platform === 'pc' ) {
	$hero_src        = get_template_directory_uri() . '/Pictures/2892.jpg';
	$spotlight_title = __( 'Resident Evil Requiem', 'globalkeys' );
	if ( function_exists( 'wc_get_product' ) ) {
		$re_q = new WP_Query(
			array(
				'post_type'      => 'product',
				'posts_per_page' => 20,
				'post_status'    => 'publish',
				's'              => 'Resident Evil',
				'orderby'        => 'relevance',
			)
		);
		if ( $re_q->have_posts() ) {
			while ( $re_q->have_posts() ) {
				$re_q->the_post();
				$p = wc_get_product( get_the_ID() );
				if ( $p && $p->is_visible() && stripos( $p->get_name(), 'Resident Evil' ) !== false ) {
					$product = $p;
					break;
				}
			}
			wp_reset_postdata();
		}
	}
} elseif ( $gk_spot_platform === 'playstation' ) {
	$hero_src        = get_template_directory_uri() . '/Pictures/njf.jpg';
	$spotlight_title = __( 'EA Sports FC 2026', 'globalkeys' );
	if ( function_exists( 'wc_get_product' ) ) {
		$ea_terms = array( 'EA SPORTS FC', 'EA FC' );
		foreach ( $ea_terms as $ea_s ) {
			$ea_q = new WP_Query(
				array(
					'post_type'      => 'product',
					'posts_per_page' => 20,
					'post_status'    => 'publish',
					's'              => $ea_s,
					'orderby'        => 'relevance',
				)
			);
			if ( ! $ea_q->have_posts() ) {
				continue;
			}
			while ( $ea_q->have_posts() ) {
				$ea_q->the_post();
				$p = wc_get_product( get_the_ID() );
				if ( ! $p || ! $p->is_visible() ) {
					continue;
				}
				$n = $p->get_name();
				if ( stripos( $n, 'EA SPORTS FC' ) !== false || stripos( $n, 'EA FC' ) !== false ) {
					$product = $p;
					break;
				}
			}
			wp_reset_postdata();
			if ( $product ) {
				break;
			}
		}
	}
} elseif ( $gk_spot_platform === 'xbox' ) {
	$hero_src        = get_template_directory_uri() . '/Pictures/54541.jpg';
	$spotlight_title = __( 'Forza Horizon 6', 'globalkeys' );
	if ( function_exists( 'wc_get_product' ) ) {
		$fz_terms = array( 'Forza Horizon 6', 'Forza Horizon' );
		foreach ( $fz_terms as $fz_s ) {
			$fz_q = new WP_Query(
				array(
					'post_type'      => 'product',
					'posts_per_page' => 20,
					'post_status'    => 'publish',
					's'              => $fz_s,
					'orderby'        => 'relevance',
				)
			);
			if ( ! $fz_q->have_posts() ) {
				continue;
			}
			while ( $fz_q->have_posts() ) {
				$fz_q->the_post();
				$p = wc_get_product( get_the_ID() );
				if ( ! $p || ! $p->is_visible() ) {
					continue;
				}
				$n = $p->get_name();
				if ( 'Forza Horizon 6' === $fz_s && stripos( $n, 'Forza Horizon 6' ) !== false ) {
					$product = $p;
					break;
				}
				if ( 'Forza Horizon' === $fz_s && stripos( $n, 'Forza' ) !== false && stripos( $n, 'Horizon' ) !== false ) {
					$product = $p;
					break;
				}
			}
			wp_reset_postdata();
			if ( $product ) {
				break;
			}
		}
	}
} elseif ( $gk_spot_platform === 'nintendo' ) {
	$hero_src        = get_template_directory_uri() . '/Pictures/6463663.jpg';
	$spotlight_title = __( 'Pokémon Pokopia', 'globalkeys' );
	if ( function_exists( 'wc_get_product' ) ) {
		$pk_terms = array( 'Pokopia', 'Pokemon Pokopia', 'Pokemon' );
		foreach ( $pk_terms as $pk_s ) {
			$pk_q = new WP_Query(
				array(
					'post_type'      => 'product',
					'posts_per_page' => 20,
					'post_status'    => 'publish',
					's'              => $pk_s,
					'orderby'        => 'relevance',
				)
			);
			if ( ! $pk_q->have_posts() ) {
				continue;
			}
			while ( $pk_q->have_posts() ) {
				$pk_q->the_post();
				$p = wc_get_product( get_the_ID() );
				if ( ! $p || ! $p->is_visible() ) {
					continue;
				}
				$n = $p->get_name();
				if ( function_exists( 'mb_stripos' ) ) {
					$has_pokopia = mb_stripos( $n, 'pokopia', 0, 'UTF-8' ) !== false;
				} else {
					$has_pokopia = stripos( $n, 'pokopia' ) !== false;
				}
				if ( ! $has_pokopia ) {
					continue;
				}
				$product = $p;
				break;
			}
			wp_reset_postdata();
			if ( $product ) {
				break;
			}
		}
	}
}

if ( ! $product ) {
	$trend_pf = in_array( $gk_spot_platform, array( 'playstation', 'xbox', 'nintendo' ), true ) ? $gk_spot_platform : null;
	$list     = function_exists( 'globalkeys_get_platform_trending_products' )
		? globalkeys_get_platform_trending_products( 12, $trend_pf )
		: array();
	if ( ! empty( $list ) ) {
		$n       = count( $list );
		$product = $n > 6 ? $list[6] : ( $n > 4 ? $list[4] : ( $n > 1 ? $list[1] : $list[0] ) );
	}
}

if ( ! $product ) {
	return;
}

if ( $gk_spot_platform === 'xbox' ) {
	if ( function_exists( 'globalkeys_format_product_release_date_display' ) ) {
		$spotlight_release_text = globalkeys_format_product_release_date_display( $product );
	}
	if ( $spotlight_release_text === '' ) {
		$spotlight_release_text = wp_date( 'j F Y', strtotime( '2026-05-19 12:00:00 ' . wp_timezone_string() ) );
	}
}

$url = $product->get_permalink();

$sale_pct = 0;
if ( $product->is_on_sale() ) {
	if ( $product->is_type( 'variable' ) ) {
		$reg_raw  = (float) $product->get_variation_regular_price( 'min', true );
		$sale_raw = (float) $product->get_variation_sale_price( 'min', true );
	} else {
		$reg_raw  = (float) $product->get_regular_price();
		$sale_raw = (float) $product->get_sale_price();
	}
	if ( $reg_raw > 0 && $sale_raw > 0 && $sale_raw < $reg_raw ) {
		$sale_pct = (int) round( ( 1 - $sale_raw / $reg_raw ) * 100 );
	}
}

$current_price_html = wc_price( wc_get_price_to_display( $product ) );
$aria_price         = wp_strip_all_tags( html_entity_decode( $current_price_html, ENT_QUOTES, 'UTF-8' ) );
$aria               = $spotlight_title . ', ' . $aria_price;
if ( $spotlight_release_text !== '' ) {
	$aria = $spotlight_release_text . ', ' . $aria;
}
if ( $sale_pct > 0 ) {
	$aria .= ', ' . sprintf( __( '-%d%%', 'globalkeys' ), $sale_pct );
}
?>

<section class="gk-section gk-section-bestsellers gk-section-platform-spotlight" role="region" aria-label="<?php esc_attr_e( 'Hervorgehobenes Produkt', 'globalkeys' ); ?>">
	<div class="gk-section-inner gk-section-featured-inner">
		<div class="gk-platform-spotlight">
			<img class="gk-platform-spotlight__img" src="<?php echo esc_url( $hero_src ); ?>" alt="" decoding="async" loading="lazy" />
			<div class="gk-platform-spotlight__fade" aria-hidden="true"></div>
			<a class="gk-platform-spotlight__hit" href="<?php echo esc_url( $url ); ?>" aria-label="<?php echo esc_attr( $aria ); ?>">
				<span class="gk-platform-spotlight__title-cluster">
					<?php if ( $spotlight_release_text !== '' ) : ?>
						<span class="gk-platform-spotlight__release"><?php echo esc_html( $spotlight_release_text ); ?></span>
					<?php endif; ?>
					<span class="gk-platform-spotlight__title"><?php echo esc_html( $spotlight_title ); ?></span>
				</span>
				<span class="gk-platform-spotlight__row">
					<span class="gk-bestseller-price-bar">
						<?php if ( $sale_pct > 0 ) : ?>
							<span class="gk-bestseller-price-badge" aria-hidden="true"><?php echo esc_html( '-' . $sale_pct . '%' ); ?></span>
						<?php endif; ?>
						<span class="gk-bestseller-price-now"><?php echo wp_kses_post( $current_price_html ); ?></span>
					</span>
				</span>
			</a>
		</div>
	</div>
</section>
