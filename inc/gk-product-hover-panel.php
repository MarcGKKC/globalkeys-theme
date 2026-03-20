<?php
/**
 * Steam-ähnliches Hover-Info-Panel für Produktkarten (Bestseller etc.).
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Deutsche Kurzbezeichnung aus WooCommerce-Sternschnitt (optional).
 *
 * @param float $average 0–5.
 * @param int   $count   Anzahl Bewertungen.
 * @return string Leer wenn keine Bewertungen.
 */
function globalkeys_product_review_sentiment_label( $average, $count ) {
	$count = (int) $count;
	if ( $count < 1 ) {
		return '';
	}
	$avg = (float) $average;
	if ( $avg >= 4.5 ) {
		return __( 'Äußerst positiv', 'globalkeys' );
	}
	if ( $avg >= 4.0 ) {
		return __( 'Sehr positiv', 'globalkeys' );
	}
	if ( $avg >= 3.5 ) {
		return __( 'Größtenteils positiv', 'globalkeys' );
	}
	if ( $avg >= 3.0 ) {
		return __( 'Positiv', 'globalkeys' );
	}
	if ( $avg >= 2.0 ) {
		return __( 'Gemischt', 'globalkeys' );
	}
	return __( 'Größtenteils negativ', 'globalkeys' );
}

/**
 * Rendert das Hover-Panel-Markup für ein WooCommerce-Produkt.
 *
 * @param WC_Product $product Produkt.
 */
function globalkeys_render_product_hover_panel( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return;
	}

	$pid   = (int) $product->get_id();
	$title = $product->get_name();

	$released_display = '';
	$created          = $product->get_date_created();
	if ( $created && method_exists( $created, 'date' ) ) {
		$ts = $created->getTimestamp();
		if ( $ts ) {
			/* translators: %s: formatted product publish date */
			$released_display = sprintf(
				__( 'Veröffentlicht: %s', 'globalkeys' ),
				date_i18n( get_option( 'date_format' ), $ts )
			);
		}
	}

	$raw_desc = $product->get_short_description();
	if ( $raw_desc === '' ) {
		$raw_desc = $product->get_description();
	}
	$excerpt = '';
	if ( $raw_desc !== '' ) {
		$excerpt = wp_trim_words( wp_strip_all_tags( $raw_desc ), 42, '…' );
	}

	$review_count   = (int) $product->get_review_count();
	$avg_rating     = (float) $product->get_average_rating();
	$sentiment      = globalkeys_product_review_sentiment_label( $avg_rating, $review_count );
	$review_formatted = $review_count > 0
		? sprintf(
			/* translators: %s: formatted number */
			__( '(%s Bewertungen)', 'globalkeys' ),
			number_format_i18n( $review_count )
		)
		: '';

	$tags            = get_the_terms( $pid, 'product_tag' );
	$tags_heading    = __( 'Schlagwörter:', 'globalkeys' );
	if ( is_wp_error( $tags ) || empty( $tags ) ) {
		$tags = get_the_terms( $pid, 'product_cat' );
		if ( is_wp_error( $tags ) ) {
			$tags = array();
		}
		$tags_heading = __( 'Kategorien:', 'globalkeys' );
	}
	if ( ! is_array( $tags ) ) {
		$tags = array();
	}
	$tags = array_values(
		array_filter(
			array_slice( $tags, 0, 10 ),
			static function ( $term ) {
				return $term && isset( $term->slug ) && $term->slug !== 'uncategorized';
			}
		)
	);
	$tags = array_slice( $tags, 0, 8 );

	?>
	<aside class="gk-product-hover-panel" aria-hidden="true">
		<div class="gk-product-hover-panel__inner">
			<h3 class="gk-product-hover-panel__title"><?php echo esc_html( $title ); ?></h3>
			<?php if ( $released_display !== '' ) : ?>
				<p class="gk-product-hover-panel__released"><?php echo esc_html( $released_display ); ?></p>
			<?php endif; ?>
			<?php if ( $excerpt !== '' ) : ?>
				<p class="gk-product-hover-panel__excerpt"><?php echo esc_html( $excerpt ); ?></p>
			<?php endif; ?>
			<?php if ( $review_count > 0 && $sentiment !== '' ) : ?>
				<div class="gk-product-hover-panel__reviews">
					<p class="gk-product-hover-panel__reviews-heading"><?php esc_html_e( 'Bewertungen:', 'globalkeys' ); ?></p>
					<p class="gk-product-hover-panel__reviews-body">
						<span class="gk-product-hover-panel__reviews-sentiment"><?php echo esc_html( $sentiment ); ?></span>
						<?php if ( $review_formatted !== '' ) : ?>
							<span class="gk-product-hover-panel__reviews-count"><?php echo esc_html( ' ' . $review_formatted ); ?></span>
						<?php endif; ?>
					</p>
				</div>
			<?php endif; ?>
			<?php if ( ! empty( $tags ) ) : ?>
				<div class="gk-product-hover-panel__tags">
					<p class="gk-product-hover-panel__tags-heading"><?php echo esc_html( $tags_heading ); ?></p>
					<ul class="gk-product-hover-panel__tag-list">
						<?php foreach ( $tags as $term ) : ?>
							<li><span class="gk-product-hover-panel__tag"><?php echo esc_html( $term->name ); ?></span></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>
	</aside>
	<?php
}
