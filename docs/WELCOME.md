# Welcome Module

> Shows a one-time upgrade offer to free users between days 7 and 12 after installation.

## When It Loads

Only when the product returns valid (enabled) metadata:

```php
add_filter( 'your_product_key_welcome_metadata', function() {
    return [
        'is_enabled' => ! defined( 'YOUR_PRO_VERSION' ),  // Only show if pro is not active
        'pro_name'   => 'Your Plugin Pro',
        'logo'       => plugin_dir_url( __FILE__ ) . 'assets/logo.png',
        'cta_link'   => tsdk_utmify( 'https://yourstore.com/upgrade?discount=WELCOME30', 'welcome-notice' ),
    ];
} );
```

If `is_enabled` is falsy the module does not load. When the pro version is already active you should return `false` (or omit `is_enabled`) so the notice never appears for existing customers.

## Timing

| Days since install | Behaviour |
|---|---|
| 0 – 7 | Not shown |
| 7 – 12 | Shown (the window) |
| 12+ | Never shown again |

The notice also goes through the standard [notification queue](NOTIFICATIONS.md), so the 5-day cooldown between notices still applies. If another notice is already showing, the welcome notice preempts it — it is given highest priority.

## Customizing the Message

The default message uses placeholders that the SDK replaces automatically:

- `{product}` — the free product's friendly name
- `{pro_product}` — the `pro_name` from your metadata (or `{product} PRO`)
- `{cta_link}` — the `cta_link` from your metadata

```php
add_filter( 'your_product_key_welcome_upsell_message', function( $message ) {
    return '<p>You\'ve been using <b>{product}</b> for a week! Upgrade to <b>{pro_product}</b> and save 30%. <a href="{cta_link}" target="_blank">Upgrade now</a></p>';
} );
```

Or override via the global labels:

```php
add_filter( 'themeisle_sdk_labels', function( $labels ) {
    $labels['welcome']['message'] = '<p>Custom message with {product} placeholder.</p>';
    $labels['welcome']['ctay']    = 'Upgrade Now!';
    $labels['welcome']['ctan']    = 'No, thanks.';
    return $labels;
} );
```

## Forcing Display for Testing

```php
add_filter( 'themeisle_sdk_welcome_debug', '__return_true' );
```

This bypasses the timing window, showing the notice regardless of install age.

> [!NOTE]
> Remove this filter before deploying to production.
