<?php
/**
 * Bewertungskarte: Kopf (Avatar + Sterne), Text & Smileys, Fuß (Datum + Useful).
 *
 * @package globalkeys
 * @version 9.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $comment;
?>
<li <?php comment_class( 'gk-product-review-card__item' ); ?> id="li-comment-<?php comment_ID(); ?>">

	<div id="comment-<?php comment_ID(); ?>" class="comment_container gk-product-review-card">

		<div class="gk-product-review-card__header">
			<div class="gk-product-review-card__identity">
				<?php
				/**
				 * Avatar.
				 *
				 * @hooked woocommerce_review_display_gravatar - 10
				 */
				do_action( 'woocommerce_review_before', $comment );
				wc_get_template( 'single-product/review-rating.php' );
				?>
			</div>
			<?php
			if ( '0' !== $comment->comment_approved && function_exists( 'globalkeys_product_review_report_menu_markup' ) ) {
				globalkeys_product_review_report_menu_markup( $comment );
			}
			?>
		</div>

		<div class="gk-product-review-card__body">
			<?php if ( '0' === $comment->comment_approved ) : ?>
				<p class="gk-product-review-card__pending meta">
					<em><?php esc_html_e( 'Your review is awaiting approval', 'woocommerce' ); ?></em>
				</p>
			<?php endif; ?>

			<?php do_action( 'woocommerce_review_before_comment_text', $comment ); ?>

			<?php
			/**
			 * Bewertungstext.
			 *
			 * @hooked woocommerce_review_display_comment_text - 10
			 */
			do_action( 'woocommerce_review_comment_text', $comment );
			?>

			<?php do_action( 'woocommerce_review_after_comment_text', $comment ); ?>
		</div>

		<?php if ( '0' !== $comment->comment_approved ) : ?>
		<div class="gk-product-review-card__footer">
			<time class="gk-product-review-card__date woocommerce-review__published-date" datetime="<?php echo esc_attr( get_comment_date( 'c' ) ); ?>">
				<?php echo esc_html( get_comment_date( wc_date_format() ) ); ?>
			</time>
			<?php
			if ( function_exists( 'globalkeys_product_review_helpful_markup' ) ) {
				globalkeys_product_review_helpful_markup( $comment );
			}
			?>
		</div>
		<?php endif; ?>

	</div>
