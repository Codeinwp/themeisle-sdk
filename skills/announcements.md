---
name: themeisle-sdk-announcements
description: Use when configuring or testing promotional banners such as the Black Friday notice in a WordPress plugin using the ThemeIsle SDK. Applies when forcing the banner on for testing, overriding the sale date, or customizing announcement labels.
---

# ThemeIsle SDK — Announcements Module

Displays time-limited promotional banners (Black Friday / Cyber Monday). Loads automatically for all non-partner products.

## How It Works

Automatically shows a Black Friday banner during the sale window (Monday before Black Friday through Cyber Monday). Dismissal is per-user per-year — resets annually.

## Testing

```php
add_filter( 'themeisle_sdk_is_black_friday_sale', '__return_true' );

// Or set a specific date:
add_filter( 'themeisle_sdk_current_date', function() {
    return new DateTime( '2024-11-29' ); // A Black Friday date
} );
// Remove before deploying.
```

## Label Overrides

```php
add_filter( 'themeisle_sdk_labels', function( $labels ) {
    $labels['announcements']['black_friday']      = 'Black Friday Sale';
    $labels['announcements']['max_savings']       = 'Our biggest sale: <strong>%s OFF everything!</strong>';
    $labels['announcements']['notice_link_label'] = 'See the Offer';
    $labels['announcements']['time_left']         = '%s left';
    return $labels;
} );
```

Dismissal stored in user meta: `themeisle_sdk_dismissed_notice_black_friday` → timestamp.
