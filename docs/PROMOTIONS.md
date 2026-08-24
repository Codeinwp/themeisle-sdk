# Promotions Module

> Shows cross-promotion notices for other Themeisle products. Handles one-time notices, installation CTAs, and integration with the WooCommerce admin and Elementor editors.

## When It Loads

Loads for the first registered product that is not a partner. Only one product's promotions run per page load (the module sets `themeisle_sdk_ran_promos` to `true` after attaching, blocking subsequent products).

Promotions are suppressed for **3 weeks** after any promotion is dismissed.

## Built-In Promotions

The following promotions are always included regardless of product:

| Slug | Promotes |
|------|---------|
| `optimole` | Optimole image optimization |
| `rop` | Revive Old Posts |
| `woo_plugins` | WooCommerce extension suite |
| `neve` | Neve theme |
| `redirection-cf7` | Redirection for Contact Form 7 |
| `hyve` | Hyve AI chatbot |
| `wp_full_pay` | WP Full Pay (Stripe) |
| `feedzy_import` | Feedzy RSS import |
| `learning-management-system` | Masteriyo LMS |
| `feedzy_embed` | Feedzy embed (only when Neve/Otter/Elementor active) |

## Adding Product-Specific Promotions

To include additional promotions that are only relevant to your product:

```php
add_filter( 'your_product_key_load_promotions', function( $promotions ) {
    $promotions[] = 'otter'; // Show Otter Blocks promotion from this product
    return $promotions;
} );
```

## Blocking Specific Promotions

To prevent a promotion from showing for your product (e.g. you already have your own Otter CTA):

```php
add_filter( 'your_product_key_dissallowed_promotions', function( $blocked ) {
    $blocked[] = 'otter';
    return $blocked;
} );
```

## Forcing Promotions for Testing

```php
add_filter( 'themeisle_sdk_promo_debug', '__return_true' );
```

This bypasses the 3-week cooldown and forces available promotions to display.

> [!NOTE]
> Remove this filter before deploying to production.

## How Promotions Are Displayed

Promotions appear in different locations depending on their type:

- **Admin notice** — banner at the top of relevant admin screens
- **Media library** — attachment fields row (e.g. Optimole image optimization suggestion)
- **Elementor editor** — injected after Elementor scripts are enqueued
- **WooCommerce screens** — shown on product pages and WooCommerce admin

The module checks `current_screen()` to display the right promotion in the right context.

## Dismiss Behaviour

Each promotion tracks dismissal in its own option:

```
themeisle_sdk_promotions_optimole_installed  →  timestamp
themeisle_sdk_promotions_otter_installed     →  timestamp
...
```

After any dismissal, no promotions are shown for **3 weeks**. The AJAX handler is `wp_ajax_tisdk_update_option`.

## Customizing Promotion Labels

All promotion copy is in `Loader::$labels['promotions']`. Override via the global labels filter:

```php
add_filter( 'themeisle_sdk_labels', function( $labels ) {
    $labels['promotions']['recommended']     = 'Recommended by %s';
    $labels['promotions']['installActivate'] = 'Install & Activate';
    $labels['promotions']['learnmore']       = 'Learn More';
    // Per-product copy:
    $labels['promotions']['optimole']['message1'] = 'Custom Optimole pitch...';
    return $labels;
} );
```

## Telemetry Events

Promotion lifecycle events are recorded through the SDK's `tiTrk` tracker (see [TELEMETRY.md](TELEMETRY.md)) via `assets/js/src/common/promoEvents.js`. Prerequisites — events flow only when **all** hold, and are dropped silently otherwise:

1. A product on the site opts into telemetry: `add_filter( 'themeisle_sdk_enable_telemetry', '__return_true' )`.
2. The **host product** (the one whose Promotions instance loaded) has logger consent: `{product_key}_logger_flag === 'yes'` (auto-`yes` for pro/licensed setups, opt-in notice otherwise).

Event shape (`GROUP BY featureComponent, featureValue` gives per-promo counts per action):

| field | value |
|---|---|
| `feature` | `promotions` |
| `featureComponent` | promotion key (`om-media`, `hyve-plugins-install`, `blocks-css`, …) |
| `featureValue` | `impression` · `cta-install` · `cta-activate` · `cta-learn-more` · `cta-preview` · `cta-gotodash` · `install-success` · `install-fail` · `dismiss` |
| `groupID` | slug of the **promoted** plugin (`optimole-wp`, `hyve-lite`, …) — aggregate across host products with this |
| `slug` / `license` (auto) | host product's telemetry slug and track hash (`free` or license hash) |

`impression` means *seen*: the notice was at least half visible for one second (IntersectionObserver). Collapsed panels and below-the-fold notices don't count.

**Read the numbers as directional rates, not a census.** Events only arrive from consented sites (skews pro), batches are lost when a page dies before flush or the endpoint is blocked, and there is no per-event timestamp or session id — so `install-success / impression` per `featureComponent` is a comparative signal between promos, not an absolute conversion funnel. Server-rendered surfaces emit nothing: the `feedzy-import` row, the WooCommerce Suggestions tab (`ppom`, `sparks-*`), and the Visualizer search injection.
