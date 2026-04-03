<?php
/**
 * Support-Nav-Section: Breite wie Community-Karte (.gk-pc-community__frame) via gleiches Grid wie .gk-pc-community__carousel-wrap.
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="gk-support-stage-outer">
	<div class="gk-support-stage-top-fill" aria-hidden="true"></div>
	<div class="gk-section-inner gk-section-featured-inner gk-support-featured">
		<div class="gk-support-community-carousel-mirror">
			<div class="gk-support-community-arrow-spacer" aria-hidden="true"></div>
			<div class="gk-support-stack">
				<section class="gk-support-hero" aria-labelledby="gk-support-hero-title">
					<h2 id="gk-support-hero-title" class="gk-support-hero__title"><?php esc_html_e( 'How can we help you?', 'globalkeys' ); ?></h2>
					<p class="gk-support-hero__lead"><?php esc_html_e( 'Answers to common questions — and help when you need it.', 'globalkeys' ); ?></p>
				</section>

				<div class="gk-support-panel-outer">
					<div class="gk-support-panel">
						<ul class="gk-support-cards">
							<li class="gk-support-card">
								<span class="gk-support-card__icon" aria-hidden="true">
									<svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6Z" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>
								</span>
								<h3 class="gk-support-card__title"><?php esc_html_e( 'Before you buy', 'globalkeys' ); ?></h3>
								<p class="gk-support-card__text"><?php esc_html_e( 'What to know for a smooth purchase on GlobalKeys.', 'globalkeys' ); ?></p>
							</li>
							<li class="gk-support-card">
								<span class="gk-support-card__icon" aria-hidden="true">
									<svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false"><path d="M21 12a9 9 0 0 1-9 9 9 9 0 0 1-6.36-2.64L3 17" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 12a9 9 0 0 1 15.36-6.36L21 7" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 17v4h4M21 7V3h-4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>
								</span>
								<h3 class="gk-support-card__title"><?php esc_html_e( 'Refunds', 'globalkeys' ); ?></h3>
								<p class="gk-support-card__text"><?php esc_html_e( 'Find out more about our refunds policy.', 'globalkeys' ); ?></p>
							</li>
							<li class="gk-support-card">
								<span class="gk-support-card__icon" aria-hidden="true">
									<svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false"><path d="M6 11h4M8 9v4M15 12h.01M18 10h.01M17.32 5H6.68a1 1 0 0 0-.98.8l-1.2 6A1 1 0 0 0 5.48 13H18.52a1 1 0 0 0 .98-1.2l-1.2-6a1 1 0 0 0-.98-.8Z" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 17h6" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/></svg>
								</span>
								<h3 class="gk-support-card__title"><?php esc_html_e( 'New to GlobalKeys?', 'globalkeys' ); ?></h3>
								<p class="gk-support-card__text"><?php esc_html_e( 'All you need to know about buying from GlobalKeys.', 'globalkeys' ); ?></p>
							</li>
						</ul>

						<span id="gk-support-request" class="gk-support-request-anchor" tabindex="-1"></span>
						<a class="gk-support-cta" href="#gk-support-request"><?php esc_html_e( 'Create a support request', 'globalkeys' ); ?></a>
					</div>
				</div>
			</div>
			<div class="gk-support-community-arrow-spacer" aria-hidden="true"></div>
		</div>
	</div>
</div>
