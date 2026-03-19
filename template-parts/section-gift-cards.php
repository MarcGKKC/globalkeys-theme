<?php
/**
 * Template part: Gift Cards Section
 * 4 Kästen mit Titel darunter, ähnlich Kategorien, erstmal ohne Bild.
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section    = get_query_var( 'gk_section', array( 'id' => 'section-gift-cards', 'aria_label' => __( 'Gift Cards', 'globalkeys' ) ) );
$id         = ! empty( $section['id'] ) ? $section['id'] : 'section-gift-cards';
$aria_label = ! empty( $section['aria_label'] ) ? $section['aria_label'] : __( 'Gift Cards', 'globalkeys' );

$gk_gift_cards = array(
	array( 'title' => __( 'Steam', 'globalkeys' ), 'image' => 'export (1).svg' ),
	array( 'title' => __( 'PlayStation', 'globalkeys' ), 'image' => 'Playstation-logo-1.avif' ),
	array( 'title' => __( 'Xbox', 'globalkeys' ), 'image' => 'xboxguth.png' ),
	array( 'title' => __( 'Nintendo', 'globalkeys' ), 'image' => 'Image 4.png', 'zoom' => 1.10 ),
);
$gk_pictures_uri = get_template_directory_uri() . '/Pictures/';
?>

<section id="<?php echo esc_attr( $id ); ?>" class="gk-section gk-section-gift-cards" role="region" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
	<div class="gk-section-inner gk-section-gift-cards-inner">
		<div class="gk-featured-heading-wrap">
			<h2 id="<?php echo esc_attr( $id ); ?>-title" class="gk-section-title gk-featured-heading">
				<span class="gk-featured-heading-text-wrap">
					<span class="gk-featured-heading-text"><?php esc_html_e( 'Gift Cards', 'globalkeys' ); ?></span>
					<span class="gk-featured-title-underline" aria-hidden="true"></span>
				</span>
				<span class="gk-featured-heading-arrow" aria-hidden="true"></span>
			</h2>
		</div>
	</div>
	<div class="gk-gift-cards-wrapper">
		<div class="gk-gift-cards-row">
			<?php foreach ( $gk_gift_cards as $card ) :
				$img_url   = ! empty( $card['image'] ) ? $gk_pictures_uri . rawurlencode( $card['image'] ) : '';
				$bg_size   = ! empty( $card['zoom'] ) ? ( (float) $card['zoom'] * 100 ) . '%' : 'cover';
				$box_style = $img_url ? 'background-image: url(\'' . esc_url( $img_url ) . '\'); background-size: ' . esc_attr( $bg_size ) . '; background-position: center; background-repeat: no-repeat;' : '';
			?>
			<div class="gk-gift-card">
				<div class="gk-gift-card-box"<?php echo $box_style ? ' style="' . esc_attr( $box_style ) . '"' : ''; ?>></div>
				<span class="gk-gift-card-title"><?php echo esc_html( $card['title'] ); ?></span>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
