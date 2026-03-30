# Logger Module

> Collects anonymous usage data and telemetry to help the team understand how products are used.

The Logger module has two components: a **PHP-side log** (environment snapshot sent on a schedule) and a **JS-side telemetry** tracker (event-level tracking in the browser). See [TELEMETRY.md](TELEMETRY.md) for the JS telemetry details.

## When It Loads

Always, for every registered product. Can be disabled per-product:

```php
add_filter( 'your_product_slug_sdk_enable_logger', '__return_false' );
```

Or disabled globally:

```php
add_filter( 'themeisle_sdk_disable_telemetry', '__return_true' );
```

## Opt-In Behaviour

For products on WordPress.org (`WordPress Available: yes`), logging is **opt-in by default**. The module adds a notification asking for consent. The user must click "Sure, I would love to help" before any data is sent.

For **premium products** (`Requires License: yes`) or products **not** on WordPress.org, logging is **opt-in by default as well**, but the opt-in flag is automatically set to `yes` if a valid license is detected.

The consent flag is stored in `{product_key}_logger_flag` (`yes` | `no`).

## Providing Logger Data

Your product can attach arbitrary data to the log payload:

```php
add_filter( 'your_product_key_logger_data', function( $data ) {
    $data['settings']       = get_option( 'your_plugin_settings', [] );
    $data['active_modules'] = [ 'module_a', 'module_b' ];
    return $data;
} );
```

The data is sent alongside standard environment info (WordPress version, active theme/plugins, product version, install time, locale).

## What Is Sent

Each log payload contains:

```json
{
  "site": "https://example.com",
  "slug": "your-plugin",
  "version": "2.1.0",
  "wp_version": "6.5.0",
  "install_time": 1700000000,
  "locale": "en_US",
  "license": "not_active",
  "data": { ... },
  "environment": {
    "theme": { "name": "...", "author": "...", "parent": "..." },
    "plugins": [ "active-plugin/active-plugin.php", "..." ]
  }
}
```

Logs are sent to `https://api.themeisle.com/tracking/log`.

## Schedule

When logging is active, the SDK schedules a single cron event with a random 1–24 hour delay:

```
{product_key}_log_activity
```

The event fires once, sends the log, and is not re-scheduled automatically. Products typically re-enable it on the next page load.

## JS Telemetry

To load the JS telemetry script alongside the logger, add this filter:

```php
add_filter( 'themeisle_sdk_enable_telemetry', '__return_true' );
```

This causes the SDK to enqueue a `tracking` script on every admin page that exposes a `tiTrk` global for event tracking. See [TELEMETRY.md](TELEMETRY.md) for full usage.

> [!NOTE]
> The JS telemetry script is only enqueued once across all products, from the product with the latest SDK version.

## Controlling Which Products Appear in Telemetry

If a product inherits its license from another product (e.g. a pro add-on), you can adjust the telemetry product list:

```php
add_filter( 'themeisle_sdk_telemetry_products', function( $products ) {
    // Remove, add, or modify entries
    return $products;
} );
```

Each entry shape: `[ 'slug' => 'otter', 'trackHash' => 'free|{hash}', 'consent' => true ]`

## Forcing Logger On From Code

If you want to force the logger active (e.g. when a pro licence is detected) do it on the `init` hook:

```php
add_action( 'init', function() {
    if ( your_license_is_active() ) {
        update_option( 'your_product_key_logger_flag', 'yes' );
    }
} );
```
