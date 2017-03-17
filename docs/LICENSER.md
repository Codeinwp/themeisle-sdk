# Licenser Module

> Handles premium license activation, deactivation, validation, and update delivery for products that are not available on WordPress.org.

## When It Loads

Only loads when the product's main file has:

```
Requires License: yes
```

It can also be disabled per-product:

```php
add_filter( 'your_product_key_enable_licenser', '__return_false' );
```

## What It Does

- Adds a license key input field to **Settings → General** in wp-admin
- Validates the key against `https://api.themeisle.com/`
- Stores license data in the `{key}_license_data` option
- Injects updates into WordPress's update mechanism so premium products receive updates without being on WP.org
- Shows admin notices for inactive, expired, or no-activations-left licenses
- Supports auto-activation from a `license.json` file bundled with the product
- Provides WP-CLI commands for headless license management
- Prevents expired licenses from blocking rollbacks

## License Status Constants

| Constant | Value | Meaning |
|----------|-------|---------|
| `STATUS_VALID` | `'valid'` | Active valid license |
| `STATUS_NOT_ACTIVE` | `'not_active'` | No license entered |
| `STATUS_ACTIVE_EXPIRED` | `'active_expired'` | Was valid but has since expired |

## Checking License Status in Your Product

Use the global helper functions from `load.php`:

```php
// Is the license valid?
if ( tsdk_lis_valid( __FILE__ ) ) {
    // unlock pro features
}

// Get current status string
$status = tsdk_lstatus( __FILE__ ); // 'valid', 'not_active', 'active_expired'

// Get the price plan (useful for feature gating)
$plan = tsdk_lplan( __FILE__ ); // int (price_id), -1 if unknown

// Get the raw license key
$key = tsdk_lkey( __FILE__ );
```

Alternatively, use the filter-based interface if you have a namespace set up:

```php
$status = apply_filters( 'product_{namespace}_license_status', '' );
$key    = apply_filters( 'product_{namespace}_license_key', '' );
$plan   = apply_filters( 'product_{namespace}_license_plan', -1 );
```

## Setting Up a Namespace

A namespace unlocks filter-based license access and WP-CLI commands. Register it once during plugin init:

```php
add_filter(
    'themesle_sdk_namespace_' . md5( YOUR_PLUGIN_FILE ),
    function() {
        return 'your_product'; // e.g. 'neve', 'otter', 'woody'
    }
);
```

With namespace `your_product`, the following become available:

```php
// Trigger activate/deactivate programmatically
$result = apply_filters( 'themeisle_sdk_license_process_your_product', $license_key, 'activate' );
// $result is true on success, WP_Error on failure

// WP-CLI
wp your_product activate <license-key>
wp your_product deactivate
wp your_product is-active
```

## Auto-Activation via File

Place a `license.json` file in the plugin/theme root directory:

```json
{ "key": "your-license-key-here" }
```

The SDK reads this on `admin_head` and activates the license automatically if the site has not been activated yet. Useful for managed/bundled deployments.

## Suppressing the UI

Hide the license field on the Settings page:

```php
add_filter( 'your_product_key_hide_license_field', '__return_true' );
```

Suppress all license-related admin notices:

```php
add_filter( 'your_product_key_hide_license_notices', '__return_true' );
```

## Customizing Labels

```php
add_filter( 'themeisle_sdk_labels', function( $labels ) {
    $labels['licenser']['notice'] = __( 'Enter your license key from %s to get updates', 'text-domain' );
    return $labels;
} );
```

Per-product label overrides (take priority over the global filter):

```php
add_filter( 'your_product_key_lc_activate_string',   fn() => __( 'Activate', 'td' ) );
add_filter( 'your_product_key_lc_deactivate_string', fn() => __( 'Deactivate', 'td' ) );
add_filter( 'your_product_key_lc_valid_string',      fn() => __( 'Valid', 'td' ) );
add_filter( 'your_product_key_lc_invalid_string',    fn() => __( 'Invalid', 'td' ) );
add_filter( 'your_product_key_lc_license_message',   fn() => __( 'Your message', 'td' ) );
```

## Update Behavior

For **plugins** (`WordPress Available: no`), the module hooks into `pre_set_site_transient_update_plugins` to inject the update payload from the license API.

For **themes** (`WordPress Available: no`), it hooks into `site_transient_update_themes` and adds a custom update nag on the Themes screen.

It also blocks WordPress.org from checking updates for the theme so the two systems don't conflict.

## Renew Link

When a license is expired (`active_expired`), the SDK adds a **Renew license to update** link in the plugin action links on the Plugins screen:

```
{store_url}/checkout/?edd_license_key={key}&download_id={download_id}
```

## Support Link

Get a pre-filled support URL for licensed users:

```php
$url = tsdk_support_link( __FILE__ );
// Returns false if license is not valid
```

## Options Used

| Option | Content |
|--------|---------|
| `{key}_license` | Raw license key entered by user |
| `{key}_license_data` | Full JSON object from license API |
| `{key}_license_status` | Current status string |
| `{key}_license_plan` | License plan/price ID |
| `{key}_failed_checks` | Count of consecutive failed API checks |
| `{key}-update-response` | Transient: cached update check response |
