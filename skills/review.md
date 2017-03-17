---
name: themeisle-sdk-review
description: Use when configuring the WordPress.org review prompt in a plugin using the ThemeIsle SDK. Applies when disabling the review notice, customizing its message or button labels, or understanding when the notice appears.
---

# ThemeIsle SDK — Review Module

Prompts users to leave a WordPress.org review after a few days of use.

## When It Loads

Only for `WordPress Available: yes` products.

Disable:

```php
add_filter( 'your_product_slug_sdk_should_review', '__return_false' );
```

## Customizing the Message

```php
add_filter( 'your_product_key_feedback_review_message', function( $message ) {
    return '<p>Enjoying {product}? Please leave us a review!</p>';
    // Placeholders: {product}, {developer}
} );
```

## Customizing Buttons

```php
add_filter( 'your_product_key_feedback_review_button_do',     fn() => __( 'Sure!', 'td' ) );
add_filter( 'your_product_key_feedback_review_button_cancel', fn() => __( 'Not now', 'td' ) );
```

## Global Label Overrides

```php
add_filter( 'themeisle_sdk_labels', function( $labels ) {
    $labels['review']['notice'] = '<p>Hey, <b>{product}</b> user! Please rate us.</p>';
    $labels['review']['ctay']   = 'Happy to help!';
    $labels['review']['ctan']   = 'No thanks.';
    return $labels;
} );
```

The notice goes through the [notification queue](notifications.md) — 100-hour install minimum and 5-day cooldown apply.
