<?php
/**
 * Support Guides: Überthemen, volle Breite über dem Footer (Hintergrund wie Startseite).
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="gk-support-guides-band">
	<div class="gk-section-featured-inner gk-support-guides-band__inner">
		<section class="gk-support-guides" aria-labelledby="gk-support-guides-title">
			<h2 id="gk-support-guides-title" class="gk-support-guides__title"><?php esc_html_e( 'GlobalKeys Support Guides', 'globalkeys' ); ?></h2>
			<p class="gk-support-guides__lead"><?php esc_html_e( 'Find answers to common questions.', 'globalkeys' ); ?></p>
			<ul class="gk-support-guides__list" role="list">
				<li class="gk-support-guides__topic">
					<span class="gk-support-guides__topic-icon" aria-hidden="true">
						<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" focusable="false"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
					</span>
					<span class="gk-support-guides__topic-text">
						<span class="gk-support-guides__topic-title"><?php esc_html_e( 'General Information', 'globalkeys' ); ?></span>
						<span class="gk-support-guides__topic-count"><?php echo esc_html( sprintf( _n( '%d article', '%d articles', 25, 'globalkeys' ), 25 ) ); ?></span>
					</span>
					<span class="gk-support-guides__topic-arrow" aria-hidden="true">
						<svg class="gk-support-guides__chevron" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
					</span>
				</li>
				<li class="gk-support-guides__topic">
					<span class="gk-support-guides__topic-icon" aria-hidden="true">
						<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>
					</span>
					<span class="gk-support-guides__topic-text">
						<span class="gk-support-guides__topic-title"><?php esc_html_e( 'Pre-Order Information', 'globalkeys' ); ?></span>
						<span class="gk-support-guides__topic-count"><?php echo esc_html( sprintf( _n( '%d article', '%d articles', 4, 'globalkeys' ), 4 ) ); ?></span>
					</span>
					<span class="gk-support-guides__topic-arrow" aria-hidden="true">
						<svg class="gk-support-guides__chevron" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
					</span>
				</li>
				<li class="gk-support-guides__topic">
					<span class="gk-support-guides__topic-icon" aria-hidden="true">
						<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" focusable="false"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
					</span>
					<span class="gk-support-guides__topic-text">
						<span class="gk-support-guides__topic-title"><?php esc_html_e( 'Order And Payment Support', 'globalkeys' ); ?></span>
						<span class="gk-support-guides__topic-count"><?php echo esc_html( sprintf( _n( '%d article', '%d articles', 9, 'globalkeys' ), 9 ) ); ?></span>
					</span>
					<span class="gk-support-guides__topic-arrow" aria-hidden="true">
						<svg class="gk-support-guides__chevron" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
					</span>
				</li>
				<li class="gk-support-guides__topic">
					<span class="gk-support-guides__topic-icon" aria-hidden="true">
						<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
					</span>
					<span class="gk-support-guides__topic-text">
						<span class="gk-support-guides__topic-title"><?php esc_html_e( 'Account Related Information', 'globalkeys' ); ?></span>
						<span class="gk-support-guides__topic-count"><?php echo esc_html( sprintf( _n( '%d article', '%d articles', 13, 'globalkeys' ), 13 ) ); ?></span>
					</span>
					<span class="gk-support-guides__topic-arrow" aria-hidden="true">
						<svg class="gk-support-guides__chevron" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
					</span>
				</li>
				<li class="gk-support-guides__topic">
					<span class="gk-support-guides__topic-icon" aria-hidden="true">
						<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
					</span>
					<span class="gk-support-guides__topic-text">
						<span class="gk-support-guides__topic-title"><?php esc_html_e( 'Code Redemption Support', 'globalkeys' ); ?></span>
						<span class="gk-support-guides__topic-count"><?php echo esc_html( sprintf( _n( '%d article', '%d articles', 5, 'globalkeys' ), 5 ) ); ?></span>
					</span>
					<span class="gk-support-guides__topic-arrow" aria-hidden="true">
						<svg class="gk-support-guides__chevron" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
					</span>
				</li>
				<li class="gk-support-guides__topic">
					<span class="gk-support-guides__topic-icon" aria-hidden="true">
						<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="M4 7V5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v2M4 7h16M4 7v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7M9 12h6"/></svg>
					</span>
					<span class="gk-support-guides__topic-text">
						<span class="gk-support-guides__topic-title"><?php esc_html_e( 'Offers & Promotions', 'globalkeys' ); ?></span>
						<span class="gk-support-guides__topic-count"><?php echo esc_html( sprintf( _n( '%d article', '%d articles', 12, 'globalkeys' ), 12 ) ); ?></span>
					</span>
					<span class="gk-support-guides__topic-arrow" aria-hidden="true">
						<svg class="gk-support-guides__chevron" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
					</span>
				</li>
			</ul>

			<div class="gk-support-guides-cta-block">
				<div class="gk-support-guides-cta-rule" aria-hidden="true"></div>
				<div class="gk-support-guides-cta" aria-labelledby="gk-support-guides-cta-heading">
					<p id="gk-support-guides-cta-heading" class="gk-support-guides-cta__title">
						<span class="gk-support-guides-cta__title-line"><?php esc_html_e( "Can't find what you're", 'globalkeys' ); ?></span>
						<span class="gk-support-guides-cta__title-line"><?php esc_html_e( 'looking for?', 'globalkeys' ); ?></span>
					</p>
					<a class="gk-support-guides-cta__btn" href="#gk-support-request"><?php esc_html_e( 'Create a support request', 'globalkeys' ); ?></a>
				</div>
			</div>
		</section>
	</div>
</div>
