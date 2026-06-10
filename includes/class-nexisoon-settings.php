<?php
/**
 * Settings storage and sanitization.
 *
 * @package NexiSoon
 */

defined( 'ABSPATH' ) || exit;

/**
 * NexiSoon settings model.
 */
class NexiSoon_Settings {
	/**
	 * WordPress option name.
	 */
	const OPTION_NAME = 'nexisoon_settings';

	/**
	 * Legacy option name used for one-time migration.
	 */
	const LEGACY_OPTION_NAME = 'soonify_settings';

	/**
	 * Return default plugin settings.
	 *
	 * @return array
	 */
	public static function get_defaults() {
		return array(
			'enabled'             => 0,
			'mode'                => 'coming-soon',
			'template'            => 'classic-centered',
			'logo_id'             => 0,
			'favicon_id'          => 0,
			'seo_title'           => __( 'Coming Soon', 'nexisoon' ),
			'seo_description'     => __( 'Our new website is launching soon.', 'nexisoon' ),
			'heading'             => __( 'We are launching soon', 'nexisoon' ),
			'subheading'          => __( 'Our new website is on the way. Stay tuned for something amazing.', 'nexisoon' ),
			'countdown_enabled'       => 0,
			'countdown_datetime'      => '',
			'countdown_display_style' => 'boxes',
			'button_enabled'      => 0,
			'button_text'         => __( 'Learn More', 'nexisoon' ),
			'button_url'          => '',
			'background_color'    => '#111827',
			'text_color'          => '#ffffff',
			'button_color'        => '#2563eb',
			'footer_text'         => __( 'Powered by NexiSoon', 'nexisoon' ),
		);
	}

	/**
	 * Return supported mode labels.
	 *
	 * @return array
	 */
	public static function get_modes() {
		return array(
			'coming-soon'        => __( 'Coming Soon', 'nexisoon' ),
			'under-construction' => __( 'Under Construction', 'nexisoon' ),
			'maintenance'        => __( 'Maintenance Mode', 'nexisoon' ),
		);
	}

	/**
	 * Return built-in template labels.
	 *
	 * @return array
	 */
	public static function get_templates() {
		return array(
			'classic-centered' => __( 'Classic Centered', 'nexisoon' ),
			'minimal-launch'   => __( 'Minimal Launch', 'nexisoon' ),
			'bold-countdown'   => __( 'Bold Countdown', 'nexisoon' ),
		);
	}

	/**
	 * Migrate legacy settings when NexiSoon settings do not exist yet.
	 *
	 * @return void
	 */
	public static function migrate_legacy_settings() {
		if ( false !== get_option( self::OPTION_NAME, false ) ) {
			return;
		}

		$legacy_settings = get_option( self::LEGACY_OPTION_NAME, false );

		if ( is_array( $legacy_settings ) ) {
			$legacy_default_footer = 'Powered by ' . 'Soon' . 'ify';

			if ( isset( $legacy_settings['footer_text'] ) && $legacy_default_footer === $legacy_settings['footer_text'] ) {
				$legacy_settings['footer_text'] = __( 'Powered by NexiSoon', 'nexisoon' );
			}

			update_option( self::OPTION_NAME, $legacy_settings );
		}
	}

	/**
	 * Get merged settings.
	 *
	 * @return array
	 */
	public function get() {
		self::migrate_legacy_settings();

		$saved = get_option( self::OPTION_NAME, array() );

		if ( ! is_array( $saved ) ) {
			$saved = array();
		}

		return wp_parse_args( $saved, self::get_defaults() );
	}

	/**
	 * Save sanitized settings.
	 *
	 * @param array $settings Raw settings.
	 * @return array Sanitized settings.
	 */
	public function save( $settings ) {
		$sanitized = $this->sanitize( $settings );
		update_option( self::OPTION_NAME, $sanitized );

		return $sanitized;
	}

	/**
	 * Sanitize submitted settings.
	 *
	 * @param array $settings Raw settings.
	 * @return array
	 */
	public function sanitize( $settings ) {
		$defaults  = self::get_defaults();
		$current   = $this->get();
		$settings  = is_array( $settings ) ? $settings : array();
		$sanitized = array();
		$modes     = array_keys( self::get_modes() );
		$templates = array_keys( self::get_templates() );

		$sanitized['enabled'] = empty( $settings['enabled'] ) ? 0 : 1;

		$mode = isset( $settings['mode'] ) ? sanitize_key( $settings['mode'] ) : $current['mode'];
		$sanitized['mode'] = in_array( $mode, $modes, true ) ? $mode : $defaults['mode'];

		$template = isset( $settings['template'] ) ? sanitize_key( $settings['template'] ) : $current['template'];
		$sanitized['template'] = in_array( $template, $templates, true ) ? $template : $defaults['template'];

		$sanitized['logo_id']    = isset( $settings['logo_id'] ) ? absint( $settings['logo_id'] ) : 0;
		$sanitized['favicon_id'] = isset( $settings['favicon_id'] ) ? absint( $settings['favicon_id'] ) : 0;

		$sanitized['seo_title']       = isset( $settings['seo_title'] ) ? sanitize_text_field( $settings['seo_title'] ) : '';
		$sanitized['seo_description'] = isset( $settings['seo_description'] ) ? sanitize_textarea_field( $settings['seo_description'] ) : '';
		$sanitized['heading']         = isset( $settings['heading'] ) ? sanitize_text_field( $settings['heading'] ) : $defaults['heading'];
		$sanitized['subheading']      = isset( $settings['subheading'] ) ? sanitize_textarea_field( $settings['subheading'] ) : $defaults['subheading'];

		$sanitized['countdown_enabled']  = empty( $settings['countdown_enabled'] ) ? 0 : 1;
		$sanitized['countdown_datetime'] = isset( $settings['countdown_datetime'] ) ? $this->sanitize_datetime( $settings['countdown_datetime'] ) : '';

		$countdown_display_style = isset( $settings['countdown_display_style'] ) ? sanitize_key( $settings['countdown_display_style'] ) : $current['countdown_display_style'];
		$sanitized['countdown_display_style'] = in_array( $countdown_display_style, array( 'boxes', 'inline' ), true )
			? $countdown_display_style
			: $defaults['countdown_display_style'];

		$sanitized['button_enabled'] = empty( $settings['button_enabled'] ) ? 0 : 1;
		$sanitized['button_text']    = isset( $settings['button_text'] ) ? sanitize_text_field( $settings['button_text'] ) : $defaults['button_text'];
		$sanitized['button_url']     = isset( $settings['button_url'] ) ? esc_url_raw( $settings['button_url'] ) : '';

		$sanitized['background_color'] = isset( $settings['background_color'] ) ? nexisoon_sanitize_hex_color_or_default( $settings['background_color'], $defaults['background_color'] ) : $defaults['background_color'];
		$sanitized['text_color']       = isset( $settings['text_color'] ) ? nexisoon_sanitize_hex_color_or_default( $settings['text_color'], $defaults['text_color'] ) : $defaults['text_color'];
		$sanitized['button_color']     = isset( $settings['button_color'] ) ? nexisoon_sanitize_hex_color_or_default( $settings['button_color'], $defaults['button_color'] ) : $defaults['button_color'];

		$sanitized['footer_text'] = isset( $settings['footer_text'] ) ? sanitize_textarea_field( $settings['footer_text'] ) : $defaults['footer_text'];

		return $sanitized;
	}

	/**
	 * Normalize a datetime-local value.
	 *
	 * @param string $value Raw value.
	 * @return string
	 */
	private function sanitize_datetime( $value ) {
		$value = sanitize_text_field( $value );

		if ( '' === $value ) {
			return '';
		}

		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $value ) ) {
			return '';
		}

		return $value;
	}
}
