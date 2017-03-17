---
name: themeisle-sdk-setup
description: Use when registering a WordPress plugin or theme with the ThemeIsle SDK, setting required file headers (WordPress Available, Requires License, Pro Slug), or using global SDK helpers like tsdk_lstatus, tsdk_lis_valid, tsdk_lkey, tsdk_utmify, or tsdk_support_link.
---

# ThemeIsle SDK — Setup & Registration

## Product Registration

In your plugin's main file:

```php
add_filter( 'themeisle_sdk_products', function( $products ) {
    $products[] = __FILE__;
    return $products;
} );
```

For themes, use the path to `style.css`.

## Required File Headers

```
Plugin Name:          Your Plugin
Version:              1.0.0
WordPress Available:  yes   # yes = on WP.org; no = premium only
Requires License:     no    # yes = Licenser module loads
Pro Slug:             your-plugin-pro  # optional, links free ↔ pro for telemetry
```

`WordPress Available` and `Requires License` control which modules load automatically.

## Product Key vs Slug

| Term | How derived | Example |
|---|---|---|
| **slug** | `basename(dirname($basefile))` — the plugin directory name, hyphens preserved | `my-plugin` |
| **key** | slug with hyphens → underscores | `my_plugin` |

Filter names use **slug** (e.g. `my-plugin_sdk_migrations_path`).
Option names use **key** (e.g. `my_plugin_ran_migrations`).

## Global Helpers (from load.php)

```php
tsdk_lstatus( __FILE__ )      // License status string
tsdk_lis_valid( __FILE__ )    // bool: license valid?
tsdk_lkey( __FILE__ )         // Raw license key
tsdk_lplan( __FILE__ )        // License plan/price ID (int)
tsdk_support_link( __FILE__ ) // Pre-filled support URL (false if not licensed)
tsdk_utmify( $url, $area, $location ) // Append UTM params to a URL
```

## Global Label Overrides

Any module label can be overridden via:

```php
add_filter( 'themeisle_sdk_labels', function( $labels ) {
    // See individual module skills for available label keys
    return $labels;
} );
```

## UTM Helper

```php
$url = tsdk_utmify( 'https://yourstore.com/upgrade', 'dashboard', 'sidebar' );
// Appends: ?utm_source=wpadmin&utm_medium=dashboard&utm_campaign=sidebar
```
