<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package globalkeys
 */

get_header();
?>

	<main id="primary" class="site-main">

		<?php if ( is_front_page() && is_home() ) : ?>
		<div class="gk-test-banner" style="background: #e8f4f8; padding: 0.5rem 1rem; margin-bottom: 1rem; border-left: 4px solid #0073aa;">
			✨ Test: Du siehst diese Box auf der Homepage – funktioniert!
		</div>
		<div class="gk-test-2" style="background: #e8f8e8; padding: 0.5rem 1rem; margin-bottom: 1rem; border-left: 4px solid #46b450;">
			🚀 Mini-Test 2: Auch diese zweite Box wird angezeigt!
		</div>
		<?php endif; ?>

		<?php
		if ( have_posts() ) :

			if ( is_home() && ! is_front_page() ) :
				?>
				<header>
					<h1 class="page-title screen-reader-text"><?php single_post_title(); ?></h1>
				</header>
				<?php
			endif;

			/* Start the Loop */
			while ( have_posts() ) :
				the_post();

				/*
				 * Include the Post-Type-specific template for the content.
				 * If you want to override this in a child theme, then include a file
				 * called content-___.php (where ___ is the Post Type name) and that will be used instead.
				 */
				get_template_part( 'template-parts/content', get_post_type() );

			endwhile;

			the_posts_navigation();

		else :

			get_template_part( 'template-parts/content', 'none' );

		endif;
		?>

	</main><!-- #main -->

<?php
get_sidebar();
get_footer();
