---
name: themeisle-sdk-compatibilities
description: Use when declaring version compatibility requirements between companion plugins or themes using the ThemeIsle SDK. Applies when registering a compatibility check, understanding when updates are blocked, or customizing compatibility warning messages.
---

# ThemeIsle SDK — Compatibilities Module

Warns admins when a companion plugin/theme is outside the tested version range, and blocks updates that would break the site.

## Registration

Declare from the product that **depends on** the companion:

```php
add_filter(
    'themeisle_sdk_compatibilities/' . basename( plugin_dir_path( __FILE__ ) ),
    function( $compatibilities ) {
        $compatibilities['MyCompanionPlugin'] = [
            'basefile'  => defined( 'COMPANION_FILE' ) ? COMPANION_FILE : '',
            'required'  => '2.0.0',   // Minimum version (must be < tested_up)
            'tested_up' => '3.9.9',   // Maximum tested version
        ];
        return $compatibilities;
    }
);
```

`basefile` empty string = companion not active, skip check.

> `required` must be strictly lower than `tested_up` or an Exception is thrown.

## Behaviour

| State | What happens |
|---|---|
| Companion version < `required` | Admin notice + blocks main product update |
| Companion version > `tested_up` | Warning badge on companion in Plugins/Themes screen |

## Free/Pro Pair Example

```php
// In free plugin:
add_filter(
    'themeisle_sdk_compatibilities/' . basename( plugin_dir_path( __FILE__ ) ),
    function( $compatibilities ) {
        $compatibilities['MyPluginPro'] = [
            'basefile' => defined( 'MY_PRO_FILE' ) ? MY_PRO_FILE : '',
            'required' => '1.3.0',
        ];
        return $compatibilities;
    }
);
```

## Label Overrides

```php
add_filter( 'themeisle_sdk_labels', function( $labels ) {
    $labels['compatibilities']['notice']  = '%s requires a newer version of %s. Please %supdate%s %s %s.';
    $labels['compatibilities']['notice2'] = '%s update requires a newer version of %s. Please %supdate%s %s %s.';
    return $labels;
} );
```
