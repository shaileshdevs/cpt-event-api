<?php
/**
 * Plugin Name: StoreApps Event
 * Plugin URI: https://example.com
 * Description: The plugin provides the APIs for Event.
 * Version: 1.0.0
 * Author: Shailesh
 * Text Domain: storeapps-event
 * Domain Path: /i18n/languages/
 * Requires at least: 6.0
 * Requires PHP: 7.3
 *
 * @package StoreApps
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'STOREAPPS_EVENT_PLUGIN_FILE' ) ) {
	define( 'STOREAPPS_EVENT_PLUGIN_FILE', __FILE__ );
}

// Include the main StoreApps Event class.
if ( ! class_exists( 'StoreApps\Init', false ) ) {
	require_once dirname( STOREAPPS_EVENT_PLUGIN_FILE ) . '/includes/class-init.php';
	new StoreApps\Init();
}
