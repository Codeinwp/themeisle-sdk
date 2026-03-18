# Compatibilities Module

> Warns administrators when a product's companion plugin or theme is outside the tested version range, and blocks updates that would break the site.

## When It Loads

Loads for every registered product (not partner products) when the current user has `install_plugins` (plugins) or `switch_themes` (themes) capability.

## Registration

Declare version requirements from the product that **depends on** the companion:

```php
add_filter(
    'themeisle_sdk_compatibilities/' . basename( plugin_dir_path( __FILE__ ) ),
    function( $compatibilities ) {
        $compatibilities['MyCompanionPlugin'] = [
            'basefile'  => defined( 'COMPANION_FILE' ) ? COMPANION_FILE : '',
            'required'  => '2.0.0',   // Minimum version of the companion
            'tested_up' => '3.9.9',   // Maximum tested version of the companion
        ];
        return $compatibilities;
    }
);
```

| Key | Required | Description |
|-----|----------|-------------|
| `basefile` | Yes | Full path to the companion's main PHP file. Empty string = companion not active, skip check. |
| `required` | Yes | Minimum version of the companion. Must be lower than `tested_up`. |
| `tested_up` | No | Maximum tested version. Defaults to `'999'`. A warning appears if companion exceeds this. |

> [!IMPORTANT]
> `required` must be strictly lower than `tested_up`, or the SDK will throw an `Exception`.

## Two Warning States

### `required` — Companion is too old

If the companion's current version is below `required`, the SDK:
- Shows a persistent **admin notice** asking the administrator to update the companion
- **Blocks the main product's update** via `upgrader_pre_download`, printing an error and halting the upgrade

### `tested_up` — Companion is too new (untested)

If the companion's current version is above `tested_up`, the SDK:
- Injects a **warning badge** next to the companion in the **Plugins list** (or the **Themes screen**)

## Customizing Warning Messages

```php
add_filter( 'themeisle_sdk_labels', function( $labels ) {
    // Companion is too old — admin notice
    $labels['compatibilities']['notice']  = '%s requires a newer version of %s. Please %supdate%s %s %s to the latest version.';

    // Companion is too old — blocks upgrade
    $labels['compatibilities']['notice2'] = '%s update requires a newer version of %s. Please %supdate%s %s %s.';

    // Companion is untested (theme variant)
    $labels['compatibilities']['notice_theme']  = '%1$sWarning:%2$s This theme has not been tested with your current version of %1$s%3$s%2$s. Please update %3$s plugin.';

    // Companion is untested (plugin variant)
    $labels['compatibilities']['notice_plugin'] = '%1$sWarning:%2$s This plugin has not been tested with your current version of %1$s%3$s%2$s. Please update %3$s %4$s.';

    return $labels;
} );
```

## Practical Example — Free/Pro Pair

The free plugin registers a compatibility check against its pro companion:

```php
// In free plugin's main file:
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

If a user has pro version `1.1.0` (older than `1.3.0`), the SDK will show a notice and block the free plugin from updating until the pro plugin is updated first.
