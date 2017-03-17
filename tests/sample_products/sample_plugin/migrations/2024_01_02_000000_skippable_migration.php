<?php
/**
 * Sample migration with a should_run() guard.
 *
 * Used only in unit tests to verify should_run() is respected.
 */
return new class extends \ThemeisleSDK\Modules\Abstract_Migration {
	public function up() {
		update_option( 'sample_plugin_skippable_ran', 'yes' );
	}

	public function should_run() {
		return false; // Always skipped.
	}
};
