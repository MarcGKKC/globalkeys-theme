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
			<section id="gk-search-results-grid" class="gk-section gk-section-bestsellers gk-section-shop-results" role="region" aria-label="<?php esc_attr_e( 'Produkte', 'globalkeys' ); ?>">
				<div class="gk-section-inner gk-section-featured-inner">
					<p id="gk-search-results-count" class="gk-search-results-count"><?php echo esc_html( sprintf( _n( '%d result', '%d results', $gk_total_count, 'globalkeys' ), $gk_total_count ) ); ?></p>
					<div id="gk-search-filter-bar" class="gk-search-filter-bar">
						<div class="gk-search-filter-row gk-search-filter-row-1">
							<div class="gk-search-filter-box">
								<select id="gk-search-sort" class="gk-search-filter-select" aria-label="<?php esc_attr_e( 'Relevance', 'globalkeys' ); ?>">
									<option value="name-asc"><?php esc_html_e( 'Relevance', 'globalkeys' ); ?></option>
									<option value="name-asc"><?php esc_html_e( 'Name A-Z', 'globalkeys' ); ?></option>
									<option value="name-desc"><?php esc_html_e( 'Name Z-A', 'globalkeys' ); ?></option>
									<option value="price-asc"><?php esc_html_e( 'Price: Low to High', 'globalkeys' ); ?></option>
									<option value="price-desc"><?php esc_html_e( 'Price: High to Low', 'globalkeys' ); ?></option>
								</select>
								<span class="gk-search-filter-chevron" aria-hidden="true">▼</span>
							</div>
							<div class="gk-search-filter-box">
								<span class="gk-search-filter-label"><?php esc_html_e( 'Platform', 'globalkeys' ); ?></span>
								<span class="gk-search-filter-chevron" aria-hidden="true">▼</span>
							</div>
							<div class="gk-search-filter-box">
								<span class="gk-search-filter-label"><?php esc_html_e( 'Region', 'globalkeys' ); ?></span>
								<span class="gk-search-filter-chevron" aria-hidden="true">▼</span>
							</div>
							<div class="gk-search-filter-box">
								<span class="gk-search-filter-label"><?php esc_html_e( 'Price', 'globalkeys' ); ?></span>
								<span class="gk-search-filter-chevron" aria-hidden="true">▼</span>
							</div>
						</div>
						<div class="gk-search-filter-row gk-search-filter-row-2">
							<div class="gk-search-filter-box">
								<span class="gk-search-filter-label"><?php esc_html_e( 'Language', 'globalkeys' ); ?></span>
								<span class="gk-search-filter-chevron" aria-hidden="true">▼</span>
							</div>
							<div class="gk-search-filter-box">
								<span class="gk-search-filter-label"><?php esc_html_e( 'Genre', 'globalkeys' ); ?></span>
								<span class="gk-search-filter-chevron" aria-hidden="true">▼</span>
							</div>
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
