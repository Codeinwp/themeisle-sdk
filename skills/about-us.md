---
name: themeisle-sdk-about-us
description: Use when adding or configuring an About Us admin page in a WordPress plugin or theme using the ThemeIsle SDK. Applies when registering the about_us_metadata filter, setting the page location, logo, upgrade CTA, or review link.
---

# ThemeIsle SDK — About Us Module

Adds an "About Us" admin sub-page showing company info, other Themeisle products, and optional upgrade/review CTAs.

## Activation

```php
add_filter( 'your_product_key_about_us_metadata', function() {
    return [
        'location' => 'your-menu-slug',               // Existing top-level menu slug
        'logo'     => plugin_dir_url( __FILE__ ) . 'assets/logo.png',
    ];
} );
```

Returns empty array → module does not load.

## Full Configuration

```php
add_filter( 'your_product_key_about_us_metadata', function() {
    return [
        // Required
        'location' => 'your-menu-slug',
        'logo'     => plugin_dir_url( __FILE__ ) . 'assets/logo.png',

        // Optional navigation tabs
        'page_menu' => [
            [ 'text' => 'Settings', 'url' => admin_url( 'admin.php?page=your-settings' ) ],
            [ 'text' => 'About',    'url' => admin_url( 'admin.php?page=your-about' ) ],
        ],

        // Upgrade CTA
        'has_upgrade_menu' => ! defined( 'YOUR_PRO_VERSION' ),
        'upgrade_link'     => tsdk_utmify( 'https://yourstore.com/upgrade', 'about-us' ),
        'upgrade_text'     => 'Get Pro Version',

        // Review link (false = hide)
        'review_link' => 'https://wordpress.org/support/plugin/your-slug/reviews/',
    ];
} );
```

`location` must be an existing top-level menu slug (including WP built-ins like `'options-general.php'`, `'edit.php'`).
