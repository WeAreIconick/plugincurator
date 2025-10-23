<?php
/**
 * API Client class.
 *
 * @package Plugin_Curator
 * @since 1.0.0
 */

namespace RFPM;

/**
 * Handles WordPress.org API interactions.
 *
 * @since 2.0.0
 */
class API_Client {

    /**
     * WordPress.org API base URL.
     *
     * @since 2.0.0
     * @var string
     */
    const API_BASE_URL = 'https://api.wordpress.org/plugins/info/1.0/';

    /**
     * Cache manager instance.
     *
     * @since 2.0.0
     * @var Cache_Manager
     */
    private $cache_manager;

    /**
     * Constructor.
     *
     * @since 2.0.0
     *
     * @param Cache_Manager $cache_manager Cache manager instance.
     */
    public function __construct( Cache_Manager $cache_manager ) {
        $this->cache_manager = $cache_manager;
    }

    /**
     * Fetch plugin data from WordPress.org for multiple slugs.
     *
     * @since 2.0.0
     *
     * @param array $slugs Array of plugin slugs.
     * @return array Array of plugin data objects.
     */
    public function fetch_plugins( $slugs ) {
        $plugins = array();
        $failed  = array();

        foreach ( $slugs as $slug ) {
            $plugin_data = $this->fetch_plugin( $slug );

            if ( false !== $plugin_data ) {
                $plugins[] = $plugin_data;
            } else {
                $failed[] = $slug;
            }

            // Small delay to avoid rate limiting.
            usleep( 100000 ); // 0.1 second
        }

        // Log any failures.
        if ( ! empty( $failed ) ) {
            $this->log_error(
                sprintf(
                    'Failed to fetch %d plugins: %s',
                    count( $failed ),
                    implode( ', ', $failed )
                )
            );
        }

        /**
         * Action hook after fetching plugins.
         *
         * @since 2.0.0
         *
         * @param array $plugins Fetched plugins.
         * @param array $failed Failed slugs.
         */
        do_action( 'rfpm_plugins_fetched', $plugins, $failed );

        return $plugins;
    }

    /**
     * Fetch single plugin data from WordPress.org.
     *
     * Uses the WordPress Plugins API with full field specification to ensure
     * all data needed for complete plugin card display is retrieved.
     *
     * @since 2.0.0
     *
     * @param string $slug Plugin slug.
     * @return object|false Plugin data object or false on failure.
     */
    private function fetch_plugin( $slug ) {
        // Use plugins_api() to get complete plugin information with all fields.
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

        $args = array(
            'slug'   => $slug,
            'fields' => array(
                'short_description'    => true,
                'description'          => true,
                'sections'             => true,
                'tested'               => true,
                'requires'             => true,
                'requires_php'         => true,
                'rating'               => true,
                'ratings'              => true,
                'num_ratings'          => true,
                'downloaded'           => true,
                'active_installs'      => true,
                'last_updated'         => true,
                'added'                => true,
                'homepage'             => true,
                'tags'                 => true,
                'donate_link'          => true,
                'icons'                => true,
                'banners'              => true,
                'screenshots'          => true,
                'contributors'         => true,
                'author'               => true,
                'author_profile'       => true,
                'download_link'        => true,
                'version'              => true,
            ),
        );

        // Call the WordPress.org API using the standard WordPress function.
        $plugin_data = plugins_api( 'plugin_information', $args );

        if ( is_wp_error( $plugin_data ) ) {
            $this->log_error( "Failed to fetch {$slug}: " . $plugin_data->get_error_message() );
            return false;
        }

        if ( ! $plugin_data || ! isset( $plugin_data->slug ) ) {
            $this->log_error( "Invalid data received for {$slug}" );
            return false;
        }

        return $plugin_data;
    }

    /**
     * Build plugins API response object.
     *
     * @since 2.0.0
     *
     * @param array $plugins Array of plugin data objects.
     * @return object Response object formatted for plugins_api.
     */
    public function build_api_response( $plugins ) {
        $response = new \stdClass();
        $response->plugins = $plugins;
        $response->info    = array(
            'page'    => 1,
            'pages'   => 1,
            'results' => count( $plugins ),
        );

        /**
         * Filter the API response object.
         *
         * @since 2.0.0
         *
         * @param object $response Response object.
         * @param array  $plugins Plugin data.
         */
        return apply_filters( 'rfpm_api_response', $response, $plugins );
    }

    /**
     * Verify a plugin exists on WordPress.org.
     *
     * @since 2.0.0
     *
     * @param string $slug Plugin slug.
     * @return bool True if exists, false otherwise.
     */
    public function plugin_exists( $slug ) {
        $plugin_data = $this->fetch_plugin( $slug );
        return false !== $plugin_data;
    }

    /**
     * Batch verify multiple plugins exist.
     *
     * @since 2.0.0
     *
     * @param array $slugs Array of plugin slugs.
     * @return array Array with 'valid' and 'invalid' keys containing slug arrays.
     */
    public function batch_verify( $slugs ) {
        $results = array(
            'valid'   => array(),
            'invalid' => array(),
        );

        foreach ( $slugs as $slug ) {
            if ( $this->plugin_exists( $slug ) ) {
                $results['valid'][] = $slug;
            } else {
                $results['invalid'][] = $slug;
            }

            // Small delay to avoid rate limiting.
            usleep( 100000 ); // 0.1 second
        }

        return $results;
    }

    /**
     * Log error message.
     *
     * @since 2.0.0
     *
     * @param string $message Error message.
     */
    private function log_error( $message ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log( 'RFPM API Client: ' . $message );
        }

        /**
         * Action hook for logging errors.
         *
         * @since 2.0.0
         *
         * @param string $message Error message.
         * @param string $component Component name.
         */
        do_action( 'rfpm_log_error', $message, 'api_client' );
    }
}

