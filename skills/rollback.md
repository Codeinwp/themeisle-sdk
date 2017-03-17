---
name: themeisle-sdk-rollback
description: Use when customizing the version rollback feature in a WordPress plugin or theme that uses the ThemeIsle SDK. Applies when changing the rollback button label, filtering the list of available rollback versions, or understanding how the module fetches version data.
---

# ThemeIsle SDK — Rollback Module

Adds a rollback link to the Plugins screen (and Themes overlay) so admins can downgrade to the previous version.

Loads automatically for all registered products. No configuration required.

## Customizing the Button Label

```php
add_filter( 'your_product_key_rollback_label', function( $label ) {
    return __( 'Downgrade to v%s', 'text-domain' ); // %s = version number
} );

// Or via global labels:
add_filter( 'themeisle_sdk_labels', function( $labels ) {
    $labels['rollback']['cta'] = 'Rollback to v%s';
    return $labels;
} );
```

## Filtering Available Versions

```php
add_filter( 'your_product_key_rollbacks', function( $versions ) {
    // $versions = [ ['version' => 'x.x.x', 'url' => '...'], ... ]
    return $versions;
} );
```

## Version Sources

| Product type | Source |
|---|---|
| WP.org plugin | `https://api.wordpress.org/plugins/info/1.0/{slug}` |
| WP.org theme | WordPress.org themes API |
| Premium (licensed) | `https://api.themeisle.com/license/versions/{product}/{key}/{url}/{version}` |

> For premium products the license must be active and have a key ≥ 10 characters.

Version data is cached for 5 days via transient `{product_key}_{version}versions`.
