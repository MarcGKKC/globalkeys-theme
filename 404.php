<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package globalkeys
 */

get_header();
?>

	<main id="primary" class="site-main site-main--404-empty">

		<section class="error-404 not-found" aria-label="<?php esc_attr_e( 'Error 404', 'globalkeys' ); ?>">
			<h1 class="screen-reader-text"><?php esc_html_e( 'Page not found', 'globalkeys' ); ?></h1>
		</section>

	</main>

<?php
get_footer();
