<?php
/**
 * E-Mail-Verifizierung bei Registrierung (6-stelliger Code).
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GK_VERIFY_EXPIRE_HOURS', 24 );

/**
 * Verifizierungs-E-Mail versenden (von Registrierung und „Erneut senden“ genutzt).
 *
 * @param string $email Empfänger.
 * @param string $code  6-stelliger Code.
 * @return bool True wenn Versand ausgelöst wurde (nicht zwingend zugestellt).
 */
function globalkeys_verify_send_email( $email, $code ) {
	$subject = sprintf( /* translators: %s: Site name */ __( '[%s] Verifiziere deine E-Mail', 'globalkeys' ), get_bloginfo( 'name' ) );
	$message = sprintf(
		/* translators: 1: 6-digit code, 2: Site name, 3: Expiry in hours */
		__( "Dein Bestätigungscode lautet: %1\$s\n\nGib diesen Code auf der Verifizierungsseite ein, um dein Konto zu aktivieren.\n\n%2\$s\n\nDer Code ist %3\$d Stunden gültig.", 'globalkeys' ),
		$code,
		get_bloginfo( 'name' ),
		GK_VERIFY_EXPIRE_HOURS
	);
	$headers  = "Content-Type: text/plain; charset=UTF-8\r\n";
	$from_email = is_email( get_option( 'woocommerce_email_from_address' ) ) ? get_option( 'woocommerce_email_from_address' ) : get_option( 'admin_email' );
	$from_name  = get_option( 'woocommerce_email_from_name' ) ?: get_bloginfo( 'name' );
	$headers   .= "From: " . $from_name . " <" . $from_email . ">\r\n";

	if ( function_exists( 'wc_mail' ) && WC() && WC()->mailer() ) {
		return wc_mail( $email, $subject, $message, $headers );
	}
	return wp_mail( $email, $subject, $message, $headers );
}

/**
 * E-Mail-Fehler protokollieren (z. B. in wp-content/debug.log wenn WP_DEBUG_LOG aktiv).
 */
function globalkeys_verify_log_mail_failed( $wp_error ) {
	if ( ! $wp_error instanceof WP_Error ) {
		return;
	}
	$msg = 'GK Verify E-Mail fehlgeschlagen: ' . $wp_error->get_error_message();
	if ( function_exists( 'wc_get_logger' ) ) {
		$logger = wc_get_logger();
		$logger->warning( $msg, array( 'source' => 'gk-verify-email' ) );
	}
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		error_log( $msg );
	}
}
add_action( 'wp_mail_failed', 'globalkeys_verify_log_mail_failed' );

/**
 * Query-Var und Rewrite für Verifizierungsseite.
 */
function globalkeys_verify_query_vars( $vars ) {
	$vars[] = 'gk_verify';
	return $vars;
}
add_filter( 'query_vars', 'globalkeys_verify_query_vars' );

function globalkeys_verify_rewrite_rules() {
	add_rewrite_rule( '^verify-email/?', 'index.php?gk_verify=1', 'top' );
}
add_action( 'init', 'globalkeys_verify_rewrite_rules' );

function globalkeys_verify_flush_rewrite() {
	if ( get_option( 'globalkeys_verify_flush_done' ) ) {
		return;
	}
	flush_rewrite_rules();
	update_option( 'globalkeys_verify_flush_done', 1 );
}
add_action( 'init', 'globalkeys_verify_flush_rewrite', 999 );

/**
 * Nach erfolgreicher Validierung: User noch nicht anlegen, stattdessen
 * 6-stelligen Code generieren, E-Mail senden, zur Verifizierungsseite leiten.
 */
function globalkeys_registration_require_email_verify( $validation_error, $username, $password, $email ) {
	// Nur weitermachen, wenn bisher keine Fehler.
	if ( $validation_error->has_errors() ) {
		return $validation_error;
	}

	$code    = str_pad( (string) wp_rand( 0, 999999 ), 6, '0', STR_PAD_LEFT );
	$token   = bin2hex( random_bytes( 16 ) );
	$expires = time() + ( GK_VERIFY_EXPIRE_HOURS * HOUR_IN_SECONDS );

	$data = array(
		'username'  => sanitize_user( $username, true ),
		'email'     => sanitize_email( $email ),
		'password'  => $password,
		'code'      => $code,
		'expires'   => $expires,
	);

	set_transient( 'gk_pending_verify_' . $token, $data, GK_VERIFY_EXPIRE_HOURS * HOUR_IN_SECONDS );
	set_transient( 'gk_verify_redirect', $token, 120 );

	globalkeys_verify_send_email( $email, $code );

	$validation_error->add( 'gk_email_verify_required', __( 'Bitte bestätige deine E-Mail.', 'globalkeys' ) );
	return $validation_error;
}
add_filter( 'woocommerce_process_registration_errors', 'globalkeys_registration_require_email_verify', 20, 4 );

/**
 * Nach Registrierungs-POST: Wenn Redirect-Token gesetzt, zur Verifizierungsseite leiten.
 */
function globalkeys_verify_redirect_after_register() {
	$token = get_transient( 'gk_verify_redirect' );
	if ( ! $token ) {
		return;
	}
	delete_transient( 'gk_verify_redirect' );
	if ( function_exists( 'wc_clear_notices' ) ) {
		wc_clear_notices();
	}
	wp_safe_redirect( home_url( '/verify-email/?token=' . rawurlencode( $token ) ) );
	exit;
}
add_action( 'template_redirect', 'globalkeys_verify_redirect_after_register', 4 );

/**
 * Seitentitel für Verifizierungsseite.
 */
add_filter( 'document_title_parts', function( $parts ) {
	if ( get_query_var( 'gk_verify' ) ) {
		$parts['title'] = __( 'E-Mail verifizieren', 'globalkeys' );
	}
	return $parts;
} );

/**
 * Template für Verifizierungsseite laden.
 */
function globalkeys_verify_template_include( $template ) {
	if ( ! get_query_var( 'gk_verify' ) ) {
		return $template;
	}
	$verify_template = get_template_directory() . '/verify-email.php';
	if ( file_exists( $verify_template ) ) {
		return $verify_template;
	}
	return $template;
}
add_filter( 'template_include', 'globalkeys_verify_template_include' );

/**
 * Verifizierungs-POST verarbeiten.
 */
function globalkeys_verify_handle_submit() {
	if ( ! isset( $_POST['gk_verify_submit'], $_POST['gk_verify_token'], $_POST['gk_verify_code'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gk_verify_submit'] ) ), 'gk_email_verify' ) ) {
		return;
	}

	$token = sanitize_text_field( wp_unslash( $_POST['gk_verify_token'] ) );
	$code  = preg_replace( '/\D/', '', isset( $_POST['gk_verify_code'] ) ? wp_unslash( $_POST['gk_verify_code'] ) : '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	$data = get_transient( 'gk_pending_verify_' . $token );
	if ( ! $data || ! is_array( $data ) ) {
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}
	if ( empty( $code ) || strlen( $code ) !== 6 ) {
		wp_safe_redirect( add_query_arg( array( 'token' => $token, 'gk_error' => 'code' ), home_url( '/verify-email/' ) ) );
		exit;
	}
	if ( (int) $data['expires'] < time() ) {
		delete_transient( 'gk_pending_verify_' . $token );
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}
	if ( $data['code'] !== $code ) {
		wp_safe_redirect( add_query_arg( array( 'token' => $token, 'gk_error' => 'code' ), home_url( '/verify-email/' ) ) );
		exit;
	}

	delete_transient( 'gk_pending_verify_' . $token );

	$customer_id = wc_create_new_customer( $data['email'], $data['username'], $data['password'] );
	if ( is_wp_error( $customer_id ) ) {
		wp_safe_redirect( add_query_arg( array( 'token' => $token, 'gk_error' => 'code' ), home_url( '/verify-email/' ) ) );
		exit;
	}

	update_user_meta( $customer_id, 'gamertag', $data['username'] );
	wc_set_customer_auth_cookie( $customer_id );

	wp_safe_redirect( wc_get_page_permalink( 'myaccount' ) );
	exit;
}
add_action( 'template_redirect', 'globalkeys_verify_handle_submit', 1 );

/**
 * "Konto später erstellen": Token löschen, zur Startseite.
 */
function globalkeys_verify_handle_skip() {
	if ( ! isset( $_GET['gk_skip'], $_GET['token'] ) ) {
		return;
	}
	$token = sanitize_text_field( wp_unslash( $_GET['token'] ) );
	if ( '' === $token ) {
		return;
	}
	delete_transient( 'gk_pending_verify_' . $token );
	wp_safe_redirect( home_url( '/' ) );
	exit;
}
add_action( 'template_redirect', 'globalkeys_verify_handle_skip', 1 );

/**
 * "Code erneut senden": E-Mail mit gleichem Code nochmals senden (Rate-Limit 60 Sek.).
 */
function globalkeys_verify_handle_resend() {
	if ( ! isset( $_GET['gk_resend'], $_GET['token'] ) ) {
		return;
	}
	$token = sanitize_text_field( wp_unslash( $_GET['token'] ) );
	if ( '' === $token ) {
		return;
	}
	$rate_key = 'gk_verify_resend_' . $token;
	if ( get_transient( $rate_key ) ) {
		wp_safe_redirect( add_query_arg( array( 'token' => $token, 'gk_resent' => '0' ), home_url( '/verify-email/' ) ) );
		exit;
	}
	$data = get_transient( 'gk_pending_verify_' . $token );
	if ( ! $data || ! is_array( $data ) || ( (int) ( $data['expires'] ?? 0 ) ) < time() ) {
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}
	set_transient( $rate_key, 1, 60 );
	globalkeys_verify_send_email( $data['email'], $data['code'] );
	wp_safe_redirect( add_query_arg( array( 'token' => $token, 'gk_resent' => '1' ), home_url( '/verify-email/' ) ) );
	exit;
}
add_action( 'template_redirect', 'globalkeys_verify_handle_resend', 1 );
