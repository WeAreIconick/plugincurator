<?php
/**
 * Plugin Filter class.
 *
 * @package Plugin_Curator
 * @since 1.0.0
 */

namespace RFPM;

/**
 * Filters featured plugins API requests.
 *
 * @since 2.0.0
 */
class Plugin_Filter {

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
     * @param Remote_Source $remote_source Remote source instance.
     * @param API_Client    $api_client API client instance.
     * @param Cache_Manager $cache_manager Cache manager instance.
     */
    public function __construct( Remote_Source $remote_source, API_Client $api_client, Cache_Manager $cache_manager ) {
        $this->remote_source = $remote_source;
        $this->api_client    = $api_client;
        $this->cache_manager = $cache_manager;
    }

    /**
     * Initialize hooks.
     *
     * @since 2.0.0
     *
     * Note: This filter is used to customize the Featured plugins list display only.
     * It does NOT interfere with plugin updates or the WordPress.org update system.
     * The filter only intercepts 'query_plugins' requests with 'browse=featured',
     * allowing the plugin to replace the default featured plugins list with a curated one.
     */
    public function init() {
        add_filter( 'plugins_api', array( $this, 'filter_featured_plugins' ), 10, 3 );
    }

    /**
     * Filter featured plugins API requests.
     *
     * This method ONLY filters the Featured plugins list display in Plugins → Add New.
     * It does NOT interfere with plugin updates, update checks, or the WordPress.org update system.
     * Updates continue to work normally through WordPress.org.
     *
     * @since 2.0.0
     *
     * @param false|object|array $result The result object or array. Default false.
     * @param string             $action The type of information being requested from the Plugin Installation API.
     * @param object             $args Plugin API arguments.
     * @return false|object Modified result or false to use default.
     */
    public function filter_featured_plugins( $result, $action, $args ) {
        // Only intercept featured plugin queries for display purposes.
        // This does NOT affect plugin updates or update checks.
        if ( 'query_plugins' !== $action ) {
            return $result;
        }

        if ( ! isset( $args->browse ) || 'featured' !== $args->browse ) {
            return $result;
        }

        // Get cached response if available.
        $cached_response = $this->cache_manager->get( Cache_Manager::PLUGINS_CACHE_KEY );
        if ( false !== $cached_response ) {
            return $cached_response;
        }

        // Get plugin slugs from remote source.
        $slugs = $this->remote_source->get_slugs();

        if ( empty( $slugs ) ) {
            // Fallback to WordPress.org if remote fails.
            $this->log_error( 'No slugs available, using WordPress.org default' );
            return $result;
        }

        // Fetch plugin data from WordPress.org.
        $plugins = $this->api_client->fetch_plugins( $slugs );

        if ( empty( $plugins ) ) {
            // If fetch fails completely, fall back to WordPress.org.
            $this->log_error( 'Failed to fetch plugin data, using WordPress.org default' );
            return $result;
        }

        // Build response object.
        $custom_response = $this->api_client->build_api_response( $plugins );

        // Cache the response.
        $this->cache_manager->set( Cache_Manager::PLUGINS_CACHE_KEY, $custom_response );

        // If we got fewer plugins than expected, store partial data separately.
        if ( count( $plugins ) < count( $slugs ) ) {
            $this->cache_manager->set(
                Cache_Manager::PARTIAL_CACHE_KEY,
                array(
                    'expected' => count( $slugs ),
                    'received' => count( $plugins ),
                    'missing'  => count( $slugs ) - count( $plugins ),
                )
            );
        }

        return $custom_response;
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
            error_log( 'RFPM Plugin Filter: ' . $message );
        }

        /**
         * Action hook for logging errors.
         *
         * @since 2.0.0
         *
         * @param string $message Error message.
         * @param string $component Component name.
         */
        do_action( 'rfpm_log_error', $message, 'plugin_filter' );
    }
}

