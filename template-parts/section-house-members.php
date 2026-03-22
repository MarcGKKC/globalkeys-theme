<?php
/**
 * Template part: House Rewards (Section-ID section-house-members)
 * Bis zu 6 Produkte (GLOBALKEYS_HOUSE_REWARDS_MAX): Customizer-IDs oder Theme-Standard-Slugs; Fallback Tag „house-members“ / Featured.
 * Kein globalkeys_wc_product_args_exclude_preorders – kuratierte Slots sollen auch Vorbesteller zeigen (sonst fehlen Karten).
 * Mitgliederpreis optional über Feld „House-Mitgliederpreis“ am Produkt.
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section    = get_query_var( 'gk_section', array( 'id' => 'section-house-members', 'aria_label' => __( 'Premium Discounts', 'globalkeys' ) ) );
$id         = ! empty( $section['id'] ) ? $section['id'] : 'section-house-members';
$aria_label = ! empty( $section['aria_label'] ) ? $section['aria_label'] : __( 'Premium Discounts', 'globalkeys' );

$products = array();
$limit    = defined( 'GLOBALKEYS_HOUSE_REWARDS_MAX' ) ? (int) GLOBALKEYS_HOUSE_REWARDS_MAX : 6;

if ( function_exists( 'wc_get_products' ) ) {
	$curated_ids = function_exists( 'globalkeys_get_house_members_curated_product_ids' )
		? globalkeys_get_house_members_curated_product_ids()
		: array();

	if ( ! empty( $curated_ids ) ) {
		$args     = array(
			'status'  => 'publish',
			'limit'   => $limit,
			'include' => $curated_ids,
			'orderby' => 'post__in',
			'return'  => 'objects',
		);
		$products = wc_get_products( $args );
	}

	if ( empty( $products ) ) {
		$args_tag = array(
			'status'  => 'publish',
			'limit'   => $limit,
			'tag'     => array( 'house-members' ),
			'orderby' => 'menu_order title',
			'order'   => 'ASC',
		);
		$products = wc_get_products( $args_tag );
	}

	if ( empty( $products ) ) {
		$args_feat = array(
			'featured' => true,
			'status'   => 'publish',
			'limit'    => $limit,
			'orderby'  => 'menu_order title',
			'order'    => 'ASC',
		);
		$products  = wc_get_products( $args_feat );
	}
}
?>

<section id="<?php echo esc_attr( $id ); ?>" class="gk-section gk-section-bestsellers gk-section-house-members" role="region" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
	<div class="gk-section-inner gk-section-featured-inner">
		<div class="gk-featured-heading-wrap">
			<h2 id="<?php echo esc_attr( $id ); ?>-title" class="gk-section-title gk-featured-heading">
				<span class="gk-featured-heading-text-wrap">
					<span class="gk-featured-heading-text"><?php esc_html_e( 'Premium Discounts', 'globalkeys' ); ?></span>
					<span class="gk-featured-title-underline" aria-hidden="true"></span>
				</span>
				<span class="gk-featured-heading-arrow" aria-hidden="true"></span>
			</h2>
		</div>
		<?php if ( ! empty( $products ) ) : ?>
			<ul class="gk-featured-products" aria-label="<?php esc_attr_e( 'Premium Discounts products', 'globalkeys' ); ?>">
				<?php foreach ( $products as $product ) : ?>
					<?php
					if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
						continue;
					}
					if ( function_exists( 'globalkeys_house_rewards_pricing_context_enter' ) ) {
						globalkeys_house_rewards_pricing_context_enter();
					}
					try {
						set_query_var( 'product', $product );
						set_query_var( 'gk_house_rewards_card_context', true );
						get_template_part( 'template-parts/product-card', 'bestseller' );
					} finally {
						if ( function_exists( 'globalkeys_house_rewards_pricing_context_leave' ) ) {
							globalkeys_house_rewards_pricing_context_leave();
						}
					}
					?>
				<?php endforeach; ?>
				<?php
				set_query_var( 'gk_house_rewards_card_context', false );
				?>
			</ul>
		<?php else : ?>
			<p class="gk-section-text gk-featured-empty"><?php esc_html_e( 'Aktuell sind keine Premium-Discounts-Produkte hinterlegt.', 'globalkeys' ); ?></p>
		<?php endif; ?>
	</div>
</section>
