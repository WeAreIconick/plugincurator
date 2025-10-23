<?php
/**
 * Admin settings page template.
 *
 * @package Plugin_Curator
 * @since 1.0.0
 *
 * @var string $remote_url      Current remote URL.
 * @var string $api_key         Current API key.
 * @var int    $cache_duration  Current cache duration.
 * @var array  $cache_stats     Cache statistics.
 * @var array|false $cached_slugs Cached plugin slugs.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <?php settings_errors( 'rfpm_messages' ); ?>

    <?php if ( isset( $_GET['refreshed'] ) && '1' === $_GET['refreshed'] ) : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e( 'Cache refreshed successfully!', 'remote-featured-plugins' ); ?></p>
        </div>
    <?php endif; ?>

    <?php
    if ( isset( $_GET['tested'] ) && '1' === $_GET['tested'] ) :
        $test_results = get_transient( 'rfpm_test_results' );
        if ( $test_results ) :
            $notice_class = $test_results['success'] ? 'notice-success' : 'notice-error';
            ?>
            <div class="notice <?php echo esc_attr( $notice_class ); ?> is-dismissible">
                <p><strong><?php esc_html_e( 'Connection Test Results:', 'remote-featured-plugins' ); ?></strong></p>
                <p><?php echo esc_html( $test_results['message'] ); ?></p>
                <?php if ( ! empty( $test_results['data'] ) ) : ?>
                    <ul style="list-style: disc; margin-left: 20px;">
                        <li><?php printf( esc_html__( 'Total Slugs: %d', 'remote-featured-plugins' ), (int) $test_results['data']['total_slugs'] ); ?></li>
                        <li><?php printf( esc_html__( 'Valid Slugs: %d', 'remote-featured-plugins' ), (int) $test_results['data']['valid_slugs'] ); ?></li>
                        <?php if ( $test_results['data']['invalid_slugs'] > 0 ) : ?>
                            <li><?php printf( esc_html__( 'Invalid Slugs: %d', 'remote-featured-plugins' ), (int) $test_results['data']['invalid_slugs'] ); ?></li>
                        <?php endif; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <?php
            delete_transient( 'rfpm_test_results' );
        endif;
    endif;
    ?>

    <div class="card">
        <h2><?php esc_html_e( 'Settings', 'remote-featured-plugins' ); ?></h2>
        <form method="post" action="">
            <?php wp_nonce_field( 'rfpm_save_settings', 'rfpm_settings_nonce' ); ?>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="rfpm_remote_url"><?php esc_html_e( 'Remote JSON URL', 'remote-featured-plugins' ); ?></label>
                    </th>
                    <td>
                        <input type="url"
                               id="rfpm_remote_url"
                               name="rfpm_remote_url"
                               value="<?php echo esc_attr( $remote_url ); ?>"
                               class="regular-text"
                               required />
                        <p class="description">
                            <?php esc_html_e( 'URL to your JSON file containing the list of plugin slugs', 'remote-featured-plugins' ); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="rfpm_api_key"><?php esc_html_e( 'API Key (Optional)', 'remote-featured-plugins' ); ?></label>
                    </th>
                    <td>
                        <input type="password"
                               id="rfpm_api_key"
                               name="rfpm_api_key"
                               value="<?php echo esc_attr( $api_key ); ?>"
                               class="regular-text"
                               autocomplete="off" />
                        <p class="description">
                            <?php esc_html_e( 'Bearer token for authenticated requests (leave empty if not needed)', 'remote-featured-plugins' ); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="rfpm_cache_duration"><?php esc_html_e( 'Cache Duration', 'remote-featured-plugins' ); ?></label>
                    </th>
                    <td>
                        <select id="rfpm_cache_duration" name="rfpm_cache_duration">
                            <?php
                            $duration_options = $this->settings->get_cache_duration_options();
                            foreach ( $duration_options as $value => $label ) :
                                ?>
                                <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $cache_duration, $value ); ?>>
                                    <?php echo esc_html( $label ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">
                            <?php esc_html_e( 'How long to cache plugin data before refreshing', 'remote-featured-plugins' ); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <?php submit_button( __( 'Save Settings', 'remote-featured-plugins' ), 'primary', 'rfpm_save_settings' ); ?>
        </form>
    </div>

    <div class="card">
        <h2><?php esc_html_e( 'Cache Statistics', 'remote-featured-plugins' ); ?></h2>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Cache Type', 'remote-featured-plugins' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'remote-featured-plugins' ); ?></th>
                    <th><?php esc_html_e( 'Size', 'remote-featured-plugins' ); ?></th>
                    <th><?php esc_html_e( 'Time Remaining', 'remote-featured-plugins' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $cache_stats as $label => $stats ) : ?>
                    <tr>
                        <td><strong><?php echo esc_html( ucfirst( $label ) ); ?></strong></td>
                        <td>
                            <?php if ( $stats['exists'] ) : ?>
                                <span class="dashicons dashicons-yes-alt" style="color: green;"></span>
                                <?php esc_html_e( 'Cached', 'remote-featured-plugins' ); ?>
                            <?php else : ?>
                                <span class="dashicons dashicons-dismiss" style="color: #ccc;"></span>
                                <?php esc_html_e( 'Empty', 'remote-featured-plugins' ); ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html( $stats['size'] ); ?></td>
                        <td>
                            <?php
                            if ( $stats['remaining'] > 0 ) {
                                echo esc_html( human_time_diff( time(), $stats['timeout'] ) );
                            } else {
                                esc_html_e( 'Expired', 'remote-featured-plugins' );
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ( $cached_slugs && is_array( $cached_slugs ) ) : ?>
        <div class="card">
            <h2><?php esc_html_e( 'Current Featured Plugins', 'remote-featured-plugins' ); ?></h2>
            <p>
                <?php
                printf(
                    /* translators: %d: number of plugins */
                    esc_html__( '%d plugins are currently featured:', 'remote-featured-plugins' ),
                    count( $cached_slugs )
                );
                ?>
            </p>
            <ul style="list-style: disc; margin-left: 20px; column-count: 3;">
                <?php foreach ( $cached_slugs as $slug ) : ?>
                    <li>
                        <code><?php echo esc_html( $slug ); ?></code>
                        <a href="<?php echo esc_url( 'https://wordpress.org/plugins/' . $slug . '/' ); ?>" target="_blank" rel="noopener noreferrer">
                            <?php esc_html_e( 'View →', 'remote-featured-plugins' ); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2><?php esc_html_e( 'Actions', 'remote-featured-plugins' ); ?></h2>

        <p><?php esc_html_e( 'Use the buttons below to manually refresh the cache or test your remote connection.', 'remote-featured-plugins' ); ?></p>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display: inline-block; margin-right: 10px;">
            <input type="hidden" name="action" value="rfpm_refresh_cache">
            <?php wp_nonce_field( 'rfpm_refresh_cache', 'rfpm_refresh_nonce' ); ?>
            <?php submit_button( __( 'Refresh Cache Now', 'remote-featured-plugins' ), 'secondary', 'submit', false ); ?>
        </form>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display: inline-block;">
            <input type="hidden" name="action" value="rfpm_test_connection">
            <?php wp_nonce_field( 'rfpm_test_connection', 'rfpm_test_nonce' ); ?>
            <?php submit_button( __( 'Test Connection', 'remote-featured-plugins' ), 'secondary', 'submit', false ); ?>
        </form>
    </div>

    <div class="card">
        <h2><?php esc_html_e( 'JSON File Format', 'remote-featured-plugins' ); ?></h2>
        <p><?php esc_html_e( 'Your remote JSON file should use one of these formats:', 'remote-featured-plugins' ); ?></p>

        <h3><?php esc_html_e( 'Simple Array (Recommended)', 'remote-featured-plugins' ); ?></h3>
        <pre style="background: #f5f5f5; padding: 15px; border-left: 4px solid #0073aa; overflow-x: auto;">[
  "contact-form-7",
  "wordpress-seo",
  "woocommerce",
  "elementor",
  "updraftplus",
  "wordfence"
]</pre>

        <h3><?php esc_html_e( 'Object with Metadata', 'remote-featured-plugins' ); ?></h3>
        <pre style="background: #f5f5f5; padding: 15px; border-left: 4px solid #0073aa; overflow-x: auto;">{
  "version": "1.0",
  "last_updated": "2025-10-22",
  "plugins": [
    "contact-form-7",
    "wordpress-seo",
    "woocommerce"
  ]
}</pre>

        <p>
            <strong><?php esc_html_e( 'Plugin Slugs:', 'remote-featured-plugins' ); ?></strong>
            <?php esc_html_e( 'Use the slug from the WordPress.org plugin URL. For example,', 'remote-featured-plugins' ); ?>
            <code>wordpress.org/plugins/<strong>contact-form-7</strong>/</code> →
            <?php esc_html_e( 'slug is', 'remote-featured-plugins' ); ?>
            <code>contact-form-7</code>
        </p>
    </div>

    <div class="card">
        <h2><?php esc_html_e( 'How It Works', 'remote-featured-plugins' ); ?></h2>
        <ol style="line-height: 1.8;">
            <li><?php esc_html_e( 'This plugin fetches the list of plugin slugs from your remote JSON file', 'remote-featured-plugins' ); ?></li>
            <li><?php esc_html_e( 'For each slug, it retrieves real, current data from WordPress.org (versions, ratings, downloads, etc.)', 'remote-featured-plugins' ); ?></li>
            <li><?php esc_html_e( 'The Featured tab in Plugins → Add New shows your curated list with live WordPress.org data', 'remote-featured-plugins' ); ?></li>
            <li>
                <?php
                printf(
                    /* translators: %s: cache duration in hours */
                    esc_html__( 'Everything is cached for %s for performance', 'remote-featured-plugins' ),
                    esc_html( human_time_diff( 0, $cache_duration ) )
                );
                ?>
            </li>
            <li><?php esc_html_e( 'If your remote file is unreachable, WordPress.org\'s default featured plugins are shown as fallback', 'remote-featured-plugins' ); ?></li>
        </ol>
    </div>
</div>

