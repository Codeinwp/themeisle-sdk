---
name: themeisle-sdk-uninstall-feedback
description: Use when customizing the deactivation survey shown when a WordPress plugin or theme is uninstalled using the ThemeIsle SDK. Applies when adding custom reason options, changing modal labels, or overriding the info disclosure link.
---

# ThemeIsle SDK — Uninstall Feedback Module

Shows a survey on plugin deactivation (modal) or theme switch (drawer). Loads automatically for all products.

## Customizing Reason Options

```php
add_filter( 'your_product_key_feedback_deactivate_options', function( $options ) {
    $options['id_custom'] = [
        'id'          => 100,
        'type'        => 'textarea',
        'title'       => 'My custom reason',
        'placeholder' => 'Tell us more...',
    ];
    return $options;
} );
```

## Customizing Labels

```php
add_filter( 'themeisle_sdk_labels', function( $labels ) {
    $labels['uninstall']['heading_plugin'] = "What's wrong?";
    $labels['uninstall']['button_submit']  = 'Submit &amp; Deactivate';
    $labels['uninstall']['button_cancel']  = 'Skip &amp; Deactivate';
    return $labels;
} );
```

## Info Disclosure CTA

```php
add_filter( 'your_product_slug_themeisle_sdk_info_collect_cta', function() {
    return __( 'What do you collect?', 'text-domain' );
} );
```

Data sent to `https://api.themeisle.com/tracking/uninstall`: product name/version, site URL, install age, selected reason.
