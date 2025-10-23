<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package Plugin_Curator
 */

// Composer autoloader.
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// WP_Mock bootstrap.
WP_Mock::bootstrap();

// Plugin constants for testing.
define( 'RFPM_VERSION', '1.0.0' );
define( 'RFPM_PLUGIN_DIR', dirname( __DIR__ ) . '/' );
define( 'RFPM_PLUGIN_URL', 'https://example.com/wp-content/plugins/plugin-curator/' );
define( 'RFPM_PLUGIN_BASENAME', 'plugin-curator/plugin-curator.php' );

// WordPress constants.
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', '/tmp/wordpress/' );
}

if ( ! defined( 'HOUR_IN_SECONDS' ) ) {
    define( 'HOUR_IN_SECONDS', 3600 );
}

if ( ! defined( 'MINUTE_IN_SECONDS' ) ) {
    define( 'MINUTE_IN_SECONDS', 60 );
}

