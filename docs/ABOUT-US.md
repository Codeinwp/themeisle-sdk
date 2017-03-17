# About Us Module

> Adds an "About Us" admin page to your plugin or theme, showing company information, other Themeisle products, and optional upgrade/review CTAs.

## When It Loads

Only when the product returns data from the metadata filter:

```php
add_filter( 'your_product_key_about_us_metadata', function() {
    return [
        'location' => 'edit.php?post_type=wbcr-snippets',
        'logo'     => plugin_dir_url( __FILE__ ) . 'assets/img/logo.png',
    ];
} );
```

If the filter returns an empty array the module does not load.

## Minimal Configuration

```php
add_filter( 'your_product_key_about_us_metadata', function() {
    return [
        'location' => 'your-plugin-top-level-menu-slug',
        'logo'     => plugin_dir_url( __FILE__ ) . 'assets/logo.png',
    ];
} );
```

This adds a sub-menu page "About Us" under your plugin's top-level menu.

## Full Configuration

```php
add_filter( 'your_product_key_about_us_metadata', function() {
    return [
        // Required
        'location' => 'your-menu-slug',       // Parent menu slug
        'logo'     => 'https://example.com/logo.png',

        // Optional navigation tabs shown at the top of the page
        'page_menu' => [
            [ 'text' => 'Settings',  'url' => admin_url( 'admin.php?page=your-settings' ) ],
            [ 'text' => 'About',     'url' => admin_url( 'admin.php?page=your-about' ) ],
        ],

        // Upgrade CTA in the page header
        'has_upgrade_menu' => ! defined( 'YOUR_PRO_VERSION' ),
        'upgrade_link'     => tsdk_utmify( 'https://yourstore.com/upgrade', 'about-us' ),
        'upgrade_text'     => 'Get Pro Version',

        // Review link in the page footer (false = hide, empty = use WP.org default)
        'review_link' => 'https://wordpress.org/support/plugin/your-slug/reviews/',
    ];
} );
```

## What the Page Shows

The About Us page is a React app (the `about` JS bundle) that renders:

- A hero section with the Themeisle company story
- A newsletter sign-up widget
- A grid of other Themeisle products with install/activate CTAs
- Product-specific content (e.g. an Otter Blocks feature showcase when relevant)

Product descriptions and copy shown on the page can be customized via `themeisle_sdk_labels`:

```php
add_filter( 'themeisle_sdk_labels', function( $labels ) {
    $labels['about_us']['others']['optimole_desc'] = __( 'Custom description...', 'td' );
    return $labels;
} );
```

## Page Location

The `'location'` key must be an **existing top-level menu slug** — the same string you pass to `add_submenu_page()` as the parent. The About Us page is registered as a sub-menu item under it.

> [!NOTE]
> You can also use WordPress built-in top-level slugs like `'themes.php'`, `'edit.php'`, or `'options-general.php'`.
