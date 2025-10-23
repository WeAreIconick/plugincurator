# Plugin Card Enhancement - Technical Documentation

## Overview

This document explains the enhancement made to display complete plugin card information when using the simple JSON format.

## Problem

Previously, when using a simple JSON format with just plugin slugs (e.g., `["akismet", "wordpress-seo"]`), the plugin cards displayed in the WordPress admin "Featured" tab showed minimal information - typically just the plugin name and basic details.

## Solution

Modified the API Client to use WordPress's native `plugins_api()` function with comprehensive field specifications, ensuring all data needed for complete plugin card display is retrieved from WordPress.org.

## Technical Changes

### File Modified: `includes/class-api-client.php`

#### Before

The `fetch_plugin()` method used a direct HTTP request to the WordPress.org API endpoint:

```php
private function fetch_plugin( $slug ) {
    $api_url = self::API_BASE_URL . $slug . '.json';
    
    $response = wp_remote_get( $api_url, array(
        'timeout'   => 10,
        'sslverify' => true,
    ));
    
    // Basic JSON parsing...
}
```

This approach fetched basic plugin data but didn't specify which fields to include.

#### After

Now uses WordPress's built-in `plugins_api()` function with explicit field specifications:

```php
private function fetch_plugin( $slug ) {
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
    
    $plugin_data = plugins_api( 'plugin_information', $args );
    
    // Error handling...
}
```

## Data Now Included

With this enhancement, each plugin card now displays:

### Visual Elements
- **Plugin Icon**: High-resolution plugin icon image
- **Banner Image**: Plugin banner (if available)

### Core Information
- **Plugin Name**: Full plugin name/title
- **Description**: Short description of the plugin
- **Author**: Plugin author name and profile link

### Metrics & Statistics
- **Star Rating**: Visual star rating (0-5 stars)
- **Number of Ratings**: Total count of user ratings
- **Active Installations**: Number of active WordPress sites using the plugin
- **Download Count**: Total number of downloads

### Version & Compatibility
- **Version**: Current plugin version
- **Last Updated**: Human-readable time since last update
- **WordPress Compatibility**: "Compatible with your version of WordPress" badge
- **Requires WordPress**: Minimum WordPress version required
- **Requires PHP**: Minimum PHP version required

### Actions
- **Install/Activate Button**: Action button with proper state
- **More Details Link**: Link to full plugin information modal

## Benefits

1. **Complete User Experience**: Users see the same rich information as they would with default WordPress.org featured plugins
2. **Better Decision Making**: All metrics and compatibility info help users make informed decisions
3. **Professional Appearance**: Plugin cards look polished and complete
4. **Standards Compliance**: Uses WordPress's official API approach
5. **Consistency**: Display matches WordPress core behavior exactly

## Usage

No changes needed to your JSON format! Simply use the simple slug format:

```json
[
  "akismet",
  "wordpress-seo",
  "contact-form-7",
  "wordfence"
]
```

Each plugin will now display with complete information automatically.

## Performance Considerations

- **Caching**: All plugin data is cached according to your configured cache duration
- **Rate Limiting**: Small delay (0.1 seconds) between API requests prevents rate limiting
- **Efficient Requests**: Only fetches data when cache expires
- **Single Source**: Uses WordPress.org as the authoritative source for all plugin data

## WordPress API Details

The `plugins_api()` function is WordPress's official way to interact with the WordPress.org Plugin API. Benefits include:

- **Standardized**: Uses WordPress's built-in HTTP API
- **Error Handling**: Built-in WordPress error handling
- **Filters**: Respects WordPress filters and hooks
- **Compatibility**: Works with all WordPress versions 5.8+

## Testing

After updating, you can verify the enhancement works:

1. Clear your plugin cache (Settings → Featured Plugins → "Refresh Cache Now")
2. Navigate to Plugins → Add New → Featured
3. Verify each plugin card shows:
   - ✅ Plugin icon image
   - ✅ Star rating display
   - ✅ Number of ratings (e.g., "(1,153)")
   - ✅ "Last Updated" with time
   - ✅ Active installations count
   - ✅ Compatibility badge
   - ✅ Author name and link
   - ✅ Full description
   - ✅ Install/Activate buttons
   - ✅ "More Details" link

## Backwards Compatibility

This change is fully backwards compatible:

- ✅ Existing JSON files work without modification
- ✅ Simple array format still supported
- ✅ Object format with metadata still supported
- ✅ All existing features continue to work
- ✅ No database changes required
- ✅ No configuration changes needed

## Version History

- **v1.0.1** (2025-10-23): Enhanced to fetch complete plugin data
- **v1.0.0** (2025-10-23): Initial release with basic functionality

## Related Files

- `includes/class-api-client.php` - API client with enhanced field requests
- `includes/class-plugin-filter.php` - Filters featured plugins API requests
- `includes/class-remote-source.php` - Fetches plugin slugs from remote JSON
- `plugin-curator.php` - Main plugin file

## Additional Notes

The enhancement maintains all existing security measures:
- Input sanitization
- Output escaping
- Nonce verification
- Capability checks
- Secure HTTP requests with SSL verification

## Support

If you encounter any issues with plugin card display:

1. Check WordPress debug log for errors
2. Use "Test Connection" to verify remote JSON is accessible
3. Try "Refresh Cache Now" to clear stale data
4. Verify WordPress.org API is accessible from your server
5. Check that plugin slugs are correct and plugins exist on WordPress.org

