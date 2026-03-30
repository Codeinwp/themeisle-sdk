# Migrator Module

The Migrator module lets products ship PHP migration files that run automatically on admin page loads. Migrations are tracked via `wp_options` — no custom database tables are required.

## Registering Your Migrations Directory

Add a filter returning the absolute path to your `migrations/` folder:

```php
add_filter( 'my-plugin_sdk_migrations_path', function() {
    return plugin_dir_path( __FILE__ ) . 'migrations/';
} );
```

The filter name follows the pattern `{product-slug}_sdk_migrations_path`, where the slug is the plugin directory name (hyphens preserved, lowercase).

## Migration File Naming

Files must follow the Laravel-style timestamp convention:

```
YYYY_MM_DD_HHmmss_description.php
```

Example: `2024_03_15_120000_rename_settings_option.php`

Files are sorted alphabetically before execution, so the timestamp prefix guarantees correct chronological order.

## Writing a Migration

Each file must **return** an anonymous class instance that extends `\ThemeisleSDK\Modules\Abstract_Migration`:

```php
// migrations/2024_03_15_120000_rename_settings_option.php

return new class extends \ThemeisleSDK\Modules\Abstract_Migration {

    /**
     * Run the migration.
     */
    public function up() {
        $old = get_option( 'my_plugin_old_settings' );
        if ( $old ) {
            update_option( 'my_plugin_settings', $old );
            delete_option( 'my_plugin_old_settings' );
        }
    }

    /**
     * Reverse the migration (optional).
     */
    public function down() {
        $new = get_option( 'my_plugin_settings' );
        if ( $new ) {
            update_option( 'my_plugin_old_settings', $new );
            delete_option( 'my_plugin_settings' );
        }
    }

    /**
     * Custom condition checked before up() runs (optional).
     *
     * Return false to skip this migration without recording it.
     * Useful when the migration condition can be verified independently
     * of the name-based tracking (e.g. the old option still exists).
     */
    public function should_run() {
        return (bool) get_option( 'my_plugin_old_settings' );
    }
};
```

### Available Properties

Inside `up()` and `down()` you have access to:

| Property | Type | Description |
|---|---|---|
| `$this->wpdb` | `\wpdb` | WordPress database object |
| `$this->prefix` | `string` | WordPress table prefix (`wp_`) |
| `$this->charset_collate` | `string` | DB charset/collation string for `CREATE TABLE` |

## How Tracking Works

Ran migration names are stored in a single wp_option:

```
{product_key}_ran_migrations  →  [ '2024_03_15_120000_rename_settings_option', ... ]
```

`product_key` is the product slug with hyphens replaced by underscores.

A migration runs if **both** conditions are met:
1. Its filename (without `.php`) is **not** in the ran list.
2. `should_run()` returns `true`.

After `up()` succeeds the name is appended to the option. If `up()` throws an exception, the name is **not** recorded and the migration will retry on the next admin page load.

## Execution Timing

Migrations run on `admin_init`, which fires on every WordPress admin page load. The check is cheap: it reads one option and compares an array of filenames.

## Disabling the Module

A product can opt out entirely:

```php
add_filter( 'my-plugin_sdk_enable_migrator', '__return_false' );
```

## Programmatic Rollback

To roll back a specific migration at runtime:

```php
// Get the Migrator instance attached to your product.
$modules = \ThemeisleSDK\Common\Module_Factory::get_modules_map();
foreach ( $modules['my-plugin'] as $module ) {
    if ( $module instanceof \ThemeisleSDK\Modules\Migrator ) {
        $module->rollback( '2024_03_15_120000_rename_settings_option' );
        break;
    }
}
```

`rollback()` calls `down()` on the migration and removes it from the ran list, so it will execute again on the next `admin_init`.
