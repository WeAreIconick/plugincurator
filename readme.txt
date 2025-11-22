=== The Curator by Iconick ===
Contributors: iconick
Tags: plugins, featured, curate, manage, remote
Requires at least: 5.8
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Curate and manage featured plugins from a remote JSON source with real-time WordPress.org data.

== Description ==

The Curator by Iconick allows you to curate and manage the featured plugins list from a remote JSON source while pulling real, up-to-date data from WordPress.org. Perfect for agencies, hosting companies, and WordPress networks that want to provide a customized plugin installation experience.

= Features =

* **Remote Control**: Manage featured plugins from a centralized JSON file
* **Live Data**: Pulls real-time plugin information from WordPress.org
* **Complete Plugin Cards**: Displays all standard plugin information including icons, ratings, downloads, author info, and compatibility
* **Smart Caching**: Configurable caching system for optimal performance
* **Security First**: Built with WordPress security best practices
* **API Authentication**: Optional Bearer token support for protected endpoints
* **Health Checks**: Test remote connectivity directly from admin
* **Fallback Mode**: Automatically falls back to WordPress.org defaults if remote is unavailable
* **Detailed Logging**: WP_DEBUG-aware error logging
* **Cache Management**: Easy cache refresh and statistics viewing

= How It Works =

1. The plugin fetches plugin slugs from your remote JSON file
2. For each slug, it retrieves complete data from WordPress.org API
3. Data includes all standard plugin card fields: name, description, icons, ratings, downloads, author info, compatibility, and more
4. Everything is cached based on your configured duration
5. If remote is unreachable, WordPress.org defaults are shown

= JSON Format =

Your remote JSON file can use either format:

**Simple Array (Recommended)**

`[
  "contact-form-7",
  "wordpress-seo",
  "woocommerce"
]`

**Object with Metadata**

`{
  "version": "1.0",
  "last_updated": "2025-10-23",
  "plugins": [
    "contact-form-7",
    "wordpress-seo",
    "woocommerce"
  ]
}`

= Requirements =

* WordPress 5.8 or higher
* PHP 7.4 or higher
* wp_remote_get enabled (standard WordPress HTTP API)

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/featured-curator` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to Tools → Featured Plugins to configure the plugin
4. Enter your remote JSON file URL
5. Optionally add an API key if your endpoint requires authentication
6. Click "Test Connection" to verify your remote source
7. Save settings and visit Plugins → Add New → Featured to see your curated list

== Frequently Asked Questions ==

= Where do I host my JSON file? =

You can host your JSON file anywhere that's publicly accessible via HTTP or HTTPS - your own server, GitHub, a CDN, etc.

= What if my remote source is unavailable? =

The plugin automatically falls back to WordPress.org's default featured plugins if your remote source cannot be reached.

= Can I protect my JSON endpoint? =

Yes! You can configure an API key in the settings, which will be sent as a Bearer token in the Authorization header.

= How often does the plugin fetch data? =

The plugin caches data according to your configured cache duration (1-24 hours). You can manually refresh the cache at any time.

= What happens if a plugin slug is invalid? =

Invalid slugs are skipped, and only valid plugins from WordPress.org are displayed.

== Screenshots ==

1. Settings page with configuration options
2. Featured plugins tab showing curated plugins
3. Cache statistics and management
4. Connection test results

== Changelog ==

= 1.0.1 - 2025-10-23 =
* Enhanced plugin card display to show all standard information
* Now fetches complete plugin data including icons, banners, ratings, downloads, and more
* Improved API client to request all available fields from WordPress.org
* Fixed text domain for proper internationalization

= 1.0.0 - 2025-10-23 =
* Initial release
* Complete OOP architecture with namespaces
* Remote JSON fetching with caching
* WordPress.org API integration
* Security-first design
* PHPUnit test suite
* Comprehensive documentation

== Upgrade Notice ==

= 1.0.1 =
Enhanced plugin cards now display complete information including icons, ratings, and compatibility badges.

= 1.0.0 =
Initial release of The Curator by Iconick - professional plugin curation for WordPress.

== Developer Information ==

= Filters =

* `rfpm_remote_request_args` - Modify remote request arguments
* `rfpm_validated_slugs` - Filter validated plugin slugs
* `rfpm_api_response` - Modify API response object

= Actions =

* `rfpm_plugins_fetched` - Fires after fetching plugins from WordPress.org
* `rfpm_log_error` - Hook into error logging

= GitHub =

Development happens on GitHub: https://github.com/iconick/featured-curator

== Support ==

For bugs, feature requests, or questions:

* GitHub Issues: https://github.com/iconick/the-curator/issues
* WordPress.org Support Forum

== External Services ==

This plugin connects to external services to provide its functionality:

**WordPress.org Plugin API**

This plugin connects to the WordPress.org Plugin API (https://api.wordpress.org/plugins/info/1.0/) to retrieve plugin information including names, descriptions, ratings, download counts, compatibility information, and other metadata.

* **What data is sent**: Plugin slugs (identifiers) are sent to WordPress.org when fetching plugin information. No user data, site information, or personal information is transmitted.
* **When data is sent**: Plugin information is fetched when:
  - A user visits the Featured tab in Plugins → Add New
  - The cache expires and needs to be refreshed
  - A manual cache refresh is triggered
  - A connection test is performed from the admin settings page
* **Service provider**: WordPress.org (WordPress Foundation)
* **Terms of Service**: https://wordpress.org/about/tos/
* **Privacy Policy**: https://wordpress.org/about/privacy/

**User-Configured Remote JSON Source**

This plugin can optionally connect to a remote JSON file (configured by the site administrator) to fetch a list of plugin slugs to feature.

* **What data is sent**: HTTP request headers may include an optional API key (Bearer token) if configured by the administrator. No user data or personal information is transmitted.
* **When data is sent**: The remote JSON file is fetched when:
  - Plugin slugs need to be retrieved (on cache expiration or manual refresh)
  - A connection test is performed from the admin settings page
* **Service provider**: This is configured by the site administrator and may be hosted on any server of their choice (their own server, GitHub, CDN, etc.)
* **Terms of Service and Privacy Policy**: Determined by the service provider chosen by the site administrator

== Privacy Policy ==

The Curator by Iconick does not collect, store, or transmit any user data. It only fetches public plugin information from WordPress.org and your configured remote JSON source.

