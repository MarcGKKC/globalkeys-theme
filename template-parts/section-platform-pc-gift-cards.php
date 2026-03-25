<?php
/**
 * PC-Kollektion: Gift Cards – gleiche Anordnung/Typo wie .gk-platform-header (ohne gk-platform-nav-below).
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gk_pc_gift_pills = array(
	array(
		'label'  => __( 'All gift cards', 'globalkeys' ),
		'url'    => '#',
		'active' => true,
	),
	array(
		'label' => __( 'Steam', 'globalkeys' ),
		'url'   => '#',
	),
	array(
		'label' => __( 'FC Points', 'globalkeys' ),
		'url'   => '#',
	),
	array(
		'label' => __( 'Valorant Points', 'globalkeys' ),
		'url'   => '#',
	),
	array(
		'label' => __( 'Roblox', 'globalkeys' ),
		'url'   => '#',
	),
);
?>

<section class="gk-section gk-section-pc-gift-cards" role="region" aria-labelledby="gk-pc-gift-cards-title">
	<div class="gk-platform-header gk-pc-gift-cards__header">
		<h2 id="gk-pc-gift-cards-title" class="gk-platform-title"><?php esc_html_e( 'PC gift cards', 'globalkeys' ); ?></h2>
		<p class="gk-platform-desc"><?php esc_html_e( 'Our Steam gift cards, FC Points, Valorant Points, Roblox and many more!', 'globalkeys' ); ?></p>
		<nav class="gk-platform-stores-bar" aria-label="<?php esc_attr_e( 'Gift card categories', 'globalkeys' ); ?>">
			<?php foreach ( $gk_pc_gift_pills as $gk_pill ) : ?>
				<?php
				$gk_is_active = ! empty( $gk_pill['active'] );
				$gk_link_class = $gk_is_active ? 'active' : '';
				?>
				<a href="<?php echo esc_url( $gk_pill['url'] ); ?>"<?php echo $gk_link_class !== '' ? ' class="' . esc_attr( $gk_link_class ) . '"' : ''; ?><?php echo $gk_is_active ? ' aria-current="true"' : ''; ?>><?php echo esc_html( $gk_pill['label'] ); ?></a>
			<?php endforeach; ?>
		</nav>
	</div>
</section>
