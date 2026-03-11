<?php
/**
 * Template part for Intro Section
 *
 * @package globalkeys
 */

$section   = get_query_var( 'gk_section', array( 'id' => 'section-intro', 'aria_label' => __( 'Einführung', 'globalkeys' ) ) );
$id        = ! empty( $section['id'] ) ? $section['id'] : 'section-intro';
$aria_label = ! empty( $section['aria_label'] ) ? $section['aria_label'] : __( 'Intro', 'globalkeys' );
?>

<section id="<?php echo esc_attr( $id ); ?>" class="gk-section gk-section-intro" role="region" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
	<div class="gk-section-inner">
		<h2 id="<?php echo esc_attr( $id ); ?>-title" class="gk-section-title"><?php esc_html_e( 'Intro Section', 'globalkeys' ); ?></h2>
		<p class="gk-section-text"><?php esc_html_e( 'Kurzer Textblock für Vorstellung oder Beschreibung.', 'globalkeys' ); ?></p>
	</div>
</section>
