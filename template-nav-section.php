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

		<!-- Erstmal leer: nur Hintergrund, Inhalt kommt später -->

	</main><!-- #primary -->

<?php
get_footer();
