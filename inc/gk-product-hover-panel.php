<?php
/**
 * Hover-Info-Panel für Produktkarten (Bestseller): Titel, Datum, Kurztext, Tags/Kategorien.
 * Keine Shop-/Woo-Bewertungen (kein Steam-ähnlicher Rezensionsblock).
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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

	$tags                   = get_the_terms( $pid, 'product_tag' );
	$tags_heading           = __( 'Schlagwörter:', 'globalkeys' );
	$terms_are_product_tags = true;
	if ( is_wp_error( $tags ) || empty( $tags ) ) {
		$tags = get_the_terms( $pid, 'product_cat' );
		if ( is_wp_error( $tags ) ) {
			$tags = array();
		}
		$tags_heading           = __( 'Kategorien:', 'globalkeys' );
		$terms_are_product_tags = false;
	}
	if ( ! is_array( $tags ) ) {
		$tags = array();
	}
	$tags = array_values(
		array_filter(
			$tags,
			static function ( $term ) {
				return $term && isset( $term->slug ) && $term->slug !== 'uncategorized';
			}
		)
	);

	$tag_total      = count( $tags );
	$tags_display   = array_slice( $tags, 0, 6 );
	$tag_more_count = max( 0, $tag_total - 6 );

	$tag_more_title = '';
	if ( $tag_more_count > 0 ) {
		if ( $terms_are_product_tags ) {
			$tag_more_title = sprintf(
				/* translators: %d: number of additional product tags */
				_n( '%d weiteres Schlagwort', '%d weitere Schlagwörter', $tag_more_count, 'globalkeys' ),
				$tag_more_count
			);
		} else {
			$tag_more_title = sprintf(
				/* translators: %d: number of additional product categories */
				_n( '%d weitere Kategorie', '%d weitere Kategorien', $tag_more_count, 'globalkeys' ),
				$tag_more_count
			);
		}
	}

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
			<?php if ( ! empty( $tags_display ) ) : ?>
				<div class="gk-product-hover-panel__tags">
					<p class="gk-product-hover-panel__tags-heading"><?php echo esc_html( $tags_heading ); ?></p>
					<ul class="gk-product-hover-panel__tag-list">
						<?php foreach ( $tags_display as $term ) : ?>
							<li><span class="gk-product-hover-panel__tag"><?php echo esc_html( $term->name ); ?></span></li>
						<?php endforeach; ?>
						<?php if ( $tag_more_count > 0 ) : ?>
							<li>
								<span class="gk-product-hover-panel__tag" title="<?php echo esc_attr( $tag_more_title ); ?>">
									<?php
									echo esc_html(
										sprintf(
											/* translators: %d: count of additional tags/categories not shown */
											__( '+%d', 'globalkeys' ),
											$tag_more_count
										)
									);
									?>
								</span>
							</li>
						<?php endif; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>
	</aside>
	<?php
}
