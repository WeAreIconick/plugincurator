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
            __( 'The Curator by Iconick', 'iconick-featured-curator' ),
            __( 'The Curator by Iconick', 'iconick-featured-curator' ),
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
            esc_html__( 'Settings', 'iconick-featured-curator' )
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
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'iconick-featured-curator' ) );
        }

        // Handle settings save - verify nonce before processing.
        if ( isset( $_POST['rfpm_save_settings'] ) && 
             isset( $_POST['rfpm_settings_nonce'] ) && 
             wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rfpm_settings_nonce'] ) ), 'rfpm_save_settings' ) ) {
            
            // Extract and sanitize POST data.
            $remote_url_input = isset( $_POST['rfpm_remote_url'] ) ? sanitize_text_field( wp_unslash( $_POST['rfpm_remote_url'] ) ) : '';
            $api_key_input    = isset( $_POST['rfpm_api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['rfpm_api_key'] ) ) : '';
            $cache_duration_input = isset( $_POST['rfpm_cache_duration'] ) ? absint( $_POST['rfpm_cache_duration'] ) : 0;
            
            // Pass sanitized data to handler.
            $this->handle_settings_save( $remote_url_input, $api_key_input, $cache_duration_input );
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
     *
     * @param string $remote_url Remote URL input.
     * @param string $api_key API key input.
     * @param int    $cache_duration Cache duration input.
     */
    private function handle_settings_save( $remote_url, $api_key, $cache_duration ) {
        // Validate and save remote URL.
        if ( ! empty( $remote_url ) ) {
            $url = $this->settings->validate_url( $remote_url );
            if ( false !== $url ) {
                $this->settings->update( 'rfpm_remote_url', $url );
            } else {
                add_settings_error(
                    'rfpm_messages',
                    'rfpm_invalid_url',
                    __( 'Invalid remote URL. Please provide a valid HTTP or HTTPS URL.', 'iconick-featured-curator' ),
                    'error'
                );
                return;
            }
        }

        // Save API key.
        if ( ! empty( $api_key ) || '' === $api_key ) {
            $this->settings->update( 'rfpm_api_key', $api_key );
        }

        // Save cache duration.
        if ( $cache_duration > 0 ) {
            $this->settings->update( 'rfpm_cache_duration', $cache_duration );
        }

        // Clear cache after settings change.
        $this->cache_manager->clear_all();

        add_settings_error(
            'rfpm_messages',
            'rfpm_settings_saved',
            __( 'Settings saved successfully!', 'iconick-featured-curator' ),
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
            wp_die( esc_html__( 'Security check failed.', 'iconick-featured-curator' ) );
        }

        // Check capabilities.
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Unauthorized access.', 'iconick-featured-curator' ) );
        }

        // Clear cache.
        $this->cache_manager->clear_all();

        // Store success message in transient.
        set_transient( 'rfpm_cache_refreshed', true, 30 );

        // Redirect back.
        wp_safe_redirect(
            add_query_arg(
                'page',
                'rfpm-settings',
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
            wp_die( esc_html__( 'Security check failed.', 'iconick-featured-curator' ) );
        }

        // Check capabilities.
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Unauthorized access.', 'iconick-featured-curator' ) );
        }

        // Test connection.
        $test_results = $this->remote_source->test_connection();

        // Store results in transient for display.
        set_transient( 'rfpm_test_results', $test_results, 30 );

        // Redirect back.
        wp_safe_redirect(
            add_query_arg(
                'page',
                'rfpm-settings',
                admin_url( 'tools.php' )
            )
        );
        exit;
    }
}

