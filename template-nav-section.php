<?php
/**
 * Template für Nav-Punkte über der Pill (Trending Games, Preorders, Available Soon, Activation, Support).
 * Zeigt Header + leeren Hauptbereich (nur Hintergrund).
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gk_nav_slug = get_query_var( 'gk_nav_section' );
if ( ! is_string( $gk_nav_slug ) || $gk_nav_slug === '' ) {
	$gk_nav_slug = '';
}

get_header();
?>

	<main id="primary" class="site-main site-main--nav-section gk-nav-section-page" data-nav-section="<?php echo esc_attr( $gk_nav_slug ); ?>">

		<?php if ( 'support' === $gk_nav_slug ) : ?>
			<div class="gk-nav-section-inner gk-nav-section-inner--support">
				<h1 class="screen-reader-text"><?php esc_html_e( '24/7 Support', 'globalkeys' ); ?></h1>
				<?php get_template_part( 'template-parts/support', 'landing' ); ?>
			</div>
			<?php get_template_part( 'template-parts/support', 'guides' ); ?>
		<?php else : ?>
			<!-- Erstmal leer: nur Hintergrund, Inhalt kommt später -->
		<?php endif; ?>

	</main><!-- #primary -->

<?php
get_footer();
