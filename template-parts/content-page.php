<?php
/**
 * Template part for displaying page content in page.php
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package globalkeys
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php if ( function_exists( 'is_cart' ) && is_cart() ) : ?>
	<div class="gk-cart-stage-outer">
		<div class="gk-cart-stage gk-section-inner gk-section-featured-inner">
			<div class="gk-cart-page-split">
				<div class="gk-cart-page-split__products">
					<?php $gk_cart_continue_url = globalkeys_get_browse_all_games_url(); ?>
					<div class="gk-cart-products-head">
						<div class="gk-cart-products-head__row">
							<a class="gk-cart-products-head__link" href="<?php echo esc_url( $gk_cart_continue_url ); ?>">
								<span class="gk-cart-products-head__chevron" aria-hidden="true">
									<svg width="10" height="16" viewBox="0 0 10 16" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">
										<path d="M8.5 1L2 8l6.5 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
									</svg>
								</span>
								<span class="gk-cart-products-head__text"><?php esc_html_e( 'Continue shopping', 'globalkeys' ); ?></span>
							</a>
						</div>
					</div>
					<header class="entry-header">
						<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
					</header>
					<?php
					if ( function_exists( 'globalkeys_render_cart_custom_cards_before_table' ) ) {
						globalkeys_render_cart_custom_cards_before_table();
					}
					?>
					<div class="entry-content">
						<?php
						the_content();

						wp_link_pages(
							array(
								'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'globalkeys' ),
								'after'  => '</div>',
							)
						);
						?>
					</div>
				</div>
				<div class="gk-cart-page-split__summary">
					<?php get_template_part( 'template-parts/cart', 'stage' ); ?>
				</div>
			</div>
		</div>
	</div>
	<?php else : ?>
	<header class="entry-header">
		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
	</header>

	<?php globalkeys_post_thumbnail(); ?>

	<div class="entry-content">
		<?php
		the_content();

		wp_link_pages(
			array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'globalkeys' ),
				'after'  => '</div>',
			)
		);
		?>
	</div>
	<?php endif; ?>

	<?php if ( get_edit_post_link() ) : ?>
		<footer class="entry-footer">
			<?php
			edit_post_link(
				sprintf(
					wp_kses(
						/* translators: %s: Name of current post. Only visible to screen readers */
						__( 'Edit <span class="screen-reader-text">%s</span>', 'globalkeys' ),
						array(
							'span' => array(
								'class' => array(),
							),
						)
					),
					wp_kses_post( get_the_title() )
				),
				'<span class="edit-link">',
				'</span>'
			);
			?>
		</footer><!-- .entry-footer -->
	<?php endif; ?>
</article><!-- #post-<?php the_ID(); ?> -->
