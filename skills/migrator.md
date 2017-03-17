---
name: themeisle-sdk-migrator
description: Use when writing or configuring database migrations for a WordPress plugin using the ThemeIsle SDK. Applies when creating migration files, registering a migrations directory, using Abstract_Migration, or understanding how SDK migrations are tracked and executed on plugin version upgrade.
---

# ThemeIsle SDK — Migrator Module

Runs PHP migration files automatically on the first complete WordPress request for each plugin version. Migrations are tracked in `wp_options` — no custom tables needed.

## Activation

Register the migrations directory path:

```php
add_filter( 'your-plugin_sdk_migrations_path', function() {
    return plugin_dir_path( __FILE__ ) . 'migrations/';
} );
```

The filter name is `{plugin-directory-name}_sdk_migrations_path` (hyphens preserved, lowercase).

Opt out entirely:

```php
add_filter( 'your-plugin_sdk_enable_migrator', '__return_false' );
```

## File Naming

```
YYYY_MM_DD_HHmmss_description.php
```

Example: `2024_03_15_120000_rename_settings_option.php`

Files run in alphabetical order — the timestamp prefix ensures chronological execution.

## Writing a Migration

Each file must **return** an anonymous class extending `\ThemeisleSDK\Modules\Abstract_Migration`:

```php
return new class extends \ThemeisleSDK\Modules\Abstract_Migration {

    public function up() {
        $old = get_option( 'my_plugin_old_key' );
        if ( $old ) {
            update_option( 'my_plugin_new_key', $old );
            delete_option( 'my_plugin_old_key' );
        }
    }

    // Optional: return false to skip without recording (rechecked next version)
    public function should_run() {
        return (bool) get_option( 'my_plugin_old_key' );
    }
};
```

Available inside `up()`:

| Property | Type | Description |
|---|---|---|
| `$this->wpdb` | `\wpdb` | WordPress database object |
| `$this->prefix` | `string` | Table prefix (`wp_`) |
| `$this->charset_collate` | `string` | Charset/collation for `CREATE TABLE` |

## How It Works

- Runs on `wp_loaded` when the current product version differs from `{product_key}_migrated_version`.
- Ran migration names are stored in `{product_key}_ran_migrations` (hyphens → underscores in key).
- A migration runs if its filename (without `.php`) is not in the ran list **and** `should_run()` returns `true`.
- After `up()` succeeds the name is appended. If `up()` throws, the name and migrated version are not recorded and the migration retries on the next complete request.
- The migrated version is updated only after every pending migration succeeds.
- `should_run() = false` skips the migration **without** recording it and is checked again on the next product version.
- A cross-request lock prevents concurrent requests from executing the same migration.
