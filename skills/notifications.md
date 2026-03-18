---
name: themeisle-sdk-notifications
description: Use when registering or managing admin notices in a WordPress plugin using the ThemeIsle SDK notification queue. Applies when adding a notification definition, controlling notice visibility, reacting to confirm/cancel actions, or suppressing all SDK notifications.
---

# ThemeIsle SDK — Notifications Module

Central queue for admin notices. Manages display throttling (5-day cooldown), 7-day TTL, and dismissal.

Loads after 100 hours post-install for users with `manage_options`.

## Registering a Notification

```php
add_filter( 'themeisle_sdk_registered_notifications', function( $notifications ) {
    $notifications[] = [
        'id'      => 'my_plugin_custom_notice',  // Unique. Used as dismiss option key.
        'message' => '<p>My notice message.</p>',
        'ctas'    => [
            'confirm' => [ 'link' => 'https://example.com', 'text' => 'Take Action' ],
            'cancel'  => [ 'link' => '#',                   'text' => 'No thanks' ],
        ],
        // Optional:
        'heading'    => 'Heading text',
        'img_src'    => 'https://example.com/icon.png',
        'type'       => 'info',              // success | info | warning | error
        'sticky'     => true,                // Show before non-sticky
        'expires'    => 3 * DAY_IN_SECONDS,  // Override 7-day default TTL
        'expires_at' => strtotime( '2025-12-31' ),  // Or absolute timestamp
    ];
    return $notifications;
} );
```

A notification only shows if the `{id}` option does not exist in the DB. Once dismissed, it never shows again.

## Conditional Visibility

```php
add_filter( 'my_plugin_custom_notice_should_show', '__return_false' );
```

## Reacting to Confirm / Cancel

```php
add_action( 'my_plugin_custom_notice_process_confirm', function( $confirm ) {
    if ( 'yes' === $confirm ) {
        // User clicked confirm CTA
    }
} );
```

## Suppress All Notifications

```php
add_filter( 'themeisle_sdk_hide_notifications', '__return_true' );
```

## Hooks Reference

| Hook | Type | Description |
|---|---|---|
| `themeisle_sdk_registered_notifications` | filter | Push notification definitions |
| `{id}_should_show` | filter | Return `false` to hide |
| `{id}_process_confirm` | action | Fires with `'yes'`/`'no'` on dismiss |
| `{id}_before_render` | action | Before HTML output |
| `{id}_after_render` | action | After HTML output |
| `themeisle_sdk_hide_notifications` | filter | Suppress all notices |
