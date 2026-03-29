<?php
/**
 * Produktdetail: Section „System Requirements“ unter „Game Description“.
 *
 * @package globalkeys
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'GK_PRODUCT_SYSTEM_REQUIREMENTS_META' ) ) {
	define( 'GK_PRODUCT_SYSTEM_REQUIREMENTS_META', '_gk_system_requirements' );
}

/**
 * @param WC_Product|null $product Produkt.
 * @return bool
 */
function globalkeys_product_page_has_system_requirements( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return false;
	}
	$html = $product->get_meta( GK_PRODUCT_SYSTEM_REQUIREMENTS_META );
	if ( is_string( $html ) && trim( wp_strip_all_tags( $html ) ) !== '' ) {
		return true;
	}
	return (bool) apply_filters( 'gk_system_requirements_section_force_show', false, $product );
}

/**
 * Admin: Textfeld unter Produktdaten.
 */
function globalkeys_product_system_requirements_admin_field() {
	global $post;
	$raw = ( $post && (int) $post->ID > 0 ) ? get_post_meta( (int) $post->ID, GK_PRODUCT_SYSTEM_REQUIREMENTS_META, true ) : '';
	?>
	<div class="options_group">
		<?php
		woocommerce_wp_textarea_input(
			array(
				'id'          => '_gk_system_requirements',
				'name'        => '_gk_system_requirements',
				'label'       => __( 'System Requirements (Produktseite)', 'globalkeys' ),
				'placeholder' => '',
				'description' => __( 'Erscheint unter „Game Description“ mit der Überschrift „System Requirements“. HTML erlaubt (Listen, Zeilenumbrüche).', 'globalkeys' ),
				'value'       => is_string( $raw ) ? $raw : '',
				'rows'        => 8,
				'cols'        => 40,
				'class'       => 'large-text',
				'style'       => 'width:100%;min-height:10em;',
			)
		);
		?>
	</div>
	<?php
}
add_action( 'woocommerce_product_options_general_product_data', 'globalkeys_product_system_requirements_admin_field', 16 );

/**
 * @param WC_Product $product Produkt.
 */
function globalkeys_save_product_system_requirements( $product ) {
	if ( ! is_a( $product, 'WC_Product' ) ) {
		return;
	}
	if ( ! isset( $_POST['_gk_system_requirements'] ) ) {
		return;
	}
	$html = wp_unslash( $_POST['_gk_system_requirements'] );
	$html = is_string( $html ) ? wp_kses_post( $html ) : '';
	if ( $html === '' || trim( wp_strip_all_tags( $html ) ) === '' ) {
		$product->delete_meta_data( GK_PRODUCT_SYSTEM_REQUIREMENTS_META );
	} else {
		$product->update_meta_data( GK_PRODUCT_SYSTEM_REQUIREMENTS_META, $html );
	}
}
add_action( 'woocommerce_admin_process_product_object', 'globalkeys_save_product_system_requirements', 15, 1 );

/**
 * Section unter Game Description (gleiche Überschriften-Optik).
 */
function globalkeys_single_product_system_requirements_section() {
	if ( ! function_exists( 'globalkeys_single_product_is_purchase_card_active' ) || ! globalkeys_single_product_is_purchase_card_active() ) {
		return;
	}
	global $product;
	$heading = apply_filters( 'gk_system_requirements_section_heading_text', 'System Requirements', $product );
	if ( ! is_string( $heading ) || $heading === '' ) {
		return;
	}
	$content = apply_filters( 'gk_system_requirements_section_content_html', '', $product );
	$has_default = globalkeys_product_page_has_system_requirements( $product );
	if ( ( ! is_string( $content ) || $content === '' ) && ! $has_default ) {
		return;
	}
	$heading_id = 'gk-product-page-system-requirements-heading';
	if ( $product && is_a( $product, 'WC_Product' ) ) {
		$heading_id .= '-' . (int) $product->get_id();
	}
	echo '<section class="gk-product-page-system-requirements" aria-labelledby="' . esc_attr( $heading_id ) . '">';
	echo '<div class="gk-section-inner gk-section-featured-inner">';
	echo '<div class="gk-featured-heading-wrap gk-product-page-system-requirements__heading-wrap">';
	echo '<h2 id="' . esc_attr( $heading_id ) . '" class="gk-section-title gk-featured-heading">';
	echo '<span class="gk-featured-heading-text-wrap">';
	echo '<span class="gk-featured-heading-text">' . esc_html( $heading ) . '</span>';
	echo '<span class="gk-featured-title-underline" aria-hidden="true"></span>';
	echo '</span>';
	echo '</h2>';
	echo '</div>';

	echo '<div class="gk-product-page-system-requirements__body">';
	if ( is_string( $content ) && $content !== '' ) {
		echo wp_kses_post( $content );
	} elseif ( $product && is_a( $product, 'WC_Product' ) ) {
		$html = $product->get_meta( GK_PRODUCT_SYSTEM_REQUIREMENTS_META );
		if ( is_string( $html ) && trim( wp_strip_all_tags( $html ) ) !== '' ) {
			if ( function_exists( 'wc_format_content' ) ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wc_format_content() wie Produktbeschreibung.
				echo wc_format_content( $html );
			} else {
				echo wp_kses_post( wpautop( $html ) );
			}
		}
	}
	echo '</div>';

	echo '</div>';
	echo '</section>';
}
add_action( 'woocommerce_after_single_product_summary', 'globalkeys_single_product_system_requirements_section', 9 );
