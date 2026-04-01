<?php
/**
 * Produktbewertungen für die Section „Reviews“ (Theme-Override).
 *
 * @package globalkeys
 * @version 9.7.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

$reviews_open = comments_open();
$has_reviews  = have_comments();

if ( ! $reviews_open && ! $has_reviews ) {
	echo '<div id="gk-after-hero-reviews" class="gk-product-reviews__scroll-anchor" tabindex="-1"></div>';
	echo '<p class="gk-product-reviews__empty woocommerce-noreviews">';
	esc_html_e( 'There are no reviews yet.', 'woocommerce' );
	echo '</p>';
	return;
}
?>
<div id="reviews" class="woocommerce-Reviews gk-woocommerce-Reviews">
	<div id="gk-after-hero-reviews" class="gk-product-reviews__scroll-anchor" tabindex="-1"></div>

	<div id="comments" class="gk-product-reviews__comments">
		<?php if ( $has_reviews ) : ?>
			<ol class="commentlist gk-product-reviews__list">
				<?php wp_list_comments( apply_filters( 'woocommerce_product_review_list_args', array( 'callback' => 'woocommerce_comments' ) ) ); ?>
			</ol>

			<?php
			if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
				echo '<nav class="woocommerce-pagination gk-product-reviews__pagination" aria-label="' . esc_attr__( 'Reviews pagination', 'globalkeys' ) . '">';
				paginate_comments_links(
					apply_filters(
						'woocommerce_comment_pagination_args',
						array(
							'prev_text' => is_rtl() ? '&rarr;' : '&larr;',
							'next_text' => is_rtl() ? '&larr;' : '&rarr;',
							'type'      => 'list',
						)
					)
				);
				echo '</nav>';
			endif;
			?>
		<?php else : ?>
			<p class="gk-product-reviews__empty woocommerce-noreviews"><?php esc_html_e( 'There are no reviews yet.', 'woocommerce' ); ?></p>
		<?php endif; ?>
	</div>

	<?php if ( $reviews_open ) : ?>
		<?php if ( get_option( 'woocommerce_review_rating_verification_required' ) === 'no' || wc_customer_bought_product( '', get_current_user_id(), $product->get_id() ) ) : ?>
			<div id="gk-review-modal" class="gk-review-modal gk-review-modal--closed" data-gk-review-modal aria-hidden="true">
				<div class="gk-review-modal__backdrop" data-gk-review-modal-close tabindex="-1" aria-hidden="true"></div>
				<div class="gk-review-modal__scroll-inner">
				<div class="gk-review-modal__panel" role="dialog" aria-modal="true" aria-labelledby="gk-review-modal-title" tabindex="-1">
					<button type="button" class="gk-review-modal__close" data-gk-review-modal-close aria-label="<?php esc_attr_e( 'Close', 'globalkeys' ); ?>">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
					</button>
					<header class="gk-review-modal__head">
						<div class="gk-review-modal__avatar"<?php echo is_user_logged_in() ? '' : ' aria-hidden="true"'; ?>>
							<?php
							if ( is_user_logged_in() ) {
								$gk_review_user = wp_get_current_user();
								$gk_avatar_alt  = sprintf(
									/* translators: %s: user display name */
									__( 'Profile photo of %s', 'globalkeys' ),
									$gk_review_user->display_name
								);
								echo wp_kses_post(
									get_avatar(
										$gk_review_user->ID,
										192,
										'',
										$gk_avatar_alt,
										array(
											'class' => 'gk-review-modal__avatar-img',
										)
									)
								);
							} else {
								?>
							<svg class="gk-review-modal__avatar-icon" width="34" height="34" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">
								<circle cx="12" cy="9" r="3.25" stroke="currentColor" stroke-width="1.5"/>
								<path d="M6.5 19.25c0-3.25 2.75-5.5 5.5-5.5s5.5 2.25 5.5 5.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
							</svg>
								<?php
							}
							?>
						</div>
						<h2 id="gk-review-modal-title" class="gk-review-modal__title"><?php esc_html_e( 'Write your review', 'globalkeys' ); ?></h2>
						<p class="gk-review-modal__intro"><?php esc_html_e( 'To help other players decide, share your opinion about this game — what you liked and what you did not.', 'globalkeys' ); ?></p>
					</header>
					<div id="review_form_wrapper" class="gk-product-reviews__form-wrap gk-product-reviews__form-wrap--modal">
						<div id="review_form" class="gk-product-reviews__form-inner">
					<?php
					$commenter    = wp_get_current_commenter();
					$comment_form = array(
						'title_reply'         => '',
						'title_reply_to'      => '',
						'title_reply_before'  => '',
						'title_reply_after'   => '',
						'comment_notes_after' => '',
						'label_submit'        => esc_html__( 'Submit', 'woocommerce' ),
						'logged_in_as'        => '',
						'comment_field'       => '',
						'class_submit'        => 'gk-product-reviews__submit',
					);

					$name_email_required = (bool) get_option( 'require_name_email', 1 );
					$fields              = array(
						'author' => array(
							'label'        => __( 'Name', 'woocommerce' ),
							'type'         => 'text',
							'value'        => $commenter['comment_author'],
							'required'     => $name_email_required,
							'autocomplete' => 'name',
						),
						'email'  => array(
							'label'        => __( 'Email', 'woocommerce' ),
							'type'         => 'email',
							'value'        => $commenter['comment_author_email'],
							'required'     => $name_email_required,
							'autocomplete' => 'email',
						),
					);

					$comment_form['fields'] = array();

					foreach ( $fields as $key => $field ) {
						$field_html  = '<p class="comment-form-' . esc_attr( $key ) . ' gk-product-reviews__field">';
						$field_html .= '<label for="' . esc_attr( $key ) . '">' . esc_html( $field['label'] );

						if ( $field['required'] ) {
							$field_html .= '&nbsp;<span class="required">*</span>';
						}

						$field_html .= '</label><input id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" type="' . esc_attr( $field['type'] ) . '" autocomplete="' . esc_attr( $field['autocomplete'] ) . '" value="' . esc_attr( $field['value'] ) . '" size="30" ' . ( $field['required'] ? 'required' : '' ) . ' /></p>';

						$comment_form['fields'][ $key ] = $field_html;
					}

					$account_page_url = wc_get_page_permalink( 'myaccount' );
					if ( $account_page_url ) {
						$comment_form['must_log_in'] = '<p class="must-log-in gk-product-reviews__must-log-in">' . sprintf( esc_html__( 'You must be %1$slogged in%2$s to post a review.', 'woocommerce' ), '<a href="' . esc_url( $account_page_url ) . '">', '</a>' ) . '</p>';
					}

					$comment_form['comment_field'] = '';
					$gk_wc_review_ratings           = wc_review_ratings_enabled();

					if ( $gk_wc_review_ratings ) {
						ob_start();
						echo '<div class="gk-product-reviews__blocks-wrap">';
						globalkeys_product_review_print_5_star_rating();

						echo '<hr class="gk-review-stars__divider" role="presentation" />';
						echo '<p class="gk-review-opinion-heading" id="gk-review-opinion-heading">' . esc_html__( 'Your Opinion about this Game', 'globalkeys' ) . '</p>';

						echo '<div class="gk-product-reviews__rating-field gk-product-reviews__rating-field--woo-sync screen-reader-text">';
						echo '<label for="rating">' . esc_html__( 'Shop-Bewertung (1–5)', 'globalkeys' ) . '</label>';
						echo '<select name="rating" id="rating" required>';
						echo '<option value="">' . esc_html__( 'Bewertung wählen', 'globalkeys' ) . '</option>';
						for ( $sv = 5; $sv >= 1; $sv-- ) {
							printf( '<option value="%1$d">%1$d</option>', $sv );
						}
						echo '</select></div>';

						echo '</div>';
						$comment_form['comment_field'] .= ob_get_clean();
					}

					$comment_form['comment_field'] .= '<p class="comment-form-comment gk-product-reviews__field"><textarea id="comment" name="comment" cols="45" rows="8" required';
					if ( $gk_wc_review_ratings ) {
						$comment_form['comment_field'] .= ' aria-labelledby="gk-review-opinion-heading"';
					} else {
						$comment_form['comment_field'] .= ' aria-label="' . esc_attr__( 'Deine Bewertung', 'globalkeys' ) . '"';
					}
					$comment_form['comment_field'] .= '></textarea></p>';

					$comment_form['comment_field'] .= '<div class="gk-review-pro-con" aria-label="' . esc_attr__( 'Good and Bad (optional)', 'globalkeys' ) . '">';
					$comment_form['comment_field'] .= '<div class="gk-review-pro-con__col"><span class="gk-review-pro-con__heading">' . esc_html__( 'Good', 'globalkeys' ) . ' <span class="gk-review-pro-con__optional">(' . esc_html__( 'optional', 'globalkeys' ) . ')</span></span>';
					for ( $gk_pc = 0; $gk_pc < 3; $gk_pc++ ) {
						$comment_form['comment_field'] .= '<input type="text" class="gk-review-pro-con__line" name="gk_review_pro[]" maxlength="220" autocomplete="off" />';
					}
					$comment_form['comment_field'] .= '</div><div class="gk-review-pro-con__col"><span class="gk-review-pro-con__heading">' . esc_html__( 'Bad', 'globalkeys' ) . ' <span class="gk-review-pro-con__optional">(' . esc_html__( 'optional', 'globalkeys' ) . ')</span></span>';
					for ( $gk_pc = 0; $gk_pc < 3; $gk_pc++ ) {
						$comment_form['comment_field'] .= '<input type="text" class="gk-review-pro-con__line" name="gk_review_con[]" maxlength="220" autocomplete="off" />';
					}
					$comment_form['comment_field'] .= '</div></div>';

					$comment_form['label_submit'] = esc_html__( 'Submit my review', 'globalkeys' );

					comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ) );
					?>
						</div>
					</div>
				</div>
				</div>
			</div>
		<?php else : ?>
			<p class="woocommerce-verification-required gk-product-reviews__verification"><?php esc_html_e( 'Only logged in customers who have purchased this product may leave a review.', 'woocommerce' ); ?></p>
		<?php endif; ?>
	<?php elseif ( $has_reviews ) : ?>
		<p class="gk-product-reviews__closed-note"><?php esc_html_e( 'New reviews cannot be submitted at this time.', 'globalkeys' ); ?></p>
	<?php endif; ?>

	<div class="clear"></div>
</div>
