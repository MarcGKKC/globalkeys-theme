<?php
/**
 * Sterne der Bewertung (Theme-Sterne wie im Modal, kein WooCommerce-Star-Font).
 *
 * @package globalkeys
 * @version 9.7.0
 */

defined( 'ABSPATH' ) || exit;

global $comment;

if ( ! wc_review_ratings_enabled() ) {
	return;
}

$rating = function_exists( 'globalkeys_get_review_star_rating_for_comment' )
	? globalkeys_get_review_star_rating_for_comment( $comment )
	: (int) get_comment_meta( $comment->comment_ID, 'rating', true );

echo globalkeys_get_product_review_stars_display_html( $rating ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
