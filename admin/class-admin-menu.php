<?php
/**
 * Admin Menu class.
 *
 * @package Plugin_Curator
 * @since 1.0.0
 */

namespace RFPM;

/**
 * Manages admin menu and pages.
 *
 * @since 2.0.0
 */
class Admin_Menu {

    /**
     * Settings instance.
     *
     * @since 2.0.0
     * @var Settings
     */
    private $settings;

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
     * Constructor.
     *
     * @since 2.0.0
     *
     * @param Settings      $settings Settings instance.
     * @param Cache_Manager $cache_manager Cache manager instance.
     * @param Remote_Source $remote_source Remote source instance.
     */
    public function __construct( Settings $settings, Cache_Manager $cache_manager, Remote_Source $remote_source ) {
        $this->settings      = $settings;
        $this->cache_manager = $cache_manager;
        $this->remote_source = $remote_source;
    }

    /**
     * Initialize admin hooks.
     *
     * @since 2.0.0
     */
    public function init() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_post_rfpm_refresh_cache', array( $this, 'handle_refresh' ) );
        add_action( 'admin_post_rfpm_test_connection', array( $this, 'handle_test_connection' ) );
        add_filter( 'plugin_action_links_' . RFPM_PLUGIN_BASENAME, array( $this, 'add_action_links' ) );
    }

    /**
     * Add admin menu page.
     *
     * @since 2.0.0
     */
    public function add_admin_menu() {
        add_management_page(
            __( 'Featured Plugins Manager', 'remote-featured-plugins' ),
            __( 'Featured Plugins', 'remote-featured-plugins' ),
            'manage_options',
            'rfpm-settings',
            array( $this, 'render_admin_page' )
        );
    }

    /**
     * Add action links to plugin page.
     *
     * @since 2.0.0
     *
     * @param array $links Existing action links.
     * @return array Modified action links.
     */
    public function add_action_links( $links ) {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            esc_url( admin_url( 'tools.php?page=rfpm-settings' ) ),
            esc_html__( 'Settings', 'remote-featured-plugins' )
        );

        array_unshift( $links, $settings_link );

        return $links;
    }

    /**
     * Render admin page.
     *
     * @since 2.0.0
     */
    public function render_admin_page() {
        // Check user capabilities.
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'remote-featured-plugins' ) );
        }

        // Handle settings save.
        if ( isset( $_POST['rfpm_save_settings'] ) ) {
            $this->handle_settings_save();
        }

        // Get current settings.
        $remote_url      = $this->settings->get( 'rfpm_remote_url', '' );
        $api_key         = $this->settings->get( 'rfpm_api_key', '' );
        $cache_duration  = $this->settings->get( 'rfpm_cache_duration', 6 * HOUR_IN_SECONDS );
        $cache_stats     = $this->cache_manager->get_stats();
        $cached_slugs    = $this->cache_manager->get( Cache_Manager::SLUGS_CACHE_KEY );

        // Load view template.
        include RFPM_PLUGIN_DIR . 'admin/views/settings-page.php';
    }

    /**
     * Handle settings save.
     *
     * @since 2.0.0
     */
    private function handle_settings_save() {
        // Verify nonce.
        if ( ! isset( $_POST['rfpm_settings_nonce'] ) || 
             ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rfpm_settings_nonce'] ) ), 'rfpm_save_settings' ) ) {
            add_settings_error(
                'rfpm_messages',
                'rfpm_nonce_error',
                __( 'Security check failed. Please try again.', 'remote-featured-plugins' ),
                'error'
            );
            return;
        }

        // Validate and save remote URL.
        if ( isset( $_POST['rfpm_remote_url'] ) ) {
            $url = $this->settings->validate_url( sanitize_text_field( wp_unslash( $_POST['rfpm_remote_url'] ) ) );
            if ( false !== $url ) {
                $this->settings->update( 'rfpm_remote_url', $url );
            } else {
                add_settings_error(
                    'rfpm_messages',
                    'rfpm_invalid_url',
                    __( 'Invalid remote URL. Please provide a valid HTTP or HTTPS URL.', 'remote-featured-plugins' ),
                    'error'
                );
                return;
            }
        }

        // Save API key.
        if ( isset( $_POST['rfpm_api_key'] ) ) {
            $api_key = sanitize_text_field( wp_unslash( $_POST['rfpm_api_key'] ) );
            $this->settings->update( 'rfpm_api_key', $api_key );
        }

        // Save cache duration.
        if ( isset( $_POST['rfpm_cache_duration'] ) ) {
            $duration = absint( $_POST['rfpm_cache_duration'] );
            $this->settings->update( 'rfpm_cache_duration', $duration );
        }

        // Clear cache after settings change.
        $this->cache_manager->clear_all();

        add_settings_error(
            'rfpm_messages',
            'rfpm_settings_saved',
            __( 'Settings saved successfully!', 'remote-featured-plugins' ),
            'success'
        );
    }

    /**
     * Handle manual cache refresh.
     *
     * @since 2.0.0
     */
    public function handle_refresh() {
        // Verify nonce.
        if ( ! isset( $_POST['rfpm_refresh_nonce'] ) ||
             ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rfpm_refresh_nonce'] ) ), 'rfpm_refresh_cache' ) ) {
            wp_die( esc_html__( 'Security check failed.', 'remote-featured-plugins' ) );
        }

        // Check capabilities.
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Unauthorized access.', 'remote-featured-plugins' ) );
        }

        // Clear cache.
        $this->cache_manager->clear_all();

        // Redirect back with success message.
        wp_safe_redirect(
            add_query_arg(
                array(
                    'page'      => 'rfpm-settings',
                    'refreshed' => '1',
                ),
                admin_url( 'tools.php' )
            )
        );
        exit;
    }

    /**
     * Handle connection test.
     *
     * @since 2.0.0
     */
    public function handle_test_connection() {
        // Verify nonce.
        if ( ! isset( $_POST['rfpm_test_nonce'] ) ||
             ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rfpm_test_nonce'] ) ), 'rfpm_test_connection' ) ) {
            wp_die( esc_html__( 'Security check failed.', 'remote-featured-plugins' ) );
        }

        // Check capabilities.
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Unauthorized access.', 'remote-featured-plugins' ) );
        }

        // Test connection.
        $test_results = $this->remote_source->test_connection();

        // Store results in transient for display.
        set_transient( 'rfpm_test_results', $test_results, 60 );

        // Redirect back.
        wp_safe_redirect(
            add_query_arg(
                array(
                    'page'   => 'rfpm-settings',
                    'tested' => '1',
                ),
                admin_url( 'tools.php' )
            )
        );
        exit;
    }
}

