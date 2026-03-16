<?php
/**
 * Template für Plattform-Seiten (PC, PlayStation, Xbox, Nintendo).
 * Zeigt Header + leeren Hauptbereich (nur Hintergrund).
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gk_platform_slug = get_query_var( 'gk_platform' );
if ( ! is_string( $gk_platform_slug ) || $gk_platform_slug === '' ) {
	$gk_platform_slug = '';
}

get_header();
?>

	<main id="primary" class="site-main site-main--platform gk-platform-page" data-platform="<?php echo esc_attr( $gk_platform_slug ); ?>">

		<!-- Erstmal leer: nur Hintergrund, Inhalt kommt später -->

	</main><!-- #primary -->

<?php
get_footer();
