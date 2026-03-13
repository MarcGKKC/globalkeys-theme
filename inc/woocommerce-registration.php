<?php
/**
 * WooCommerce-Registrierung: Gamertag, Passwort direkt setzen.
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Prüft, ob ein Gamertag bereits vergeben ist.
 *
 * @param string $gamertag Der zu prüfende Gamertag.
 * @return bool True wenn vergeben, sonst false.
 */
function globalkeys_gamertag_is_taken( $gamertag ) {
	$gamertag = trim( $gamertag );
	if ( '' === $gamertag ) {
		return false;
	}
	if ( username_exists( $gamertag ) ) {
		return true;
	}
	$users = get_users(
		array(
			'meta_key'   => 'gamertag',
			'meta_value' => $gamertag,
			'number'     => 1,
			'fields'     => 'ID',
		)
	);
	return ! empty( $users );
}

/**
 * Gamertag im Registrierungsformular anzeigen (dient gleichzeitig als Benutzername).
 */
function globalkeys_register_form_gamertag() {
	$gamertag = ! empty( $_POST['username'] ) ? wp_unslash( $_POST['username'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	if ( '' === $gamertag ) {
		$kept = get_transient( 'gk_register_keep_username' );
		if ( false !== $kept ) {
			$gamertag = $kept;
			delete_transient( 'gk_register_keep_username' );
		}
	}
	$placeholders = array( 'Globalkeys_Master', 'CryptoKing88', 'PixelHunter_', 'RealmRoyale99', 'SteelFist42' );
	$placeholder  = $placeholders[ array_rand( $placeholders ) ];
	?>
	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide gk-login-row gk-gamertag-row">
		<span id="gk-gamertag-error" class="gk-gamertag-error" role="alert" aria-live="polite" style="display:none;"></span>
		<label for="reg_username"><?php esc_html_e( 'Gamertag', 'globalkeys' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
		<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" placeholder="<?php echo esc_attr( $placeholder ); ?>" value="<?php echo esc_attr( $gamertag ); ?>" required aria-required="true" />
	</p>
	<?php
}
add_action( 'woocommerce_register_form_start', 'globalkeys_register_form_gamertag', 5 );

/**
 * Standard-Privacy-Text entfernen, eigene Checkbox verwenden.
 */
add_action( 'init', function() {
	remove_action( 'woocommerce_register_form', 'wc_registration_privacy_policy_text', 20 );
}, 20 );

/**
 * Terms- und Privacy-Checkbox im Registrierungsformular.
 */
function globalkeys_register_form_terms_checkbox() {
	$privacy_page_id = function_exists( 'wc_privacy_policy_page_id' ) ? wc_privacy_policy_page_id() : 0;
	$terms_page_id   = function_exists( 'wc_terms_and_conditions_page_id' ) ? wc_terms_and_conditions_page_id() : 0;

	$privacy_link = $privacy_page_id ? '<a href="' . esc_url( get_permalink( $privacy_page_id ) ) . '" class="gk-terms-link" target="_blank" rel="noopener">' . esc_html__( 'Privacy Policy', 'globalkeys' ) . '</a>' : esc_html__( 'Privacy Policy', 'globalkeys' );
	$terms_link   = $terms_page_id ? '<a href="' . esc_url( get_permalink( $terms_page_id ) ) . '" class="gk-terms-link" target="_blank" rel="noopener">' . esc_html__( 'Terms', 'globalkeys' ) . '</a>' : esc_html__( 'Terms', 'globalkeys' );

	$label = sprintf(
		/* translators: 1: Terms link, 2: Privacy Policy link */
		__( 'Ich stimme den %1$s und %2$s zu', 'globalkeys' ),
		$terms_link,
		$privacy_link
	);
	?>
	<p class="form-row gk-terms-checkbox-row">
		<label class="gk-terms-checkbox-label">
			<span class="gk-terms-checkbox-inner">
				<input type="checkbox" name="gk_agree_terms" id="gk_agree_terms" value="1" required aria-required="true" class="gk-terms-checkbox-input" />
				<span class="gk-terms-checkbox-box" aria-hidden="true"></span>
			</span>
			<span class="gk-terms-checkbox-text"><?php echo wp_kses( $label, array( 'a' => array( 'href' => array(), 'class' => array(), 'target' => array(), 'rel' => array() ) ) ); ?></span>
		</label>
	</p>
	<?php
}
add_action( 'woocommerce_register_form', 'globalkeys_register_form_terms_checkbox', 15 );

/**
 * AJAX: Gamertag-Verfügbarkeit prüfen.
 */
function globalkeys_ajax_check_gamertag() {
	$gamertag = isset( $_POST['gamertag'] ) ? sanitize_text_field( wp_unslash( $_POST['gamertag'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$taken    = globalkeys_gamertag_is_taken( $gamertag );
	wp_send_json( array( 'taken' => $taken ) );
}
add_action( 'wp_ajax_globalkeys_check_gamertag', 'globalkeys_ajax_check_gamertag' );
add_action( 'wp_ajax_nopriv_globalkeys_check_gamertag', 'globalkeys_ajax_check_gamertag' );

/**
 * Prüft, ob eine E-Mail-Adresse bereits registriert ist.
 *
 * @param string $email Die zu prüfende E-Mail.
 * @return bool True wenn vergeben, sonst false.
 */
function globalkeys_email_is_taken( $email ) {
	$email = trim( $email );
	if ( '' === $email || ! is_email( $email ) ) {
		return false;
	}
	return (bool) email_exists( $email );
}

/**
 * AJAX: E-Mail-Verfügbarkeit prüfen.
 */
function globalkeys_ajax_check_email() {
	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$taken = globalkeys_email_is_taken( $email );
	wp_send_json( array( 'taken' => $taken ) );
}
add_action( 'wp_ajax_globalkeys_check_email', 'globalkeys_ajax_check_email' );
add_action( 'wp_ajax_nopriv_globalkeys_check_email', 'globalkeys_ajax_check_email' );

/**
 * Gamertag validieren.
 *
 * @param WP_Error $validation_error Fehlerobjekt.
 * @param string   $username        Benutzername (Gamertag).
 * @param string   $password        Passwort.
 * @param string   $email           E-Mail.
 * @return WP_Error
 */
function globalkeys_validate_registration_gamertag( $validation_error, $username, $password, $email ) {
	if ( empty( $_POST['gk_agree_terms'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$validation_error->add( 'terms_required', __( 'Bitte stimme den Nutzungsbedingungen und der Datenschutzerklärung zu.', 'globalkeys' ) );
	}
	if ( empty( $username ) || '' === trim( $username ) ) {
		$validation_error->add( 'missing_gamertag', __( 'Bitte gib deinen Gamertag ein.', 'globalkeys' ) );
	} elseif ( globalkeys_gamertag_is_taken( $username ) ) {
		$validation_error->add( 'gamertag_taken', __( 'Dieser Gamertag wird bereits verwendet.', 'globalkeys' ) );
	}
	if ( ! empty( $email ) && globalkeys_email_is_taken( $email ) ) {
		$validation_error->add( 'email_taken', __( 'Diese E-Mail-Adresse wird bereits verwendet.', 'globalkeys' ) );
	}
	if ( ! empty( $password ) ) {
		$pw_error = globalkeys_validate_password( $password );
		if ( is_wp_error( $pw_error ) ) {
			$validation_error->add( $pw_error->get_error_code(), $pw_error->get_error_message() );
		}
	}
	return $validation_error;
}

/**
 * Passwort-Anforderungen prüfen: mind. 8 Zeichen, 1 Zahl, 1 Großbuchstabe.
 *
 * @param string $password Passwort.
 * @return true|WP_Error True wenn gültig, sonst WP_Error.
 */
function globalkeys_validate_password( $password ) {
	if ( strlen( $password ) < 8 ) {
		return new WP_Error( 'password_too_short', __( 'Das Passwort muss mindestens 8 Zeichen lang sein.', 'globalkeys' ) );
	}
	if ( ! preg_match( '/[0-9]/', $password ) ) {
		return new WP_Error( 'password_no_number', __( 'Das Passwort muss mindestens eine Zahl enthalten.', 'globalkeys' ) );
	}
	if ( ! preg_match( '/[A-Z]/', $password ) ) {
		return new WP_Error( 'password_no_uppercase', __( 'Das Passwort muss mindestens einen Großbuchstaben enthalten.', 'globalkeys' ) );
	}
	return true;
}

/**
 * Passwort-Hinweis an unsere Anforderungen anpassen.
 *
 * @param string $hint Aktueller Hinweis.
 * @return string
 */
function globalkeys_password_hint( $hint ) {
	return __( 'Mindestens 8 Zeichen, eine Zahl und ein Großbuchstabe.', 'globalkeys' );
}
add_filter( 'password_hint', 'globalkeys_password_hint' );
add_filter( 'woocommerce_process_registration_errors', 'globalkeys_validate_registration_gamertag', 10, 4 );

/**
 * Gamertag als User-Meta speichern (zusätzlich zum Benutzernamen).
 *
 * @param int   $customer_id   Kunden-ID.
 * @param array $customer_data Kundendaten.
 * @param bool  $password_gen  Passwort generiert.
 */
function globalkeys_save_registration_gamertag( $customer_id, $customer_data, $password_gen ) {
	$gamertag = ! empty( $customer_data['user_login'] ) ? $customer_data['user_login'] : '';
	if ( '' !== $gamertag ) {
		update_user_meta( $customer_id, 'gamertag', $gamertag );
	}
}
add_action( 'woocommerce_created_customer', 'globalkeys_save_registration_gamertag', 10, 3 );

/**
 * Login per Gamertag ermöglichen (wenn Gamertag in user_meta gespeichert ist).
 *
 * @param null|WP_User $user     Benutzer oder null.
 * @param string       $username Eingegebener Benutzername/Gamertag/E-Mail.
 * @param string       $password Passwort.
 * @return null|WP_User
 */
function globalkeys_authenticate_by_gamertag( $user, $username, $password ) {
	if ( $user instanceof WP_User ) {
		return $user;
	}
	if ( empty( $username ) || empty( $password ) ) {
		return $user;
	}
	// Prüfen, ob Eingabe ein Gamertag ist (nicht E-Mail, nicht existierender Login).
	if ( is_email( $username ) ) {
		return $user;
	}
	$user_by_login = get_user_by( 'login', $username );
	if ( $user_by_login ) {
		return $user;
	}
	// Nach Gamertag in user_meta suchen.
	$users = get_users(
		array(
			'meta_key'    => 'gamertag',
			'meta_value'  => $username,
			'number'      => 1,
			'count_total' => false,
		)
	);
	if ( ! empty( $users ) ) {
		return wp_authenticate_username_password( null, $users[0]->user_login, $password );
	}
	return $user;
}
add_filter( 'authenticate', 'globalkeys_authenticate_by_gamertag', 20, 3 );
