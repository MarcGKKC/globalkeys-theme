<?php
/**
 * Produktdetail: Layout per CSS am Ende von head und footer (überschreibt Plugin-Styles).
 *
 * @package globalkeys
 */

defined( 'ABSPATH' ) || exit;

/**
 * @param string $suffix Id-Suffix für zweites Style-Tag im Footer.
 */
function globalkeys_single_product_layout_force_print( $suffix = '' ) {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}

	$max = apply_filters( 'gk_single_product_content_max', '72rem' );
	$max = is_string( $max ) ? trim( $max ) : '72rem';
	if ( ! preg_match( '/^[\d.]+\s*(rem|em|ch|px|%|vw|vmin|vmax)$/i', $max ) ) {
		$max = '72rem';
	}

	$hero_row_max = apply_filters( 'gk_single_product_hero_row_max', '83rem' );
	$hero_row_max = is_string( $hero_row_max ) ? trim( $hero_row_max ) : '83rem';
	if ( ! preg_match( '/^[\d.]+\s*(rem|em|ch|px|%|vw|vmin|vmax)$/i', $hero_row_max ) ) {
		$hero_row_max = '83rem';
	}

	$id = 'gk-single-product-layout-force' . $suffix;
	?>
	<style id="<?php echo esc_attr( $id ); ?>">
		body.single-product #gk-product-page-root,
		body.single-product .gk-single-product-shell,
		body.single-product div#primary.content-area {
			width: 100% !important;
			max-width: none !important;
			margin-left: 0 !important;
			margin-right: 0 !important;
			padding-left: 0 !important;
			padding-right: 0 !important;
			box-sizing: border-box !important;
		}
		body.single-product #gk-product-page-root main.site-main,
		body.single-product main.site-main,
		body.single-product main#primary.site-main {
			position: relative !important;
			max-width: min(<?php echo esc_html( $max ); ?>, 100%) !important;
			width: 100% !important;
			margin-left: 0 !important;
			margin-right: auto !important;
			padding-left: max(1.5rem, env(safe-area-inset-left, 0px)) !important;
			padding-right: max(1.5rem, env(safe-area-inset-right, 0px)) !important;
			box-sizing: border-box !important;
		}
		body.gk-has-product-page-hero #gk-product-page-root main.site-main,
		body.gk-has-product-page-hero main.site-main,
		body.gk-has-product-page-hero main#primary.site-main,
		body.single-product main.site-main:has(.gk-single-product-hero),
		body.single-product #gk-product-page-root main.site-main:has(.gk-single-product-hero) {
			margin-top: 0 !important;
			margin-left: 0 !important;
			margin-right: 0 !important;
			max-width: none !important;
			width: 100% !important;
			padding-top: var(--gk-product-hero-block, 34rem) !important;
			padding-left: max(1.25rem, env(safe-area-inset-left, 0px)) !important;
			padding-right: max(1.25rem, env(safe-area-inset-right, 0px)) !important;
			overflow-anchor: none !important;
		}
		body.gk-has-product-page-hero.gk-header-scrolled {
			--gk-main-padding-top: 8rem;
			--gk-front-padding-top: 0;
		}
		body.gk-has-product-page-hero.gk-header-scrolled .site-header-inner,
		body.single-product:has(.gk-single-product-hero).gk-header-scrolled .site-header-inner {
			height: 8.5rem !important;
			min-height: 8.5rem !important;
			position: relative !important;
			z-index: 1 !important;
		}
		body.gk-has-product-page-hero.gk-header-scrolled.gk-header-blur .site-header,
		body.single-product:has(.gk-single-product-hero).gk-header-scrolled.gk-header-blur .site-header {
			background: transparent !important;
			-webkit-backdrop-filter: none !important;
			backdrop-filter: none !important;
		}
		body.gk-has-product-page-hero.gk-header-scrolled.gk-header-blur .site-header::before,
		body.single-product:has(.gk-single-product-hero).gk-header-scrolled.gk-header-blur .site-header::before {
			content: '' !important;
			position: absolute !important;
			left: 0 !important;
			right: 0 !important;
			top: 0 !important;
			height: 4.5rem !important;
			z-index: 0 !important;
			background: rgba(15, 15, 20, 0.72) !important;
			-webkit-backdrop-filter: blur(12px) !important;
			backdrop-filter: blur(12px) !important;
			pointer-events: none !important;
		}
		body.single-product .gk-single-product-hero.has-hero-image {
			position: absolute !important;
			top: calc(-1 * var(--gk-product-hero-header-pull, 8.5rem)) !important;
			left: calc(50% - 50vw) !important;
			right: auto !important;
			transform: none !important;
			width: 100vw !important;
			max-width: 100vw !important;
			z-index: 0 !important;
		}
		body.single-product #page.site {
			overflow-x: clip !important;
		}
		/* Hero immer sichtbar (kein „> * :not()“ – das kann den Hero mit verstecken). */
		body.single-product .gk-product-page-hero-root,
		body.single-product .gk-single-product-hero.has-hero-image {
			display: block !important;
			visibility: visible !important;
			opacity: 1 !important;
		}
		/* Unter dem Hero: nur Breadcrumb aus; Produktkarte mit Bild liegt über dem Hero. */
		body.single-product.gk-has-product-page-hero main.site-main .woocommerce-breadcrumb,
		body.single-product main.site-main:has(.gk-product-page-hero-root) .woocommerce-breadcrumb {
			display: none !important;
		}
		/* Kein doppelter Abstand: Padding auf main reserviert die Hero-Höhe, Wrapper braucht keine min-height im Fluss. */
		body.single-product.gk-has-product-page-hero main.site-main .gk-product-page-hero-root,
		body.single-product main.site-main:has(.gk-product-page-hero-root) .gk-product-page-hero-root {
			min-height: 0 !important;
		}
		/* Keyart an den unteren Rand des Hero-Hintergrunds: Lücke (≈ Header-Pull) schließen + leichter Überlapp. */
		body.single-product.gk-has-product-page-hero main.site-main div.product.type-product,
		body.single-product main.site-main:has(.gk-product-page-hero-root) div.product.type-product {
			position: relative !important;
			z-index: 3 !important;
			max-width: min(<?php echo esc_html( $hero_row_max ); ?>, 100%) !important;
			width: 100% !important;
			margin-top: calc(-1 * (var(--gk-product-hero-header-pull, 8.5rem) + var(--gk-product-hero-overlap, 5rem) + 7.5rem)) !important;
			transform: translateY(calc(-1 * var(--gk-product-hero-row-lift, 0))) !important;
			margin-left: auto !important;
			margin-right: auto !important;
			padding: 0 !important;
			background: transparent !important;
			border: none !important;
			box-shadow: none !important;
			display: block !important;
			grid-template-columns: unset !important;
		}
		body.single-product.gk-has-product-page-hero main.site-main div.product:not(:has(.woocommerce-product-gallery)) .summary.entry-summary:has(.gk-product-sidebar-keyart),
		body.single-product main.site-main:has(.gk-product-page-hero-root) div.product:not(:has(.woocommerce-product-gallery)) .summary.entry-summary:has(.gk-product-sidebar-keyart) {
			display: flex !important;
			flex-direction: row !important;
			flex-wrap: nowrap !important;
			justify-content: center !important;
			align-items: stretch !important;
			gap: 0 !important;
			width: 100% !important;
			max-width: none !important;
			padding: 0 !important;
			margin-left: auto !important;
			margin-right: auto !important;
			box-sizing: border-box !important;
		}
		body.single-product.gk-has-product-page-hero main.site-main div.product:not(:has(.woocommerce-product-gallery)) .summary .gk-product-sidebar-keyart,
		body.single-product main.site-main:has(.gk-product-page-hero-root) div.product:not(:has(.woocommerce-product-gallery)) .summary .gk-product-sidebar-keyart {
			flex: 0 1 auto !important;
			min-width: 0 !important;
			margin: 0 !important;
			width: auto !important;
			max-width: min(100%, clamp(24.5rem, 58.5vw, 54.5rem)) !important;
			border-radius: 12px 0 0 12px !important;
			overflow: hidden !important;
			align-self: stretch !important;
			display: flex !important;
			align-items: center !important;
			justify-content: center !important;
			background: linear-gradient(165deg, #232b44 0%, #1a1f32 45%, #151827 100%) !important;
		}
		body.single-product.gk-has-product-page-hero main.site-main div.product:not(:has(.woocommerce-product-gallery)) .summary .gk-product-summary-main,
		body.single-product main.site-main:has(.gk-product-page-hero-root) div.product:not(:has(.woocommerce-product-gallery)) .summary .gk-product-summary-main {
			flex: 1 1 0 !important;
			min-width: 0 !important;
			max-width: min(34rem, 100%) !important;
			width: 100% !important;
			display: flex !important;
			flex-direction: column !important;
			align-self: stretch !important;
			box-sizing: border-box !important;
			padding: 1.35rem 1.4rem 1.45rem !important;
			background: #2a2744 !important;
			border-radius: 0 12px 12px 0 !important;
			border: 1px solid rgba(255, 255, 255, 0.08) !important;
			border-left-width: 0 !important;
			box-shadow: 0 24px 56px rgba(0, 0, 0, 0.42) !important;
			color: rgba(255, 255, 255, 0.92) !important;
		}
		body.single-product.gk-has-product-page-hero main.site-main div.product .gk-product-sidebar-keyart,
		body.single-product main.site-main:has(.gk-product-page-hero-root) div.product .gk-product-sidebar-keyart {
			border: none !important;
			box-shadow: none !important;
		}
		body.single-product.gk-has-product-page-hero main.site-main div.product .gk-product-sidebar-keyart-img,
		body.single-product main.site-main:has(.gk-product-page-hero-root) div.product .gk-product-sidebar-keyart-img {
			max-height: min(97vh, 77rem) !important;
		}
		body.single-product.gk-has-product-page-hero main.site-main div.product .woocommerce-tabs,
		body.single-product.gk-has-product-page-hero main.site-main div.product .related.products,
		body.single-product.gk-has-product-page-hero main.site-main div.product .up-sells.products,
		body.single-product main.site-main:has(.gk-product-page-hero-root) div.product .woocommerce-tabs,
		body.single-product main.site-main:has(.gk-product-page-hero-root) div.product .related.products,
		body.single-product main.site-main:has(.gk-product-page-hero-root) div.product .up-sells.products {
			display: none !important;
		}
		body.single-product.gk-has-product-page-hero #gk-product-page-root aside {
			display: none !important;
		}
		@media screen and (max-width: 782px) {
			body.gk-purchase-card-ui .gk-product-summary-main .variations tbody {
				grid-template-columns: minmax(0, 1fr) !important;
			}
		}
		@media screen and (max-width: 640px) {
			body.single-product.gk-has-product-page-hero main.site-main div.product:not(:has(.woocommerce-product-gallery)) .summary.entry-summary:has(.gk-product-sidebar-keyart),
			body.single-product main.site-main:has(.gk-product-page-hero-root) div.product:not(:has(.woocommerce-product-gallery)) .summary.entry-summary:has(.gk-product-sidebar-keyart) {
				flex-direction: column !important;
				flex-wrap: nowrap !important;
				align-items: center !important;
			}
			body.single-product.gk-has-product-page-hero main.site-main div.product:not(:has(.woocommerce-product-gallery)) .summary .gk-product-sidebar-keyart,
			body.single-product main.site-main:has(.gk-product-page-hero-root) div.product:not(:has(.woocommerce-product-gallery)) .summary .gk-product-sidebar-keyart {
				max-width: min(53rem, 100%) !important;
				width: 100% !important;
				border-radius: 12px 12px 0 0 !important;
			}
			body.single-product.gk-has-product-page-hero main.site-main div.product:not(:has(.woocommerce-product-gallery)) .summary .gk-product-summary-main,
			body.single-product main.site-main:has(.gk-product-page-hero-root) div.product:not(:has(.woocommerce-product-gallery)) .summary .gk-product-summary-main {
				max-width: min(34rem, 100%) !important;
				flex: 1 1 auto !important;
				width: 100% !important;
				border-radius: 0 0 12px 12px !important;
				border-left-width: 1px !important;
				border-top-width: 0 !important;
			}
		}
		body.gk-purchase-card-ui .gk-product-summary-main > form.cart {
			flex: 1 1 auto !important;
			display: flex !important;
			flex-direction: column !important;
			min-height: 0 !important;
			width: 100% !important;
			margin: 0 !important;
		}
		body.gk-purchase-card-ui .gk-product-summary-main > form.variations_form.cart .single_variation_wrap {
			flex: 1 1 auto !important;
			display: flex !important;
			flex-direction: column !important;
			min-height: 0 !important;
			width: 100% !important;
		}
		body.gk-purchase-card-ui .gk-purchase-card__cta-cluster {
			display: flex !important;
			flex-direction: column !important;
			align-items: stretch !important;
			gap: 0.35rem !important;
			width: 100% !important;
			margin-top: auto !important;
		}
		body.gk-purchase-card-ui .gk-product-summary-main > form.variations_form.cart .woocommerce-variation-add-to-cart.variations_button {
			margin-top: 0 !important;
		}
		body.gk-purchase-card-ui .gk-product-summary-main > form.cart:not(.variations_form) > .gk-purchase-card__actions {
			margin-top: 0 !important;
		}
		body.gk-purchase-card-ui .gk-product-summary-main .gk-purchase-card__actions-primary .single_add_to_cart_button {
			background: linear-gradient(90deg, #ff7a2e 0%, #e63b2e 55%, #d62828 100%) !important;
			color: #fff !important;
			-webkit-text-fill-color: #fff !important;
			font-size: 1.12rem !important;
			font-weight: 600 !important;
			border: none !important;
			box-shadow: none !important;
			text-shadow: none !important;
			gap: 0.55rem !important;
		}
		body.gk-purchase-card-ui .gk-product-summary-main .gk-purchase-card__actions-primary .single_add_to_cart_button::before {
			content: '' !important;
			display: block !important;
			width: 1.75em !important;
			height: 1.75em !important;
			flex-shrink: 0 !important;
			background-color: #fff !important;
			-webkit-mask-image: url('<?php echo esc_url( get_template_directory_uri() . '/Pictures/cart.g.svg' ); ?>') !important;
			mask-image: url('<?php echo esc_url( get_template_directory_uri() . '/Pictures/cart.g.svg' ); ?>') !important;
			-webkit-mask-size: contain !important;
			mask-size: contain !important;
			-webkit-mask-repeat: no-repeat !important;
			mask-repeat: no-repeat !important;
			-webkit-mask-position: center !important;
			mask-position: center !important;
		}
	</style>
	<?php
}

add_action( 'wp_head', static function () {
	globalkeys_single_product_layout_force_print( '' );
}, 99999 );

add_action( 'wp_footer', static function () {
	globalkeys_single_product_layout_force_print( '-footer' );
}, 99999 );
