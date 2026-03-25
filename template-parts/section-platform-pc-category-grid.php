<?php
/**
 * PC-Kollektion: Kategorie-Raster – zwei Reihen (10), wichtigste Genres wie Homepage.
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gk_pc_category_labels = function_exists( 'globalkeys_get_pc_category_grid_labels' )
	? globalkeys_get_pc_category_grid_labels()
	: array();
$gk_pc_category_all_labels = function_exists( 'globalkeys_get_homepage_category_collection_labels' )
	? globalkeys_get_homepage_category_collection_labels()
	: array();
$gk_pc_category_full_labels = $gk_pc_category_labels;
foreach ( $gk_pc_category_all_labels as $gk_cat_label ) {
	if ( in_array( $gk_cat_label, $gk_pc_category_full_labels, true ) ) {
		continue;
	}
	$gk_pc_category_full_labels[] = $gk_cat_label;
}
?>

<section class="gk-section gk-section-pc-category-grid" role="region" aria-labelledby="gk-pc-category-grid-title">
	<div class="gk-section-inner gk-section-featured-inner">
		<div class="gk-platform-header gk-pc-category-grid__header">
			<div class="gk-pc-category-grid__title-row">
				<h2 id="gk-pc-category-grid-title" class="gk-platform-title"><?php esc_html_e( 'You don\'t find what you are looking for?', 'globalkeys' ); ?></h2>
			</div>
			<p class="gk-platform-desc"><?php esc_html_e( 'All the categories for the best prices only on Globalkeys.co', 'globalkeys' ); ?></p>
		</div>
		<ul id="gk-pc-category-grid-list-compact" class="gk-pc-category-grid__list gk-pc-category-grid__list--compact" aria-hidden="false" aria-label="<?php esc_attr_e( 'Top categories', 'globalkeys' ); ?>">
			<?php foreach ( $gk_pc_category_labels as $gk_cat_label ) : ?>
				<li class="gk-pc-category-grid__item">
					<a class="gk-pc-category-grid__link" href="#"><?php echo esc_html( $gk_cat_label ); ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
		<ul id="gk-pc-category-grid-list-full" class="gk-pc-category-grid__list gk-pc-category-grid__list--full" aria-hidden="true" aria-label="<?php esc_attr_e( 'All categories', 'globalkeys' ); ?>">
			<?php foreach ( $gk_pc_category_full_labels as $gk_cat_label ) : ?>
				<li class="gk-pc-category-grid__item">
					<a class="gk-pc-category-grid__link" href="#"><?php echo esc_html( $gk_cat_label ); ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
		<div class="gk-pc-category-grid__footer">
			<button type="button" class="gk-pc-category-grid__toggle" aria-expanded="false" aria-controls="gk-pc-category-grid-list-full">
				<?php esc_html_e( 'Show All', 'globalkeys' ); ?>
			</button>
		</div>
	</div>
</section>
