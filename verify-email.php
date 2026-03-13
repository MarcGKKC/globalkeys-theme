<?php
/**
 * E-Mail-Verifizierung: 6-stelliger Code eingeben.
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gk_verify_token = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';
$gk_verify_data  = $gk_verify_token ? get_transient( 'gk_pending_verify_' . $gk_verify_token ) : false;
$gk_verify_valid = $gk_verify_data && is_array( $gk_verify_data ) && ( (int) ( $gk_verify_data['expires'] ?? 0 ) ) >= time();

if ( ! $gk_verify_valid ) {
	wp_safe_redirect( home_url( '/' ) );
	exit;
}

$gk_verify_error  = isset( $_GET['gk_error'] ) && 'code' === $_GET['gk_error'];
$gk_verify_resent  = isset( $_GET['gk_resent'] ) && '1' === $_GET['gk_resent'];

get_header();
?>
<main id="primary" class="site-main">
<div class="gk-account-split gk-verify-email-page">
	<div class="gk-account-form-col">
		<div class="gk-verify-box">
			<h2 class="gk-login-box-title"><?php esc_html_e( 'Verifiziere deine E-Mail', 'globalkeys' ); ?></h2>
			<p class="gk-verify-intro"><?php esc_html_e( 'Gib den 6-stelligen Code ein, den wir an deine E-Mail-Adresse gesendet haben, um dein Konto zu aktivieren.', 'globalkeys' ); ?></p>

			<?php if ( $gk_verify_error ) : ?>
				<p class="gk-verify-error" role="alert"><?php esc_html_e( 'Der eingegebene Code ist ungültig. Bitte versuche es erneut.', 'globalkeys' ); ?></p>
			<?php endif; ?>
			<?php if ( $gk_verify_resent ) : ?>
				<p class="gk-verify-success" role="status"><?php esc_html_e( 'Wir haben dir einen neuen Code per E-Mail gesendet. Bitte prüfe auch deinen Spam-Ordner.', 'globalkeys' ); ?></p>
			<?php endif; ?>

			<form method="post" class="gk-verify-form" id="gk-verify-form">
				<?php wp_nonce_field( 'gk_email_verify', 'gk_verify_submit' ); ?>
				<input type="hidden" name="gk_verify_token" value="<?php echo esc_attr( $gk_verify_token ); ?>" />

				<div class="gk-verify-code-inputs" role="group" aria-label="<?php esc_attr_e( '6-stelliger Bestätigungscode', 'globalkeys' ); ?>">
					<input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="gk-verify-digit" data-index="0" autocomplete="one-time-code" aria-label="<?php esc_attr_e( 'Ziffer 1', 'globalkeys' ); ?>" />
					<input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="gk-verify-digit" data-index="1" aria-label="<?php esc_attr_e( 'Ziffer 2', 'globalkeys' ); ?>" />
					<input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="gk-verify-digit" data-index="2" aria-label="<?php esc_attr_e( 'Ziffer 3', 'globalkeys' ); ?>" />
					<input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="gk-verify-digit" data-index="3" aria-label="<?php esc_attr_e( 'Ziffer 4', 'globalkeys' ); ?>" />
					<input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="gk-verify-digit" data-index="4" aria-label="<?php esc_attr_e( 'Ziffer 5', 'globalkeys' ); ?>" />
					<input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="gk-verify-digit" data-index="5" aria-label="<?php esc_attr_e( 'Ziffer 6', 'globalkeys' ); ?>" />
				</div>
				<input type="hidden" name="gk_verify_code" id="gk-verify-code-full" value="" />

				<p class="form-row gk-login-submit-row">
					<button type="submit" class="gk-btn-login" id="gk-verify-btn" disabled><?php esc_html_e( 'Konto aktivieren', 'globalkeys' ); ?></button>
				</p>
			</form>

			<p class="gk-verify-resend">
				<a href="<?php echo esc_url( add_query_arg( array( 'token' => $gk_verify_token, 'gk_resend' => '1' ), home_url( '/verify-email/' ) ) ); ?>" class="gk-verify-later-link"><?php esc_html_e( 'Code erneut senden', 'globalkeys' ); ?></a>
			</p>
			<div class="gk-divider-line-only"></div>
			<p class="gk-verify-later">
				<a href="<?php echo esc_url( add_query_arg( array( 'token' => $gk_verify_token, 'gk_skip' => '1' ), home_url( '/verify-email/' ) ) ); ?>" class="gk-verify-later-link"><?php esc_html_e( 'Konto später erstellen', 'globalkeys' ); ?></a>
			</p>
			<p class="gk-verify-later-hint"><?php esc_html_e( 'Du wirst zur Startseite weitergeleitet.', 'globalkeys' ); ?></p>
		</div>
	</div>
	<div class="gk-account-image-col">
		<div class="gk-account-image-placeholder" role="img" aria-label="<?php esc_attr_e( 'Decorative', 'globalkeys' ); ?>"></div>
	</div>
</div>

<script>
(function(){
	var form = document.getElementById('gk-verify-form');
	var digits = document.querySelectorAll('.gk-verify-digit');
	var fullInput = document.getElementById('gk-verify-code-full');
	var btn = document.getElementById('gk-verify-btn');

	function updateCode() {
		var code = Array.from(digits).map(function(d){ return d.value; }).join('');
		if (fullInput) fullInput.value = code;
		if (btn) btn.disabled = code.length !== 6;
	}

	function pasteHandler(e) {
		var paste = (e.clipboardData || window.clipboardData).getData('text');
		var num = paste.replace(/\D/g,'').slice(0,6);
		if (num.length > 0) {
			e.preventDefault();
			for (var i = 0; i < num.length && i < digits.length; i++) {
				digits[i].value = num[i];
			}
			var next = digits[num.length];
			if (next) next.focus(); else digits[5].focus();
			updateCode();
		}
	}

	digits.forEach(function(d, i) {
		d.addEventListener('input', function() {
			var v = this.value.replace(/\D/g,'').slice(-1);
			this.value = v;
			if (v && i < 5) digits[i+1].focus();
			updateCode();
		});
		d.addEventListener('keydown', function(e) {
			if (e.key === 'Backspace' && !this.value && i > 0) {
				digits[i-1].focus();
			}
		});
	});

	if (form) form.addEventListener('paste', pasteHandler);
	updateCode();
})();
</script>
</main>
<?php
get_footer();
