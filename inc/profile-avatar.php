<?php
/**
 * Custom Profile Picture – Upload & Anzeige
 *
 * @package globalkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', 'globalkeys_add_avatar_image_size' );
function globalkeys_add_avatar_image_size() {
	add_image_size( 'gk_avatar', 400, 400, array( 'center', 'bottom' ) );
}

/**
 * Avatar-URL mit Custom-Bild (user_meta) oder Gravatar-Fallback.
 *
 * @param int   $user_id User-ID.
 * @param int   $size    Bildgröße.
 * @return string
 */
/**
 * Cache-Buster für Profilbild-URLs (Browser/CDN nach Upload).
 *
 * @param int    $user_id User-ID.
 * @param string $url     Bild-URL.
 * @return string
 */
function globalkeys_avatar_url_with_cache_buster( $user_id, $url ) {
	$ver = (int) get_user_meta( $user_id, 'gk_avatar_ver', true );
	if ( $ver > 0 ) {
		$url = add_query_arg( 'gk_av', (string) $ver, $url );
	}
	return $url;
}

function globalkeys_get_user_avatar_url( $user_id, $size = 290 ) {
	$attach_id = get_user_meta( $user_id, 'custom_avatar_id', true );
	if ( $attach_id ) {
		$url = wp_get_attachment_image_url( (int) $attach_id, 'full' );
		if ( $url ) {
			return esc_url( globalkeys_avatar_url_with_cache_buster( (int) $user_id, $url ) );
		}
	}
	$custom = get_user_meta( $user_id, 'custom_avatar_url', true );
	if ( ! empty( $custom ) && filter_var( $custom, FILTER_VALIDATE_URL ) ) {
		$aid = attachment_url_to_postid( $custom );
		if ( $aid ) {
			$full_url = wp_get_attachment_image_url( $aid, 'full' );
			if ( $full_url ) {
				update_user_meta( $user_id, 'custom_avatar_id', $aid );
				return esc_url( globalkeys_avatar_url_with_cache_buster( (int) $user_id, $full_url ) );
			}
		}
		return esc_url( globalkeys_avatar_url_with_cache_buster( (int) $user_id, $custom ) );
	}
	return get_avatar_url( $user_id, array( 'size' => $size ) );
}

/**
 * get_avatar_url überschreiben, damit Header etc. Custom-Avatar nutzen.
 */
function globalkeys_avatar_url_filter( $url, $id_or_email, $args ) {
	$user = false;
	if ( is_numeric( $id_or_email ) ) {
		$user = get_user_by( 'id', (int) $id_or_email );
	} elseif ( $id_or_email instanceof WP_User ) {
		$user = $id_or_email;
	} elseif ( $id_or_email instanceof WP_Post ) {
		$user = get_user_by( 'id', (int) $id_or_email->post_author );
	} elseif ( $id_or_email instanceof WP_Comment ) {
		// WooCommerce-Bewertungen rufen get_avatar( $comment ) auf – ohne diesen Zweig bliebe Gravatar.
		if ( ! empty( $id_or_email->user_id ) ) {
			$user = get_user_by( 'id', (int) $id_or_email->user_id );
		}
		if ( ( ! $user || ! $user->ID ) && ! empty( $id_or_email->comment_author_email ) && is_email( $id_or_email->comment_author_email ) ) {
			$user = get_user_by( 'email', $id_or_email->comment_author_email );
		}
	} elseif ( is_string( $id_or_email ) && is_email( $id_or_email ) ) {
		$user = get_user_by( 'email', $id_or_email );
	}
	if ( $user && $user->ID ) {
		$attach_id = get_user_meta( $user->ID, 'custom_avatar_id', true );
		if ( $attach_id ) {
			$custom_url = wp_get_attachment_image_url( (int) $attach_id, 'full' );
			if ( $custom_url ) {
				return esc_url( globalkeys_avatar_url_with_cache_buster( (int) $user->ID, $custom_url ) );
			}
		}
		$custom = get_user_meta( $user->ID, 'custom_avatar_url', true );
		if ( ! empty( $custom ) && filter_var( $custom, FILTER_VALIDATE_URL ) ) {
			return esc_url( globalkeys_avatar_url_with_cache_buster( (int) $user->ID, $custom ) );
		}
	}
	return $url;
}
add_filter( 'get_avatar_url', 'globalkeys_avatar_url_filter', 10, 3 );

/**
 * AJAX: Profilbild hochladen.
 */
function globalkeys_ajax_upload_profile_avatar() {
	check_ajax_referer( 'gk_upload_avatar', 'nonce' );
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Nicht angemeldet.', 'globalkeys' ) ) );
	}
	if ( empty( $_FILES['avatar'] ) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK ) {
		wp_send_json_error( array( 'message' => __( 'Keine Datei ausgewählt oder Upload-Fehler.', 'globalkeys' ) ) );
	}
	$file = $_FILES['avatar'];
	$allowed = array( 'image/jpeg', 'image/png', 'image/gif', 'image/webp' );
	$finfo   = wp_check_filetype( $file['name'], null );
	if ( ! $finfo['type'] || ! in_array( $finfo['type'], $allowed, true ) ) {
		wp_send_json_error( array( 'message' => __( 'Nur JPG, PNG, GIF oder WebP erlaubt.', 'globalkeys' ) ) );
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	$overrides = array(
		'test_form' => false,
		'mimes'     => array(
			'jpg|jpeg|jpe' => 'image/jpeg',
			'png'          => 'image/png',
			'gif'          => 'image/gif',
			'webp'         => 'image/webp',
		),
	);
	$upload = wp_handle_upload( $file, $overrides );
	if ( isset( $upload['error'] ) ) {
		wp_send_json_error( array( 'message' => $upload['error'] ) );
	}
	$attachment = array(
		'post_mime_type' => $upload['type'],
		'post_title'     => sanitize_file_name( $file['name'] ),
		'post_content'   => '',
		'post_status'    => 'inherit',
	);
	$attach_id = wp_insert_attachment( $attachment, $upload['file'] );
	if ( is_wp_error( $attach_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Fehler beim Speichern.', 'globalkeys' ) ) );
	}
	$attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
	wp_update_attachment_metadata( $attach_id, $attach_data );
	$url = wp_get_attachment_image_url( $attach_id, 'full' );
	if ( ! $url ) {
		$url = $upload['url'];
	}
	$uid = get_current_user_id();
	update_user_meta( $uid, 'custom_avatar_id', $attach_id );
	update_user_meta( $uid, 'custom_avatar_url', $url );
	update_user_meta( $uid, 'gk_avatar_ver', time() );
	$url_busted = globalkeys_avatar_url_with_cache_buster( $uid, $url );
	wp_send_json_success( array( 'url' => $url_busted ) );
}
add_action( 'wp_ajax_gk_upload_profile_avatar', 'globalkeys_ajax_upload_profile_avatar' );
