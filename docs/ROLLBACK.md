# Rollback Module

> Lets site administrators roll back a plugin or theme to its previous version from within wp-admin.

## When It Loads

Always, for every registered product (not partner products).

## How It Works

### Plugins

A "Rollback to vX.X" link is added to the plugin's action links on the **Plugins** screen. Clicking it triggers a standard WordPress upgrade flow that installs the previous version.

### Themes

A "Rollback to vX.X" button is injected into the **Theme details overlay** on the Themes screen via a jQuery interval check.

## Version Sources

The module fetches the previous version from different places depending on the product type:

| Product type | Source |
|---|---|
| WP.org plugin | `https://api.wordpress.org/plugins/info/1.0/{slug}` |
| WP.org theme | WordPress.org themes API |
| Premium (licensed) | `https://api.themeisle.com/license/versions/{product}/{key}/{url}/{version}` |

> [!NOTE]
> For premium products the license must be active and have a key of at least 10 characters before the rollback API is called.

Version data is cached for **5 days** using a transient keyed by `{product_key}_{version}versions`.

## Customizing the Button Label

```php
add_filter( 'your_product_key_rollback_label', function( $label ) {
    return __( 'Downgrade to v%s', 'text-domain' );
    // %s is replaced with the version number
} );
```

Or via the global labels:

```php
add_filter( 'themeisle_sdk_labels', function( $labels ) {
    $labels['rollback']['cta'] = 'Rollback to v%s';
    return $labels;
} );
```

## Filtering Available Rollback Versions

Intercept and modify the version list before the module picks one:

```php
add_filter( 'your_product_key_rollbacks', function( $versions ) {
    // $versions is an array of ['version' => 'x.x.x', 'url' => '...']
    return $versions;
} );
```
