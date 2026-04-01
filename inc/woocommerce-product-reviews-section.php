<?php
/**
 * Produktseite: Section „Reviews“ (WooCommerce-Produktbewertungen).
 *
 * @package globalkeys
 */

defined( 'ABSPATH' ) || exit;

/**
 * Testphase: Bewertungen ohne Kauf erlauben (entspricht WooCommerce „Nein“ bei „Bewertungen nur von „Verifizierten Besitzern“).
 *
 * Live-Umgebung: in wp-config.php vor require wp-settings.php z. B.:
 * define( 'GLOBALKEYS_ALLOW_PRODUCT_REVIEWS_WITHOUT_PURCHASE', false );
 */
if ( ! defined( 'GLOBALKEYS_ALLOW_PRODUCT_REVIEWS_WITHOUT_PURCHASE' ) ) {
	define( 'GLOBALKEYS_ALLOW_PRODUCT_REVIEWS_WITHOUT_PURCHASE', true );
}

/**
 * @param mixed $pre Short-circuit-Wert (false = Option normal aus der DB lesen).
 * @return mixed
 */
function globalkeys_pre_option_review_rating_verification_for_testing( $pre ) {
	if ( defined( 'GLOBALKEYS_ALLOW_PRODUCT_REVIEWS_WITHOUT_PURCHASE' ) && GLOBALKEYS_ALLOW_PRODUCT_REVIEWS_WITHOUT_PURCHASE ) {
		return 'no';
	}
	return $pre;
}

add_filter( 'pre_option_woocommerce_review_rating_verification_required', 'globalkeys_pre_option_review_rating_verification_for_testing', 10, 1 );

/**
 * Reviews-Tab ausblenden, damit Bewertungen nur in dieser Section erscheinen.
 *
 * @param array<string, array<string, mixed>> $tabs Register tabs.
 * @return array<string, array<string, mixed>>
 */
function globalkeys_single_product_remove_reviews_tab( $tabs ) {
	if ( ! function_exists( 'is_product' ) || ! is_product() || ! function_exists( 'globalkeys_single_product_is_purchase_card_active' ) || ! globalkeys_single_product_is_purchase_card_active() ) {
		return $tabs;
	}
	unset( $tabs['reviews'] );
	return $tabs;
}

add_filter( 'woocommerce_product_tabs', 'globalkeys_single_product_remove_reviews_tab', 99 );

/**
 * Skript für 10er-Block-Bewertung + Formularlogik (Modal).
 */
function globalkeys_product_reviews_enqueue_assets() {
	$is_product_page = ( function_exists( 'woocommerce_is_product_page' ) && woocommerce_is_product_page() )
		|| ( function_exists( 'is_product' ) && is_product() );
	if ( ! $is_product_page ) {
		return;
	}
	if ( ! function_exists( 'globalkeys_single_product_is_purchase_card_active' ) || ! globalkeys_single_product_is_purchase_card_active() ) {
		return;
	}
	if ( ! function_exists( 'wc_reviews_enabled' ) || ! wc_reviews_enabled() ) {
		return;
	}
	$gk_pr_path = get_template_directory() . '/js/gk-product-reviews.js';
	$ver          = ( is_readable( $gk_pr_path ) ? (string) filemtime( $gk_pr_path ) : '' );
	if ( $ver === '' ) {
		$ver = defined( '_S_VERSION' ) ? (string) _S_VERSION : '1';
	}
	wp_enqueue_script(
		'globalkeys-product-reviews',
		get_template_directory_uri() . '/js/gk-product-reviews.js',
		array(),
		$ver,
		true
	);
}

add_action( 'wp_enqueue_scripts', 'globalkeys_product_reviews_enqueue_assets', 99 );

/**
 * Zusätzliche 10er-Skalen unter „Weitere Kategorien“ (Slug => sichtbares Label).
 *
 * @return array<string, string>
 */
function globalkeys_review_extra_rating_categories() {
	return apply_filters(
		'gk_review_extra_rating_categories',
		array(
			'graphics' => __( 'Grafik', 'globalkeys' ),
			'fun'      => __( 'Spielspaß', 'globalkeys' ),
			'story'    => __( 'Story', 'globalkeys' ),
		)
	);
}

/**
 * 10er-Block-Skala (Allgemein / Kategorien).
 *
 * @param string $field_name POST-Name (z. B. gk_rating_general).
 * @param string $id_base    ID für Hidden-Input und Label.
 * @param string $label      Kategorie-Überschrift.
 * @param bool   $is_required Nur für data-Attribut / JS (Hidden hat kein HTML-required).
 */
function globalkeys_product_review_print_10_scale( $field_name, $id_base, $label, $is_required = false ) {
	$req = $is_required ? '1' : '0';
	?>
	<div class="gk-review-scale" data-gk-review-scale data-required="<?php echo esc_attr( $req ); ?>">
		<div class="gk-review-scale__label" id="<?php echo esc_attr( $id_base ); ?>-label"><?php echo esc_html( $label ); ?></div>
		<div class="gk-review-scale__row" role="group" aria-labelledby="<?php echo esc_attr( $id_base ); ?>-label">
			<?php
			for ( $i = 1; $i <= 10; $i++ ) {
				printf(
					'<button type="button" class="gk-review-scale__block" data-value="%1$d" aria-pressed="false" aria-label="%2$s"></button>',
					(int) $i,
					esc_attr(
						sprintf(
							/* translators: 1: number 1-10, 2: category name */
							__( '%1$d von 10 – %2$s', 'globalkeys' ),
							$i,
							$label
						)
					)
				);
			}
			?>
		</div>
		<input type="hidden" name="<?php echo esc_attr( $field_name ); ?>" id="<?php echo esc_attr( $id_base ); ?>" value="" autocomplete="off" />
	</div>
	<?php
}

/**
 * Zusatzfelder der Produktbewertung in Comment-Meta speichern.
 *
 * @param int        $comment_id ID des Kommentars.
 * @param int|string $approved   Genehmigungsstatus.
 * @param array      $commentdata Kommentardaten.
 */
function globalkeys_product_review_save_extended_meta( $comment_id, $approved, $commentdata ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce-Kommentarformular
	if ( empty( $_POST['comment_post_ID'] ) ) {
		return;
	}
	$post_id = (int) $_POST['comment_post_ID'];
	if ( $post_id <= 0 || get_post_type( $post_id ) !== 'product' ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$general = isset( $_POST['gk_rating_general'] ) ? absint( wp_unslash( $_POST['gk_rating_general'] ) ) : 0;
	if ( $general >= 1 && $general <= 10 ) {
		update_comment_meta( $comment_id, 'gk_rating_general', $general );
	}

	foreach ( globalkeys_review_extra_rating_categories() as $slug => $_lab ) {
		$slug = sanitize_key( $slug );
		if ( $slug === '' ) {
			continue;
		}
		$key = 'gk_rating_' . $slug;
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}
		$v = absint( wp_unslash( $_POST[ $key ] ) );
		if ( $v >= 1 && $v <= 10 ) {
			update_comment_meta( $comment_id, $key, $v );
		}
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$pros = isset( $_POST['gk_review_pro'] ) ? array_map( 'sanitize_text_field', wp_unslash( (array) $_POST['gk_review_pro'] ) ) : array();
	$pros = array_filter( $pros );
	update_comment_meta( $comment_id, 'gk_review_pros', implode( "\n", $pros ) );

	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$cons = isset( $_POST['gk_review_con'] ) ? array_map( 'sanitize_text_field', wp_unslash( (array) $_POST['gk_review_con'] ) ) : array();
	$cons = array_filter( $cons );
	update_comment_meta( $comment_id, 'gk_review_cons', implode( "\n", $cons ) );
}

add_action( 'comment_post', 'globalkeys_product_review_save_extended_meta', 25, 3 );

/**
 * Review-Modal: Inline-Skript im Footer (unabhängig von externer JS-Datei / Cache / Optimierern).
 */
function globalkeys_product_reviews_footer_modal_inline() {
	$is_product_page = ( function_exists( 'woocommerce_is_product_page' ) && woocommerce_is_product_page() )
		|| ( function_exists( 'is_product' ) && is_product() );
	if ( ! $is_product_page ) {
		return;
	}
	if ( ! function_exists( 'globalkeys_single_product_is_purchase_card_active' ) || ! globalkeys_single_product_is_purchase_card_active() ) {
		return;
	}
	if ( ! function_exists( 'wc_reviews_enabled' ) || ! wc_reviews_enabled() ) {
		return;
	}
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- reines statisches JS
	?>
<script id="gk-review-modal-boot">
(function(){'use strict';
var K='gk-review-modal--closed';
function M(){return document.getElementById('gk-review-modal');}
/** Ohne Verschiebung an body: position:fixed hängt an Vorfahren mit transform (z. B. div.product). */
function portalModal(){var m=M();if(m&&m.parentNode!==document.body){document.body.appendChild(m);}}
function scrollAnc(){var t=document.getElementById('gk-after-hero-reviews');if(t){t.scrollIntoView({behavior:'smooth',block:'start'});}}
function openM(e){if(e){e.preventDefault();}portalModal();var m=M();if(!m){scrollAnc();return;}if(!m.classList.contains(K)){return;}m.gkLF=document.activeElement;m.classList.remove(K);m.setAttribute('aria-hidden','false');document.documentElement.classList.add('gk-review-modal-is-open');var c=m.querySelector('.gk-review-modal__close')||m.querySelector('input:not([type="hidden"]),textarea,select,button[type="submit"]');if(c){setTimeout(function(){try{c.focus();}catch(x){}},50);}}
function closeM(){var m=M();if(!m||m.classList.contains(K)){return;}m.classList.add(K);m.setAttribute('aria-hidden','true');document.documentElement.classList.remove('gk-review-modal-is-open');var lf=m.gkLF;if(lf&&lf.focus){try{lf.focus();}catch(x){}}}
document.addEventListener('click',function(ev){var el=ev.target;if(!el||!el.closest){return;}if(el.closest('[data-gk-review-modal-open]')){openM(ev);return;}var x=el.closest('[data-gk-review-modal-close]');if(x&&M()&&x.closest('#gk-review-modal')){ev.preventDefault();closeM();}},true);
document.addEventListener('keydown',function(ev){if(ev.key!=='Escape'){return;}var m=M();if(m&&!m.classList.contains(K)){closeM();}},true);
window.gkOpenProductReviewModal=openM;
window.gkCloseProductReviewModal=closeM;
if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',portalModal);}else{portalModal();}
})();
</script>
	<?php
}

add_action( 'wp_footer', 'globalkeys_product_reviews_footer_modal_inline', 999 );

/**
 * Hero-Zeile unter der Reviews-Überschrift (Score / Text / CTA).
 *
 * @param WC_Product $product Produkt.
 */
function globalkeys_product_reviews_print_hero_bar( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return;
	}

	$count = (int) $product->get_review_count();
	$avg   = (float) $product->get_average_rating();

	if ( $count > 0 && $avg > 0 && function_exists( 'wc_review_ratings_enabled' ) && wc_review_ratings_enabled() ) {
		$score_display = (string) (int) round( $avg * 2, 0 );
		$score_attr    = $score_display;
	} else {
		$score_display = '–';
		$score_attr    = '';
	}

	$title = apply_filters(
		'gk_reviews_hero_title',
		__( 'Game rating', 'globalkeys' ),
		$product
	);

	$subtitle = apply_filters(
		'gk_reviews_hero_subtitle',
		sprintf(
			/* translators: %d: number of reviews */
			_n(
				'Based on %d review, all languages included.',
				'Based on %d reviews, all languages included.',
				$count,
				'globalkeys'
			),
			$count
		),
		$product,
		$count
	);

	$cta_text = apply_filters(
		'gk_reviews_hero_cta_text',
		__( 'Rate this game!', 'globalkeys' ),
		$product
	);

	echo '<div class="gk-product-reviews-hero">';
	echo '<div class="gk-product-reviews-hero__score" role="img" aria-label="' . esc_attr(
		$score_attr !== ''
			? sprintf(
				/* translators: %s: score out of 10 */
				__( 'User score %s out of 10', 'globalkeys' ),
				$score_attr
			)
			: __( 'No user score yet', 'globalkeys' )
	) . '">';
	echo '<span class="gk-product-reviews-hero__score-ring">';
	echo '<span class="gk-product-reviews-hero__score-value">' . esc_html( $score_display ) . '</span>';
	echo '</span></div>';

	echo '<div class="gk-product-reviews-hero__text">';
	echo '<p class="gk-product-reviews-hero__title">' . esc_html( is_string( $title ) ? $title : '' ) . '</p>';
	echo '<p class="gk-product-reviews-hero__sub">' . esc_html( is_string( $subtitle ) ? $subtitle : '' ) . '</p>';
	echo '</div>';

	echo '<button type="button" class="gk-product-reviews-hero__cta" data-gk-review-modal-open onclick="if(typeof window.gkOpenProductReviewModal===\'function\'){window.gkOpenProductReviewModal(event);}return false;">';
	echo '<span class="gk-product-reviews-hero__cta-label">' . esc_html( is_string( $cta_text ) ? $cta_text : '' ) . '</span>';
	echo '<span class="gk-product-reviews-hero__cta-icon" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false"><path d="M4 21v-3.5l10.5-10.5 3.5 3.5L7.5 21H4zm2-2h1.2L15 11.2l-1.2-1.2L6 17.8V19zm11.7-12.3l1 1c.4.4.4 1 0 1.4l-1.6 1.6-3.5-3.5 1.6-1.6c.4-.4 1-.4 1.4 0z" fill="currentColor"/></svg></span>';
	echo '</button>';

	echo '</div>';
}

/**
 * Section unter „Similar products“ – Überschrift wie andere Produkt-Sections; Body per Filter befüllbar oder Standard: comments_template.
 */
function globalkeys_single_product_reviews_section() {
	if ( ! function_exists( 'globalkeys_single_product_is_purchase_card_active' ) || ! globalkeys_single_product_is_purchase_card_active() ) {
		return;
	}
	global $product;
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return;
	}
	if ( ! function_exists( 'wc_reviews_enabled' ) || ! wc_reviews_enabled() ) {
		return;
	}

	$heading = apply_filters( 'gk_reviews_section_heading_text', __( 'Reviews', 'globalkeys' ), $product );
	if ( ! is_string( $heading ) || $heading === '' ) {
		return;
	}

	$heading_id = 'gk-product-page-reviews-heading-' . (int) $product->get_id();

	echo '<section class="gk-product-page-reviews" aria-labelledby="' . esc_attr( $heading_id ) . '">';
	echo '<div class="gk-section-inner gk-section-featured-inner">';
	echo '<div class="gk-featured-heading-wrap gk-product-page-reviews__heading-wrap">';
	echo '<h2 id="' . esc_attr( $heading_id ) . '" class="gk-section-title gk-featured-heading">';
	echo '<span class="gk-featured-heading-text-wrap">';
	echo '<span class="gk-featured-heading-text">' . esc_html( $heading ) . '</span>';
	echo '<span class="gk-featured-title-underline" aria-hidden="true"></span>';
	echo '</span>';
	echo '</h2>';
	echo '</div>';

	globalkeys_product_reviews_print_hero_bar( $product );

	$content = apply_filters( 'gk_reviews_section_content_html', '', $product );
	echo '<div class="gk-product-page-reviews__body gk-product-page-reviews__body--wc">';
	if ( is_string( $content ) && $content !== '' ) {
		echo wp_kses_post( $content );
	} else {
		comments_template();
	}
	echo '</div>';

	echo '</div>';
	echo '</section>';
}

add_action( 'woocommerce_after_single_product_summary', 'globalkeys_single_product_reviews_section', 12 );
