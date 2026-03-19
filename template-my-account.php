<?php
/**
 * Template für My Account (Dashboard, Orders, etc.) – gleiche Struktur wie Plattform/Kollektion.
 * Gleicher DOM-Aufbau = Header-Scroll funktioniert normal.
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

	<main id="primary" class="site-main site-main--account">

		<?php echo do_shortcode( '[woocommerce_my_account]' ); ?>

	</main><!-- #primary -->

<?php
get_footer();
