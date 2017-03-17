# Float Widget Module

> A floating help panel in the bottom corner of specified admin pages, giving users quick access to documentation, support, upgrade, and other links.

## When It Loads

Only when the product returns data from the metadata filter:

```php
add_filter( 'your_product_key_float_widget_metadata', function() {
    return [
        'logo'               => plugin_dir_url( __FILE__ ) . 'assets/logo.png',
        'pages'              => [ 'your-page-slug' ],
        'documentation_link' => 'https://docs.example.com',
        'has_upgrade_menu'   => true,
        'upgrade_link'       => 'https://example.com/upgrade',
    ];
} );
```

If the filter returns an empty array the module does not load.

## Full Configuration

```php
add_filter( 'your_product_key_float_widget_metadata', function() {
    return [
        // Required
        'logo'  => plugin_dir_url( __FILE__ ) . 'assets/logo.png',
        'pages' => [ 'your-admin-page-slug', 'another-page-slug' ],

        // Required links
        'documentation_link' => tsdk_utmify( 'https://docs.example.com', 'float-widget' ),

        // Optional appearance
        'nice_name'     => 'My Plugin',         // Defaults to product name
        'primary_color' => '#7B61FF',            // Hex color for the widget button

        // Upgrade CTA
        'has_upgrade_menu' => ! defined( 'MY_PRO_VERSION' ),
        'upgrade_link'     => tsdk_utmify( 'https://example.com/upgrade', 'float-widget' ),

        // Pro-only links (add from your pro plugin)
        'premium_support_link' => tsdk_support_link( __FILE__ ),
        'feature_request_link' => 'https://feedback.example.com',

        // Setup wizard link
        'wizard_link' => admin_url( 'admin.php?page=your-wizard' ),
    ];
} );
```

## Pages

The `'pages'` array controls **which admin pages show the widget**. Each entry is a page slug matched against the current admin page. Use the `$screen->id` or the URL `page=` parameter value.

```php
'pages' => [
    'your-plugin-dashboard',   // matches ?page=your-plugin-dashboard
    'your-plugin-settings',
],
```

## Link Behaviour

| Config key | Shown when |
|---|---|
| `documentation_link` | Always |
| `upgrade_link` | `has_upgrade_menu` is truthy |
| `premium_support_link` | Always (add only from pro version) |
| `feature_request_link` | Always (add only from pro version) |
| `wizard_link` | Always if provided |

The SDK renders standard labels for each link type. Override them via `themeisle_sdk_labels`:

```php
add_filter( 'themeisle_sdk_labels', function( $labels ) {
    $labels['float_widget']['links']['documentation']   = 'View Docs';
    $labels['float_widget']['links']['support']         = 'Get Help';
    $labels['float_widget']['links']['upgrade']         = 'Go Pro';
    $labels['float_widget']['links']['feature_request'] = 'Request a Feature';
    $labels['float_widget']['links']['wizard']          = 'Setup Wizard';
    $labels['float_widget']['links']['rate']            = 'Rate Us';
    return $labels;
} );
```

> [!NOTE]
> The float widget is rendered via the `float_widget` JS bundle. The bundle is enqueued automatically on the configured pages.
