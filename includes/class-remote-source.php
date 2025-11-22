<?php
/**
 * Remote Source class.
 *
 * @package Plugin_Curator
 * @since 1.0.0
 */

namespace RFPM;

/**
 * Handles fetching plugin slugs from remote JSON source.
 *
 * @since 2.0.0
 */
class Remote_Source {

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
     * Get plugin slugs from remote source.
     *
     * @since 2.0.0
     *
     * @param bool $force_refresh Force refresh from remote. Default false.
     * @return array|false Array of plugin slugs or false on failure.
     */
    public function get_slugs( $force_refresh = false ) {
        // Check cache first unless force refresh.
        if ( ! $force_refresh ) {
            $cached = $this->cache_manager->get( Cache_Manager::SLUGS_CACHE_KEY );
            if ( false !== $cached ) {
                return $cached;
            }
        }

        // Fetch from remote.
        $slugs = $this->fetch_remote_slugs();

        if ( false === $slugs ) {
            return false;
        }

        // Validate and sanitize slugs.
        $slugs = $this->validate_slugs( $slugs );

        if ( empty( $slugs ) ) {
            $this->log_error( 'No valid plugin slugs found after validation' );
            return false;
        }

        // Cache the slugs.
        $this->cache_manager->set( Cache_Manager::SLUGS_CACHE_KEY, $slugs );

        return $slugs;
    }

    /**
     * Fetch plugin slugs from remote URL.
     *
     * @since 2.0.0
     *
     * @return array|false Array of slugs or false on failure.
     */
    private function fetch_remote_slugs() {
        $remote_url = get_option( 'rfpm_remote_url', '' );

        if ( empty( $remote_url ) ) {
            $this->log_error( 'Remote URL not configured' );
            return false;
        }

        // Validate URL.
        if ( ! filter_var( $remote_url, FILTER_VALIDATE_URL ) ) {
            $this->log_error( 'Invalid remote URL: ' . $remote_url );
            return false;
        }

        // Build request args.
        $args = array(
            'timeout'   => 15,
            'sslverify' => true,
            'headers'   => array(
                'Accept' => 'application/json',
            ),
        );

        // Add authentication if API key is configured.
        $api_key = get_option( 'rfpm_api_key', '' );
        if ( ! empty( $api_key ) ) {
            $args['headers']['Authorization'] = 'Bearer ' . sanitize_text_field( $api_key );
        }

        /**
         * Filter remote request args.
         *
         * @since 2.0.0
         *
         * @param array  $args Request arguments.
         * @param string $remote_url Remote URL.
         */
        $args = apply_filters( 'rfpm_remote_request_args', $args, $remote_url );

        // Fetch from remote.
        $response = wp_remote_get( $remote_url, $args );

        if ( is_wp_error( $response ) ) {
            $this->log_error( 'Failed to fetch remote list: ' . $response->get_error_message() );
            return false;
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        if ( 200 !== $response_code ) {
            $this->log_error( 'Remote server returned HTTP ' . $response_code );
            return false;
        }

        // Parse JSON.
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( JSON_ERROR_NONE !== json_last_error() ) {
            $this->log_error( 'Invalid JSON: ' . json_last_error_msg() );
            return false;
        }

        // Extract slugs from data.
        return $this->extract_slugs( $data );
    }

    /**
     * Extract slugs from JSON data.
     *
     * Handles both array format and object format with "plugins" key.
     *
     * @since 2.0.0
     *
     * @param mixed $data JSON data.
     * @return array Array of slugs.
     */
    private function extract_slugs( $data ) {
        $slugs = array();

        if ( isset( $data['plugins'] ) && is_array( $data['plugins'] ) ) {
            // Object format with "plugins" key.
            $slugs = $data['plugins'];
        } elseif ( is_array( $data ) ) {
            // Simple array format.
            $slugs = $data;
        }

        return $slugs;
    }

    /**
     * Validate and sanitize plugin slugs.
     *
     * @since 2.0.0
     *
     * @param array $slugs Raw slugs from remote.
     * @return array Validated and sanitized slugs.
     */
    private function validate_slugs( $slugs ) {
        if ( ! is_array( $slugs ) ) {
            return array();
        }

        // Sanitize each slug.
        $slugs = array_map( 'sanitize_key', $slugs );

        // Remove empty values.
        $slugs = array_filter( $slugs );

        // Remove duplicates.
        $slugs = array_unique( $slugs );

        // Validate slug format.
        $slugs = array_filter( $slugs, array( $this, 'is_valid_slug' ) );

        /**
         * Filter validated slugs.
         *
         * @since 2.0.0
         *
         * @param array $slugs Validated slugs.
         */
        return apply_filters( 'rfpm_validated_slugs', array_values( $slugs ) );
    }

    /**
     * Check if a slug is valid.
     *
     * @since 2.0.0
     *
     * @param string $slug Plugin slug.
     * @return bool True if valid, false otherwise.
     */
    private function is_valid_slug( $slug ) {
        // Slug must be non-empty and contain only lowercase letters, numbers, and hyphens.
        return ! empty( $slug ) && preg_match( '/^[a-z0-9-]+$/', $slug );
    }

    /**
     * Test connection to remote source.
     *
     * @since 2.0.0
     *
     * @return array Test results.
     */
    public function test_connection() {
        $results = array(
            'success' => false,
            'message' => '',
            'data'    => array(),
        );

        $remote_url = get_option( 'rfpm_remote_url', '' );

        if ( empty( $remote_url ) ) {
            $results['message'] = __( 'Remote URL not configured', 'iconick-featured-curator' );
            return $results;
        }

        // Try to fetch slugs without using cache.
        $slugs = $this->fetch_remote_slugs();

        if ( false === $slugs ) {
            $results['message'] = __( 'Failed to fetch data from remote source', 'iconick-featured-curator' );
            return $results;
        }

        $validated_slugs = $this->validate_slugs( $slugs );

        $results['success'] = true;
        $results['message'] = sprintf(
            /* translators: %d: number of plugins */
            __( 'Successfully fetched %d plugin slugs', 'iconick-featured-curator' ),
            count( $validated_slugs )
        );
        $results['data'] = array(
            'total_slugs'     => count( $slugs ),
            'valid_slugs'     => count( $validated_slugs ),
            'invalid_slugs'   => count( $slugs ) - count( $validated_slugs ),
            'slugs'           => $validated_slugs,
        );

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
            error_log( 'RFPM Remote Source: ' . $message );
        }

        /**
         * Action hook for logging errors.
         *
         * @since 2.0.0
         *
         * @param string $message Error message.
         * @param string $component Component name.
         */
        do_action( 'rfpm_log_error', $message, 'remote_source' );
    }
}

