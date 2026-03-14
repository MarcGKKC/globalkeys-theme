<?php
/**
 * Template part for Hero Section
 *
 * @package globalkeys
 */

$section = get_query_var( 'gk_section', array( 'id' => 'section-hero', 'aria_label' => __( 'Willkommensbereich', 'globalkeys' ) ) );
$id        = ! empty( $section['id'] ) ? $section['id'] : 'section-hero';
$aria_label = ! empty( $section['aria_label'] ) ? $section['aria_label'] : __( 'Hero', 'globalkeys' );
?>

<?php $hero_bg = get_template_directory_uri() . '/Pictures/testbild-gk.jpg'; ?>
<section id="<?php echo esc_attr( $id ); ?>" class="gk-section gk-section-hero has-hero-image" role="region" aria-label="<?php echo esc_attr( $aria_label ); ?>" style="background-image: url('<?php echo esc_url( $hero_bg ); ?>');">
	<div class="gk-section-inner"></div>
</section>
<?php
$gk_hero_stats = array(
	array( 'number' => 12500,   'label' => __( 'Aktive Gamer auf der Website', 'globalkeys' ) ),
	array( 'number' => 1840,    'label' => __( 'Erstellte Gamer Kontos', 'globalkeys' ) ),
	array( 'number' => 2100000, 'label' => __( 'Zufriedene Kunden', 'globalkeys' ) ),
	array( 'number' => 47,      'label' => __( 'Aktive Partnerschaften', 'globalkeys' ) ),
	array( 'number' => 3200,    'label' => __( 'Spiele auf Lager', 'globalkeys' ) ),
);
?>
<div class="gk-hero-stats-bar" role="region" aria-label="<?php esc_attr_e( 'Statistiken', 'globalkeys' ); ?>">
	<?php foreach ( $gk_hero_stats as $i => $stat ) : ?>
		<?php if ( $i > 0 ) : ?><span class="gk-hero-stat-divider" aria-hidden="true"></span><?php endif; ?>
		<div class="gk-hero-stat">
			<span class="gk-hero-stat-number"><?php echo esc_html( globalkeys_format_stat_number( $stat['number'] ) ); ?></span>
			<span class="gk-hero-stat-label"><?php echo esc_html( $stat['label'] ); ?></span>
		</div>
	<?php endforeach; ?>
</div>
