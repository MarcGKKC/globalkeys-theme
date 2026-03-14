<?php
/**
 * globalkeys Theme Customizer
 *
 * @package globalkeys
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function globalkeys_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

	// Login-Seite: Hintergrund-Video im dunklen Bereich
	$wp_customize->add_section(
		'gk_login_video',
		array(
			'title'    => __( 'Login-Seite: Hintergrund-Video', 'globalkeys' ),
			'priority' => 130,
		)
	);
	$wp_customize->add_setting(
		'gk_login_video_url',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		'gk_login_video_url',
		array(
			'label'       => __( 'Video-URL (MP4)', 'globalkeys' ),
			'description' => __( 'URL zum MP4-Video für den dunklen Bereich rechts. Leer lassen für den Standard-Hintergrund.', 'globalkeys' ),
			'section'     => 'gk_login_video',
			'type'        => 'url',
		)
	);

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial(
			'blogname',
			array(
				'selector'        => '.site-title a',
				'render_callback' => 'globalkeys_customize_partial_blogname',
			)
		);
		$wp_customize->selective_refresh->add_partial(
			'blogdescription',
			array(
				'selector'        => '.site-description',
				'render_callback' => 'globalkeys_customize_partial_blogdescription',
			)
		);
	}
}
add_action( 'customize_register', 'globalkeys_customize_register' );

/**
 * Render the site title for the selective refresh partial.
 *
 * @return void
 */
function globalkeys_customize_partial_blogname() {
	bloginfo( 'name' );
}

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @return void
 */
function globalkeys_customize_partial_blogdescription() {
	bloginfo( 'description' );
}

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function globalkeys_customize_preview_js() {
	wp_enqueue_script( 'globalkeys-customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), _S_VERSION, true );
}
add_action( 'customize_preview_init', 'globalkeys_customize_preview_js' );
