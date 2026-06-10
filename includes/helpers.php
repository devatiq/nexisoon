<?php
/**
 * Shared helper functions.
 *
 * @package NexiSoon
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return a public attachment URL.
 *
 * @param int    $attachment_id Attachment ID.
 * @param string $size          Image size.
 * @return string
 */
function nexisoon_get_attachment_url( $attachment_id, $size = 'full' ) {
	$attachment_id = absint( $attachment_id );

	if ( ! $attachment_id ) {
		return '';
	}

	$url = wp_get_attachment_image_url( $attachment_id, $size );

	return $url ? $url : '';
}

/**
 * Determine whether the current request is for the REST API.
 *
 * @return bool
 */
function nexisoon_is_rest_request() {
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return true;
	}

	if ( isset( $_GET['rest_route'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return true;
	}

	$rest_prefix = function_exists( 'rest_get_url_prefix' ) ? rest_get_url_prefix() : 'wp-json';
	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

	return false !== strpos( $request_uri, '/' . trim( $rest_prefix, '/' ) . '/' );
}

/**
 * Return a valid hex color, falling back to a default value.
 *
 * @param string $color   Submitted color.
 * @param string $default Default color.
 * @return string
 */
function nexisoon_sanitize_hex_color_or_default( $color, $default ) {
	$color = sanitize_hex_color( $color );

	return $color ? $color : $default;
}

/**
 * Convert a template slug into a local template path.
 *
 * @param string $template Template slug.
 * @return string
 */
function nexisoon_get_template_path( $template ) {
	$template = sanitize_key( $template );
	$path     = NEXISOON_PLUGIN_DIR . 'templates/' . $template . '.php';

	if ( ! file_exists( $path ) ) {
		$path = NEXISOON_PLUGIN_DIR . 'templates/classic-centered.php';
	}

	return $path;
}
