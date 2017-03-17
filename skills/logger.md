---
name: themeisle-sdk-logger
description: Use when configuring anonymous usage tracking or JS feature telemetry in a WordPress plugin using the ThemeIsle SDK. Applies when attaching custom data to the logger payload, enabling the tiTrk JS telemetry tracker, managing consent flags, or adjusting which products appear in telemetry.
---

# ThemeIsle SDK — Logger & Telemetry Module

Collects anonymous usage data (PHP-side environment snapshot) and optional JS-side feature telemetry.

## Opt-Out

```php
add_filter( 'your_product_slug_sdk_enable_logger', '__return_false' ); // Per-product
add_filter( 'themeisle_sdk_disable_telemetry',     '__return_true'  ); // Global
```

## Opt-In Behaviour

- **WP.org products** (`WordPress Available: yes`): opt-in by default, user must consent via notice.
- **Premium / non-WP.org products**: opt-in by default, auto-set to `yes` when a valid license is detected.

Consent flag: `{product_key}_logger_flag` (`'yes'` | `'no'`)

Force active from code:

```php
add_action( 'init', function() {
    if ( your_license_is_active() ) {
        update_option( 'your_product_key_logger_flag', 'yes' );
    }
} );
```

## Attaching Custom Data to the Log

```php
add_filter( 'your_product_key_logger_data', function( $data ) {
    $data['active_modules'] = [ 'module_a', 'module_b' ];
    $data['settings']       = get_option( 'your_plugin_settings', [] );
    return $data;
} );
```

Sent to `https://api.themeisle.com/tracking/log` alongside standard environment info (WP version, active plugins/theme, install time, locale).

## JS Telemetry

Enable the `tiTrk` JS tracker:

```php
add_filter( 'themeisle_sdk_enable_telemetry', '__return_true' );
```

Track feature usage in JS:

```javascript
// Record once per page session (de-duped by object hash)
window.tiTrk?.with('your-plugin').add({
    feature: 'settings-panel',
    featureComponent: 'color-picker',
});

// Record the last value of a user input
window.tiTrk?.with('your-plugin').set( `${id}_size`, {
    feature: 'form-field',
    featureComponent: 'max-size',
    featureValue: maxSize,
    groupID: id,
});
```

`add` → answers "Did the user use this?" (unique per session)
`set` → answers "What value did the user set?" (overrides on each call)

## Telemetry Product Overrides

For pro add-ons that inherit a license from their free counterpart:

```php
add_filter( 'themeisle_sdk_telemetry_products', function( $products ) {
    // Modify the product list sent to tiTelemetry
    return $products;
} );
```
