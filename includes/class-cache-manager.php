<?php
/**
 * Cache Manager class.
 *
 * @package Plugin_Curator
 * @since 1.0.0
 */

namespace RFPM;

/**
 * Manages caching for plugin data.
 *
 * Handles transient caching with improved error handling and statistics.
 *
 * @since 2.0.0
 */
class Cache_Manager {

    /**
     * Cache key for remote slugs.
     *
     * @since 2.0.0
     * @var string
     */
    const SLUGS_CACHE_KEY = 'rfpm_remote_slugs';

    /**
     * Cache key for plugin data.
     *
     * @since 2.0.0
     * @var string
     */
    const PLUGINS_CACHE_KEY = 'rfpm_plugins_data';

    /**
     * Cache key for partial data (when some plugins fail).
     *
     * @since 2.0.0
     * @var string
     */
    const PARTIAL_CACHE_KEY = 'rfpm_plugins_partial';

    /**
     * Get cached data.
     *
     * @since 2.0.0
     *
     * @param string $key Cache key.
     * @return mixed|false Cached data or false if not found.
     */
    public function get( $key ) {
        return get_transient( $key );
    }

    /**
     * Set cached data.
     *
     * @since 2.0.0
     *
     * @param string $key Cache key.
     * @param mixed  $data Data to cache.
     * @param int    $expiration Time until expiration in seconds. Default is plugin setting.
     * @return bool True on success, false on failure.
     */
    public function set( $key, $data, $expiration = null ) {
        if ( null === $expiration ) {
            $expiration = $this->get_cache_duration();
        }

        return set_transient( $key, $data, $expiration );
    }

    /**
     * Delete cached data.
     *
     * @since 2.0.0
     *
     * @param string $key Cache key.
     * @return bool True on success, false on failure.
     */
    public function delete( $key ) {
        return delete_transient( $key );
    }

    /**
     * Clear all plugin caches.
     *
     * @since 2.0.0
     *
     * @return bool True if all caches cleared successfully.
     */
    public function clear_all() {
        $results = array(
            $this->delete( self::SLUGS_CACHE_KEY ),
            $this->delete( self::PLUGINS_CACHE_KEY ),
            $this->delete( self::PARTIAL_CACHE_KEY ),
        );

        return ! in_array( false, $results, true );
    }

    /**
     * Get cache statistics.
     *
     * @since 2.0.0
     *
     * @return array Array of cache stats.
     */
    public function get_stats() {
        $stats = array();

        $keys = array(
            'slugs'   => self::SLUGS_CACHE_KEY,
            'plugins' => self::PLUGINS_CACHE_KEY,
            'partial' => self::PARTIAL_CACHE_KEY,
        );

        foreach ( $keys as $label => $key ) {
            $data    = $this->get( $key );
            $timeout = get_option( '_transient_timeout_' . $key, 0 );

            $stats[ $label ] = array(
                'exists'    => false !== $data,
                'timeout'   => $timeout,
                'remaining' => $timeout > time() ? $timeout - time() : 0,
                'size'      => false !== $data ? $this->get_data_size( $data ) : 0,
            );
        }

        return $stats;
    }

    /**
     * Get cache duration from settings.
     *
     * @since 2.0.0
     *
     * @return int Cache duration in seconds.
     */
    private function get_cache_duration() {
        $duration = get_option( 'rfpm_cache_duration', 6 * HOUR_IN_SECONDS );

        /**
         * Filter the cache duration.
         *
         * @since 2.0.0
         *
         * @param int $duration Cache duration in seconds.
         */
        return apply_filters( 'rfpm_cache_duration', absint( $duration ) );
    }

    /**
     * Get approximate size of data.
     *
     * @since 2.0.0
     *
     * @param mixed $data Data to measure.
     * @return string Human-readable size.
     */
    private function get_data_size( $data ) {
        $bytes = strlen( maybe_serialize( $data ) );

        if ( $bytes < 1024 ) {
            return $bytes . ' B';
        } elseif ( $bytes < 1048576 ) {
            return round( $bytes / 1024, 2 ) . ' KB';
        } else {
            return round( $bytes / 1048576, 2 ) . ' MB';
        }
    }

    /**
     * Warm the cache by fetching fresh data.
     *
     * @since 2.0.0
     *
     * @return bool True on success, false on failure.
     */
    public function warm() {
        $this->clear_all();
        
        /**
         * Action hook to allow other components to warm their caches.
         *
         * @since 2.0.0
         */
        do_action( 'rfpm_cache_warming' );

        return true;
    }
}

