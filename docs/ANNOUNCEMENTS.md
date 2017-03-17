# Announcements Module

> Displays time-limited promotional banners for sale events such as Black Friday. Currently supports one announcement type: **Black Friday / Cyber Monday**.

## When It Loads

Loads for every registered product that is not from a partner agency.

## Black Friday Banner

The module automatically calculates whether the current date falls within the Black Friday sale window:

- **Start**: Monday of the week that contains Black Friday (the Friday after US Thanksgiving)
- **End**: 7 days after Black Friday (inclusive of Cyber Monday)

If the current date is within that window and the user has not dismissed the notice this calendar year, the banner is shown on all admin pages.

Dismissal is per-user and per-year — if a user dismisses in November 2024, they will see the banner again in November 2025.

## Forcing the Banner On (Testing)

```php
add_filter( 'themeisle_sdk_is_black_friday_sale', '__return_true' );
```

Or override the current date (the module uses `Abstract_Module::get_current_date()`):

```php
add_filter( 'themeisle_sdk_current_date', function() {
    return new DateTime( '2024-11-29' ); // A Black Friday date
} );
```

> [!NOTE]
> Remove these filters before deploying to production.

## Customizing Labels

```php
add_filter( 'themeisle_sdk_labels', function( $labels ) {
    $labels['announcements']['black_friday']      = 'Black Friday Sale';
    $labels['announcements']['max_savings']       = 'Our biggest sale of the year: <strong>%s OFF everything!</strong> Don\'t miss this limited-time offer.';
    $labels['announcements']['notice_link_label'] = 'See the Offer';
    $labels['announcements']['time_left']         = '%s left';
    return $labels;
} );
```

## AJAX Dismissal

The banner registers `wp_ajax_themeisle_sdk_dismiss_black_friday_notice`. When the user dismisses, the current timestamp is stored in user meta:

```
themeisle_sdk_dismissed_notice_black_friday  →  {timestamp}
```

Only the year is compared on subsequent loads, so the notice resets each year automatically.
