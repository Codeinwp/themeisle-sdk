---
name: themeisle-sdk-licenser
description: Use when working with premium license activation, validation, or update delivery in a WordPress plugin using the ThemeIsle SDK. Applies when checking license status, setting up a namespace, using WP-CLI license commands, suppressing the license UI, or configuring auto-activation via license.json.
---

# ThemeIsle SDK — Licenser Module

Handles premium license activation, validation, update delivery, and WP-CLI commands for products not on WordPress.org.

## Activation

Set in the plugin file header — no filter needed:

```
Requires License: yes
```

Disable per-product:

```php
add_filter( 'your_product_key_enable_licenser', '__return_false' );
```

## Checking License Status

```php
// Is the license valid?
if ( tsdk_lis_valid( __FILE__ ) ) { /* unlock pro features */ }

// Status string: 'valid' | 'not_active' | 'active_expired'
$status = tsdk_lstatus( __FILE__ );

// Price plan int (-1 if unknown)
$plan = tsdk_lplan( __FILE__ );

// Raw license key
$key = tsdk_lkey( __FILE__ );
```

## Namespace (enables filter-based access + WP-CLI)

```php
add_filter(
    'themesle_sdk_namespace_' . md5( YOUR_PLUGIN_FILE ),
    fn() => 'your_product'  // e.g. 'neve', 'otter', 'woody'
);
```

With namespace set:

```php
// Activate/deactivate programmatically
$result = apply_filters( 'themeisle_sdk_license_process_your_product', $key, 'activate' );
// true on success, WP_Error on failure

// Via filter
$status = apply_filters( 'product_your_product_license_status', '' );
$key    = apply_filters( 'product_your_product_license_key', '' );
$plan   = apply_filters( 'product_your_product_license_plan', -1 );

// WP-CLI
wp your_product activate <license-key>
wp your_product deactivate
wp your_product is-active
```

## Auto-Activation via File

Place `license.json` in the plugin root:

```json
{ "key": "your-license-key-here" }
```

Automatically activates on `admin_head` if not yet activated — useful for managed deployments.

## Suppressing UI

```php
add_filter( 'your_product_key_hide_license_field',   '__return_true' ); // Hide Settings field
add_filter( 'your_product_key_hide_license_notices', '__return_true' ); // Hide all notices
```

## Label Overrides

```php
add_filter( 'your_product_key_lc_activate_string',   fn() => __( 'Activate', 'td' ) );
add_filter( 'your_product_key_lc_deactivate_string', fn() => __( 'Deactivate', 'td' ) );
add_filter( 'your_product_key_lc_valid_string',      fn() => __( 'Valid', 'td' ) );
add_filter( 'your_product_key_lc_invalid_string',    fn() => __( 'Invalid', 'td' ) );
```

## Support Link

```php
$url = tsdk_support_link( __FILE__ ); // false if license not valid
```

## Options Used

| Option | Content |
|---|---|
| `{key}_license` | Raw license key |
| `{key}_license_data` | Full JSON from license API |
| `{key}_license_status` | Status string |
| `{key}_license_plan` | Price plan ID |
