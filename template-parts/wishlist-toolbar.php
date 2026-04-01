<?php
/**
 * Wunschliste: Toolbar (Suche, Anzahl, Sortieren).
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gk_wl_toolbar_count = 0;
if ( is_user_logged_in() && class_exists( 'WooCommerce' ) && function_exists( 'globalkeys_wishlist_get_ids' ) ) {
	foreach ( globalkeys_wishlist_get_ids( get_current_user_id() ) as $gk_wl_cid ) {
		$gk_wl_cp = wc_get_product( $gk_wl_cid );
		if ( $gk_wl_cp && is_a( $gk_wl_cp, 'WC_Product' ) && $gk_wl_cp->is_visible() ) {
			++$gk_wl_toolbar_count;
		}
	}
}
if ( 0 === $gk_wl_toolbar_count ) {
	$gk_wl_count_label = __( 'Keine Produkte', 'globalkeys' );
} elseif ( 1 === $gk_wl_toolbar_count ) {
	$gk_wl_count_label = __( '1 Produkt', 'globalkeys' );
} else {
	$gk_wl_count_label = sprintf(
		/* translators: %d: number of products */
		__( '%d Produkte', 'globalkeys' ),
		$gk_wl_toolbar_count
	);
}
if ( ! is_user_logged_in() ) {
	$gk_wl_count_label = __( '…', 'globalkeys' );
}

if ( function_exists( 'globalkeys_wishlist_toolbar_print_layout_css' ) ) {
	globalkeys_wishlist_toolbar_print_layout_css();
}
?>
<div class="gk-wishlist__toolbar" role="region" aria-label="<?php esc_attr_e( 'Wunschliste filtern und sortieren', 'globalkeys' ); ?>">
	<div id="gk-wishlist-toolbar-row" class="gk-wishlist__toolbar-row">
		<div class="gk-wishlist__search-wrap">
			<label class="screen-reader-text" for="gk-wishlist-search"><?php esc_html_e( 'Nach Name oder Tag suchen', 'globalkeys' ); ?></label>
			<input
				type="search"
				id="gk-wishlist-search"
				class="gk-wishlist__search-input"
				placeholder="<?php echo esc_attr__( 'Nach Name oder Tag suchen', 'globalkeys' ); ?>"
				autocomplete="off"
				inputmode="search"
			/>
		</div>
		<div class="gk-wishlist__toolbar-actions">
			<div class="gk-wishlist__tb-count" id="gk-wl-product-count" role="status" aria-live="polite"><?php echo esc_html( $gk_wl_count_label ); ?></div>
			<div class="gk-wishlist__tb-sort-wrap" id="gk-wl-sort-wrap">
				<button
					type="button"
					class="gk-wishlist__tb-btn gk-wishlist__tb-btn--sort"
					id="gk-wl-sort-toggle"
					aria-haspopup="listbox"
					aria-expanded="false"
					aria-controls="gk-wl-sort-menu"
				>
					<span class="gk-wishlist__tb-sort">
						<span class="gk-wishlist__tb-sort-label"><?php esc_html_e( 'Sortieren nach:', 'globalkeys' ); ?></span>
						<span class="gk-wishlist__tb-sort-value"><?php esc_html_e( 'Ihre Reihenfolge', 'globalkeys' ); ?></span>
					</span>
					<svg class="gk-wishlist__tb-chevron" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg>
				</button>
				<div
					id="gk-wl-sort-menu"
					class="gk-wishlist__tb-sort-menu"
					role="listbox"
					aria-label="<?php esc_attr_e( 'Sortierung wählen', 'globalkeys' ); ?>"
					hidden
				>
					<button type="button" class="gk-wishlist__tb-sort-opt" role="option" data-gk-wl-sort="order"><?php esc_html_e( 'Ihre Reihenfolge', 'globalkeys' ); ?></button>
					<button type="button" class="gk-wishlist__tb-sort-opt" role="option" data-gk-wl-sort="price-asc"><?php esc_html_e( 'Preis: niedrig → hoch', 'globalkeys' ); ?></button>
					<button type="button" class="gk-wishlist__tb-sort-opt" role="option" data-gk-wl-sort="price-desc"><?php esc_html_e( 'Preis: hoch → niedrig', 'globalkeys' ); ?></button>
					<button type="button" class="gk-wishlist__tb-sort-opt" role="option" data-gk-wl-sort="release-desc"><?php esc_html_e( 'Erscheinungsdatum: neueste zuerst', 'globalkeys' ); ?></button>
					<button type="button" class="gk-wishlist__tb-sort-opt" role="option" data-gk-wl-sort="added-desc"><?php esc_html_e( 'Hinzugefügt: zuletzt zuerst', 'globalkeys' ); ?></button>
					<button type="button" class="gk-wishlist__tb-sort-opt" role="option" data-gk-wl-sort="name-asc"><?php esc_html_e( 'Name A–Z', 'globalkeys' ); ?></button>
				</div>
			</div>
		</div>
	</div>
	<?php
	$gk_wishlist_toolbar_rule_style = 'display:block;box-sizing:border-box;width:100%;height:1px;min-height:1px;padding:0;border:0;overflow:hidden;background-color:rgba(255,255,255,.2);';
	?>
	<div id="gk-wishlist-toolbar-rule" class="gk-wishlist__toolbar-rule" style="<?php echo esc_attr( $gk_wishlist_toolbar_rule_style ); ?>" role="separator" aria-hidden="true"></div>
</div>
