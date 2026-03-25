<?php
/**
 * The template for displaying search results pages
 *
 * Produktsuche (?s=query&post_type=product): nur Produkte, deren Name oder SKU
 * mit dem Suchbegriff beginnt (nicht irgendwo enthält).
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package globalkeys
 */

get_header();

$gk_product_search = isset( $_GET['post_type'] ) && $_GET['post_type'] === 'product'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$gk_search_term    = isset( $_GET['s'] ) ? trim( (string) $_GET['s'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$gk_pt_param     = isset( $_GET['gk_pt'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['gk_pt'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$gk_pt_preorders = ( 'pre-orders' === $gk_pt_param );
if ( $gk_product_search && class_exists( 'WooCommerce' ) ) {
	if ( $gk_search_term !== '' ) {
		$gk_exclude_preorders = ! $gk_pt_preorders;
		$gk_search_result     = function_exists( 'globalkeys_search_products_starts_with' )
			? globalkeys_search_products_starts_with( $gk_search_term, 0, $gk_exclude_preorders )
			: array( 'ids' => array() );
		$gk_product_ids   = isset( $gk_search_result['ids'] ) ? $gk_search_result['ids'] : array();
		if ( $gk_pt_preorders && function_exists( 'globalkeys_get_preorder_list_product_ids' ) ) {
			$gk_pre_ids = globalkeys_get_preorder_list_product_ids();
			$gk_product_ids = ! empty( $gk_pre_ids ) ? array_values( array_intersect( $gk_product_ids, $gk_pre_ids ) ) : array();
		}
		$gk_product_query = new WP_Query(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'post__in'       => $gk_product_ids ?: array( 0 ),
				'orderby'        => 'post_title',
				'order'          => 'ASC',
				'paged'          => get_query_var( 'paged' ) ? (int) get_query_var( 'paged' ) : 1,
				'posts_per_page' => get_option( 'posts_per_page', 12 ),
			)
		);
	} elseif ( $gk_pt_preorders && function_exists( 'globalkeys_get_preorder_list_product_ids' ) ) {
		$gk_preorder_ids = globalkeys_get_preorder_list_product_ids();
		$gk_product_query = new WP_Query(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'post__in'       => ! empty( $gk_preorder_ids ) ? $gk_preorder_ids : array( 0 ),
				'orderby'        => 'post_title',
				'order'          => 'ASC',
				'paged'          => get_query_var( 'paged' ) ? (int) get_query_var( 'paged' ) : 1,
				'posts_per_page' => get_option( 'posts_per_page', 12 ),
			)
		);
	} else {
		$gk_product_query = new WP_Query(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'orderby'        => 'post_title',
				'order'          => 'ASC',
				'paged'          => get_query_var( 'paged' ) ? (int) get_query_var( 'paged' ) : 1,
				'posts_per_page' => get_option( 'posts_per_page', 12 ),
			)
		);
	}
} else {
	$gk_product_query = null;
}
?>

	<main id="primary" class="site-main site-main--shop site-main--search-results">

		<?php if ( $gk_product_search && class_exists( 'WooCommerce' ) ) : ?>
			<?php
			$gk_total_count = ( $gk_product_query && $gk_product_query->found_posts >= 0 ) ? (int) $gk_product_query->found_posts : 0;
			$gk_filters_launch_open = isset( $_GET['gk_filters'] ) && 'open' === sanitize_text_field( wp_unslash( (string) $_GET['gk_filters'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			?>
			<div id="gk-search-layout" class="gk-search-layout<?php echo $gk_filters_launch_open ? ' is-sidebar-open' : ''; ?>">
				<aside id="gk-search-filter-sidebar" class="gk-search-filter-sidebar" role="complementary" aria-label="<?php esc_attr_e( 'Filters', 'globalkeys' ); ?>">
					<div class="gk-search-filter-sidebar-header">
						<h2 class="gk-search-filter-sidebar-title"><?php esc_html_e( 'Filters', 'globalkeys' ); ?></h2>
						<button type="button" class="gk-search-filter-sidebar-close" aria-label="<?php esc_attr_e( 'Close filters', 'globalkeys' ); ?>">
							<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
						</button>
					</div>
					<div class="gk-search-filter-sidebar-content">
						<div class="gk-filter-price">
							<div class="gk-filter-price-header">
								<label class="gk-filter-price-label" for="gk-price-min"><?php esc_html_e( 'Price', 'globalkeys' ); ?></label>
								<button type="button" class="gk-filter-price-reset" id="gk-price-reset" aria-label="<?php esc_attr_e( 'Reset price filter', 'globalkeys' ); ?>" title="<?php esc_attr_e( 'Reset to default', 'globalkeys' ); ?>">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
								</button>
							</div>
							<div class="gk-filter-price-slider-wrap">
								<div class="gk-filter-price-track">
									<div class="gk-filter-price-fill" id="gk-price-fill"></div>
								</div>
								<input type="range" min="0" max="100" value="0" step="1" class="gk-filter-price-input gk-filter-price-min" id="gk-price-min" aria-label="<?php esc_attr_e( 'Minimum price', 'globalkeys' ); ?>">
								<input type="range" min="0" max="100" value="100" step="1" class="gk-filter-price-input gk-filter-price-max" id="gk-price-max" aria-label="<?php esc_attr_e( 'Maximum price', 'globalkeys' ); ?>">
							</div>
							<p class="gk-filter-price-value" id="gk-price-value"><?php esc_html_e( 'Between 0 € and MAX', 'globalkeys' ); ?></p>
						</div>
						<div class="gk-filter-preferences">
							<div class="gk-filter-preferences-header">
								<button type="button" class="gk-filter-preferences-toggle" id="gk-preferences-toggle" aria-expanded="true" aria-controls="gk-preferences-content" aria-label="<?php esc_attr_e( 'Toggle preferences filter', 'globalkeys' ); ?>">
									<span class="gk-filter-preferences-title"><?php esc_html_e( 'Preferences', 'globalkeys' ); ?></span>
									<svg class="gk-filter-preferences-chevron" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg>
								</button>
								<button type="button" class="gk-filter-preferences-reset" id="gk-preferences-reset" aria-label="<?php esc_attr_e( 'Reset preferences filter', 'globalkeys' ); ?>" title="<?php esc_attr_e( 'Reset to default', 'globalkeys' ); ?>">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
								</button>
							</div>
							<div class="gk-filter-preferences-content" id="gk-preferences-content">
								<label class="gk-filter-checkbox-label">
									<input type="checkbox" class="gk-filter-checkbox" id="gk-hide-out-of-stock" aria-label="<?php esc_attr_e( 'Hide out of stock items', 'globalkeys' ); ?>">
									<span class="gk-filter-checkbox-text"><?php esc_html_e( 'Hide out of stock items', 'globalkeys' ); ?></span>
								</label>
							</div>
						</div>
						<div class="gk-filter-devices">
							<div class="gk-filter-devices-header">
								<button type="button" class="gk-filter-devices-toggle" id="gk-devices-toggle" aria-expanded="true" aria-controls="gk-devices-content" aria-label="<?php esc_attr_e( 'Toggle devices filter', 'globalkeys' ); ?>">
									<span class="gk-filter-devices-title"><?php esc_html_e( 'Devices', 'globalkeys' ); ?></span>
									<svg class="gk-filter-devices-chevron" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg>
								</button>
								<button type="button" class="gk-filter-devices-reset" id="gk-devices-reset" aria-label="<?php esc_attr_e( 'Reset devices filter', 'globalkeys' ); ?>" title="<?php esc_attr_e( 'Reset to default', 'globalkeys' ); ?>">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
								</button>
							</div>
							<div class="gk-filter-devices-content" id="gk-devices-content"></div>
						</div>
						<div class="gk-filter-product-type">
							<div class="gk-filter-product-type-header">
								<button type="button" class="gk-filter-product-type-toggle" id="gk-product-type-toggle" aria-expanded="true" aria-controls="gk-product-type-content" aria-label="<?php esc_attr_e( 'Toggle product type filter', 'globalkeys' ); ?>">
									<span class="gk-filter-product-type-title"><?php esc_html_e( 'Product type', 'globalkeys' ); ?></span>
									<svg class="gk-filter-product-type-chevron" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg>
								</button>
								<button type="button" class="gk-filter-product-type-reset" id="gk-product-type-reset" aria-label="<?php esc_attr_e( 'Reset product type filter', 'globalkeys' ); ?>" title="<?php esc_attr_e( 'Reset to default', 'globalkeys' ); ?>">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
								</button>
							</div>
							<div class="gk-filter-product-type-content" id="gk-product-type-content"<?php echo ! empty( $gk_pt_preorders ) ? ' data-gk-initial-pt="pre-orders"' : ''; ?>></div>
						</div>
						<div class="gk-filter-game-modes">
							<div class="gk-filter-game-modes-header">
								<button type="button" class="gk-filter-game-modes-toggle" id="gk-game-modes-toggle" aria-expanded="true" aria-controls="gk-game-modes-content" aria-label="<?php esc_attr_e( 'Toggle game modes filter', 'globalkeys' ); ?>">
									<span class="gk-filter-game-modes-title"><?php esc_html_e( 'Game modes', 'globalkeys' ); ?></span>
									<svg class="gk-filter-game-modes-chevron" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg>
								</button>
								<button type="button" class="gk-filter-game-modes-reset" id="gk-game-modes-reset" aria-label="<?php esc_attr_e( 'Reset game modes filter', 'globalkeys' ); ?>" title="<?php esc_attr_e( 'Reset to default', 'globalkeys' ); ?>">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
								</button>
							</div>
							<div class="gk-filter-game-modes-content" id="gk-game-modes-content"></div>
						</div>
						<div class="gk-filter-categories">
							<div class="gk-filter-categories-header">
								<button type="button" class="gk-filter-categories-toggle" id="gk-categories-toggle" aria-expanded="true" aria-controls="gk-categories-content" aria-label="<?php esc_attr_e( 'Toggle categories filter', 'globalkeys' ); ?>">
									<span class="gk-filter-categories-title"><?php esc_html_e( 'Categories', 'globalkeys' ); ?></span>
									<svg class="gk-filter-categories-chevron" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg>
								</button>
								<button type="button" class="gk-filter-categories-reset" id="gk-categories-reset" aria-label="<?php esc_attr_e( 'Reset categories filter', 'globalkeys' ); ?>" title="<?php esc_attr_e( 'Reset to default', 'globalkeys' ); ?>">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
								</button>
							</div>
							<div class="gk-filter-categories-content" id="gk-categories-content"></div>
						</div>
						<div class="gk-filter-gamepads is-collapsed">
							<div class="gk-filter-gamepads-header">
								<button type="button" class="gk-filter-gamepads-toggle" id="gk-gamepads-toggle" aria-expanded="false" aria-controls="gk-gamepads-content" aria-label="<?php esc_attr_e( 'Toggle gamepads filter', 'globalkeys' ); ?>">
									<span class="gk-filter-gamepads-title"><?php esc_html_e( 'Gamepads', 'globalkeys' ); ?></span>
									<svg class="gk-filter-gamepads-chevron" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg>
								</button>
								<button type="button" class="gk-filter-gamepads-reset" id="gk-gamepads-reset" aria-label="<?php esc_attr_e( 'Reset gamepads filter', 'globalkeys' ); ?>" title="<?php esc_attr_e( 'Reset to default', 'globalkeys' ); ?>">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
								</button>
							</div>
							<div class="gk-filter-gamepads-content" id="gk-gamepads-content"></div>
						</div>
					</div>
				</aside>
				<div class="gk-search-main">
			<section id="gk-search-results-grid" class="gk-section gk-section-bestsellers gk-section-shop-results" role="region" aria-label="<?php esc_attr_e( 'Produkte', 'globalkeys' ); ?>">
				<div class="gk-section-inner gk-section-featured-inner">
					<div id="gk-active-filters-bar" class="gk-active-filters-bar" aria-hidden="true">
						<div class="gk-active-filters-chips"></div>
						<button type="button" class="gk-active-filters-clear-all" id="gk-active-filters-clear-all"><?php esc_html_e( 'Clear all', 'globalkeys' ); ?></button>
					</div>
					<div class="gk-search-results-header">
						<p id="gk-search-results-count" class="gk-search-results-count"><?php echo esc_html( sprintf( _n( '%d result', '%d results', $gk_total_count, 'globalkeys' ), $gk_total_count ) ); ?></p>
						<div class="gk-search-sort-by-wrap">
							<div class="gk-search-filters-toggle-wrap">
								<span id="gk-search-filters-count-badge" class="gk-search-filters-count-badge" aria-hidden="true">0</span>
								<button type="button" class="gk-search-filters-toggle" aria-label="<?php esc_attr_e( 'Open filters', 'globalkeys' ); ?>" aria-expanded="<?php echo $gk_filters_launch_open ? 'true' : 'false'; ?>" aria-controls="gk-search-filter-sidebar">
									<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>
									<span><?php esc_html_e( 'Filters', 'globalkeys' ); ?></span>
								</button>
							</div>
							<label for="gk-search-sort" class="gk-search-sort-by-label"><?php esc_html_e( 'Sort by:', 'globalkeys' ); ?></label>
							<select id="gk-search-sort" class="gk-search-sort-select" aria-label="<?php esc_attr_e( 'Sort by', 'globalkeys' ); ?>">
								<option value="name-asc"><?php esc_html_e( 'Relevance', 'globalkeys' ); ?></option>
								<option value="name-desc"><?php esc_html_e( 'Name Z-A', 'globalkeys' ); ?></option>
								<option value="price-asc"><?php esc_html_e( 'Price: Low to High', 'globalkeys' ); ?></option>
								<option value="price-desc"><?php esc_html_e( 'Price: High to Low', 'globalkeys' ); ?></option>
								<option value="date-desc"><?php esc_html_e( 'Latest released', 'globalkeys' ); ?></option>
							</select>
						</div>
					</div>
					<ul class="gk-featured-products gk-featured-products--shop" aria-label="<?php esc_attr_e( 'Produkte', 'globalkeys' ); ?>">
						<?php
						if ( $gk_product_query && $gk_product_query->have_posts() ) {
							while ( $gk_product_query->have_posts() ) {
								$gk_product_query->the_post();
								$product = wc_get_product( get_the_ID() );
								if ( $product && $product->is_visible() ) {
									$GLOBALS['product'] = $product;
									get_template_part( 'template-parts/product-card', 'bestseller' );
								}
							}
						}
						?>
					</ul>
					<div id="gk-search-no-results" class="gk-search-no-results-wrap" style="display:<?php echo ( $gk_product_query && $gk_product_query->have_posts() ) ? 'none' : 'flex'; ?>;">
						<?php get_template_part( 'template-parts/content', 'search-none' ); ?>
					</div>
				</div>
				<div id="gk-search-pagination">
					<?php
					if ( $gk_product_query && $gk_product_query->have_posts() ) {
						wp_reset_postdata();
						global $wp_query;
						$gk_original_query = $wp_query; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
						$wp_query         = $gk_product_query; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
						the_posts_pagination();
						$wp_query = $gk_original_query; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					}
					?>
				</div>
			</section>
				</div><!-- .gk-search-main -->
			</div><!-- .gk-search-layout -->
		<?php elseif ( have_posts() ) : ?>
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content', 'search' );
			endwhile;
			the_posts_navigation();
			?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content', 'none' ); ?>
		<?php endif; ?>

	</main><!-- #main -->

<?php
get_footer();
