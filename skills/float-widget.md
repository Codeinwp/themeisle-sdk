---
name: themeisle-sdk-float-widget
description: Use when adding or configuring a floating help panel in a WordPress plugin using the ThemeIsle SDK. Applies when registering the float_widget_metadata filter, specifying which admin pages show the widget, or setting links for docs, support, upgrade, and feature requests.
---

# ThemeIsle SDK — Float Widget Module

A floating help panel in the corner of specified admin pages with links to docs, support, upgrade, and more.

## Activation

```php
add_filter( 'your_product_key_float_widget_metadata', function() {
    return [
        'logo'               => plugin_dir_url( __FILE__ ) . 'assets/logo.png',
        'pages'              => [ 'your-page-slug' ],  // ?page= value(s) or $screen->id
        'documentation_link' => tsdk_utmify( 'https://docs.example.com', 'float-widget' ),
    ];
} );
```

Returns empty array → module does not load.

## Full Configuration

```php
add_filter( 'your_product_key_float_widget_metadata', function() {
    return [
        // Required
        'logo'               => plugin_dir_url( __FILE__ ) . 'assets/logo.png',
        'pages'              => [ 'your-plugin-settings', 'your-plugin-dashboard' ],
        'documentation_link' => tsdk_utmify( 'https://docs.example.com', 'float-widget' ),

        // Optional
        'nice_name'     => 'My Plugin',
        'primary_color' => '#7B61FF',

        // Upgrade CTA (free version only)
        'has_upgrade_menu' => ! defined( 'MY_PRO_VERSION' ),
        'upgrade_link'     => tsdk_utmify( 'https://example.com/upgrade', 'float-widget' ),

        // Pro-only links (add from pro plugin)
        'premium_support_link' => tsdk_support_link( __FILE__ ),
        'feature_request_link' => 'https://feedback.example.com',

        // Setup wizard
        'wizard_link' => admin_url( 'admin.php?page=your-wizard' ),
    ];
} );
```

## Label Overrides

```php
add_filter( 'themeisle_sdk_labels', function( $labels ) {
    $labels['float_widget']['links']['documentation']   = 'View Docs';
    $labels['float_widget']['links']['support']         = 'Get Help';
    $labels['float_widget']['links']['upgrade']         = 'Go Pro';
    $labels['float_widget']['links']['feature_request'] = 'Request a Feature';
    return $labels;
} );
```
