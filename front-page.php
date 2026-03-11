<?php
/**
 * The front page template file
 *
 * Wird angezeigt, wenn unter Einstellungen > Lesen eine statische Seite
 * als Startseite gewählt wurde.
 *
 * Sections: Hero, Intro, CTA – weitere mit get_template_part() hinzufügen.
 *
 * @package globalkeys
 */

get_header();
?>

	<main id="primary" class="site-main site-main--front" role="main">

		<?php foreach ( globalkeys_get_front_page_sections() as $gk_section ) : ?>
			<?php
			set_query_var( 'gk_section', $gk_section );
			get_template_part( 'template-parts/section', $gk_section['slug'] );
			?>
		<?php endforeach; ?>

	</main><!-- #primary -->

<?php
get_footer();
