# Uninstall Feedback Module

> Shows a short survey when a user deactivates a plugin or switches away from a theme, so the team can understand why people leave.

## When It Loads

Always, for every registered product (not partner products).

## How It Works

### Plugins

A modal popup appears on the **Plugins** screen when the user clicks "Deactivate". The user can choose a reason from a list, optionally add a text note, and submit before the plugin is deactivated. Submitting is optional — there is a "Skip & Deactivate" button.

### Themes

A slide-up **drawer** appears on the **Appearance → Themes** screen. It is triggered automatically after a short delay (`AUTO_TRIGGER_DEACTIVATE_WINDOW_SECONDS = 3`) when the user is on the themes screen. Once dismissed (submitted or closed), it does not reappear for `PAUSE_DEACTIVATE_WINDOW_DAYS = 100` days.

## Data Sent

Feedback is posted to `https://api.themeisle.com/tracking/uninstall` with:

- Product name, version, type
- Current website URL
- Time since installation
- Selected reason ID
- Optional free-text note

A "What info do we collect?" link in the modal shows users a disclosure of exactly what is sent.

## Default Reason Options

### Plugins

| ID | Title |
|----|-------|
| `id3` | I found a better plugin |
| `id4` | I could not get the plugin to work |
| `id5` | I no longer need the plugin |
| `id6` | It's a temporary deactivation. I'm just debugging an issue. |
| `id999` | Other |

### Themes

| ID | Title |
|----|-------|
| `id7` | I don't know how to make it look like demo |
| `id8` | It lacks options |
| `id9` | Is not working with a plugin that I need |
| `id10` | I want to try a new design, I don't like {theme} style |
| `id999` | Other |

## Customizing the Reason List

```php
add_filter( 'your_product_key_feedback_deactivate_options', function( $options ) {
    // Add a custom reason
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

## Appending Markup Below the Heading

Use this action to output extra content inside `.popup--header`, directly below the `<h5>`. The heading itself is customized via `themeisle_sdk_labels` (see above). For themes, the close toggle button is always rendered by the SDK after your markup.

```php
add_action( 'your_product_key_uninstall_feedback_popup_header_after_heading', function( $product, $context ) {
    if ( 'plugin' !== $context ) {
        return;
    }
    echo '<p class="my-uninstall-subtitle">' . esc_html__( 'Your feedback helps us improve.', 'text-domain' ) . '</p>';
}, 10, 2 );
```

Arguments: `$product` (`ThemeisleSDK\Product`), `$context` (`'theme'` or `'plugin'`). You are responsible for escaping any output.

## Customizing the Info Disclosure Link

```php
add_filter( 'your_product_slug_themeisle_sdk_info_collect_cta', function() {
    return __( 'What do you collect?', 'text-domain' );
} );
```
