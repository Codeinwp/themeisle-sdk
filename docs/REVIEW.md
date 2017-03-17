# Review Module

> Asks users to leave a review on WordPress.org after they've been using the plugin/theme for a few days.

## When It Loads

Only for products that are:
- Available on WordPress.org (`WordPress Available: yes`)
- Not from a partner agency

Can be disabled per-product:

```php
add_filter( 'your_product_slug_sdk_should_review', '__return_false' );
```

## How It Works

The module adds a notification to the [notification queue](NOTIFICATIONS.md). The notification respects the standard 100-hour install minimum and 5-day cooldown between notices.

The notice links directly to the product's WordPress.org review page:

```
https://wordpress.org/support/{type}/{slug}/reviews/#wporg-footer
```

## Customizing the Message

```php
add_filter( 'your_product_key_feedback_review_message', function( $message ) {
    return '<p>Enjoying {product}? Please leave us a review!</p>';
} );
```

Placeholders available in the message:
- `{product}` — friendly product name
- `{developer}` — randomly selected from a team list (Marius, Hardeep, Andrei, Robert)

## Customizing the Buttons

```php
add_filter( 'your_product_key_feedback_review_button_do',     fn() => __( 'Sure, happy to help!', 'td' ) );
add_filter( 'your_product_key_feedback_review_button_cancel', fn() => __( 'Not now', 'td' ) );
```

## Default Labels

Override via the `themeisle_sdk_labels` filter:

```php
add_filter( 'themeisle_sdk_labels', function( $labels ) {
    $labels['review']['notice'] = '<p>Hey, it\'s great to see you have <b>{product}</b> ...';
    $labels['review']['ctay']   = 'Ok, I will gladly help.';
    $labels['review']['ctan']   = 'No, thanks.';
    return $labels;
} );
```
