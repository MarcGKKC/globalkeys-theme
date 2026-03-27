<?php
/**
 * PC-Kollektion: Discover-Box mit animierten Produktbild-Karten.
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gk_discover_products = array();
if ( function_exists( 'wc_get_products' ) ) {
	$gk_discover_products = wc_get_products(
		array(
			'status'  => 'publish',
			'limit'   => 8,
			'orderby' => 'date',
			'order'   => 'DESC',
			'return'  => 'objects',
		)
	);
}

$gk_discover_cards = array();
foreach ( $gk_discover_products as $gk_discover_product ) {
	if ( ! $gk_discover_product || ! is_a( $gk_discover_product, 'WC_Product' ) || ! $gk_discover_product->is_visible() ) {
		continue;
	}

	$gk_discover_img = '';
	$gk_discover_id  = (int) $gk_discover_product->get_image_id();
	if ( $gk_discover_id ) {
		$gk_discover_img = wp_get_attachment_image_url( $gk_discover_id, 'large' );
	}
	if ( ! $gk_discover_img && function_exists( 'globalkeys_get_product_hero_image_url' ) ) {
		$gk_discover_img = globalkeys_get_product_hero_image_url( $gk_discover_product, 'large' );
	}
	if ( ! $gk_discover_img && function_exists( 'wc_placeholder_img_src' ) ) {
		$gk_discover_img = wc_placeholder_img_src( 'woocommerce_single' );
	}
	if ( ! $gk_discover_img ) {
		continue;
	}

	$gk_discover_cards[] = array(
		'url'   => $gk_discover_product->get_permalink(),
		'title' => $gk_discover_product->get_name(),
		'img'   => $gk_discover_img,
	);
}

if ( empty( $gk_discover_cards ) ) {
	$gk_discover_cards[] = array(
		'url'   => '#',
		'title' => __( 'Discover product', 'globalkeys' ),
		'img'   => get_template_directory_uri() . '/Pictures/2892.jpg',
	);
}
while ( count( $gk_discover_cards ) < 2 ) {
	$gk_discover_cards[] = $gk_discover_cards[ count( $gk_discover_cards ) % max( 1, count( $gk_discover_cards ) ) ];
}
$gk_discover_cards = array_slice( $gk_discover_cards, 0, 2 );
?>

<section class="gk-section gk-section-platform-pc-discover" aria-label="<?php esc_attr_e( 'Entdeckungsliste', 'globalkeys' ); ?>">
	<div class="gk-section-inner gk-section-featured-inner">
		<div class="gk-platform-pc-discover">
			<div class="gk-platform-pc-discover__content">
				<h2 class="gk-platform-pc-discover__title"><?php esc_html_e( 'Erkunden Sie Ihre Entdeckungsliste', 'globalkeys' ); ?></h2>
				<p class="gk-platform-pc-discover__text"><?php esc_html_e( 'Öffnen Sie Ihre Entdeckungsliste und finden Sie Topseller, Neuerscheinungen und empfohlene Titel', 'globalkeys' ); ?></p>
			</div>
			<div class="gk-platform-pc-discover__cards" aria-hidden="true">
				<?php foreach ( $gk_discover_cards as $gk_discover_index => $gk_discover_card ) : ?>
					<a
						class="gk-platform-pc-discover__card"
						href="<?php echo esc_url( $gk_discover_card['url'] ); ?>"
						style="--gk-discover-image: url('<?php echo esc_url( $gk_discover_card['img'] ); ?>'); --gk-card-index: <?php echo esc_attr( (string) $gk_discover_index ); ?>;"
						tabindex="-1"
					>
						<span class="screen-reader-text"><?php echo esc_html( $gk_discover_card['title'] ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
