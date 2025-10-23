# Changelog

All notable changes to Plugin Curator will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.1] - 2025-10-23

### Changed
- Enhanced API client to request all plugin fields from WordPress.org API
- Now uses `plugins_api()` with comprehensive field specification for complete plugin data
- Plugin cards now display all standard information: icons, banners, ratings, downloads, author info, compatibility, and more

### Fixed
- Fixed plugin cards showing minimal information by requesting all available fields from WordPress.org
- Improved data completeness for better user experience in Featured plugins tab

## [1.0.0] - 2025-10-23

### Added

#### Architecture
- Complete OOP refactor with PSR-4 autoloading
- Namespace support (`RFPM\`)
- Dependency injection pattern
- Separation of concerns across multiple classes

#### Core Classes
- `RFPM\Plugin` - Main plugin coordinator
- `RFPM\Cache_Manager` - Enhanced caching system
- `RFPM\Remote_Source` - Remote JSON fetching
- `RFPM\API_Client` - WordPress.org API interactions
- `RFPM\Plugin_Filter` - Featured plugins filtering
- `RFPM\Settings` - Settings management
- `RFPM\Admin_Menu` - Admin interface handler

#### Features
- Connection testing functionality
- Cache statistics dashboard
- Health check system
- Batch plugin verification
- Rate limiting for API requests
- Partial cache handling
- Cache warming capability
- Enhanced error logging with WP_DEBUG awareness

#### Security
- Comprehensive nonce verification on all forms
- CSRF protection on admin actions
- Proper capability checks throughout
- Input sanitization with type-specific functions
- Output escaping on all displays
- Secure API key storage
- URL validation and filtering

#### Testing
- Complete PHPUnit test suite
- WP_Mock integration
- Mockery for object mocking
- Test coverage configuration
- Bootstrap file for test environment
- Tests for all core classes

#### Documentation
- Comprehensive README.md with badges
- DEVELOPER.md with architecture details
- USER-GUIDE.md with step-by-step instructions
- Complete PHPDoc blocks on all classes/methods
- Inline code comments
- Hook reference documentation
- Contributing guidelines
- Code standards documentation

#### Developer Tools
- composer.json with autoloading
- phpunit.xml.dist configuration
- .gitignore for common files
- Action hooks for extensibility
- Filter hooks for customization

### Changed

#### Breaking Changes
- Moved from single file to multi-file structure
- Organized code with namespaced classes under `RFPM\` namespace
- Updated cache keys (prefixed with `rfpm_`)
- Changed option names (prefixed with `rfpm_`)

#### Improvements
- Enhanced cache management with statistics
- Better error handling and logging
- Improved validation for plugin slugs
- More efficient API request handling
- Better fallback mechanisms
- Optimized admin interface
- Enhanced settings validation
- Better user feedback and notices

#### Admin Interface
- Redesigned settings page with cards
- Added cache statistics table
- Improved visual feedback
- Better error messages
- Added action buttons for common tasks
- Enhanced documentation on settings page

### Fixed
- Proper transient expiration handling
- Better JSON parsing error messages
- Improved remote URL validation
- Fixed potential race conditions in caching
- Better handling of partial data fetches
- Proper WordPress coding standards compliance

### Security
- All user inputs properly sanitized
- All outputs properly escaped
- Nonce verification on all forms
- Proper capability checks
- Secure HTTP requests with SSL verification
- Protected against XSS, CSRF, and SQL injection

## [1.0.0] - Initial Release

### Added
- Basic plugin functionality
- Remote JSON file fetching
- WordPress.org API integration
- Featured plugins filtering
- Admin settings page
- Cache management with transients
- API key authentication support
- Basic error handling

---

## Upgrade Notice

### 1.0.0

Initial release of Plugin Curator. A professional WordPress plugin to curate and manage featured plugins from a remote JSON source with enterprise-grade architecture, security, and testing.

---

## Support

For questions, bug reports, or feature requests:
- GitHub Issues: https://github.com/iconick/plugin-curator/issues
- WordPress.org Support: https://wordpress.org/support/plugin/plugin-curator/

## Contributors

- iconick (@iconick)

---

[1.0.1]: https://github.com/iconick/plugin-curator/releases/tag/v1.0.1
[1.0.0]: https://github.com/iconick/plugin-curator/releases/tag/v1.0.0

