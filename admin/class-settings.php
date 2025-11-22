<?php
/**
 * Settings class.
 *
 * @package Plugin_Curator
 * @since 1.0.0
 */

namespace RFPM;

/**
 * Manages plugin settings.
 *
 * @since 2.0.0
 */
class Settings {

    /**
     * Settings group name.
     *
     * @since 2.0.0
     * @var string
     */
    const SETTINGS_GROUP = 'rfpm_settings';

    /**
     * Initialize settings.
     *
     * @since 2.0.0
     */
    public function init() {
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    /**
     * Register plugin settings.
     *
     * @since 2.0.0
     */
    public function register_settings() {
        // Remote URL setting.
        register_setting(
            self::SETTINGS_GROUP,
            'rfpm_remote_url',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'esc_url_raw',
                'default'           => 'https://yourserver.com/featured-plugins.json',
                'show_in_rest'      => false,
            )
        );

        // API Key setting.
        register_setting(
            self::SETTINGS_GROUP,
            'rfpm_api_key',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
                'show_in_rest'      => false,
            )
        );

        // Cache duration setting.
        register_setting(
            self::SETTINGS_GROUP,
            'rfpm_cache_duration',
            array(
                'type'              => 'integer',
                'sanitize_callback' => 'absint',
                'default'           => 6 * HOUR_IN_SECONDS,
                'show_in_rest'      => false,
            )
        );
    }

    /**
     * Get setting value.
     *
     * @since 2.0.0
     *
     * @param string $key Setting key.
     * @param mixed  $default Default value.
     * @return mixed Setting value.
     */
    public function get( $key, $default = null ) {
        return get_option( $key, $default );
    }

    /**
     * Update setting value.
     *
     * @since 2.0.0
     *
     * @param string $key Setting key.
     * @param mixed  $value Setting value.
     * @return bool True if updated, false otherwise.
     */
    public function update( $key, $value ) {
        return update_option( $key, $value );
    }

    /**
     * Validate remote URL.
     *
     * @since 2.0.0
     *
     * @param string $url URL to validate.
     * @return string|false Validated URL or false on failure.
     */
    public function validate_url( $url ) {
        $url = esc_url_raw( $url );

        if ( empty( $url ) ) {
            return false;
        }

        if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
            return false;
        }

        // Check if URL has proper scheme.
        $parsed = wp_parse_url( $url );
        if ( ! isset( $parsed['scheme'] ) || ! in_array( $parsed['scheme'], array( 'http', 'https' ), true ) ) {
            return false;
        }

        return $url;
    }

    /**
     * Get cache duration options for dropdown.
     *
     * @since 2.0.0
     *
     * @return array Array of duration options.
     */
    public function get_cache_duration_options() {
        return array(
            1 * HOUR_IN_SECONDS  => __( '1 Hour', 'iconick-featured-curator' ),
            3 * HOUR_IN_SECONDS  => __( '3 Hours', 'iconick-featured-curator' ),
            6 * HOUR_IN_SECONDS  => __( '6 Hours', 'iconick-featured-curator' ),
            12 * HOUR_IN_SECONDS => __( '12 Hours', 'iconick-featured-curator' ),
            24 * HOUR_IN_SECONDS => __( '24 Hours', 'iconick-featured-curator' ),
        );
    }
}

