---
name: themeisle-sdk-promotions
description: Use when configuring cross-promotion notices for other Themeisle products in a WordPress plugin using the ThemeIsle SDK. Applies when adding product-specific promotions, blocking built-in promotions, or debugging the 3-week cooldown.
---

# ThemeIsle SDK — Promotions Module

Cross-promotion notices for other Themeisle products. Loads for the first registered non-partner product per page load.

Promotions are suppressed for 3 weeks after any dismissal.

## Adding Product-Specific Promotions

```php
add_filter( 'your_product_key_load_promotions', function( $promotions ) {
    $promotions[] = 'otter'; // Add Otter Blocks promotion from this product
    return $promotions;
} );
```

## Blocking Specific Promotions

```php
add_filter( 'your_product_key_dissallowed_promotions', function( $blocked ) {
    $blocked[] = 'otter'; // Already have your own Otter CTA
    return $blocked;
} );
```

## Testing

```php
add_filter( 'themeisle_sdk_promo_debug', '__return_true' );
// Bypasses 3-week cooldown. Remove before deploying.
```

## Built-In Promotions

`optimole`, `rop`, `woo_plugins`, `neve`, `redirection-cf7`, `hyve`, `wp_full_pay`, `feedzy_import`, `learning-management-system`, `feedzy_embed`

## Label Overrides

```php
add_filter( 'themeisle_sdk_labels', function( $labels ) {
    $labels['promotions']['recommended']              = 'Recommended by %s';
    $labels['promotions']['installActivate']          = 'Install & Activate';
    $labels['promotions']['optimole']['message1']     = 'Custom Optimole pitch...';
    return $labels;
} );
```
