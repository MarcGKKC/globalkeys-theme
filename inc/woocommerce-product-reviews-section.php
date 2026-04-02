<?php
/**
 * Produktseite: Section „Reviews“ (WooCommerce-Produktbewertungen).
 *
 * @package globalkeys
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'GLOBALKEYS_REVIEW_HELPFUL_COOKIE' ) ) {
	define( 'GLOBALKEYS_REVIEW_HELPFUL_COOKIE', 'gk_pr_votes' );
}
if ( ! defined( 'GLOBALKEYS_REVIEW_HELPFUL_USER_META' ) ) {
	define( 'GLOBALKEYS_REVIEW_HELPFUL_USER_META', 'gk_product_review_votes' );
}

/**
 * Testphase: Bewertungen ohne Kauf erlauben (entspricht WooCommerce „Nein“ bei „Bewertungen nur von „Verifizierten Besitzern“).
 *
 * Live-Umgebung: in wp-config.php vor require wp-settings.php z. B.:
 * define( 'GLOBALKEYS_ALLOW_PRODUCT_REVIEWS_WITHOUT_PURCHASE', false );
 */
if ( ! defined( 'GLOBALKEYS_ALLOW_PRODUCT_REVIEWS_WITHOUT_PURCHASE' ) ) {
	define( 'GLOBALKEYS_ALLOW_PRODUCT_REVIEWS_WITHOUT_PURCHASE', true );
}

/**
 * @param mixed $pre Short-circuit-Wert (false = Option normal aus der DB lesen).
 * @return mixed
 */
function globalkeys_pre_option_review_rating_verification_for_testing( $pre ) {
	if ( defined( 'GLOBALKEYS_ALLOW_PRODUCT_REVIEWS_WITHOUT_PURCHASE' ) && GLOBALKEYS_ALLOW_PRODUCT_REVIEWS_WITHOUT_PURCHASE ) {
		return 'no';
	}
	return $pre;
}

add_filter( 'pre_option_woocommerce_review_rating_verification_required', 'globalkeys_pre_option_review_rating_verification_for_testing', 10, 1 );

/**
 * Reviews-Tab ausblenden, damit Bewertungen nur in dieser Section erscheinen.
 *
 * @param array<string, array<string, mixed>> $tabs Register tabs.
 * @return array<string, array<string, mixed>>
 */
function globalkeys_single_product_remove_reviews_tab( $tabs ) {
	if ( ! function_exists( 'is_product' ) || ! is_product() || ! function_exists( 'globalkeys_single_product_is_purchase_card_active' ) || ! globalkeys_single_product_is_purchase_card_active() ) {
		return $tabs;
	}
	unset( $tabs['reviews'] );
	return $tabs;
}

add_filter( 'woocommerce_product_tabs', 'globalkeys_single_product_remove_reviews_tab', 99 );

/**
 * Größeres Profilbild in der Review-Karte (get_avatar-Quelle, damit die Darstellung scharf bleibt).
 *
 * @return int
 */
function globalkeys_woocommerce_review_gravatar_size() {
	return 180;
}

add_filter( 'woocommerce_review_gravatar_size', 'globalkeys_woocommerce_review_gravatar_size', 10 );

/**
 * Modal-Formular / Pflichtfelder: Inline-Footer-Skript `gk-review-modal-validate` (nicht externe JS-Datei, damit Optimierer nichts überschreiben).
 */

/**
 * Submit-Button: festes Markup mit data-gk-review-submit (JS findet den Button zuverlässig; Filter läuft nach anderen Erweiterungen).
 *
 * @param array<string, mixed> $args Kommentarformular-Args.
 * @return array<string, mixed>
 */
function globalkeys_product_review_comment_form_submit_markup( $args ) {
	if ( ! is_array( $args ) ) {
		return $args;
	}
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return $args;
	}
	if ( ! function_exists( 'globalkeys_single_product_is_purchase_card_active' ) || ! globalkeys_single_product_is_purchase_card_active() ) {
		return $args;
	}
	$args['submit_button'] = '<input name="%1$s" type="submit" id="%2$s" class="%3$s" value="%4$s" data-gk-review-submit="1" disabled="disabled" aria-disabled="true" />';
	return $args;
}

add_filter( 'woocommerce_product_review_comment_form_args', 'globalkeys_product_review_comment_form_submit_markup', 999 );

/**
 * Optional: Zusatz-Kategorien (Filter für Erweiterungen; Standard-Formular nutzt sie nicht).
 *
 * @return array<string, string>
 */
function globalkeys_review_extra_rating_categories() {
	return apply_filters(
		'gk_review_extra_rating_categories',
		array(
			'graphics' => __( 'Grafik', 'globalkeys' ),
			'fun'      => __( 'Spielspaß', 'globalkeys' ),
			'story'    => __( 'Story', 'globalkeys' ),
		)
	);
}

/**
 * 5-Sterne-Bewertung (ohne Überschrift; synchron mit WooCommerce #rating).
 */
function globalkeys_product_review_print_5_star_rating() {
	$aria_group = __( 'Sternbewertung', 'globalkeys' );
	?>
	<div class="gk-review-stars" data-gk-review-stars data-required="1" onmouseleave="if(window.gkStarHoverOut){window.gkStarHoverOut(this);}">
		<div class="gk-review-stars__row" role="group" aria-label="<?php echo esc_attr( $aria_group ); ?>">
			<?php
			for ( $i = 1; $i <= 5; $i++ ) {
				printf(
					'<button type="button" class="gk-review-stars__star" data-value="%1$d" aria-pressed="false" aria-label="%2$s" onclick="if(window.gkStarPick){window.gkStarPick(this);}" onmouseenter="if(window.gkStarHoverIn){window.gkStarHoverIn(this);}"><span class="gk-review-stars__glyph" aria-hidden="true" style="pointer-events:none">★</span></button>',
					(int) $i,
					esc_attr(
						sprintf(
							/* translators: %d: rating 1–5 */
							__( '%d von 5 Sternen', 'globalkeys' ),
							$i
						)
					)
				);
			}
			?>
		</div>
		<input type="hidden" name="gk_rating_general" id="gk_rating_general" value="" autocomplete="off" required />
	</div>
	<?php
}

/**
 * Zusatzfelder der Produktbewertung in Comment-Meta speichern.
 *
 * @param int        $comment_id ID des Kommentars.
 * @param int|string $approved   Genehmigungsstatus.
 * @param array      $commentdata Kommentardaten.
 */
function globalkeys_product_review_save_extended_meta( $comment_id, $approved, $commentdata ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce-Kommentarformular
	if ( empty( $_POST['comment_post_ID'] ) ) {
		return;
	}
	$post_id = (int) $_POST['comment_post_ID'];
	if ( $post_id <= 0 || get_post_type( $post_id ) !== 'product' ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$general = isset( $_POST['gk_rating_general'] ) ? absint( wp_unslash( $_POST['gk_rating_general'] ) ) : 0;
	if ( $general >= 1 && $general <= 5 ) {
		update_comment_meta( $comment_id, 'gk_rating_general', $general );
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$pros = isset( $_POST['gk_review_pro'] ) ? array_map( 'sanitize_text_field', wp_unslash( (array) $_POST['gk_review_pro'] ) ) : array();
	$pros = array_filter( $pros );
	update_comment_meta( $comment_id, 'gk_review_pros', implode( "\n", $pros ) );

	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$cons = isset( $_POST['gk_review_con'] ) ? array_map( 'sanitize_text_field', wp_unslash( (array) $_POST['gk_review_con'] ) ) : array();
	$cons = array_filter( $cons );
	update_comment_meta( $comment_id, 'gk_review_cons', implode( "\n", $cons ) );
}

add_action( 'comment_post', 'globalkeys_product_review_save_extended_meta', 25, 3 );

/**
 * Sterne nur neben dem Avatar (review.php); nicht noch einmal über dem Meta-Block.
 */
function globalkeys_product_reviews_remove_inline_star_hook() {
	remove_action( 'woocommerce_review_before_comment_meta', 'woocommerce_review_display_rating', 10 );
}

add_action( 'woocommerce_init', 'globalkeys_product_reviews_remove_inline_star_hook' );

/**
 * Sternzeile für die öffentliche Bewertungsanzeige (wie Modal, ohne WooCommerce-Star-Font).
 *
 * @param int $rating 1–5.
 * @return string HTML.
 */
function globalkeys_get_product_review_stars_display_html( $rating ) {
	$rating = min( 5, max( 0, absint( $rating ) ) );
	if ( $rating >= 1 ) {
		$label = sprintf(
			/* translators: %d: star count 1–5 */
			__( '%d out of 5 stars', 'globalkeys' ),
			$rating
		);
	} else {
		$label = __( 'No star rating', 'globalkeys' );
	}
	$html = '<div class="gk-review-display-stars" role="img" aria-label="' . esc_attr( $label ) . '">';
	for ( $i = 1; $i <= 5; $i++ ) {
		$class = ( $rating >= 1 && $i <= $rating ) ? 'gk-review-display-stars__star is-lit' : 'gk-review-display-stars__star';
		$html .= '<span class="' . esc_attr( $class ) . '" aria-hidden="true">★</span>';
	}
	$html .= '</div>';
	return $html;
}

/**
 * @return string Statisches SVG (Smiley „Gut“).
 */
function globalkeys_get_review_pro_smiley_svg() {
	return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="gk-review-pros-cons-display__face gk-review-pros-cons-display__face--pro" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="1.65"/><circle cx="8.25" cy="9.5" r="1.15" fill="currentColor"/><circle cx="15.75" cy="9.5" r="1.15" fill="currentColor"/><path d="M8.2 14.6c1.15 1.85 2.65 3 3.8 3s2.65-1.15 3.8-3" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round"/></svg>';
}

/**
 * @return string Statisches SVG (Smiley „Schlecht“).
 */
function globalkeys_get_review_con_smiley_svg() {
	return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="gk-review-pros-cons-display__face gk-review-pros-cons-display__face--con" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="1.65"/><circle cx="8.25" cy="9.25" r="1.15" fill="currentColor"/><circle cx="15.75" cy="9.25" r="1.15" fill="currentColor"/><path d="M8.2 17.2c1.15-1.85 2.65-3 3.8-3s2.65 1.15 3.8 3" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round"/></svg>';
}

/**
 * Pro-/Contra-Zeilen aus Comment-Meta mit Smileys ausgeben.
 *
 * @param WP_Comment $comment Kommentar.
 */
function globalkeys_product_review_output_pros_cons( $comment ) {
	if ( ! is_object( $comment ) || empty( $comment->comment_ID ) ) {
		return;
	}
	$pid      = (int) $comment->comment_ID;
	$pros_raw = get_comment_meta( $pid, 'gk_review_pros', true );
	$cons_raw = get_comment_meta( $pid, 'gk_review_cons', true );
	$pros     = is_string( $pros_raw ) ? array_filter( array_map( 'trim', explode( "\n", $pros_raw ) ) ) : array();
	$cons     = is_string( $cons_raw ) ? array_filter( array_map( 'trim', explode( "\n", $cons_raw ) ) ) : array();
	if ( ! $pros && ! $cons ) {
		return;
	}
	$pro_icon = globalkeys_get_review_pro_smiley_svg();
	$con_icon = globalkeys_get_review_con_smiley_svg();
	echo '<div class="gk-review-pros-cons-display">';
	if ( $pros ) {
		echo '<ul class="gk-review-pros-cons-display__list gk-review-pros-cons-display__list--pros" aria-label="' . esc_attr__( 'Good', 'globalkeys' ) . '">';
		foreach ( $pros as $line ) {
			echo '<li class="gk-review-pros-cons-display__item"><span class="gk-review-pros-cons-display__icon-wrap">' . $pro_icon . '</span><span class="gk-review-pros-cons-display__text">' . esc_html( $line ) . '</span></li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statisch, Text esc_html.
		}
		echo '</ul>';
	}
	if ( $cons ) {
		echo '<ul class="gk-review-pros-cons-display__list gk-review-pros-cons-display__list--cons" aria-label="' . esc_attr__( 'Bad', 'globalkeys' ) . '">';
		foreach ( $cons as $line ) {
			echo '<li class="gk-review-pros-cons-display__item"><span class="gk-review-pros-cons-display__icon-wrap">' . $con_icon . '</span><span class="gk-review-pros-cons-display__text">' . esc_html( $line ) . '</span></li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '</ul>';
	}
	echo '</div>';
}

add_action( 'woocommerce_review_after_comment_text', 'globalkeys_product_review_output_pros_cons', 15 );

/**
 * Sternbewertung eines Kommentars (Woo „rating“ oder gk_rating_general).
 *
 * @param WP_Comment $comment Kommentar.
 * @return int 0–5
 */
function globalkeys_get_review_star_rating_for_comment( $comment ) {
	if ( ! is_object( $comment ) || empty( $comment->comment_ID ) ) {
		return 0;
	}
	$cid = (int) $comment->comment_ID;
	$r   = (int) get_comment_meta( $cid, 'rating', true );
	if ( $r >= 1 && $r <= 5 ) {
		return $r;
	}
	$g = (int) get_comment_meta( $cid, 'gk_rating_general', true );
	if ( $g >= 1 && $g <= 5 ) {
		return $g;
	}
	return 0;
}

/**
 * Anzahl „Hilfreich“-Stimmen (Up) für eine Bewertung.
 *
 * @param WP_Comment $comment Kommentar.
 * @return int
 */
function globalkeys_get_review_helpful_up_count( $comment ) {
	if ( ! is_object( $comment ) || empty( $comment->comment_ID ) ) {
		return 0;
	}
	return max( 0, (int) get_comment_meta( (int) $comment->comment_ID, 'gk_review_helpful_up', true ) );
}

/**
 * Bewertungen für Zwei-Spalten-Ansicht (Best / Recent).
 *
 * „Best“: nach meisten „Hilfreich“-Stimmen (Meta gk_review_helpful_up), bei Gleichstand neuere zuerst.
 * Nur wenn mindestens eine Bewertung ≥ 1 Stimme hat; sonst bleibt „Best“ leer.
 * „Recent“: nach Veröffentlichungsdatum (neueste zuerst), ohne die in „Best“ gelisteten.
 *
 * @param WC_Product $product Produkt.
 * @param int        $best_limit Anzahl „Best“.
 * @param int        $recent_limit Anzahl „Recent“ (ohne Best-Treffer).
 * @return array{best: WP_Comment[], recent: WP_Comment[]}
 */
function globalkeys_get_product_review_comments_split( $product, $best_limit = 3, $recent_limit = 6 ) {
	$out = array(
		'best'   => array(),
		'recent' => array(),
	);
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return $out;
	}
	$best_limit   = max( 1, (int) $best_limit );
	$recent_limit = max( 0, (int) $recent_limit );

	$comments = get_comments(
		array(
			'post_id' => $product->get_id(),
			'status'  => 'approve',
			'type'    => 'review',
			'parent'  => 0,
			'number'  => 100,
		)
	);
	if ( ! is_array( $comments ) || $comments === array() ) {
		return $out;
	}

	$by_date = $comments;
	usort(
		$by_date,
		static function ( $a, $b ) {
			return strtotime( $b->comment_date ) <=> strtotime( $a->comment_date );
		}
	);

	$by_likes = $comments;
	usort(
		$by_likes,
		static function ( $a, $b ) {
			$ua = globalkeys_get_review_helpful_up_count( $a );
			$ub = globalkeys_get_review_helpful_up_count( $b );
			if ( $ub !== $ua ) {
				return $ub <=> $ua;
			}
			return strtotime( $b->comment_date ) <=> strtotime( $a->comment_date );
		}
	);

	$best = array();
	if ( $by_likes !== array() && globalkeys_get_review_helpful_up_count( $by_likes[0] ) >= 1 ) {
		$best = array_slice( $by_likes, 0, $best_limit );
	}

	$best_ids = array();
	foreach ( $best as $c ) {
		$best_ids[ (int) $c->comment_ID ] = true;
	}

	$recent = array();
	foreach ( $by_date as $c ) {
		if ( isset( $best_ids[ (int) $c->comment_ID ] ) ) {
			continue;
		}
		$recent[] = $c;
		if ( count( $recent ) >= $recent_limit ) {
			break;
		}
	}

	$out['best']   = $best;
	$out['recent'] = $recent;
	return $out;
}

/**
 * Pill-Text rechts im Kartenkopf (Filterbar).
 *
 * @param string     $label   Vorgabe.
 * @param WC_Product $product Produkt.
 * @return string
 */
function globalkeys_default_product_review_platform_label( $label, $product ) {
	if ( is_string( $label ) && $label !== '' ) {
		return $label;
	}
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return '';
	}
	$terms = get_the_terms( $product->get_id(), 'product_cat' );
	if ( is_array( $terms ) && $terms !== array() && ! is_wp_error( $terms ) ) {
		return (string) $terms[0]->name;
	}
	return '';
}

add_filter( 'gk_product_review_card_platform_label', 'globalkeys_default_product_review_platform_label', 10, 2 );

/**
 * Hilfsfunktion: stabiles JSON für HMAC.
 *
 * @param array<string, string> $votes Kommentar-ID => 'u'|'d'.
 * @return string
 */
function globalkeys_review_helpful_stable_json( array $votes ) {
	ksort( $votes, SORT_STRING );
	return wp_json_encode( $votes, JSON_UNESCAPED_UNICODE );
}

/**
 * Gast-Stimmen aus Cookie lesen (signiert).
 *
 * @return array<string, string>
 */
function globalkeys_review_helpful_decode_guest_votes() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$raw = isset( $_COOKIE[ GLOBALKEYS_REVIEW_HELPFUL_COOKIE ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ GLOBALKEYS_REVIEW_HELPFUL_COOKIE ] ) ) : '';
	if ( $raw === '' ) {
		return array();
	}
	$bin = base64_decode( rawurldecode( $raw ), true );
	if ( $bin === false || strpos( $bin, "\n" ) === false ) {
		return array();
	}
	$parts = explode( "\n", $bin, 2 );
	if ( count( $parts ) !== 2 ) {
		return array();
	}
	list( $payload, $sig ) = $parts;
	$expected = hash_hmac( 'sha256', $payload, wp_salt( 'gk_rh_guest' ) );
	if ( ! hash_equals( $expected, $sig ) ) {
		return array();
	}
	$data = json_decode( $payload, true );
	if ( ! is_array( $data ) ) {
		return array();
	}
	$out = array();
	foreach ( $data as $k => $v ) {
		$kid = (string) $k;
		if ( ! preg_match( '/^\d+$/', $kid ) ) {
			continue;
		}
		if ( $v === 'u' || $v === 'd' ) {
			$out[ $kid ] = $v;
		}
	}
	return $out;
}

/**
 * @param array<string, string> $votes
 * @return string
 */
function globalkeys_review_helpful_encode_guest_votes( array $votes ) {
	$payload = globalkeys_review_helpful_stable_json( $votes );
	$sig       = hash_hmac( 'sha256', $payload, wp_salt( 'gk_rh_guest' ) );
	return rawurlencode( base64_encode( $payload . "\n" . $sig ) );
}

/**
 * Stimmen-Map für aktuellen Nutzer bzw. Gast (Cookie).
 *
 * @return array<string, string> comment_id => 'u'|'d'
 */
function globalkeys_review_helpful_get_user_votes_map() {
	if ( is_user_logged_in() ) {
		$uid = get_current_user_id();
		$m   = get_user_meta( $uid, GLOBALKEYS_REVIEW_HELPFUL_USER_META, true );
		if ( ! is_array( $m ) ) {
			return array();
		}
		$out = array();
		foreach ( $m as $k => $v ) {
			$kid = (string) $k;
			if ( ! preg_match( '/^\d+$/', $kid ) ) {
				continue;
			}
			if ( $v === 'u' || $v === 'd' ) {
				$out[ $kid ] = $v;
			}
		}
		return $out;
	}
	return globalkeys_review_helpful_decode_guest_votes();
}

/**
 * @param array<string, string> $votes
 */
function globalkeys_review_helpful_set_user_votes_map( array $votes ) {
	if ( is_user_logged_in() ) {
		$uid = get_current_user_id();
		if ( $votes === array() ) {
			delete_user_meta( $uid, GLOBALKEYS_REVIEW_HELPFUL_USER_META );
			return;
		}
		update_user_meta( $uid, GLOBALKEYS_REVIEW_HELPFUL_USER_META, $votes );
		return;
	}
	$encoded = globalkeys_review_helpful_encode_guest_votes( $votes );
	$path    = ( defined( 'COOKIEPATH' ) && COOKIEPATH ) ? COOKIEPATH : '/';
	$domain  = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '';
	$expire  = time() + YEAR_IN_SECONDS;
	setcookie(
		GLOBALKEYS_REVIEW_HELPFUL_COOKIE,
		$encoded,
		array(
			'expires'  => $expire,
			'path'     => $path,
			'domain'   => $domain,
			'secure'   => is_ssl(),
			'httponly' => true,
			'samesite' => 'Lax',
		)
	);
	// Sofortige Lesbarkeit innerhalb derselben Anfrage (z. B. Folge-Logik).
	$_COOKIE[ GLOBALKEYS_REVIEW_HELPFUL_COOKIE ] = $encoded;
}

/**
 * @param int $comment_id Kommentar-ID.
 * @return string|null 'up', 'down' oder null
 */
function globalkeys_review_helpful_get_my_vote_for_comment( $comment_id ) {
	$map = globalkeys_review_helpful_get_user_votes_map();
	$k   = (string) (int) $comment_id;
	if ( ! isset( $map[ $k ] ) ) {
		return null;
	}
	return 'u' === $map[ $k ] ? 'up' : 'down';
}

/**
 * „Useful?“-Zeile mit Zählern.
 *
 * @param WP_Comment $comment Kommentar.
 */
function globalkeys_product_review_helpful_markup( $comment ) {
	if ( ! is_object( $comment ) || empty( $comment->comment_ID ) ) {
		return;
	}
	$cid  = (int) $comment->comment_ID;
	$up   = (int) get_comment_meta( $cid, 'gk_review_helpful_up', true );
	$down = (int) get_comment_meta( $cid, 'gk_review_helpful_down', true );
	$mine = globalkeys_review_helpful_get_my_vote_for_comment( $cid );
	$nonce = wp_create_nonce( 'gk_review_helpful_' . $cid );
	$up_active   = ( 'up' === $mine );
	$down_active = ( 'down' === $mine );
	?>
	<div class="gk-product-review-card__useful" data-gk-review-helpful-wrap="<?php echo esc_attr( (string) $cid ); ?>">
		<span class="gk-product-review-card__useful-label"><?php esc_html_e( 'Useful?', 'globalkeys' ); ?></span>
		<div class="gk-product-review-card__useful-actions">
			<button type="button" class="gk-product-review-card__vote gk-product-review-card__vote--up<?php echo $up_active ? ' is-active' : ''; ?>" data-gk-review-vote="up" data-comment-id="<?php echo esc_attr( (string) $cid ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>" aria-pressed="<?php echo $up_active ? 'true' : 'false'; ?>" aria-label="<?php esc_attr_e( 'Mark review as helpful', 'globalkeys' ); ?>">
				<span class="gk-product-review-card__vote-icon" aria-hidden="true">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 22V11M2 13v8a2 2 0 002 2h3M16 13h5l-2.5 7.5a2 2 0 01-2 1.5H11V9l3.5-5.5a1 1 0 011.7.5L17 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</span>
				<span class="gk-product-review-card__vote-count" data-gk-helpful-count="up"><?php echo esc_html( (string) max( 0, $up ) ); ?></span>
			</button>
			<button type="button" class="gk-product-review-card__vote gk-product-review-card__vote--down<?php echo $down_active ? ' is-active' : ''; ?>" data-gk-review-vote="down" data-comment-id="<?php echo esc_attr( (string) $cid ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>" aria-pressed="<?php echo $down_active ? 'true' : 'false'; ?>" aria-label="<?php esc_attr_e( 'Mark review as not helpful', 'globalkeys' ); ?>">
				<span class="gk-product-review-card__vote-icon" aria-hidden="true">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17 2v11M22 11V3a2 2 0 00-2-2h-3M8 11H3l2.5-7.5a2 2 0 012-1.5H13v12l-3.5 5.5a1 1 0 01-1.7-.5L7 11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</span>
			</button>
		</div>
	</div>
	<?php
}

/**
 * AJAX: Hilfreich / nicht hilfreich (Toggle, eine Stimme pro Nutzer/Gast pro Bewertung).
 */
function globalkeys_product_review_helpful_ajax() {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce checked below.
	$cid   = isset( $_POST['comment_id'] ) ? absint( wp_unslash( $_POST['comment_id'] ) ) : 0;
	$dir   = isset( $_POST['dir'] ) ? sanitize_key( wp_unslash( $_POST['dir'] ) ) : '';
	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
	if ( $cid <= 0 || ! in_array( $dir, array( 'up', 'down' ), true ) ) {
		wp_send_json_error( array( 'message' => 'bad_request' ), 400 );
	}
	if ( ! wp_verify_nonce( $nonce, 'gk_review_helpful_' . $cid ) ) {
		wp_send_json_error( array( 'message' => 'bad_nonce' ), 403 );
	}
	$c = get_comment( $cid );
	if ( ! $c || (int) $c->comment_approved !== 1 || $c->comment_type !== 'review' ) {
		wp_send_json_error( array( 'message' => 'not_found' ), 404 );
	}

	$votes = globalkeys_review_helpful_get_user_votes_map();
	$ck    = (string) $cid;
	$cur   = isset( $votes[ $ck ] ) ? $votes[ $ck ] : null;

	$up   = max( 0, (int) get_comment_meta( $cid, 'gk_review_helpful_up', true ) );
	$down = max( 0, (int) get_comment_meta( $cid, 'gk_review_helpful_down', true ) );

	if ( 'up' === $dir ) {
		if ( 'u' === $cur ) {
			unset( $votes[ $ck ] );
			$up = max( 0, $up - 1 );
			update_comment_meta( $cid, 'gk_review_helpful_up', $up );
		} elseif ( 'd' === $cur ) {
			$down = max( 0, $down - 1 );
			$up++;
			$votes[ $ck ] = 'u';
			update_comment_meta( $cid, 'gk_review_helpful_down', $down );
			update_comment_meta( $cid, 'gk_review_helpful_up', $up );
		} else {
			$votes[ $ck ] = 'u';
			$up++;
			update_comment_meta( $cid, 'gk_review_helpful_up', $up );
		}
	} else {
		if ( 'd' === $cur ) {
			unset( $votes[ $ck ] );
			$down = max( 0, $down - 1 );
			update_comment_meta( $cid, 'gk_review_helpful_down', $down );
		} elseif ( 'u' === $cur ) {
			$up = max( 0, $up - 1 );
			$down++;
			$votes[ $ck ] = 'd';
			update_comment_meta( $cid, 'gk_review_helpful_up', $up );
			update_comment_meta( $cid, 'gk_review_helpful_down', $down );
		} else {
			$votes[ $ck ] = 'd';
			$down++;
			update_comment_meta( $cid, 'gk_review_helpful_down', $down );
		}
	}

	globalkeys_review_helpful_set_user_votes_map( $votes );

	$up   = max( 0, (int) get_comment_meta( $cid, 'gk_review_helpful_up', true ) );
	$down = max( 0, (int) get_comment_meta( $cid, 'gk_review_helpful_down', true ) );
	$yours = isset( $votes[ $ck ] ) ? ( 'u' === $votes[ $ck ] ? 'up' : 'down' ) : null;

	wp_send_json_success(
		array(
			'up'        => $up,
			'down'      => $down,
			'your_vote' => $yours,
		)
	);
}

add_action( 'wp_ajax_gk_review_helpful', 'globalkeys_product_review_helpful_ajax' );
add_action( 'wp_ajax_nopriv_gk_review_helpful', 'globalkeys_product_review_helpful_ajax' );

/**
 * Review-Modal: Inline-Skript im Footer (unabhängig von externer JS-Datei / Cache / Optimierern).
 */
function globalkeys_product_reviews_footer_modal_inline() {
	$is_product_page = ( function_exists( 'woocommerce_is_product_page' ) && woocommerce_is_product_page() )
		|| ( function_exists( 'is_product' ) && is_product() );
	if ( ! $is_product_page ) {
		return;
	}
	if ( ! function_exists( 'globalkeys_single_product_is_purchase_card_active' ) || ! globalkeys_single_product_is_purchase_card_active() ) {
		return;
	}
	if ( ! function_exists( 'wc_reviews_enabled' ) || ! wc_reviews_enabled() ) {
		return;
	}
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- reines statisches JS
	?>
<script id="gk-review-modal-boot">
(function(){'use strict';
var K='gk-review-modal--closed';
function M(){return document.getElementById('gk-review-modal');}
/** Ohne Verschiebung an body: position:fixed hängt an Vorfahren mit transform (z. B. div.product). */
function portalModal(){var m=M();if(m&&m.parentNode!==document.body){document.body.appendChild(m);}}
function scrollAnc(){var t=document.getElementById('gk-after-hero-reviews');if(t){t.scrollIntoView({behavior:'smooth',block:'start'});}}
function openM(e){if(e){e.preventDefault();}portalModal();var m=M();if(!m){scrollAnc();return;}if(!m.classList.contains(K)){return;}m.gkLF=document.activeElement;m.classList.remove(K);m.setAttribute('aria-hidden','false');document.documentElement.classList.add('gk-review-modal-is-open');var c=m.querySelector('.gk-review-modal__close');if(c){setTimeout(function(){try{c.focus();}catch(x){}},50);}setTimeout(function(){if(window.gkReviewModalFormInit){window.gkReviewModalFormInit();}if(window.gkInitReviewCommentDeco){window.gkInitReviewCommentDeco();}},0);setTimeout(function(){if(window.gkInitReviewCommentDeco){window.gkInitReviewCommentDeco();}},400);}
function closeM(){var m=M();if(!m||m.classList.contains(K)){return;}if(window.gkStopReviewCommentDeco){window.gkStopReviewCommentDeco();}m.classList.add(K);m.setAttribute('aria-hidden','true');document.documentElement.classList.remove('gk-review-modal-is-open');var lf=m.gkLF;if(lf&&lf.focus){try{lf.focus();}catch(x){}}}
document.addEventListener('click',function(ev){var el=ev.target;if(!el||!el.closest){return;}if(el.closest('[data-gk-review-modal-open]')){openM(ev);return;}var x=el.closest('[data-gk-review-modal-close]');if(x&&M()&&x.closest('#gk-review-modal')){ev.preventDefault();closeM();}},true);
document.addEventListener('keydown',function(ev){if(ev.key!=='Escape'){return;}var m=M();if(m&&!m.classList.contains(K)){closeM();}},true);
window.gkOpenProductReviewModal=openM;
window.gkCloseProductReviewModal=closeM;
if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',portalModal);}else{portalModal();}
})();
window.gkStarPaint=function(r){if(!r||!r.querySelectorAll){return;}var h=r.querySelector('input[type="hidden"]');var v=h?parseInt(h.value,10)||0:0;var ho=parseInt(r.getAttribute('data-gk-hover-star'),10)||0;r.querySelectorAll('.gk-review-stars__star').forEach(function(b){var d=parseInt(b.getAttribute('data-value'),10);var sel=v>=1&&d<=v;b.classList.toggle('is-selected',sel);b.setAttribute('aria-pressed',sel?'true':'false');b.classList.remove('gk-star-lit','gk-star-hover-outline');if(sel){b.classList.add('gk-star-lit');}if(ho>0&&d<=ho){b.classList.add('gk-star-hover-outline');}});};
window.gkStarHoverIn=function(btn){if(!btn||!btn.closest){return;}var r=btn.closest('[data-gk-review-stars]');if(!r){return;}r.setAttribute('data-gk-hover-star',btn.getAttribute('data-value'));window.gkStarPaint(r);};
window.gkStarHoverOut=function(r){if(!r||!r.removeAttribute){return;}r.removeAttribute('data-gk-hover-star');window.gkStarPaint(r);};
window.gkStarPick=function(btn){if(!btn||!btn.closest){return;}var r=btn.closest('[data-gk-review-stars]');if(!r){return;}var h=r.querySelector('input[type="hidden"]');if(!h){return;}var val=parseInt(btn.getAttribute('data-value'),10)||0;var cur=parseInt(h.value,10)||0;if(val>=1&&val<=5){if(val===cur){h.value='';}else{h.value=String(val);}}r.removeAttribute('data-gk-hover-star');window.gkStarPaint(r);var sel=document.getElementById('rating');if(sel&&h.id==='gk_rating_general'&&val>=1&&val<=5){sel.value=val===cur?'':String(val);try{sel.dispatchEvent(new Event('change',{bubbles:true}));}catch(e){}}if(typeof window.gkReviewModalValidate==='function'){window.gkReviewModalValidate();}};
</script>
<script id="gk-review-modal-validate">
(function(){'use strict';
var MID='gk-review-modal';
function gF(m){if(!m||!m.querySelector){return null;}return m.querySelector('form#commentform')||m.querySelector('#commentform')||m.querySelector('#respond form')||m.querySelector('form[action*="wp-comments-post.php"]')||m.querySelector('form[action*="wp-comments-post"]')||m.querySelector('form');}
function gB(f){if(!f||!f.querySelector){return null;}return f.querySelector('input[data-gk-review-submit][type="submit"]')||f.querySelector('button[data-gk-review-submit][type="submit"]')||f.querySelector('input.gk-product-reviews__submit[type="submit"]')||f.querySelector('button.gk-product-reviews__submit[type="submit"]')||f.querySelector('#submit')||f.querySelector('.form-submit input[type="submit"]')||f.querySelector('.form-submit button[type="submit"]')||f.querySelector('input[name="submit"][type="submit"]')||f.querySelector('input[type="submit"]')||f.querySelector('button[type="submit"]');}
function ok(f){if(!f){return false;}var g=f.querySelector('#gk_rating_general'),rt=f.querySelector('#rating');if(rt){var gv=g?parseInt(g.value,10)||0:0;if(gv<1||gv>5){return false;}if(!rt.value){return false;}}var c=f.querySelector('#comment');if(!c||!String(c.value).trim()){return false;}var a=f.querySelector('#author');if(a&&a.hasAttribute('required')&&!String(a.value).trim()){return false;}var e=f.querySelector('#email');if(e&&e.hasAttribute('required')&&!String(e.value).trim()){return false;}return true;}
function v(){var m=document.getElementById(MID);if(!m){return;}var f=gF(m),b=gB(f);if(!f||!b){return;}var k=ok(f);b.disabled=!k;if(k){b.removeAttribute('disabled');b.removeAttribute('aria-disabled');}else{b.setAttribute('disabled','disabled');b.setAttribute('aria-disabled','true');}b.classList.toggle('gk-product-reviews__submit--disabled',!k);}
function bind(f){if(!f||f.getAttribute('data-gk-review-form-init')==='1'){return;}f.setAttribute('data-gk-review-form-init','1');f.addEventListener('input',v);f.addEventListener('change',v);f.addEventListener('click',function(ev){var t=ev.target;if(!t||t.nodeType!==1){return;}var tag=(t.nodeName||'').toLowerCase();if((tag!=='input'&&tag!=='button')||t.type!=='submit'){return;}if(!f.contains(t)||!t.disabled){return;}ev.preventDefault();ev.stopImmediatePropagation();},true);f.addEventListener('submit',function(ev){if(!ok(f)){ev.preventDefault();ev.stopPropagation();}});v();}
function init(){var m=document.getElementById(MID);if(!m){return;}m.querySelectorAll('[data-gk-review-stars]').forEach(function(r){if(typeof window.gkStarPaint==='function'){window.gkStarPaint(r);}});var f=gF(m);if(f){bind(f);}v();}
window.gkReviewModalValidate=v;
window.gkReviewModalFormInit=init;
function boot(){init();}
if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',boot);}else{boot();}
window.addEventListener('load',init);
document.addEventListener('click',function(ev){if(!ev.target||!ev.target.closest){return;}if(ev.target.closest('[data-gk-review-modal-open]')){setTimeout(v,0);setTimeout(v,200);}},true);
})();
</script>
<script id="gk-review-comment-deco">
(function(){'use strict';
var PH=['I like saying what worked—and what did not, in clear words.','I don\'t like vague scores; a short honest line usually helps more.','I love when a game earns its praise, and I try to explain why.','I hope this helps you decide with a little more confidence.','I wish I had read a review like this earlier—so I am writing one now.'];
var TYPE_MS=38,ERASE_MS=28,PAUSE_MS=400,GAP_MS=1200,FIRST_MS=450;
var timers=[],running=false,idx=0,ta,shell,deco,modal,bound=false;
function clr(){timers.forEach(function(t){clearTimeout(t);});timers=[];}
function sch(fn,ms){var id=setTimeout(function(){timers=timers.filter(function(x){return x!==id;});fn();},ms);timers.push(id);}
function modalClosed(){return!modal||modal.classList.contains('gk-review-modal--closed');}
function fieldBusy(){return!ta||String(ta.value||'').trim().length>0||document.activeElement===ta;}
function animBlocked(){return modalClosed()||fieldBusy();}
function bootBlocked(){return modalClosed()||!ta||String(ta.value||'').trim().length>0||document.activeElement===ta;}
function hide(on){if(!shell){return;}if(on){shell.classList.add('gk-review-modal__comment-shell--hide-deco');}else{shell.classList.remove('gk-review-modal__comment-shell--hide-deco');}}
function stop(){clr();running=false;if(deco){deco.textContent='';}hide(true);}
function typeCh(str,i){if(animBlocked()){stop();return;}if(i<str.length){deco.textContent=str.slice(0,i+1);sch(function(){typeCh(str,i+1);},TYPE_MS);}else{sch(function(){eraseRun(str);},PAUSE_MS);}}
function eraseRun(s){if(animBlocked()){stop();return;}if(!s.length){idx=(idx+1)%PH.length;sch(function(){if(!animBlocked()){typeCh(PH[idx],0);}else{stop();}},GAP_MS);return;}s=s.slice(1);deco.textContent=s;sch(function(){eraseRun(s);},ERASE_MS);}
function begin(){if(bootBlocked()||running){return;}running=true;hide(false);typeCh(PH[idx],0);}
function bootBind(){if(bound||!ta||!shell){return;}bound=true;ta.addEventListener('focus',function(){stop();hide(true);});ta.addEventListener('input',function(){if(String(ta.value||'').trim().length>0){stop();hide(true);}});ta.addEventListener('blur',function(){if(String(ta.value||'').trim().length>0){return;}sch(function(){running=false;if(!bootBlocked()){begin();}else{stop();}},GAP_MS);});}
window.gkStopReviewCommentDeco=stop;
window.gkInitReviewCommentDeco=function(){modal=document.getElementById('gk-review-modal');if(!modal){return;}ta=modal.querySelector('[data-gk-review-comment]')||modal.querySelector('textarea#comment');deco=modal.querySelector('[data-gk-review-comment-deco]');shell=modal.querySelector('[data-gk-review-comment-shell]');if(!ta||!deco||!shell){return;}bootBind();stop();idx=0;if(bootBlocked()){hide(true);return;}sch(begin,FIRST_MS);};
if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',function(){var m=document.getElementById('gk-review-modal');if(m&&!m.classList.contains('gk-review-modal--closed')){window.gkInitReviewCommentDeco();}});}else{var m2=document.getElementById('gk-review-modal');if(m2&&!m2.classList.contains('gk-review-modal--closed')){window.gkInitReviewCommentDeco();}}
})();
</script>
<script id="gk-review-helpful-votes">
(function(){'use strict';
var U=<?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
document.addEventListener('click',function(ev){
var t=ev.target;if(!t||!t.closest){return;}
var b=t.closest('[data-gk-review-vote]');if(!b){return;}
var w=b.closest('[data-gk-review-helpful-wrap]');if(!w){return;}
ev.preventDefault();if(b.disabled){return;}
var cid=b.getAttribute('data-comment-id'),dir=b.getAttribute('data-gk-review-vote'),nx=b.getAttribute('data-nonce');
if(!cid||!dir||!nx){return;}
var acts=w.querySelectorAll('[data-gk-review-vote]');acts.forEach(function(x){x.disabled=true;});
var fd=new FormData();fd.append('action','gk_review_helpful');fd.append('comment_id',cid);fd.append('dir',dir);fd.append('nonce',nx);
fetch(U,{method:'POST',body:fd,credentials:'same-origin'})
.then(function(r){return r.json();})
.then(function(j){
if(!j||!j.success||!j.data){return;}
var upEl=w.querySelector('[data-gk-helpful-count="up"]');
if(upEl&&typeof j.data.up!=='undefined'){upEl.textContent=String(j.data.up);}
var upB=w.querySelector('[data-gk-review-vote="up"]'),downB=w.querySelector('[data-gk-review-vote="down"]');
if(upB&&downB){
var yv=j.data.your_vote;
if(yv==='up'){upB.classList.add('is-active');downB.classList.remove('is-active');upB.setAttribute('aria-pressed','true');downB.setAttribute('aria-pressed','false');}
else if(yv==='down'){downB.classList.add('is-active');upB.classList.remove('is-active');downB.setAttribute('aria-pressed','true');upB.setAttribute('aria-pressed','false');}
else{upB.classList.remove('is-active');downB.classList.remove('is-active');upB.setAttribute('aria-pressed','false');downB.setAttribute('aria-pressed','false');}
}
})
.catch(function(){})
.finally(function(){acts.forEach(function(x){x.disabled=false;});});
},true);
})();
</script>
	<?php
}

add_action( 'wp_footer', 'globalkeys_product_reviews_footer_modal_inline', 999 );

/**
 * Hero-Zeile unter der Reviews-Überschrift (Score / Text / CTA).
 *
 * @param WC_Product $product Produkt.
 */
function globalkeys_product_reviews_print_hero_bar( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return;
	}

	$count = (int) $product->get_review_count();
	$avg   = (float) $product->get_average_rating();

	if ( $count > 0 && $avg > 0 && function_exists( 'wc_review_ratings_enabled' ) && wc_review_ratings_enabled() ) {
		$score_display = (string) (int) round( $avg * 2, 0 );
		$score_attr    = $score_display;
	} else {
		$score_display = '–';
		$score_attr    = '';
	}

	$title = apply_filters(
		'gk_reviews_hero_title',
		__( 'Game rating', 'globalkeys' ),
		$product
	);

	$subtitle = apply_filters(
		'gk_reviews_hero_subtitle',
		sprintf(
			/* translators: %d: number of reviews */
			_n(
				'Based on %d review, all languages included.',
				'Based on %d reviews, all languages included.',
				$count,
				'globalkeys'
			),
			$count
		),
		$product,
		$count
	);

	$cta_text = apply_filters(
		'gk_reviews_hero_cta_text',
		__( 'Rate this game!', 'globalkeys' ),
		$product
	);

	echo '<div class="gk-product-reviews-hero">';
	echo '<div class="gk-product-reviews-hero__score" role="img" aria-label="' . esc_attr(
		$score_attr !== ''
			? sprintf(
				/* translators: %s: score out of 10 */
				__( 'User score %s out of 10', 'globalkeys' ),
				$score_attr
			)
			: __( 'No user score yet', 'globalkeys' )
	) . '">';
	echo '<span class="gk-product-reviews-hero__score-ring">';
	echo '<span class="gk-product-reviews-hero__score-value">' . esc_html( $score_display ) . '</span>';
	echo '</span></div>';

	echo '<div class="gk-product-reviews-hero__text">';
	echo '<p class="gk-product-reviews-hero__title">' . esc_html( is_string( $title ) ? $title : '' ) . '</p>';
	echo '<p class="gk-product-reviews-hero__sub">' . esc_html( is_string( $subtitle ) ? $subtitle : '' ) . '</p>';
	echo '</div>';

	echo '<button type="button" class="gk-product-reviews-hero__cta" data-gk-review-modal-open onclick="if(typeof window.gkOpenProductReviewModal===\'function\'){window.gkOpenProductReviewModal(event);}return false;">';
	echo '<span class="gk-product-reviews-hero__cta-label">' . esc_html( is_string( $cta_text ) ? $cta_text : '' ) . '</span>';
	echo '<span class="gk-product-reviews-hero__cta-icon" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false"><path d="M4 21v-3.5l10.5-10.5 3.5 3.5L7.5 21H4zm2-2h1.2L15 11.2l-1.2-1.2L6 17.8V19zm11.7-12.3l1 1c.4.4.4 1 0 1.4l-1.6 1.6-3.5-3.5 1.6-1.6c.4-.4 1-.4 1.4 0z" fill="currentColor"/></svg></span>';
	echo '</button>';

	echo '</div>';
}

/**
 * Section unter „Similar products“ – Überschrift wie andere Produkt-Sections; Body per Filter befüllbar oder Standard: comments_template.
 */
function globalkeys_single_product_reviews_section() {
	if ( ! function_exists( 'globalkeys_single_product_is_purchase_card_active' ) || ! globalkeys_single_product_is_purchase_card_active() ) {
		return;
	}
	global $product;
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return;
	}
	if ( ! function_exists( 'wc_reviews_enabled' ) || ! wc_reviews_enabled() ) {
		return;
	}

	$heading = apply_filters( 'gk_reviews_section_heading_text', __( 'Reviews', 'globalkeys' ), $product );
	if ( ! is_string( $heading ) || $heading === '' ) {
		return;
	}

	$heading_id = 'gk-product-page-reviews-heading-' . (int) $product->get_id();

	echo '<section class="gk-product-page-reviews" aria-labelledby="' . esc_attr( $heading_id ) . '">';
	echo '<div class="gk-section-inner gk-section-featured-inner">';
	echo '<div class="gk-featured-heading-wrap gk-product-page-reviews__heading-wrap">';
	echo '<h2 id="' . esc_attr( $heading_id ) . '" class="gk-section-title gk-featured-heading">';
	echo '<span class="gk-featured-heading-text-wrap">';
	echo '<span class="gk-featured-heading-text">' . esc_html( $heading ) . '</span>';
	echo '<span class="gk-featured-title-underline" aria-hidden="true"></span>';
	echo '</span>';
	echo '</h2>';
	echo '</div>';

	globalkeys_product_reviews_print_hero_bar( $product );

	$content = apply_filters( 'gk_reviews_section_content_html', '', $product );
	echo '<div class="gk-product-page-reviews__body gk-product-page-reviews__body--wc">';
	if ( is_string( $content ) && $content !== '' ) {
		echo wp_kses_post( $content );
	} else {
		comments_template();
	}
	echo '</div>';

	echo '</div>';
	echo '</section>';
}

add_action( 'woocommerce_after_single_product_summary', 'globalkeys_single_product_reviews_section', 12 );
