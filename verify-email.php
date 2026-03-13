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
<div class="gk-verify-logo" aria-hidden="true">
	<?php
	$logo_url = '';
	if ( has_custom_logo() ) {
		$logo_src = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), 'full' );
		$logo_url = is_array( $logo_src ) && ! empty( $logo_src[0] ) ? $logo_src[0] : '';
	}
	if ( $logo_url ) : ?>
		<img src="<?php echo esc_url( $logo_url ); ?>" alt="" class="gk-verify-logo-img" width="180" height="36" />
	<?php else : ?>
		<img src="<?php echo esc_url( get_template_directory_uri() . '/Pictures/GlobalKeysOriginalLogo-gk.svg' ); ?>" alt="" class="gk-verify-logo-img" width="180" height="36" />
	<?php endif; ?>
</div>
<main id="primary" class="site-main gk-verify-main">
	<div class="gk-verify-centering">
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

				<label class="gk-verify-code-label" for="gk-verify-digit-0"><?php esc_html_e( '6-stelliger Code', 'globalkeys' ); ?></label>
				<div class="gk-verify-code-wrap">
				<div class="gk-verify-code-inputs" role="group" aria-label="<?php esc_attr_e( '6-stelliger Bestätigungscode', 'globalkeys' ); ?>">
					<input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="gk-verify-digit" id="gk-verify-digit-0" data-index="0" autocomplete="one-time-code" aria-label="<?php esc_attr_e( 'Ziffer 1', 'globalkeys' ); ?>" />
					<input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="gk-verify-digit" data-index="1" aria-label="<?php esc_attr_e( 'Ziffer 2', 'globalkeys' ); ?>" />
					<input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="gk-verify-digit" data-index="2" aria-label="<?php esc_attr_e( 'Ziffer 3', 'globalkeys' ); ?>" />
					<span class="gk-verify-digit-sep" aria-hidden="true"></span>
					<input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="gk-verify-digit" data-index="3" aria-label="<?php esc_attr_e( 'Ziffer 4', 'globalkeys' ); ?>" />
					<input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="gk-verify-digit" data-index="4" aria-label="<?php esc_attr_e( 'Ziffer 5', 'globalkeys' ); ?>" />
					<input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="gk-verify-digit" data-index="5" aria-label="<?php esc_attr_e( 'Ziffer 6', 'globalkeys' ); ?>" />
				</div>
				</div>
				<input type="hidden" name="gk_verify_code" id="gk-verify-code-full" value="" />

				<p class="form-row gk-login-submit-row">
					<button type="submit" class="gk-btn-login" id="gk-verify-btn" disabled><?php esc_html_e( 'Konto aktivieren', 'globalkeys' ); ?></button>
				</p>
			</form>

			<div class="gk-verify-resend-wrap">
				<a href="<?php echo esc_url( add_query_arg( array( 'token' => $gk_verify_token, 'gk_resend' => '1' ), home_url( '/verify-email/' ) ) ); ?>" class="gk-verify-later-link"><?php esc_html_e( 'Code erneut senden', 'globalkeys' ); ?></a>
				<span class="gk-verify-resend-sep" aria-hidden="true"></span>
				<a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) . '#register' : home_url( '/my-account/#register' ) ); ?>" class="gk-verify-later-link"><?php esc_html_e( 'E-Mail ändern', 'globalkeys' ); ?></a>
			</div>
			<div class="gk-divider-line-only"></div>
			<p class="gk-verify-later">
				<a href="<?php echo esc_url( add_query_arg( array( 'token' => $gk_verify_token, 'gk_skip' => '1' ), home_url( '/verify-email/' ) ) ); ?>" class="gk-verify-later-link"><?php esc_html_e( 'Konto später erstellen', 'globalkeys' ); ?></a>
			</p>
			<p class="gk-verify-later-hint"><?php esc_html_e( 'Du wirst zur Startseite weitergeleitet.', 'globalkeys' ); ?></p>
		</div>
	</div>
</main>

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
<?php
get_footer();
