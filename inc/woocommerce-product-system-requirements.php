<?php
/**
 * Produktdetail: Section „System Requirements“ (Minimum / Recommended).
 *
 * @package globalkeys
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'GK_PRODUCT_SYSTEM_REQUIREMENTS_META' ) ) {
	define( 'GK_PRODUCT_SYSTEM_REQUIREMENTS_META', '_gk_system_requirements' );
}

if ( ! defined( 'GK_PRODUCT_SYSTEM_REQUIREMENTS_SPECS_META' ) ) {
	define( 'GK_PRODUCT_SYSTEM_REQUIREMENTS_SPECS_META', '_gk_system_requirements_specs' );
}

/**
 * Schlüssel der Spezifikationszeilen (Reihenfolge der Ausgabe).
 *
 * @return array<string, string> key => Frontend-Label (Englisch).
 */
function globalkeys_system_requirements_spec_field_definitions() {
	return array(
		'os'                => 'OS',
		'processor'         => 'Processor',
		'memory'            => 'Memory',
		'graphics'          => 'Graphics',
		'directx'           => 'DirectX',
		'additional_notes'  => 'Additional Notes',
	);
}

/**
 * Leeres Spez-Array (min + recommended).
 *
 * @return array<string, array<string, string>>
 */
function globalkeys_system_requirements_empty_specs() {
	$keys = array_keys( globalkeys_system_requirements_spec_field_definitions() );
	$empty = array_fill_keys( $keys, '' );
	return array(
		'minimum'     => $empty,
		'recommended' => $empty,
	);
}

/**
 * @param mixed $raw Rohdaten aus Meta.
 * @return array<string, array<string, string>>
 */
function globalkeys_system_requirements_normalize_specs( $raw ) {
	$defs  = globalkeys_system_requirements_spec_field_definitions();
	$empty = globalkeys_system_requirements_empty_specs();
	if ( ! is_array( $raw ) ) {
		return $empty;
	}
	foreach ( array( 'minimum', 'recommended' ) as $col ) {
		if ( empty( $raw[ $col ] ) || ! is_array( $raw[ $col ] ) ) {
			continue;
		}
		foreach ( array_keys( $defs ) as $key ) {
			if ( isset( $raw[ $col ][ $key ] ) && is_string( $raw[ $col ][ $key ] ) ) {
				$empty[ $col ][ $key ] = $raw[ $col ][ $key ];
			}
		}
	}
	return $empty;
}

/**
 * @param array<string, array<string, string>> $specs Normalisierte Specs.
 * @return bool
 */
function globalkeys_system_requirements_specs_has_content( $specs ) {
	if ( ! is_array( $specs ) ) {
		return false;
	}
	foreach ( array( 'minimum', 'recommended' ) as $col ) {
		if ( empty( $specs[ $col ] ) || ! is_array( $specs[ $col ] ) ) {
			continue;
		}
		foreach ( $specs[ $col ] as $val ) {
			if ( is_string( $val ) && trim( $val ) !== '' ) {
				return true;
			}
		}
	}
	return false;
}

/**
 * @param WC_Product|null $product Produkt.
 * @return array<string, array<string, string>>
 */
function globalkeys_get_product_system_requirements_specs( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return globalkeys_system_requirements_empty_specs();
	}
	$raw = $product->get_meta( GK_PRODUCT_SYSTEM_REQUIREMENTS_SPECS_META );
	return globalkeys_system_requirements_normalize_specs( $raw );
}

/**
 * @param WC_Product|null $product Produkt.
 * @return bool
 */
function globalkeys_product_page_has_system_requirements( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return false;
	}
	$specs = globalkeys_get_product_system_requirements_specs( $product );
	if ( globalkeys_system_requirements_specs_has_content( $specs ) ) {
		return true;
	}
	$html = $product->get_meta( GK_PRODUCT_SYSTEM_REQUIREMENTS_META );
	if ( is_string( $html ) && trim( wp_strip_all_tags( $html ) ) !== '' ) {
		return true;
	}
	return (bool) apply_filters( 'gk_system_requirements_section_force_show', false, $product );
}

/**
 * Admin: Minimum / Recommended nebeneinander.
 */
function globalkeys_product_system_requirements_admin_field() {
	global $post;
	$specs = globalkeys_system_requirements_empty_specs();
	if ( $post && (int) $post->ID > 0 ) {
		$raw = get_post_meta( (int) $post->ID, GK_PRODUCT_SYSTEM_REQUIREMENTS_SPECS_META, true );
		$specs = globalkeys_system_requirements_normalize_specs( $raw );
	}
	$fields = globalkeys_system_requirements_spec_field_definitions();
	wp_nonce_field( 'gk_system_requirements_specs_save', 'gk_system_requirements_specs_nonce' );
	?>
	<div class="options_group gk-sysreq-admin">
		<p class="form-field" style="padding:0 12px;">
			<strong><?php esc_html_e( 'System Requirements (Produktseite)', 'globalkeys' ); ?></strong><br />
			<span class="description"><?php esc_html_e( 'Links: Minimum · Rechts: Recommended. Leere Zeilen erscheinen nicht auf der Produktseite.', 'globalkeys' ); ?></span>
		</p>
		<div class="gk-sysreq-admin__grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;padding:0 12px 12px;max-width:920px;box-sizing:border-box;">
			<?php
			$gk_sysreq_columns = array(
				array(
					'key'   => 'minimum',
					'label' => __( 'Minimum', 'globalkeys' ),
				),
				array(
					'key'   => 'recommended',
					'label' => __( 'Recommended', 'globalkeys' ),
				),
			);
			foreach ( $gk_sysreq_columns as $gk_sysreq_col ) {
				$col_key   = $gk_sysreq_col['key'];
				$col_label = $gk_sysreq_col['label'];
				echo '<div class="gk-sysreq-admin__col gk-sysreq-admin__col--' . esc_attr( $col_key ) . '" style="margin:0;padding:10px 12px;border:1px solid #c3c4c7;border-radius:4px;background:#fafafa;box-sizing:border-box;">';
				echo '<p class="gk-sysreq-admin__col-title" style="margin:0 0 10px;padding:6px 8px;font-size:13px;font-weight:600;line-height:1.3;color:#1d2327;background:#e8e8e8;border:1px solid #c3c4c7;border-radius:3px;">' . esc_html( $col_label ) . '</p>';
				foreach ( $fields as $fkey => $flabel ) {
					$fid   = 'gk_sysreq_' . $col_key . '_' . $fkey;
					$fname = 'gk_sysreq[' . $col_key . '][' . $fkey . ']';
					$val   = isset( $specs[ $col_key ][ $fkey ] ) ? (string) $specs[ $col_key ][ $fkey ] : '';
					$rows  = ( $fkey === 'additional_notes' ) ? 4 : 2;
					echo '<p class="form-field gk-sysreq-admin__row" style="margin:0 0 6px;">';
					echo '<label for="' . esc_attr( $fid ) . '" style="display:block;font-weight:600;margin-bottom:3px;">' . esc_html( $flabel . ':' ) . '</label>';
					echo '<textarea id="' . esc_attr( $fid ) . '" name="' . esc_attr( $fname ) . '" class="large-text" rows="' . (int) $rows . '" style="width:100%;box-sizing:border-box;">' . esc_textarea( $val ) . '</textarea>';
					echo '</p>';
				}
				echo '</div>';
			}
			?>
		</div>
		<?php
		$legacy = ( $post && (int) $post->ID > 0 ) ? get_post_meta( (int) $post->ID, GK_PRODUCT_SYSTEM_REQUIREMENTS_META, true ) : '';
		if ( is_string( $legacy ) && trim( wp_strip_all_tags( $legacy ) ) !== '' ) {
			echo '<p class="form-field" style="padding:0 12px 12px;"><span class="description">';
			esc_html_e( 'Hinweis: Es existiert noch alter Freitext unter „Legacy“. Er wird auf der Seite nur angezeigt, wenn die neuen Felder leer sind.', 'globalkeys' );
			echo '</span></p>';
		}
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
	if ( ! isset( $_POST['gk_system_requirements_specs_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gk_system_requirements_specs_nonce'] ) ), 'gk_system_requirements_specs_save' ) ) {
		return;
	}
	$defs = globalkeys_system_requirements_spec_field_definitions();
	$out  = globalkeys_system_requirements_empty_specs();
	if ( ! empty( $_POST['gk_sysreq'] ) && is_array( $_POST['gk_sysreq'] ) ) {
		$post_specs = wp_unslash( $_POST['gk_sysreq'] );
		foreach ( array( 'minimum', 'recommended' ) as $col ) {
			if ( empty( $post_specs[ $col ] ) || ! is_array( $post_specs[ $col ] ) ) {
				continue;
			}
			foreach ( array_keys( $defs ) as $key ) {
				if ( ! isset( $post_specs[ $col ][ $key ] ) ) {
					continue;
				}
				$raw = $post_specs[ $col ][ $key ];
				$raw = is_string( $raw ) ? $raw : '';
				if ( $key === 'additional_notes' ) {
					$out[ $col ][ $key ] = sanitize_textarea_field( $raw );
				} else {
					$out[ $col ][ $key ] = sanitize_text_field( $raw );
				}
			}
		}
	}
	if ( globalkeys_system_requirements_specs_has_content( $out ) ) {
		$product->update_meta_data( GK_PRODUCT_SYSTEM_REQUIREMENTS_SPECS_META, $out );
	} else {
		$product->delete_meta_data( GK_PRODUCT_SYSTEM_REQUIREMENTS_SPECS_META );
	}
}
add_action( 'woocommerce_admin_process_product_object', 'globalkeys_save_product_system_requirements', 15, 1 );

/**
 * @param array<string, string> $defs Felddefinitionen.
 * @param array<string, string> $col  Werte einer Spalte.
 * @return bool
 */
function globalkeys_system_requirements_column_nonempty( $defs, $col ) {
	if ( ! is_array( $col ) ) {
		return false;
	}
	foreach ( array_keys( $defs ) as $key ) {
		if ( isset( $col[ $key ] ) && trim( (string) $col[ $key ] ) !== '' ) {
			return true;
		}
	}
	return false;
}

/**
 * @param array<string, string> $defs  Felddefinitionen.
 * @param array<string, string> $col   Werte einer Spalte.
 * @param string                $title Spaltentitel.
 */
function globalkeys_render_system_requirements_column( $defs, $col, $title ) {
	if ( ! is_array( $col ) ) {
		return;
	}
	$rows = array();
	foreach ( $defs as $key => $label ) {
		$val = isset( $col[ $key ] ) ? trim( (string) $col[ $key ] ) : '';
		if ( $val === '' ) {
			continue;
		}
		$rows[] = array(
			'key'   => $key,
			'label' => $label,
			'value' => $val,
		);
	}
	if ( empty( $rows ) ) {
		return;
	}
	echo '<div class="gk-product-page-system-requirements__column">';
	echo '<h3 class="gk-game-desc-block__title gk-product-page-system-requirements__column-title">' . esc_html( $title ) . '</h3>';
	echo '<div class="gk-game-desc-block__body gk-product-page-system-requirements__spec-list">';
	echo '<dl class="gk-product-page-system-requirements__dl">';
	foreach ( $rows as $row ) {
		echo '<dt class="gk-product-page-system-requirements__dt">' . esc_html( $row['label'] . ':' ) . '</dt>';
		echo '<dd class="gk-product-page-system-requirements__dd">' . nl2br( esc_html( $row['value'] ), false ) . '</dd>';
	}
	echo '</dl>';
	echo '</div>';
	echo '</div>';
}

/**
 * Section unter Game Description.
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
	$content     = apply_filters( 'gk_system_requirements_section_content_html', '', $product );
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
		$specs = globalkeys_get_product_system_requirements_specs( $product );
		if ( globalkeys_system_requirements_specs_has_content( $specs ) ) {
			$defs    = globalkeys_system_requirements_spec_field_definitions();
			$has_min = globalkeys_system_requirements_column_nonempty( $defs, $specs['minimum'] );
			$has_rec = globalkeys_system_requirements_column_nonempty( $defs, $specs['recommended'] );
			$col_cls = 'gk-product-page-system-requirements__columns';
			if ( ! $has_min || ! $has_rec ) {
				$col_cls .= ' gk-product-page-system-requirements__columns--single';
			}
			echo '<div class="' . esc_attr( $col_cls ) . '" role="presentation">';
			if ( $has_min ) {
				globalkeys_render_system_requirements_column( $defs, $specs['minimum'], 'Minimum' );
			}
			if ( $has_rec ) {
				globalkeys_render_system_requirements_column( $defs, $specs['recommended'], 'Recommended' );
			}
			echo '</div>';
		} else {
			$html = $product->get_meta( GK_PRODUCT_SYSTEM_REQUIREMENTS_META );
			if ( is_string( $html ) && trim( wp_strip_all_tags( $html ) ) !== '' ) {
				if ( function_exists( 'wc_format_content' ) ) {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wc_format_content().
					echo wc_format_content( $html );
				} else {
					echo wp_kses_post( wpautop( $html ) );
				}
			}
		}
	}
	echo '</div>';

	echo '</div>';
	echo '</section>';
}
add_action( 'woocommerce_after_single_product_summary', 'globalkeys_single_product_system_requirements_section', 9 );
