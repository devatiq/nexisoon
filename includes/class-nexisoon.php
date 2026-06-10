<?php
/**
 * Main plugin coordinator.
 *
 * @package NexiSoon
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main NexiSoon plugin class.
 */
class NexiSoon {
	/**
	 * Singleton instance.
	 *
	 * @var NexiSoon|null
	 */
	private static $instance = null;

	/**
	 * Settings model.
	 *
	 * @var NexiSoon_Settings
	 */
	private $settings;

	/**
	 * Assets controller.
	 *
	 * @var NexiSoon_Assets
	 */
	private $assets;

	/**
	 * Admin controller.
	 *
	 * @var NexiSoon_Admin
	 */
	private $admin;

	/**
	 * Frontend controller.
	 *
	 * @var NexiSoon_Frontend
	 */
	private $frontend;

	/**
	 * Return singleton instance.
	 *
	 * @return NexiSoon
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Activation callback.
	 *
	 * @return void
	 */
	public static function activate() {
		if ( false === get_option( NexiSoon_Settings::OPTION_NAME, false ) ) {
			add_option( NexiSoon_Settings::OPTION_NAME, NexiSoon_Settings::get_defaults() );
		}
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->settings = new NexiSoon_Settings();
		$this->assets   = new NexiSoon_Assets();
		$this->admin    = new NexiSoon_Admin( $this->settings );
		$this->frontend = new NexiSoon_Frontend( $this->settings, $this->assets );

		$this->hooks();
	}

	/**
	 * Register controller hooks.
	 *
	 * @return void
	 */
	private function hooks() {
		$this->assets->hooks();
		$this->admin->hooks();
		$this->frontend->hooks();
	}
}
