<?php
/**
 * Asset registration.
 *
 * @package NexiSoon
 */

defined('ABSPATH') || exit;

/**
 * NexiSoon assets controller.
 */
class NexiSoon_Assets
{
	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public function hooks()
	{
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
	}

	/**
	 * Enqueue assets for the NexiSoon settings screen.
	 *
	 * @param string $hook_suffix Current admin page hook.
	 * @return void
	 */
	public function enqueue_admin_assets($hook_suffix)
	{
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Used only to scope admin asset loading.
		$page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';

		$is_nexisoon_screen = in_array($hook_suffix, array('toplevel_page_nexisoon', 'nexisoon_page_nexisoon-settings'), true) || in_array($page, array('nexisoon', 'nexisoon-settings'), true);

		if (!$is_nexisoon_screen) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style('wp-color-picker');

		wp_enqueue_style(
			'nexisoon-admin',
			NEXISOON_PLUGIN_URL . 'assets/admin/css/admin.css',
			array('wp-color-picker'),
			NEXISOON_VERSION
		);

		wp_enqueue_script(
			'nexisoon-admin',
			NEXISOON_PLUGIN_URL . 'assets/admin/js/admin.js',
			array('jquery', 'wp-color-picker'),
			NEXISOON_VERSION,
			true
		);

		wp_localize_script(
			'nexisoon-admin',
			'NexiSoonAdmin',
			array(
				'ajaxUrl' => admin_url('admin-ajax.php'),
				'selectLogo' => esc_html__('Select logo', 'nexisoon'),
				'useLogo' => esc_html__('Use this logo', 'nexisoon'),
				'selectFavicon' => esc_html__('Select favicon', 'nexisoon'),
				'useFavicon' => esc_html__('Use this favicon', 'nexisoon'),
				'saving' => esc_html__('Saving...', 'nexisoon'),
				'saved' => esc_html__('Settings saved.', 'nexisoon'),
				'error' => esc_html__('Unable to save settings. Please try again.', 'nexisoon'),
				'empty' => esc_html__('Not selected', 'nexisoon'),
				'logo' => esc_html__('Logo', 'nexisoon'),
				'saveSettings' => esc_html__('Save Settings', 'nexisoon'),
				'active' => esc_html__('Active', 'nexisoon'),
				'disabled' => esc_html__('Disabled', 'nexisoon'),
				'selected' => esc_html__('Selected', 'nexisoon'),
				'selectTemplate' => esc_html__('Select template', 'nexisoon'),
			)
		);
	}

	/**
	 * Return frontend stylesheet URL.
	 *
	 * @return string
	 */
	public function get_frontend_style_url()
	{
		return NEXISOON_PLUGIN_URL . 'assets/frontend/css/frontend.css';
	}

	/**
	 * Return countdown script URL.
	 *
	 * @return string
	 */
	public function get_countdown_script_url()
	{
		return NEXISOON_PLUGIN_URL . 'assets/frontend/js/countdown.js';
	}
}
