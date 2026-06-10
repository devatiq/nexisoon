<?php
/**
 * Plugin uninstall cleanup.
 *
 * @package NexiSoon
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'nexisoon_settings' );
