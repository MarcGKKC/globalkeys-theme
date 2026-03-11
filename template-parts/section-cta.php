<?php
/**
 * Template part for CTA (Call-to-Action) Section
 *
 * @package globalkeys
 */

$section   = get_query_var( 'gk_section', array( 'id' => 'section-cta', 'aria_label' => __( 'Handlungsaufforderung', 'globalkeys' ) ) );
$id        = ! empty( $section['id'] ) ? $section['id'] : 'section-cta';
$aria_label = ! empty( $section['aria_label'] ) ? $section['aria_label'] : __( 'Call to Action', 'globalkeys' );
?>

<section id="<?php echo esc_attr( $id ); ?>" class="gk-section gk-section-cta" role="region" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
	<div class="gk-section-inner">
		<h2 id="<?php echo esc_attr( $id ); ?>-title" class="gk-section-title"><?php esc_html_e( 'Call to Action', 'globalkeys' ); ?></h2>
		<p class="gk-section-text"><?php esc_html_e( 'Bereich für einen Button oder Handlungsaufforderung.', 'globalkeys' ); ?></p>
	</div>
</section>
