# User Guide - Plugin Curator

Complete guide for using the Plugin Curator plugin.

## Table of Contents

- [Introduction](#introduction)
- [Installation](#installation)
- [Initial Setup](#initial-setup)
- [Managing Your Plugin List](#managing-your-plugin-list)
- [Admin Interface](#admin-interface)
- [Common Tasks](#common-tasks)
- [Troubleshooting](#troubleshooting)
- [FAQ](#faq)

## Introduction

Plugin Curator allows you to control which plugins appear in the "Featured" tab of the WordPress plugin installer (Plugins → Add New → Featured).

### Benefits

- **Centralized Control**: Update your featured list from one JSON file
- **Multiple Sites**: Use the same list across multiple WordPress installations
- **Live Data**: Plugin information is always current from WordPress.org
- **No Coding**: Simple JSON file, no PHP knowledge required
- **Fallback**: Automatic fallback to WordPress.org if your remote is unavailable

### How It Works

1. You create a JSON file with plugin slugs
2. Host it on any web server (yours or a service)
3. Configure the plugin with your JSON URL
4. The plugin fetches slugs and displays them with live WordPress.org data

## Installation

### Method 1: WordPress Admin

1. Download the plugin ZIP file
2. Go to **Plugins → Add New → Upload Plugin**
3. Choose the ZIP file and click **Install Now**
4. Click **Activate Plugin**

### Method 2: FTP Upload

1. Unzip the plugin file
2. Upload the `plugin-curator` folder to `/wp-content/plugins/`
3. Go to **Plugins** in WordPress admin
4. Find "Plugin Curator" and click **Activate**

### Method 3: WP-CLI

```bash
wp plugin install plugin-curator.zip --activate
```

## Initial Setup

### Step 1: Create Your JSON File

Create a file named `featured-plugins.json` with this content:

```json
[
  "contact-form-7",
  "wordpress-seo",
  "woocommerce"
]
```

**Finding Plugin Slugs**:
- Go to WordPress.org and find your plugin
- Look at the URL: `https://wordpress.org/plugins/SLUG-HERE/`
- The slug is the part after `/plugins/`

Example: For Contact Form 7, the URL is `https://wordpress.org/plugins/contact-form-7/`, so the slug is `contact-form-7`.

### Step 2: Host Your JSON File

Upload `featured-plugins.json` to any web server:

**Option A: Your Own Server**
- Upload via FTP/SFTP
- Place in a public directory
- Note the full URL (e.g., `https://example.com/featured-plugins.json`)

**Option B: GitHub**
- Create a GitHub repository
- Upload the JSON file
- Use the raw URL (e.g., `https://raw.githubusercontent.com/user/repo/main/featured-plugins.json`)

**Option C: Cloud Storage**
- Google Cloud Storage
- Amazon S3
- Digital Ocean Spaces
- Any CDN service

Make sure the URL is publicly accessible (or configure authentication).

### Step 3: Configure the Plugin

1. Go to **Tools → Featured Plugins**
2. Enter your JSON URL in the "Remote JSON URL" field
3. Click **Save Settings**
4. Click **Test Connection** to verify it works

### Step 4: Test

1. Go to **Plugins → Add New**
2. Click the **Featured** tab
3. Your curated plugins should appear!

## Managing Your Plugin List

### Adding Plugins

1. Edit your JSON file
2. Add the new plugin slug to the array:

```json
[
  "contact-form-7",
  "wordpress-seo",
  "woocommerce",
  "akismet"
]
```

3. Save the file
4. Wait for cache to expire (or click **Refresh Cache Now** in WordPress admin)

### Removing Plugins

1. Edit your JSON file
2. Remove the slug from the array
3. Save the file
4. Refresh cache if needed

### Reordering Plugins

The order in your JSON file determines the display order:

```json
[
  "woocommerce",
  "wordpress-seo",
  "contact-form-7"
]
```

WooCommerce will appear first, followed by Yoast SEO, then Contact Form 7.

### Best Practices

- **Keep it reasonable**: 6-20 plugins is a good range
- **Verify slugs**: Always test after adding new plugins
- **Use popular plugins**: Lesser-known plugins may have incomplete data
- **Update regularly**: Review and update your list quarterly

## Admin Interface

### Settings Tab

**Remote JSON URL**
- Your JSON file location
- Must be a valid HTTP or HTTPS URL
- Should return JSON content type

**API Key (Optional)**
- For protected endpoints requiring authentication
- Sent as Bearer token in Authorization header
- Leave empty for public URLs

**Cache Duration**
- How long to cache plugin data
- Options: 1, 3, 6, 12, or 24 hours
- Default: 6 hours
- Shorter = more requests, more current data
- Longer = fewer requests, better performance

### Cache Statistics

View current cache status:
- **Status**: Whether data is cached
- **Size**: Approximate data size
- **Time Remaining**: When cache expires

### Current Featured Plugins

Shows:
- List of currently cached plugins
- Links to view on WordPress.org
- Total count

### Actions

**Refresh Cache Now**
- Immediately clears all caches
- Forces fresh fetch from remote
- Use after updating your JSON file

**Test Connection**
- Verifies remote URL is accessible
- Validates JSON format
- Shows how many valid slugs were found
- Helpful for troubleshooting

## Common Tasks

### Updating Your Plugin List

1. Edit your JSON file on your server
2. Go to **Tools → Featured Plugins** in WordPress
3. Click **Refresh Cache Now**
4. Verify changes by visiting **Plugins → Add New → Featured**

### Changing the Remote URL

1. Go to **Tools → Featured Plugins**
2. Update the "Remote JSON URL" field
3. Click **Save Settings**
4. Cache is automatically cleared
5. Click **Test Connection** to verify

### Adding Authentication

If your JSON endpoint requires authentication:

1. Generate an API key/token from your service
2. Go to **Tools → Featured Plugins**
3. Enter the key in "API Key (Optional)"
4. Click **Save Settings**
5. Test connection

The plugin sends the key as:
```
Authorization: Bearer YOUR-API-KEY
```

### Troubleshooting Connection Issues

1. Go to **Tools → Featured Plugins**
2. Click **Test Connection**
3. Review the results:
   - ✓ Success: Shows number of valid slugs found
   - ✗ Error: Shows specific error message

4. Common issues:
   - **URL not accessible**: Check firewall, hosting settings
   - **Invalid JSON**: Validate at jsonlint.com
   - **Invalid slugs**: Check slug spelling and format

### Monitoring Cache

View cache status at **Tools → Featured Plugins**:

- **Green checkmark**: Data is cached
- **Gray X**: Cache is empty
- **Time Remaining**: Shows when next refresh occurs

### Performance Optimization

For best performance:

1. Use longer cache duration (12-24 hours)
2. Keep plugin list reasonable (under 20)
3. Use reliable, fast hosting for JSON file
4. Consider using a CDN for JSON file

## Troubleshooting

### Featured Plugins Not Showing

**Problem**: Custom plugins don't appear in Featured tab

**Solutions**:
1. Check remote URL is correct
2. Click "Test Connection" to diagnose
3. Verify JSON file is publicly accessible
4. Check WordPress debug log for errors
5. Try "Refresh Cache Now"

### Connection Timeout

**Problem**: Test connection times out

**Solutions**:
1. Increase timeout (contact developer for filter)
2. Move JSON file to faster server
3. Use CDN for JSON hosting
4. Check server firewall rules

### Invalid JSON Error

**Problem**: "Invalid JSON" error appears

**Solutions**:
1. Validate JSON at https://jsonlint.com/
2. Check for trailing commas
3. Ensure proper UTF-8 encoding
4. Verify quotes are straight (not curly)
5. Check for hidden characters

### Plugin Not Found

**Problem**: Some plugins show as "not found"

**Solutions**:
1. Verify slug matches WordPress.org URL exactly
2. Check plugin is publicly available
3. Ensure plugin hasn't been removed from WordPress.org
4. Use only lowercase letters, numbers, and hyphens

### Cache Won't Update

**Problem**: Changes to JSON file don't appear

**Solutions**:
1. Click "Refresh Cache Now" in admin
2. Check cache duration setting
3. Verify WordPress transients are working
4. Clear WordPress object cache if using one
5. Check server time is correct

### Authentication Fails

**Problem**: API key authentication not working

**Solutions**:
1. Verify API key is correct
2. Check key hasn't expired
3. Ensure server accepts Bearer tokens
4. Test endpoint with curl:
   ```bash
   curl -H "Authorization: Bearer YOUR-KEY" https://your-url.com/featured-plugins.json
   ```

## FAQ

### How often is data updated?

Plugin data from WordPress.org is cached based on your Cache Duration setting (default: 6 hours). Your JSON file is also cached for the same duration.

### Can I use this on multiple sites?

Yes! Use the same JSON URL on all your sites. Update one file to update all sites (after cache expires or manual refresh).

### Does it work with multisite?

Yes, each site in a multisite network can have its own configuration.

### What happens if my JSON file goes offline?

The plugin automatically falls back to WordPress.org's default featured plugins. No errors are shown to users.

### Can I use a private URL?

Yes, use the API Key field to authenticate. The plugin sends it as a Bearer token.

### How many plugins can I feature?

No hard limit, but 6-20 is recommended for best user experience and performance.

### Does this affect plugin search?

No, only the Featured tab is affected. Search, Popular, Recommended, and Favorites work normally.

### Can I track usage?

Not built-in, but you can monitor JSON file access logs on your server, or use the `rfpm_plugins_fetched` action hook to log activity.

### Is it compatible with X plugin?

Should work with all plugins. If you find an incompatibility, please report it.

### How do I uninstall?

Deactivate and delete the plugin through WordPress admin. All settings and caches are automatically removed.

## Need Help?

If you're still having issues:

1. Enable WordPress debugging:
   ```php
   define( 'WP_DEBUG', true );
   define( 'WP_DEBUG_LOG', true );
   ```

2. Check the debug log at `wp-content/debug.log`

3. Look for entries starting with "RFPM"

4. Open a support ticket with:
   - WordPress version
   - PHP version  
   - Error messages from log
   - Steps to reproduce issue

## Additional Resources

- [Plugin Homepage](#)
- [GitHub Repository](#)
- [WordPress.org Support Forum](#)
- [Video Tutorials](#)

---

**Last Updated**: October 2025  
**Plugin Version**: 1.0.0  
**Author**: [iconick](https://iconick.io)

