<?php
/**
 * Sample migration: renames a dummy option.
 *
 * Used only in unit tests.
 */
return new class extends \ThemeisleSDK\Modules\Abstract_Migration {
	public function up() {
		update_option( 'sample_plugin_migrated', 'yes' );
	}

	public function down() {
		delete_option( 'sample_plugin_migrated' );
	}
};
