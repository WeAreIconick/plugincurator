<?php
/**
 * Uninstall script.
 *
 * @package Plugin_Curator
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Delete all plugin options.
delete_option( 'rfpm_remote_url' );
delete_option( 'rfpm_api_key' );
delete_option( 'rfpm_cache_duration' );
delete_option( 'rfpm_version' );

// Delete all transients.
delete_transient( 'rfpm_remote_slugs' );
delete_transient( 'rfpm_plugins_data' );
delete_transient( 'rfpm_plugins_partial' );

// Delete any cache timeout options.
delete_option( '_transient_timeout_rfpm_remote_slugs' );
delete_option( '_transient_timeout_rfpm_plugins_data' );
delete_option( '_transient_timeout_rfpm_plugins_partial' );
