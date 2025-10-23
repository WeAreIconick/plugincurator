<?php
/**
 * Main plugin class.
 *
 * @package Plugin_Curator
 * @since 1.0.0
 */

namespace RFPM;

/**
 * Main Plugin class.
 *
 * Coordinates all plugin components and initializes hooks.
 *
 * @since 2.0.0
 */
class Plugin {

    /**
     * Cache manager instance.
     *
     * @since 2.0.0
     * @var Cache_Manager
     */
    private $cache_manager;

    /**
     * Remote source instance.
     *
     * @since 2.0.0
     * @var Remote_Source
     */
    private $remote_source;

    /**
     * API client instance.
     *
     * @since 2.0.0
     * @var API_Client
     */
    private $api_client;

    /**
     * Plugin filter instance.
     *
     * @since 2.0.0
     * @var Plugin_Filter
     */
    private $plugin_filter;

    /**
     * Initialize the plugin.
     *
     * @since 2.0.0
     */
    public function init() {
        $this->load_dependencies();
        $this->init_components();
        $this->init_admin();
    }

    /**
     * Load required files.
     *
     * @since 2.0.0
     */
    private function load_dependencies() {
        require_once RFPM_PLUGIN_DIR . 'includes/class-cache-manager.php';
        require_once RFPM_PLUGIN_DIR . 'includes/class-remote-source.php';
        require_once RFPM_PLUGIN_DIR . 'includes/class-api-client.php';
        require_once RFPM_PLUGIN_DIR . 'includes/class-plugin-filter.php';
        require_once RFPM_PLUGIN_DIR . 'admin/class-admin-menu.php';
        require_once RFPM_PLUGIN_DIR . 'admin/class-settings.php';
    }

    /**
     * Initialize core components.
     *
     * @since 2.0.0
     */
    private function init_components() {
        $this->cache_manager = new Cache_Manager();
        $this->remote_source = new Remote_Source( $this->cache_manager );
        $this->api_client    = new API_Client( $this->cache_manager );
        $this->plugin_filter = new Plugin_Filter(
            $this->remote_source,
            $this->api_client,
            $this->cache_manager
        );

        $this->plugin_filter->init();
    }

    /**
     * Initialize admin components.
     *
     * @since 2.0.0
     */
    private function init_admin() {
        if ( ! is_admin() ) {
            return;
        }

        $settings   = new Settings();
        $admin_menu = new Admin_Menu( $settings, $this->cache_manager, $this->remote_source );

        $settings->init();
        $admin_menu->init();
    }

    /**
     * Get cache manager instance.
     *
     * @since 2.0.0
     * @return Cache_Manager
     */
    public function get_cache_manager() {
        return $this->cache_manager;
    }

    /**
     * Get remote source instance.
     *
     * @since 2.0.0
     * @return Remote_Source
     */
    public function get_remote_source() {
        return $this->remote_source;
    }

    /**
     * Get API client instance.
     *
     * @since 2.0.0
     * @return API_Client
     */
    public function get_api_client() {
        return $this->api_client;
    }
}

