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
