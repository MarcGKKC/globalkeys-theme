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

do_action( 'woocommerce_before_customer_login_form' ); ?>

<div class="gk-account-split">

	<div class="gk-account-form-col">

		<div class="gk-account-blocks" id="customer_login">

			<div class="gk-login-block" id="gk-login-block">
				<div class="gk-login-box">
					<h2 class="gk-login-box-title"><?php esc_html_e( 'Login', 'globalkeys' ); ?></h2>

					<div class="gk-social-placeholders">
						<span class="gk-social-placeholder"></span>
						<span class="gk-social-placeholder"></span>
						<span class="gk-social-placeholder"></span>
						<span class="gk-social-placeholder"></span>
					</div>

					<div class="gk-divider-oder">
						<span class="gk-divider-line"></span>
						<span class="gk-divider-text"><?php esc_html_e( 'oder', 'globalkeys' ); ?></span>
						<span class="gk-divider-line"></span>
					</div>

					<form class="woocommerce-form woocommerce-form-login login" method="post" novalidate>

						<?php do_action( 'woocommerce_login_form_start' ); ?>

						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide gk-login-row">
							<label for="username"><?php esc_html_e( 'Gamertag oder E-Mail', 'globalkeys' ); ?></label>
							<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" placeholder="<?php esc_attr_e( 'Gamertag oder E-Mail', 'globalkeys' ); ?>" value="<?php echo ( ! empty( $_POST['username'] ) && is_string( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required aria-required="true" /><?php // phpcs:ignore WordPress.Security.NonceVerification.Missing ?>
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
						</p>

						<?php do_action( 'woocommerce_login_form' ); ?>

						<div class="gk-divider-line-only"></div>

						<p class="form-row gk-login-submit-row">
							<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
							<input type="hidden" name="rememberme" value="forever" />
							<button type="submit" class="woocommerce-button button woocommerce-form-login__submit gk-btn-login<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>"><?php esc_html_e( 'Anmelden', 'globalkeys' ); ?></button>
						</p>
						<p class="gk-login-links-row">
							<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" class="woocommerce-LostPassword lost_password"><?php esc_html_e( 'Passwort vergessen?', 'globalkeys' ); ?></a>
						</p>
						<?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>
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
				<h2 class="gk-account-title"><?php esc_html_e( 'Registrieren', 'globalkeys' ); ?></h2>

				<form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?> >

					<?php do_action( 'woocommerce_register_form_start' ); ?>

					<?php
					// Gamertag-Feld wird von inc/woocommerce-registration.php (woocommerce_register_form_start) ergänzt.
					?>

					<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide gk-email-row">
						<span id="gk-email-error" class="gk-gamertag-error" role="alert" aria-live="polite" style="display:none;"></span>
						<label for="reg_email"><?php esc_html_e( 'E-Mail-Adresse', 'globalkeys' ); ?>&nbsp;<span class="required">*</span></label>
						<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" required aria-required="true" /><?php // phpcs:ignore WordPress.Security.NonceVerification.Missing ?>
					</p>

					<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
					<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
						<label for="reg_password"><?php esc_html_e( 'Passwort', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
						<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" required aria-required="true" />
					</p>
					<?php endif; ?>

					<?php do_action( 'woocommerce_register_form' ); ?>

					<p class="form-row">
						<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
						<button type="submit" class="woocommerce-Button woocommerce-button button gk-btn-primary woocommerce-form-register__submit" name="register" value="<?php esc_attr_e( 'Registrieren', 'woocommerce' ); ?>"><?php esc_html_e( 'Registrieren', 'woocommerce' ); ?></button>
					</p>

					<p class="gk-register-meta">
						<a href="#login" class="gk-toggle-login" data-gk-view="login"><?php esc_html_e( 'Bereits Account? Anmelden', 'globalkeys' ); ?></a>
					</p>

					<?php do_action( 'woocommerce_register_form_end' ); ?>

				</form>
			</div>

<?php endif; ?>

		</div><!-- .gk-account-blocks -->

	</div><!-- .gk-account-form-col -->

	<div class="gk-account-image-col">
		<div class="gk-account-image-placeholder" role="img" aria-label="<?php esc_attr_e( 'Decorative', 'globalkeys' ); ?>"></div>
	</div>

</div><!-- .gk-account-split -->

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
