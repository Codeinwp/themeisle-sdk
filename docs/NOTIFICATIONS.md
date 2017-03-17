# Notifications Module

> A central queue for admin notices. Other modules push notifications into the queue; this module manages display throttling and dismissal.

## When It Loads

Loads for every product once the plugin/theme has been installed for at least **100 hours** and the current user has `manage_options` capability. Partner products are excluded.

## Architecture

Notifications work through a **filter-based queue**. Modules don't display notices directly — they add their notification definition to `themeisle_sdk_registered_notifications`. The Notification module picks one to show, managing:

- 5-day cooldown between notifications
- 7-day TTL (each notice expires after a week of being shown)
- Sticky priority (sticky notifications are shown before non-sticky ones)
- Persistence via the `themeisle_sdk_notifications` option

## Registering a Notification

```php
add_filter( 'themeisle_sdk_registered_notifications', function( $notifications ) {
    $notifications[] = [
        'id'      => 'my_plugin_custom_notice',   // Unique. Used as option key for dismiss state.
        'message' => '<p>My notice message.</p>',
        'ctas'    => [
            'confirm' => [
                'link' => 'https://example.com/action',
                'text' => 'Take Action',
            ],
            'cancel' => [
                'link' => '#',
                'text' => 'No thanks',
            ],
        ],
        // Optional fields:
        'heading'  => 'Optional heading text',
        'img_src'  => 'https://example.com/icon.png',
        'type'     => 'info',   // success | info | warning | error
        'sticky'   => true,     // Show before non-sticky notifications
        'expires'  => 3 * DAY_IN_SECONDS, // Override default 7-day TTL
        // Or set an absolute timestamp:
        // 'expires_at' => strtotime( '2025-12-31' ),
    ];
    return $notifications;
} );
```

> [!NOTE]
> A notification is only shown if the option `{id}` does not exist in the database. Once a user confirms or cancels, the option is saved and that notification is never shown again.

## Conditional Visibility

To prevent a specific notification from showing based on runtime conditions:

```php
add_filter( 'my_plugin_custom_notice_should_show', '__return_false' );
```

## Reacting to Confirm / Cancel

When a user clicks a button, the SDK fires:

```php
do_action( '{id}_process_confirm', $confirm ); // $confirm = 'yes' | 'no'
```

Example:

```php
add_action( 'my_plugin_custom_notice_process_confirm', function( $confirm ) {
    if ( $confirm === 'yes' ) {
        // User clicked the confirm CTA
    }
} );
```

## Suppressing All Notifications

To suppress all SDK notifications globally (e.g. during an onboarding wizard):

```php
add_filter( 'themeisle_sdk_hide_notifications', '__return_true' );
```

## How Dismissal Works

Two dismissal paths exist:

1. **AJAX** — When the user clicks a CTA button or the `×` dismiss icon, a POST to `wp_ajax_themeisle_sdk_dismiss_notice` is sent
2. **GET fallback** — The cancel CTA link uses a nonce URL so dismissal also works if JS fails

After dismissal, `{id}` is saved as an option with value `'yes'` (confirmed) or `'no'` (cancelled), and a timestamp is stored to enforce the cooldown period.

## Hooks Reference

| Hook | Type | Description |
|------|------|-------------|
| `themeisle_sdk_registered_notifications` | filter | Add notification definitions to the queue |
| `{id}_should_show` | filter | Return `false` to hide a specific notification |
| `{id}_before_render` | action | Fires just before the HTML is echoed |
| `{id}_after_render` | action | Fires just after the HTML is echoed |
| `{id}_process_confirm` | action | Fires on confirm/cancel with `'yes'`/`'no'` |
| `themeisle_sdk_hide_notifications` | filter | Return `true` to suppress all notices |
