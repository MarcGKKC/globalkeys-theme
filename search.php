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

if ( $gk_product_search && class_exists( 'WooCommerce' ) ) {
	if ( $gk_search_term !== '' ) {
		$gk_search_result = function_exists( 'globalkeys_search_products_starts_with' )
			? globalkeys_search_products_starts_with( $gk_search_term, 0 )
			: array( 'ids' => array() );
		$gk_product_ids   = isset( $gk_search_result['ids'] ) ? $gk_search_result['ids'] : array();
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
			?>
			<div id="gk-search-layout" class="gk-search-layout">
				<aside id="gk-search-filter-sidebar" class="gk-search-filter-sidebar" role="complementary" aria-label="<?php esc_attr_e( 'Filters', 'globalkeys' ); ?>">
					<div class="gk-search-filter-sidebar-header">
						<h2 class="gk-search-filter-sidebar-title"><?php esc_html_e( 'Filters', 'globalkeys' ); ?></h2>
						<button type="button" class="gk-search-filter-sidebar-close" aria-label="<?php esc_attr_e( 'Close filters', 'globalkeys' ); ?>">
							<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
						</button>
					</div>
					<div class="gk-search-filter-sidebar-content">
						<?php /* Filter-Inhalte werden später eingefügt */ ?>
					</div>
				</aside>
				<div class="gk-search-main">
			<section id="gk-search-results-grid" class="gk-section gk-section-bestsellers gk-section-shop-results" role="region" aria-label="<?php esc_attr_e( 'Produkte', 'globalkeys' ); ?>">
				<div class="gk-section-inner gk-section-featured-inner">
					<div class="gk-search-results-header">
						<p id="gk-search-results-count" class="gk-search-results-count"><?php echo esc_html( sprintf( _n( '%d result', '%d results', $gk_total_count, 'globalkeys' ), $gk_total_count ) ); ?></p>
						<div class="gk-search-sort-by-wrap">
							<button type="button" class="gk-search-filters-toggle" aria-label="<?php esc_attr_e( 'Open filters', 'globalkeys' ); ?>" aria-expanded="false" aria-controls="gk-search-filter-sidebar">
								<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>
								<span><?php esc_html_e( 'Filters', 'globalkeys' ); ?></span>
							</button>
							<label for="gk-search-sort" class="gk-search-sort-by-label"><?php esc_html_e( 'Sort by:', 'globalkeys' ); ?></label>
							<select id="gk-search-sort" class="gk-search-sort-select" aria-label="<?php esc_attr_e( 'Sort by', 'globalkeys' ); ?>">
								<option value="name-asc"><?php esc_html_e( 'Relevance', 'globalkeys' ); ?></option>
								<option value="name-desc"><?php esc_html_e( 'Name Z-A', 'globalkeys' ); ?></option>
								<option value="price-asc"><?php esc_html_e( 'Price: Low to High', 'globalkeys' ); ?></option>
								<option value="price-desc"><?php esc_html_e( 'Price: High to Low', 'globalkeys' ); ?></option>
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
				</div>
				<div id="gk-search-no-results" style="display:<?php echo ( $gk_product_query && $gk_product_query->have_posts() ) ? 'none' : 'block'; ?>;">
					<?php get_template_part( 'template-parts/content', 'none' ); ?>
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
