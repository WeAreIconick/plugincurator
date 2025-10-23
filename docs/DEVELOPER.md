# Developer Documentation

This document provides technical details for developers who want to understand, extend, or contribute to the Plugin Curator plugin.

## Table of Contents

- [Architecture](#architecture)
- [Class Structure](#class-structure)
- [Hooks Reference](#hooks-reference)
- [Extending the Plugin](#extending-the-plugin)
- [Testing](#testing)
- [Contributing](#contributing)
- [Code Standards](#code-standards)

## Architecture

The plugin follows modern WordPress development practices with a clean OOP architecture.

### File Structure

```
plugin-curator/
├── plugin-curator.php                   # Main plugin file (bootstrapper)
├── uninstall.php                        # Uninstall cleanup
├── composer.json                        # Composer configuration
├── phpunit.xml.dist                     # PHPUnit configuration
├── includes/                            # Core classes
│   ├── class-plugin.php                 # Main plugin coordinator
│   ├── class-cache-manager.php          # Caching logic
│   ├── class-remote-source.php          # Remote JSON fetching
│   ├── class-api-client.php             # WordPress.org API
│   └── class-plugin-filter.php          # Featured plugins filter
├── admin/                               # Admin interface
│   ├── class-admin-menu.php             # Admin menu handler
│   ├── class-settings.php               # Settings management
│   └── views/
│       └── settings-page.php            # Admin page template
├── tests/                               # PHPUnit tests
│   ├── bootstrap.php
│   ├── test-cache-manager.php
│   ├── test-remote-source.php
│   ├── test-api-client.php
│   └── test-plugin-filter.php
└── docs/                                # Documentation
    ├── DEVELOPER.md                     # This file
    └── USER-GUIDE.md                    # User documentation
```

### Design Patterns

**Dependency Injection**: Classes receive their dependencies through constructors, making testing easier and reducing tight coupling.

```php
public function __construct( Cache_Manager $cache_manager ) {
    $this->cache_manager = $cache_manager;
}
```

**Single Responsibility**: Each class has one clear purpose:
- `Cache_Manager` - Only handles caching
- `Remote_Source` - Only fetches remote data
- `API_Client` - Only interacts with WordPress.org API
- `Plugin_Filter` - Only filters plugins_api requests

**Separation of Concerns**: Admin code is separate from core functionality.

## Class Structure

### RFPM\Plugin

**Purpose**: Main plugin coordinator that initializes all components.

**Responsibilities**:
- Load dependencies
- Initialize core components
- Set up admin interface
- Provide access to component instances

**Key Methods**:
```php
init()                  // Initialize plugin
get_cache_manager()     // Get cache manager instance
get_remote_source()     // Get remote source instance
get_api_client()        // Get API client instance
```

### RFPM\Cache_Manager

**Purpose**: Manages transient caching for all plugin data.

**Responsibilities**:
- Get/set/delete cached data
- Clear all caches
- Provide cache statistics
- Handle cache expiration

**Key Methods**:
```php
get( $key )                          // Get cached data
set( $key, $data, $expiration )      // Set cached data
delete( $key )                       // Delete cached data
clear_all()                          // Clear all caches
get_stats()                          // Get cache statistics
```

**Cache Keys**:
- `rfpm_remote_slugs` - Remote plugin slugs
- `rfpm_plugins_data` - Full plugin data from WordPress.org
- `rfpm_plugins_partial` - Partial fetch metadata

### RFPM\Remote_Source

**Purpose**: Fetches and validates plugin slugs from remote JSON source.

**Responsibilities**:
- Fetch remote JSON
- Parse and validate data
- Sanitize plugin slugs
- Test remote connectivity
- Handle authentication

**Key Methods**:
```php
get_slugs( $force_refresh )  // Get plugin slugs
test_connection()            // Test remote connection
```

**Validation Rules**:
- Slugs must be non-empty
- Only lowercase letters, numbers, and hyphens allowed
- Duplicates are removed
- Invalid formats are filtered out

### RFPM\API_Client

**Purpose**: Interacts with WordPress.org Plugin API.

**Responsibilities**:
- Fetch plugin data from WordPress.org
- Build API response objects
- Verify plugin existence
- Handle rate limiting

**Key Methods**:
```php
fetch_plugins( $slugs )          // Fetch multiple plugins
build_api_response( $plugins )   // Build response object
plugin_exists( $slug )           // Check if plugin exists
batch_verify( $slugs )           // Verify multiple plugins
```

**Rate Limiting**: Adds 0.1 second delay between requests to avoid WordPress.org rate limits.

### RFPM\Plugin_Filter

**Purpose**: Filters the `plugins_api` to replace featured plugins.

**Responsibilities**:
- Hook into `plugins_api` filter
- Replace featured plugin queries
- Return cached or fresh data
- Fall back to WordPress.org defaults

**Key Methods**:
```php
init()                                      // Initialize hooks
filter_featured_plugins( $result, $action, $args )  // Filter callback
```

### RFPM\Settings

**Purpose**: Manages plugin settings registration and validation.

**Responsibilities**:
- Register settings
- Validate input
- Provide settings getters/setters
- Define cache duration options

**Key Methods**:
```php
register_settings()              // Register all settings
get( $key, $default )            // Get setting value
update( $key, $value )           // Update setting value
validate_url( $url )             // Validate remote URL
get_cache_duration_options()     // Get duration dropdown options
```

**Settings**:
- `rfpm_remote_url` - Remote JSON URL
- `rfpm_api_key` - API authentication key
- `rfpm_cache_duration` - Cache expiration time

### RFPM\Admin_Menu

**Purpose**: Handles admin interface and user actions.

**Responsibilities**:
- Add admin menu page
- Render settings page
- Handle form submissions
- Process admin actions
- Display notices

**Key Methods**:
```php
init()                      // Initialize admin hooks
add_admin_menu()            // Add admin page
render_admin_page()         // Render settings page
handle_refresh()            // Handle cache refresh
handle_test_connection()    // Handle connection test
```

## Hooks Reference

### Filters

#### `rfpm_cache_duration`

Modify the cache duration.

```php
add_filter( 'rfpm_cache_duration', function( $duration ) {
    return 12 * HOUR_IN_SECONDS; // 12 hours
} );
```

**Parameters**:
- `$duration` (int) - Duration in seconds

#### `rfpm_remote_request_args`

Modify remote HTTP request arguments.

```php
add_filter( 'rfpm_remote_request_args', function( $args, $url ) {
    $args['timeout'] = 30;
    return $args;
}, 10, 2 );
```

**Parameters**:
- `$args` (array) - Request arguments
- `$url` (string) - Remote URL

#### `rfpm_validated_slugs`

Modify validated plugin slugs.

```php
add_filter( 'rfpm_validated_slugs', function( $slugs ) {
    // Add a required plugin
    $slugs[] = 'wordpress-seo';
    return $slugs;
} );
```

**Parameters**:
- `$slugs` (array) - Array of validated slugs

#### `rfpm_api_response`

Modify the API response object.

```php
add_filter( 'rfpm_api_response', function( $response, $plugins ) {
    // Modify response
    return $response;
}, 10, 2 );
```

**Parameters**:
- `$response` (object) - API response object
- `$plugins` (array) - Plugin data array

### Actions

#### `rfpm_log_error`

Triggered when an error is logged.

```php
add_action( 'rfpm_log_error', function( $message, $component ) {
    // Custom error logging
    error_log( "RFPM [{$component}]: {$message}" );
}, 10, 2 );
```

**Parameters**:
- `$message` (string) - Error message
- `$component` (string) - Component name (remote_source, api_client, plugin_filter)

#### `rfpm_cache_warming`

Triggered during cache warming.

```php
add_action( 'rfpm_cache_warming', function() {
    // Perform custom cache warming operations
} );
```

#### `rfpm_plugins_fetched`

Triggered after plugins are fetched from WordPress.org.

```php
add_action( 'rfpm_plugins_fetched', function( $plugins, $failed ) {
    // Track successful and failed fetches
}, 10, 2 );
```

**Parameters**:
- `$plugins` (array) - Successfully fetched plugins
- `$failed` (array) - Failed plugin slugs

## Extending the Plugin

### Custom Remote Source

Replace the remote source with your own implementation:

```php
add_filter( 'rfpm_remote_request_args', function( $args, $url ) {
    // Add custom headers
    $args['headers']['X-Custom-Header'] = 'value';
    return $args;
}, 10, 2 );
```

### Custom Validation

Add custom slug validation:

```php
add_filter( 'rfpm_validated_slugs', function( $slugs ) {
    // Only allow slugs from approved list
    $approved = array( 'plugin-1', 'plugin-2' );
    return array_intersect( $slugs, $approved );
} );
```

### Custom Caching

Implement custom cache warming:

```php
add_action( 'rfpm_cache_warming', function() {
    // Pre-fetch additional data
    $remote_source = $GLOBALS['rfpm_plugin']->get_remote_source();
    $remote_source->get_slugs( true );
} );
```

### Admin Customization

Add custom admin notices:

```php
add_action( 'admin_notices', function() {
    $screen = get_current_screen();
    if ( $screen->id === 'tools_page_rfpm-settings' ) {
        echo '<div class="notice notice-info"><p>Custom message</p></div>';
    }
} );
```

## Testing

### Running Tests

```bash
# Install dependencies
composer install

# Run all tests
composer test

# Run specific test file
vendor/bin/phpunit tests/test-cache-manager.php

# Run with coverage
composer test-coverage
```

### Writing Tests

Tests use WP_Mock for WordPress function mocking:

```php
namespace RFPM\Tests;

use RFPM\Cache_Manager;
use WP_Mock\Tools\TestCase;

class Test_Cache_Manager extends TestCase {
    
    public function setUp(): void {
        \WP_Mock::setUp();
    }
    
    public function tearDown(): void {
        \WP_Mock::tearDown();
    }
    
    public function test_get() {
        \WP_Mock::userFunction( 'get_transient' )
            ->once()
            ->with( 'test_key' )
            ->andReturn( 'test_value' );
            
        $cache_manager = new Cache_Manager();
        $result = $cache_manager->get( 'test_key' );
        
        $this->assertEquals( 'test_value', $result );
    }
}
```

### Test Coverage Goals

- Core classes: 80%+ coverage
- Critical paths: 100% coverage
- Error handling: Complete coverage

## Contributing

### Getting Started

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Write/update tests
5. Update documentation
6. Submit a pull request

### Pull Request Process

1. Ensure all tests pass
2. Follow WordPress Coding Standards
3. Update CHANGELOG in README.md
4. Add PHPDoc blocks for new methods
5. Update relevant documentation

### Commit Messages

Use clear, descriptive commit messages:

```
Add connection timeout configuration

- Add new filter for timeout customization
- Update settings page with timeout option
- Add tests for timeout validation
```

## Code Standards

### WordPress Coding Standards

Follow [WordPress PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/):

- Use tabs for indentation
- Space after control structures
- Yoda conditions for comparisons
- Single quotes for strings (unless interpolation needed)

### PHPDoc Standards

All classes, methods, and functions must have PHPDoc blocks:

```php
/**
 * Short description.
 *
 * Long description if needed.
 *
 * @since 2.0.0
 *
 * @param string $param1 Parameter description.
 * @param int    $param2 Parameter description.
 * @return bool True on success, false on failure.
 */
public function method_name( $param1, $param2 ) {
    // Implementation
}
```

### Naming Conventions

- Classes: `PascalCase` (e.g., `Cache_Manager`)
- Methods: `snake_case` (e.g., `get_cache_stats()`)
- Variables: `snake_case` (e.g., `$cache_duration`)
- Constants: `SCREAMING_SNAKE_CASE` (e.g., `SLUGS_CACHE_KEY`)
- Hooks: `prefix_hook_name` (e.g., `rfpm_cache_duration`)

### Security

Always:
- Sanitize input: `sanitize_text_field()`, `esc_url_raw()`, etc.
- Escape output: `esc_html()`, `esc_attr()`, `esc_url()`, etc.
- Verify nonces: `wp_verify_nonce()`
- Check capabilities: `current_user_can()`
- Validate data types: `absint()`, `is_email()`, etc.

### Performance

Best practices:
- Cache expensive operations
- Use transients appropriately
- Minimize database queries
- Lazy-load when possible
- Add delays for rate-limited APIs

## Questions?

For technical questions or discussions:

- Open an issue on GitHub
- Check existing documentation
- Review the code comments

## License

GPL v2 or later - See LICENSE file for details.

