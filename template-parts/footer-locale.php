<?php
/**
 * Währung / Sprache / Land (wie Haupt-Footer).
 *
 * @package globalkeys
 *
 * Erwartet optional: `$gk_footer_locale_extra_class` (eine Zusatz-CSS-Klasse auf dem Root-Element).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gk_fl_extra = isset( $gk_footer_locale_extra_class ) ? trim( (string) $gk_footer_locale_extra_class ) : '';
$gk_fl_classes = array( 'gk-footer-locale' );
if ( $gk_fl_extra !== '' ) {
	$gk_fl_classes[] = sanitize_html_class( $gk_fl_extra );
}
?>
<div class="<?php echo esc_attr( implode( ' ', $gk_fl_classes ) ); ?>" role="group" aria-label="<?php esc_attr_e( 'Currency, language and country', 'globalkeys' ); ?>">
	<div class="gk-footer-locale-inner">
		<div class="gk-footer-locale-row">
			<button type="button" class="gk-footer-locale-btn">
				<svg class="gk-footer-locale-icon gk-footer-locale-icon--fill" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 48 48" fill="currentColor" aria-hidden="true">
					<path d="M44,7.1V14a2,2,0,0,1-2,2H35a2,2,0,0,1-2-2.3A2.1,2.1,0,0,1,35.1,12h2.3A18,18,0,0,0,6.1,22.2a2,2,0,0,1-2,1.8h0a2,2,0,0,1-2-2.2A22,22,0,0,1,40,8.9V7a2,2,0,0,1,2.3-2A2.1,2.1,0,0,1,44,7.1Z"/>
					<path d="M4,40.9V34a2,2,0,0,1,2-2h7a2,2,0,0,1,2,2.3A2.1,2.1,0,0,1,12.9,36H10.6A18,18,0,0,0,41.9,25.8a2,2,0,0,1,2-1.8h0a2,2,0,0,1,2,2.2A22,22,0,0,1,8,39.1V41a2,2,0,0,1-2.3,2A2.1,2.1,0,0,1,4,40.9Z"/>
					<path d="M24.7,22c-3.5-.7-3.5-1.3-3.5-1.8s.2-.6.5-.9a3.4,3.4,0,0,1,1.8-.4,6.3,6.3,0,0,1,3.3.9,1.8,1.8,0,0,0,2.7-.5,1.9,1.9,0,0,0-.4-2.8A9.1,9.1,0,0,0,26,15.3V13a2,2,0,0,0-4,0v2.2c-3,.5-5,2.5-5,5.2s3.3,4.9,6.5,5.5,3.3,1.3,3.3,1.8-1.1,1.4-2.5,1.4h0a6.7,6.7,0,0,1-4.1-1.3,2,2,0,0,0-2.8.6,1.8,1.8,0,0,0,.3,2.6A10.9,10.9,0,0,0,22,32.8V35a2,2,0,0,0,4,0V32.8a6.3,6.3,0,0,0,3-1.3,4.9,4.9,0,0,0,2-4h0C31,23.8,27.6,22.6,24.7,22Z"/>
				</svg>
				<?php esc_html_e( 'Currency', 'globalkeys' ); ?>
			</button>
			<button type="button" class="gk-footer-locale-sep" tabindex="-1" aria-hidden="true"></button>
			<button type="button" class="gk-footer-locale-btn">
				<span class="gk-footer-locale-icon gk-footer-locale-icon--lang-mask" aria-hidden="true"></span>
				<?php esc_html_e( 'Language', 'globalkeys' ); ?>
			</button>
			<button type="button" class="gk-footer-locale-sep" tabindex="-1" aria-hidden="true"></button>
			<button type="button" class="gk-footer-locale-btn">
				<svg class="gk-footer-locale-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
				<?php esc_html_e( 'Country', 'globalkeys' ); ?>
			</button>
		</div>
	</div>
</div>
