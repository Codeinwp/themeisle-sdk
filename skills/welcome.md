---
name: themeisle-sdk-welcome
description: Use when configuring the post-install upgrade offer notice in a WordPress plugin using the ThemeIsle SDK. Applies when registering the welcome_metadata filter, customizing the upsell message, or testing the welcome notice outside its normal timing window.
---

# ThemeIsle SDK — Welcome Module

Shows a one-time upgrade offer to free users between days 7–12 after installation.

## Activation

```php
add_filter( 'your_product_key_welcome_metadata', function() {
    return [
        'is_enabled' => ! defined( 'YOUR_PRO_VERSION' ),  // false = never show
        'pro_name'   => 'Your Plugin Pro',
        'logo'       => plugin_dir_url( __FILE__ ) . 'assets/logo.png',
        'cta_link'   => tsdk_utmify( 'https://yourstore.com/upgrade', 'welcome-notice' ),
    ];
} );
```

`is_enabled` falsy → module does not load. Always return false/omit when pro is active.

## Customizing the Message

```php
add_filter( 'your_product_key_welcome_upsell_message', function( $message ) {
    return '<p>You\'ve been using <b>{product}</b> for a week! <a href="{cta_link}">Upgrade</a> to <b>{pro_product}</b>.</p>';
    // Placeholders: {product}, {pro_product}, {cta_link}
} );
```

## Testing

```php
add_filter( 'themeisle_sdk_welcome_debug', '__return_true' );
// Remove before deploying.
```

Goes through the [notification queue](notifications.md) but at highest priority.
