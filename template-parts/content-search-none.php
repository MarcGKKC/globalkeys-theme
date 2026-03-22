<?php
/**
 * Template part for displaying "no results" on product search page
 *
 * @package globalkeys
 */
?>
<div class="gk-search-no-results-inner">
	<svg class="gk-search-no-results-icon" xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
		<circle cx="11" cy="11" r="8"></circle>
		<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
	</svg>
	<h2 class="gk-search-no-results-title"><?php esc_html_e( 'No result found', 'globalkeys' ); ?></h2>
	<p class="gk-search-no-results-text"><?php esc_html_e( 'No products match your search', 'globalkeys' ); ?></p>
</div>
