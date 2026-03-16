<?php
/**
 * Login Form
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_customer_login_form' );

$gk_login_error    = get_transient( 'gk_login_error' );
$gk_register_error = get_transient( 'gk_register_error' );
$gk_login_username = '';
if ( $gk_login_error ) {
	delete_transient( 'gk_login_error' );
	$kept = get_transient( 'gk_login_keep_username' );
	if ( false !== $kept ) {
		$gk_login_username = $kept;
		delete_transient( 'gk_login_keep_username' );
	}
}
if ( $gk_register_error ) {
	delete_transient( 'gk_register_error' );
}
?>
<script>
window.gkTogglePassword=function(id){var i=document.getElementById(id);if(!i)return;var w=i.closest('.gk-password-input-wrap');if(!w)return;var t=w.querySelector('.gk-password-toggle'),o=t&&t.querySelector('.gk-eye-open'),c=t&&t.querySelector('.gk-eye-closed');if(!t||!o||!c)return;var p=i.type==='password';i.type=p?'text':'password';o.style.display=p?'block':'none';c.style.display=p?'none':'block';};
document.addEventListener('click',function(e){var b=e.target.closest('.gk-password-toggle');if(!b)return;var id=b.getAttribute('data-target');if(id&&window.gkTogglePassword){e.preventDefault();e.stopPropagation();window.gkTogglePassword(id);}},true);
</script>
<div class="gk-account-split">

	<div class="gk-account-form-col">

		<div class="gk-account-blocks" id="customer_login">

			<div class="gk-login-block" id="gk-login-block">
				<div class="gk-login-box">
					<h2 class="gk-login-box-title"><?php esc_html_e( 'Login', 'globalkeys' ); ?></h2>

					<form class="woocommerce-form woocommerce-form-login login" method="post" novalidate>
						<div class="gk-social-placeholders">
							<span class="gk-social-placeholder" aria-hidden="true"></span>
							<span class="gk-social-placeholder" aria-hidden="true"></span>
							<span class="gk-social-placeholder" aria-hidden="true"></span>
							<span class="gk-social-placeholder" aria-hidden="true"></span>
						</div>

						<div class="gk-divider-oder">
							<span class="gk-divider-line"></span>
							<span class="gk-divider-text"><?php esc_html_e( 'oder', 'globalkeys' ); ?></span>
							<span class="gk-divider-line"></span>
						</div>

						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide gk-login-row">
							<label for="username"><?php esc_html_e( 'Gamertag oder E-Mail', 'globalkeys' ); ?></label>
							<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="nope" placeholder="globalkeys@games.co" value="<?php echo esc_attr( $gk_login_username ); ?>" required aria-required="true" /><?php // phpcs:ignore WordPress.Security.NonceVerification.Missing ?>
						</p>
						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide gk-login-row gk-password-wrap">
							<label for="password"><?php esc_html_e( 'Password', 'woocommerce' ); ?></label>
							<span class="gk-password-input-wrap">
								<input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" placeholder="********" required aria-required="true" />
								<button type="button" class="gk-password-toggle" aria-label="<?php esc_attr_e( 'Passwort ein-/ausblenden', 'globalkeys' ); ?>" data-target="password">
									<svg class="gk-eye-open" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" style="display:none;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
									<svg class="gk-eye-closed" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
								</button>
							</span>
							<small class="gk-password-hint" aria-live="polite"><?php echo esc_html( wp_get_password_hint() ?: __( 'Mindestens 8 Zeichen, eine Zahl und ein Großbuchstabe.', 'globalkeys' ) ); ?></small>
						</p>

						<?php do_action( 'woocommerce_login_form' ); ?>

						<div class="gk-divider-line-only"></div>

						<p class="form-row gk-login-submit-row">
							<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
							<input type="hidden" name="rememberme" value="forever" />
							<button type="submit" class="woocommerce-button button woocommerce-form-login__submit gk-btn-login<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>" disabled><?php esc_html_e( 'Anmelden', 'globalkeys' ); ?></button>
						</p>
						<p class="gk-login-links-row">
							<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" class="woocommerce-LostPassword lost_password"><?php esc_html_e( 'Passwort vergessen?', 'globalkeys' ); ?></a>
						</p>
						<?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>
						<div class="gk-divider-oder gk-divider-register">
							<span class="gk-divider-line"></span>
							<span class="gk-divider-text"><?php esc_html_e( 'Noch kein Konto?', 'globalkeys' ); ?></span>
							<span class="gk-divider-line"></span>
						</div>
						<p class="form-row gk-register-btn-row">
							<a href="#register" class="gk-btn-register gk-toggle-register" data-gk-view="register"><?php esc_html_e( 'Konto erstellen', 'globalkeys' ); ?></a>
						</p>
						<?php endif; ?>

						<?php do_action( 'woocommerce_login_form_end' ); ?>

					</form>
				</div>
			</div>

<?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>

			<div class="gk-register-block" id="gk-register-block">
				<div class="gk-login-box">
					<h2 class="gk-login-box-title"><?php esc_html_e( 'Registrieren', 'globalkeys' ); ?></h2>

					<form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?>>

						<?php do_action( 'woocommerce_register_form_start' ); ?>

						<?php
						// Gamertag-Feld wird von inc/woocommerce-registration.php (woocommerce_register_form_start) ergänzt.
						?>

						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide gk-login-row gk-email-row">
						<span id="gk-email-error" class="gk-gamertag-error" role="alert" aria-live="polite" style="display:none;"></span>
						<label for="reg_email"><?php esc_html_e( 'E-Mail-Adresse', 'globalkeys' ); ?>&nbsp;<span class="required">*</span></label>
						<?php
						$reg_email = ! empty( $_POST['email'] ) ? wp_unslash( $_POST['email'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
						if ( '' === $reg_email ) {
							$kept = get_transient( 'gk_register_keep_email' );
							if ( false !== $kept ) {
								$reg_email = $kept;
								delete_transient( 'gk_register_keep_email' );
							}
						}
						?>
						<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" placeholder="globalkeys@games.co" value="<?php echo esc_attr( $reg_email ); ?>" required aria-required="true" /><?php // phpcs:ignore WordPress.Security.NonceVerification.Missing ?>
					</p>

						<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide gk-login-row gk-password-wrap">
							<label for="reg_password"><?php esc_html_e( 'Passwort', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
							<span class="gk-password-input-wrap">
								<input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="reg_password" autocomplete="new-password" placeholder="********" required aria-required="true" />
								<button type="button" class="gk-password-toggle" aria-label="<?php esc_attr_e( 'Passwort ein-/ausblenden', 'globalkeys' ); ?>" data-target="reg_password">
									<svg class="gk-eye-open" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" style="display:none;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
									<svg class="gk-eye-closed" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
								</button>
							</span>
							<small class="gk-password-hint" aria-live="polite"><?php echo esc_html( wp_get_password_hint() ?: __( 'Mindestens 8 Zeichen, eine Zahl und ein Großbuchstabe.', 'globalkeys' ) ); ?></small>
						</p>
						<?php endif; ?>

						<?php do_action( 'woocommerce_register_form' ); ?>

						<div class="gk-divider-line-only"></div>

						<p class="form-row gk-login-submit-row">
							<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
							<button type="submit" id="gk-register-submit" class="woocommerce-Button woocommerce-button button gk-btn-login woocommerce-form-register__submit" name="register" value="<?php esc_attr_e( 'Registrieren', 'woocommerce' ); ?>" disabled><?php esc_html_e( 'Registrieren', 'globalkeys' ); ?></button>
						</p>

						<div class="gk-divider-oder gk-divider-register">
							<span class="gk-divider-line"></span>
							<span class="gk-divider-text"><?php esc_html_e( 'Schon einen Account?', 'globalkeys' ); ?></span>
							<span class="gk-divider-line"></span>
						</div>
						<p class="form-row gk-register-btn-row">
							<a href="#login" class="gk-btn-register gk-toggle-login" data-gk-view="login"><?php esc_html_e( 'Einloggen', 'globalkeys' ); ?></a>
						</p>

						<?php do_action( 'woocommerce_register_form_end' ); ?>

					</form>
				</div>
			</div>

<?php endif; ?>

		</div><!-- .gk-account-blocks -->

	</div><!-- .gk-account-form-col -->

	<div class="gk-account-image-col">
		<div class="gk-account-image-placeholder" role="img" aria-label="<?php esc_attr_e( 'Decorative', 'globalkeys' ); ?>"></div>
		<?php
		$gk_login_video = get_theme_mod( 'gk_login_video_url', get_template_directory_uri() . '/Previews/arc-raiders-pc-steam-preview.webm' );
		if ( $gk_login_video ) :
			$gk_video_ext = strtolower( pathinfo( parse_url( $gk_login_video, PHP_URL_PATH ), PATHINFO_EXTENSION ) );
			$gk_video_type = ( 'webm' === $gk_video_ext ) ? 'video/webm' : 'video/mp4';
			?>
		<video class="gk-account-login-video" autoplay loop muted playsinline aria-hidden="true">
			<source src="<?php echo esc_url( $gk_login_video ); ?>" type="<?php echo esc_attr( $gk_video_type ); ?>">
		</video>
			<?php
		endif;
		?>
	</div>

</div><!-- .gk-account-split -->

<script>
(function(){
	function gkUpdateRegisterBtn(){
		var btn=document.getElementById('gk-register-submit');
		if(!btn)return;
		var g=document.getElementById('reg_username'),e=document.getElementById('reg_email'),p=document.getElementById('reg_password'),t=document.getElementById('gk_agree_terms');
		var ok=(g&&g.value.trim().length>0)&&(e&&e.value.trim().length>0)&&(!p||p.value.trim().length>0)&&(!t||t.checked);
		btn.disabled=!ok;
		if(ok)btn.removeAttribute('disabled');
	}
	gkUpdateRegisterBtn();
	setInterval(gkUpdateRegisterBtn,150);
	var termsLabel=document.querySelector('.gk-terms-checkbox-label');
	var termsCb=document.getElementById('gk_agree_terms');
	if(termsLabel&&termsCb){
		termsLabel.addEventListener('mousedown',function(ev){
			if(ev.target.closest('a'))return;
			ev.preventDefault();
			ev.stopPropagation();
			termsCb.checked=!termsCb.checked;
			termsCb.dispatchEvent(new Event('change',{bubbles:true}));
		});
		termsLabel.addEventListener('click',function(ev){
			if(ev.target.closest('a'))return;
			ev.preventDefault();
			ev.stopPropagation();
		});
	}
})();
</script>
<?php
$gk_error_msg  = '';
$gk_error_type = '';
if ( ! empty( $gk_register_error ) ) {
	$gk_error_msg  = is_string( $gk_register_error ) ? wp_strip_all_tags( $gk_register_error ) : __( 'Bei der Registrierung ist ein Fehler aufgetreten.', 'globalkeys' );
	$gk_error_type = 'register';
} elseif ( ! empty( $gk_login_error ) ) {
	$gk_error_msg  = is_string( $gk_login_error ) ? wp_strip_all_tags( $gk_login_error ) : __( 'Ungültige E-Mail oder Passwort.', 'globalkeys' );
	$gk_error_type = 'login';
}
if ( ! empty( $gk_error_msg ) ) :
?>
<div id="gk-login-error-modal" class="gk-login-error-modal gk-login-error-modal--visible" role="alertdialog" aria-modal="true" aria-labelledby="gk-login-error-title"<?php echo $gk_error_type ? ' data-gk-error-type="' . esc_attr( $gk_error_type ) . '"' : ''; ?>>
	<div class="gk-login-error-modal__backdrop"></div>
	<div class="gk-login-error-modal__box">
		<button type="button" class="gk-login-error-modal__close" aria-label="<?php esc_attr_e( 'Schließen', 'globalkeys' ); ?>">&times;</button>
		<div class="gk-login-error-modal__icon" aria-hidden="true">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
		</div>
		<h2 id="gk-login-error-title" class="gk-login-error-modal__title"><?php echo esc_html( $gk_error_msg ); ?></h2>
		<button type="button" class="gk-login-error-modal__ok gk-btn-login"><?php esc_html_e( 'OK', 'globalkeys' ); ?></button>
	</div>
</div>
<?php endif; ?>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
