<?php
/**
 * Öffentliche Wishlist-Seite (Inhalt).
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'gk-wishlist-article' ); ?>>

	<div class="gk-wishlist-outer">
		<div class="gk-wishlist gk-section-inner gk-section-featured-inner">

			<?php if ( is_user_logged_in() ) : ?>
				<?php
				$gk_wishlist_uid      = get_current_user_id();
				$gk_wishlist_avatar   = function_exists( 'globalkeys_get_user_avatar_url' )
					? globalkeys_get_user_avatar_url( $gk_wishlist_uid, 256 )
					: get_avatar_url( $gk_wishlist_uid, array( 'size' => 256 ) );
				$gk_wishlist_gamertag = function_exists( 'globalkeys_get_user_gamertag_for_display' )
					? globalkeys_get_user_gamertag_for_display( $gk_wishlist_uid )
					: '';
				if ( '' === $gk_wishlist_gamertag ) {
					$gk_wishlist_gamertag = __( 'Player', 'globalkeys' );
				}
				?>
				<div class="gk-wishlist__top">
					<header class="gk-wishlist__bar">
						<div class="gk-wishlist__bar-inner">
							<div class="gk-wishlist__avatar" aria-hidden="true">
								<?php if ( $gk_wishlist_avatar ) : ?>
									<img
										class="gk-wishlist__avatar-img"
										src="<?php echo esc_url( $gk_wishlist_avatar ); ?>"
										alt=""
										width="68"
										height="68"
										loading="lazy"
										decoding="async"
									/>
								<?php endif; ?>
							</div>
							<h1 class="gk-wishlist__title">
								<span class="gk-wishlist__title-label"><?php esc_html_e( 'Wunschliste von', 'globalkeys' ); ?> </span>
								<span class="gk-wishlist__title-name"><?php echo esc_html( $gk_wishlist_gamertag ); ?></span>
							</h1>
						</div>
					</header>

					<?php get_template_part( 'template-parts/wishlist', 'toolbar' ); ?>
				</div>
			<?php else : ?>
				<?php
				$gk_wishlist_login = class_exists( 'WooCommerce' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
				?>
				<div class="gk-wishlist__top">
					<header class="gk-wishlist__bar gk-wishlist__bar--guest">
						<div class="gk-wishlist__bar-inner">
							<div class="gk-wishlist__avatar gk-wishlist__avatar--guest" aria-hidden="true">
								<span class="gk-wishlist__avatar-placeholder"></span>
							</div>
							<div class="gk-wishlist__guest-copy">
								<h1 class="gk-wishlist__title gk-wishlist__title--guest"><?php esc_html_e( 'Deine Wunschliste', 'globalkeys' ); ?></h1>
								<p class="gk-wishlist__guest-hint">
									<a class="gk-wishlist__guest-link" href="<?php echo esc_url( $gk_wishlist_login ); ?>"><?php esc_html_e( 'Anmelden', 'globalkeys' ); ?></a>
									<?php esc_html_e( ', um deine Wunschliste zu sehen und zu bearbeiten.', 'globalkeys' ); ?>
								</p>
							</div>
						</div>
					</header>
					<?php get_template_part( 'template-parts/wishlist', 'toolbar' ); ?>
				</div>
			<?php endif; ?>

			<div class="gk-wishlist__products" id="gk-wishlist-products">
				<?php
				if ( is_user_logged_in() && function_exists( 'globalkeys_wishlist_print_products_markup' ) ) {
					globalkeys_wishlist_print_products_markup( get_current_user_id() );
				}
				?>
			</div>
			<p id="gk-wishlist-search-no-results" class="gk-wishlist__search-empty" hidden><?php esc_html_e( 'Keine Treffer für deine Suche.', 'globalkeys' ); ?></p>

			<?php if ( get_edit_post_link() ) : ?>
				<footer class="entry-footer gk-wishlist__edit-footer">
					<?php
					edit_post_link(
						sprintf(
							wp_kses(
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
				</footer>
			<?php endif; ?>

		</div>
	</div>

</article>
