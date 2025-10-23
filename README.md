# Plugin Curator

A professional WordPress plugin that allows you to curate and manage the featured plugins list from a remote JSON source while pulling real, up-to-date data from WordPress.org.

[![WordPress](https://img.shields.io/badge/WordPress-5.8+-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4+-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v2+-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

## Features

- **Remote Control**: Manage featured plugins from a centralized JSON file
- **Live Data**: Pulls real-time plugin information from WordPress.org
- **Smart Caching**: Configurable caching system for optimal performance
- **Security First**: Built with WordPress security best practices
- **API Authentication**: Optional Bearer token support for protected endpoints
- **Health Checks**: Test remote connectivity directly from admin
- **Fallback Mode**: Automatically falls back to WordPress.org defaults if remote is unavailable
- **Detailed Logging**: WP_DEBUG-aware error logging
- **Cache Management**: Easy cache refresh and statistics viewing

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- `wp_remote_get` enabled (standard WordPress HTTP API)

## Installation

### Via Upload

1. Download the plugin ZIP file
2. Go to WordPress Admin → Plugins → Add New
3. Click "Upload Plugin" and select the ZIP file
4. Click "Install Now" and then "Activate"

### Manual Installation

1. Upload the `plugin-curator` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Tools → Featured Plugins to configure

### Via Composer

```bash
composer require iconick/plugin-curator
```

## Quick Start

1. **Configure Remote URL**
   - Go to WordPress Admin → Tools → Featured Plugins
   - Enter your remote JSON file URL
   - Optionally add an API key if your endpoint requires authentication

2. **Test Connection**
   - Click "Test Connection" to verify your remote source
   - Review the results to ensure slugs are valid

3. **Save Settings**
   - Choose your preferred cache duration
   - Click "Save Settings"

4. **View Featured Plugins**
   - Navigate to Plugins → Add New → Featured
   - Your curated list will appear instead of the default WordPress.org featured plugins

## JSON Format

Your remote JSON file can use either format:

### Simple Array (Recommended)

```json
[
  "contact-form-7",
  "wordpress-seo",
  "woocommerce",
  "elementor",
  "updraftplus",
  "wordfence"
]
```

### Object with Metadata

```json
{
  "version": "1.0",
  "last_updated": "2025-10-22",
  "plugins": [
    "contact-form-7",
    "wordpress-seo",
    "woocommerce"
  ]
}
```

**Important**: Use the plugin slug from the WordPress.org URL. For example, `wordpress.org/plugins/contact-form-7/` has the slug `contact-form-7`.

## Configuration

### Cache Duration

Configure how long plugin data is cached:

- 1 Hour
- 3 Hours
- 6 Hours (default)
- 12 Hours
- 24 Hours

### API Authentication

If your remote JSON endpoint requires authentication, add a Bearer token in the "API Key" field.

## How It Works

1. The plugin fetches plugin slugs from your remote JSON file
2. For each slug, it retrieves current data from WordPress.org API
3. Data includes: name, description, version, ratings, download counts, etc.
4. Everything is cached based on your configured duration
5. If remote is unreachable, WordPress.org defaults are shown

## Admin Features

### Settings Page

Located at **Tools → Featured Plugins**:

- Remote URL configuration
- API key management
- Cache duration settings
- Cache statistics viewing
- Manual cache refresh
- Connection testing
- Current featured plugins list
- JSON format examples

### Cache Management

- View cache status and expiration times
- See data sizes for different cache types
- Manual refresh button for immediate updates
- Automatic cache clearing when settings change

### Health Checks

- Test remote connectivity
- Validate JSON structure
- Check slug validity
- View detailed test results

## Troubleshooting

### Featured plugins not showing

1. Check that your remote URL is accessible
2. Click "Test Connection" to diagnose issues
3. Verify JSON format matches expected structure
4. Check WordPress debug log for detailed errors
5. Try "Refresh Cache Now" button

### Invalid JSON errors

- Validate your JSON at [jsonlint.com](https://jsonlint.com/)
- Ensure proper UTF-8 encoding
- Check for trailing commas

### Plugin not found errors

- Verify slug matches WordPress.org URL
- Check plugin is publicly available
- Ensure slug uses lowercase and hyphens only

### Cache not updating

- Click "Refresh Cache Now" in admin
- Check cache duration settings
- Verify transient storage is working

## Development

### Running Tests

```bash
# Install dependencies
composer install

# Run PHPUnit tests
composer test

# Run with coverage
composer test-coverage
```

### Code Standards

This plugin follows:

- WordPress Coding Standards
- PSR-4 Autoloading
- PHPDoc documentation standards

### Filters & Actions

See [DEVELOPER.md](docs/DEVELOPER.md) for complete hook reference.

## Security

- All inputs are sanitized and validated
- Nonce verification on all forms
- Capability checks throughout
- CSRF protection on admin actions
- Escaped output
- Secure API key storage

## Performance

- Efficient transient caching
- Rate limiting on API requests
- Small delay between WordPress.org requests to avoid rate limits
- Lazy-loaded admin assets
- Minimal database queries

## Changelog

### 1.0.0 - 2025-10-23

- Complete refactor with OOP architecture
- Added PHPUnit test suite
- Enhanced error handling and logging
- Improved caching strategy
- Better validation and sanitization
- Added connection testing
- Security improvements
- Comprehensive documentation

### Initial Release

- Complete OOP architecture
- Remote JSON fetching
- WordPress.org API integration
- Enhanced caching system
- Comprehensive security
- PHPUnit test suite
- Full documentation

## Support

For bugs, feature requests, or questions:

- [GitHub Issues](https://github.com/iconick/plugin-curator/issues)
- [WordPress.org Support Forum](https://wordpress.org/support/plugin/plugin-curator/)

## Contributing

Contributions are welcome! Please read [DEVELOPER.md](docs/DEVELOPER.md) for:

- Architecture overview
- Coding standards
- Testing requirements
- Pull request process

## License

This plugin is licensed under the GPL v2 or later.

```
Copyright (C) 2025 iconick

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

## Credits

Developed by [iconick](https://iconick.io)

Built with WordPress best practices from the [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/).

## Related Links

- [WordPress.org Plugin Directory](https://wordpress.org/plugins/)
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)

