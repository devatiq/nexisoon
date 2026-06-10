<?php
/**
 * Asset registration.
 *
 * @package NexiSoon
 */

defined( 'ABSPATH' ) || exit;

/**
 * NexiSoon assets controller.
 */
class NexiSoon_Assets {
	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Enqueue assets for the NexiSoon settings screen.
	 *
	 * @param string $hook_suffix Current admin page hook.
	 * @return void
	 */
	public function enqueue_admin_assets( $hook_suffix ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Used only to scope admin asset loading.
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

		$is_nexisoon_screen = in_array( $hook_suffix, array( 'toplevel_page_nexisoon', 'nexisoon_page_nexisoon-settings' ), true ) || in_array( $page, array( 'nexisoon', 'nexisoon-settings' ), true );

		if ( ! $is_nexisoon_screen ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_style(
			'nexisoon-admin',
			NEXISOON_PLUGIN_URL . 'assets/admin/css/admin.css',
			array( 'wp-color-picker' ),
			NEXISOON_VERSION
		);

		wp_enqueue_script(
			'nexisoon-admin',
			NEXISOON_PLUGIN_URL . 'assets/admin/js/admin.js',
			array( 'jquery', 'wp-color-picker' ),
			NEXISOON_VERSION,
			true
		);

		wp_localize_script(
			'nexisoon-admin',
			'NexiSoonAdmin',
			array(
				'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
				'selectLogo'    => __( 'Select logo', 'nexisoon' ),
				'useLogo'       => __( 'Use this logo', 'nexisoon' ),
				'selectFavicon' => __( 'Select favicon', 'nexisoon' ),
				'useFavicon'    => __( 'Use this favicon', 'nexisoon' ),
				'saving'        => __( 'Saving...', 'nexisoon' ),
				'saved'         => __( 'Settings saved.', 'nexisoon' ),
				'error'         => __( 'Unable to save settings. Please try again.', 'nexisoon' ),
				'empty'         => __( 'Not selected', 'nexisoon' ),
				'logo'          => __( 'Logo', 'nexisoon' ),
				'saveSettings'  => __( 'Save Settings', 'nexisoon' ),
				'active'        => __( 'Active', 'nexisoon' ),
				'disabled'      => __( 'Disabled', 'nexisoon' ),
				'selected'      => __( 'Selected', 'nexisoon' ),
				'selectTemplate' => __( 'Select template', 'nexisoon' ),
			)
		);
	}

	/**
	 * Return frontend stylesheet URL.
	 *
	 * @return string
	 */
	public function get_frontend_style_url() {
		return NEXISOON_PLUGIN_URL . 'assets/frontend/css/frontend.css';
	}

	/**
	 * Return countdown script URL.
	 *
	 * @return string
	 */
	public function get_countdown_script_url() {
		return NEXISOON_PLUGIN_URL . 'assets/frontend/js/countdown.js';
	}
}
