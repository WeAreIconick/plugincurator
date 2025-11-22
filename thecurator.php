<?php
/**
 * Plugin Name: The Curator by Iconick
 * Description: Manage featured plugins list from a remote JSON file while pulling real data from WordPress.org
 * Version: 1.0.1
 * Author: iconick
 * Author URI: https://iconick.io
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: iconick-featured-curator
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 *
 * @package Plugin_Curator
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin version.
define( 'RFPM_VERSION', '1.0.1' );

// Plugin directory path.
define( 'RFPM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Plugin directory URL.
define( 'RFPM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Plugin basename.
define( 'RFPM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) ); // plugin-curator/plugin-curator.php

/**
 * Autoloader for plugin classes.
 *
 * @since 2.0.0
 *
 * @param string $class_name The fully-qualified class name.
 */
function rfpm_autoloader( $class_name ) {
    // Project-specific namespace prefix.
    $prefix = 'RFPM\\';

    // Base directory for the namespace prefix.
    $base_dir = RFPM_PLUGIN_DIR . 'includes/';

    // Does the class use the namespace prefix?
    $len = strlen( $prefix );
    if ( strncmp( $prefix, $class_name, $len ) !== 0 ) {
        return;
    }

    // Get the relative class name.
    $relative_class = substr( $class_name, $len );

    // Replace namespace separators with directory separators.
    $relative_class = strtolower( str_replace( '_', '-', $relative_class ) );
    
    // Build the file path.
    $file = $base_dir . 'class-' . $relative_class . '.php';

    // If the file exists, require it.
    if ( file_exists( $file ) ) {
        require $file;
    }
}

spl_autoload_register( 'rfpm_autoloader' );

/**
 * Initialize the plugin.
 *
 * @since 2.0.0
 */
function rfpm_init() {
    // Initialize the main plugin class.
    require_once RFPM_PLUGIN_DIR . 'includes/class-plugin.php';
    
    $plugin = new RFPM\Plugin();
    $plugin->init();
}

add_action( 'plugins_loaded', 'rfpm_init' );

/**
 * Activation hook.
 *
 * @since 2.0.0
 */
function rfpm_activate() {
    // Set default options on activation.
    add_option( 'rfpm_remote_url', 'https://yourserver.com/featured-plugins.json' );
    add_option( 'rfpm_api_key', '' );
    add_option( 'rfpm_cache_duration', 6 * HOUR_IN_SECONDS );
    add_option( 'rfpm_version', RFPM_VERSION );
    
    // Clear any existing cache.
    delete_transient( 'rfpm_remote_slugs' );
    delete_transient( 'rfpm_plugins_data' );
}

register_activation_hook( __FILE__, 'rfpm_activate' );

/**
 * Deactivation hook.
 *
 * @since 2.0.0
 */
function rfpm_deactivate() {
    // Clear all cached data.
    delete_transient( 'rfpm_remote_slugs' );
    delete_transient( 'rfpm_plugins_data' );
}

register_deactivation_hook( __FILE__, 'rfpm_deactivate' );

