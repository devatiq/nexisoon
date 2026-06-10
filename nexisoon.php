<?php
/**
 * Plugin Name: NexiSoon – Coming Soon, Under Construction & Maintenance Mode
 * Plugin URI: https://github.com/devatiq/nexisoon
 * Description: Create a beautiful coming soon, under construction, or maintenance mode page for your WordPress website.
 * Version: 1.0.0
 * Author: Nexiby LLC
 * Author URI: https://nexiby.com
 * Text Domain: nexisoon
 * Domain Path: /languages
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package NexiSoon
 */

defined('ABSPATH') || exit;

define('NEXISOON_VERSION', '1.0.0');
define('NEXISOON_PLUGIN_FILE', __FILE__);
define('NEXISOON_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('NEXISOON_PLUGIN_URL', plugin_dir_url(__FILE__));
define('NEXISOON_PLUGIN_BASENAME', plugin_basename(__FILE__));

require_once NEXISOON_PLUGIN_DIR . 'includes/helpers.php';
require_once NEXISOON_PLUGIN_DIR . 'includes/class-nexisoon-settings.php';
require_once NEXISOON_PLUGIN_DIR . 'includes/class-nexisoon-assets.php';
require_once NEXISOON_PLUGIN_DIR . 'includes/class-nexisoon-admin.php';
require_once NEXISOON_PLUGIN_DIR . 'includes/class-nexisoon-frontend.php';
require_once NEXISOON_PLUGIN_DIR . 'includes/class-nexisoon.php';

register_activation_hook(__FILE__, array('NexiSoon', 'activate'));

/**
 * Load the plugin.
 *
 * @return void
 */
function nexisoon_init()
{
	NexiSoon::instance();
}
add_action('plugins_loaded', 'nexisoon_init');
