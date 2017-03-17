<?php
/**
 * Migrator module test.
 *
 * @package ThemeIsleSDK
 */

/**
 * Test Migrator module.
 */
class Migrator_Test extends WP_UnitTestCase {

	/**
	 * Absolute path to the sample plugin migrations directory.
	 *
	 * @var string
	 */
	private $migrations_path;

	/**
	 * Sample plugin basefile.
	 *
	 * @var string
	 */
	private $plugin_file;

	/**
	 * Set up each test.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->plugin_file     = dirname( __FILE__ ) . '/sample_products/sample_plugin/plugin_file.php';
		$this->migrations_path = dirname( __FILE__ ) . '/sample_products/sample_plugin/migrations';
	}

	/**
	 * Tear down: clean up options and filters after each test.
	 */
	public function tearDown(): void {
		parent::tearDown();
		delete_option( 'sample_plugin_ran_migrations' );
		delete_option( 'sample_plugin_migrated_version' );
		delete_option( 'sample_plugin_migration_lock' );
		delete_option( 'sample_plugin_migrated' );
		delete_option( 'sample_plugin_skippable_ran' );
		delete_option( 'sample_plugin_failure_attempts' );
		delete_option( 'sample_plugin_version' );
		wp_cache_delete( 'sample_plugin_migration_lock', 'themeisle_sdk_migrations' );
		remove_all_filters( 'sample_plugin_sdk_migrations_path' );
		remove_all_filters( 'sample_plugin_sdk_enable_migrator' );
	}

	/**
	 * Returns a Migrator loaded with the sample plugin product.
	 *
	 * @return \ThemeisleSDK\Modules\Migrator
	 */
	private function get_migrator() {
		$product  = new \ThemeisleSDK\Product( $this->plugin_file );
		$migrator = new \ThemeisleSDK\Modules\Migrator();
		$migrator->load( $product );
		return $migrator;
	}

	/**
	 * Register the sample migrations path for the product.
	 */
	private function register_migrations_path() {
		$path = $this->migrations_path;
		add_filter(
			'sample_plugin_sdk_migrations_path',
			function() use ( $path ) {
				return $path;
			}
		);
	}

	/**
	 * The can_load() method returns true by default.
	 */
	public function test_can_load_returns_true_by_default() {
		$product  = new \ThemeisleSDK\Product( $this->plugin_file );
		$migrator = new \ThemeisleSDK\Modules\Migrator();
		$this->assertTrue( $migrator->can_load( $product ) );
	}

	/**
	 * The can_load() method respects the opt-out filter.
	 */
	public function test_can_load_respects_filter() {
		add_filter( 'sample_plugin_sdk_enable_migrator', '__return_false' );
		$product  = new \ThemeisleSDK\Product( $this->plugin_file );
		$migrator = new \ThemeisleSDK\Modules\Migrator();
		$this->assertFalse( $migrator->can_load( $product ) );
	}

	/**
	 * The load() method returns the migrator instance and registers wp_loaded hook.
	 */
	public function test_load_returns_self() {
		$product  = new \ThemeisleSDK\Product( $this->plugin_file );
		$migrator = new \ThemeisleSDK\Modules\Migrator();
		$result   = $migrator->load( $product );
		$this->assertInstanceOf( 'ThemeisleSDK\\Modules\\Migrator', $result );
		$this->assertNotFalse( has_action( 'wp_loaded', array( $migrator, 'run_pending' ) ) );
	}

	/**
	 * Pending migrations run even when the product update action fired on an earlier request.
	 */
	public function test_run_pending_does_not_require_update_action_on_current_request() {
		$this->register_migrations_path();

		// Pre-seed the stored version to match the plugin version so the Product
		// constructor does not fire themeisle_sdk_update_sample_plugin.
		update_option( 'sample_plugin_version', '1.1.1' );

		$product  = new \ThemeisleSDK\Product( $this->plugin_file );
		$migrator = new \ThemeisleSDK\Modules\Migrator();
		$migrator->load( $product );

		$migrator->run_pending();

		$this->assertEquals( 'yes', get_option( 'sample_plugin_migrated' ) );
		$this->assertContains( '2024_01_01_000000_sample_migration', get_option( 'sample_plugin_ran_migrations', array() ) );
		$this->assertEquals( '1.1.1', get_option( 'sample_plugin_migrated_version' ) );
	}

	/**
	 * The run_pending() method does nothing when no migrations path is registered.
	 */
	public function test_run_pending_no_path_does_nothing() {
		$migrator = $this->get_migrator();
		$migrator->run_pending();
		$this->assertFalse( get_option( 'sample_plugin_ran_migrations' ) );
		$this->assertFalse( get_option( 'sample_plugin_migrated_version' ) );
	}

	/**
	 * The run_pending() method executes up() and records the migration name.
	 */
	public function test_run_pending_executes_migration() {
		$this->register_migrations_path();
		$migrator = $this->get_migrator();

		$migrator->run_pending();

		$this->assertEquals( 'yes', get_option( 'sample_plugin_migrated' ) );

		$ran = get_option( 'sample_plugin_ran_migrations' );
		$this->assertContains( '2024_01_01_000000_sample_migration', $ran );
		$this->assertEquals( '1.1.1', get_option( 'sample_plugin_migrated_version' ) );
	}

	/**
	 * A completed product version does not scan or execute migrations again.
	 */
	public function test_run_pending_skips_completed_product_version() {
		$this->register_migrations_path();
		update_option( 'sample_plugin_migrated_version', '1.1.1' );

		$migrator = $this->get_migrator();
		$migrator->run_pending();

		$this->assertFalse( get_option( 'sample_plugin_migrated' ) );
		$this->assertFalse( get_option( 'sample_plugin_ran_migrations' ) );
	}

	/**
	 * The run_pending() method does not re-run already recorded migrations.
	 */
	public function test_run_pending_skips_already_ran_migration() {
		$this->register_migrations_path();
		// Pre-record both migrations as already run.
		update_option(
			'sample_plugin_ran_migrations',
			array(
				'2024_01_01_000000_sample_migration',
				'2024_01_02_000000_skippable_migration',
			)
		);

		$migrator = $this->get_migrator();
		$migrator->run_pending();

		// up() should not have been called — option remains absent.
		$this->assertFalse( get_option( 'sample_plugin_migrated' ) );
		$this->assertEquals( '1.1.1', get_option( 'sample_plugin_migrated_version' ) );
	}

	/**
	 * The run_pending() method skips migrations where should_run() returns false and does not record them.
	 */
	public function test_run_pending_respects_should_run() {
		$this->register_migrations_path();
		$migrator = $this->get_migrator();

		$migrator->run_pending();

		// skippable migration's up() must not have run.
		$this->assertFalse( get_option( 'sample_plugin_skippable_ran' ) );

		// skippable migration must NOT be recorded (should_run = false → not tracked).
		$ran = get_option( 'sample_plugin_ran_migrations', array() );
		$this->assertNotContains( '2024_01_02_000000_skippable_migration', $ran );
		$this->assertEquals( '1.1.1', get_option( 'sample_plugin_migrated_version' ) );
	}

	/**
	 * The rollback() method calls down() and removes the migration from the ran list.
	 */
	public function test_rollback_calls_down_and_removes_from_ran() {
		$this->register_migrations_path();
		$migrator = $this->get_migrator();

		// Run the migration first so it is recorded.
		$migrator->run_pending();
		$this->assertEquals( 'yes', get_option( 'sample_plugin_migrated' ) );

		// Roll it back.
		$result = $migrator->rollback( '2024_01_01_000000_sample_migration' );

		$this->assertTrue( $result );
		$this->assertFalse( get_option( 'sample_plugin_migrated' ) );

		$ran = get_option( 'sample_plugin_ran_migrations', array() );
		$this->assertNotContains( '2024_01_01_000000_sample_migration', $ran );
	}

	/**
	 * The rollback() method returns false when the migration was not previously run.
	 */
	public function test_rollback_returns_false_when_not_in_ran_list() {
		$this->register_migrations_path();
		$migrator = $this->get_migrator();

		$result = $migrator->rollback( '2024_01_01_000000_sample_migration' );

		$this->assertFalse( $result );
	}

	/**
	 * The themeisle_sdk_rollback_migration_{slug} action triggers rollback.
	 */
	public function test_rollback_action_triggers_rollback() {
		$this->register_migrations_path();
		$migrator = $this->get_migrator();

		// Run the migration so it is recorded.
		$migrator->run_pending();
		$this->assertEquals( 'yes', get_option( 'sample_plugin_migrated' ) );

		// Trigger rollback via the action.
		do_action( 'themeisle_sdk_rollback_migration_sample_plugin', '2024_01_01_000000_sample_migration' );

		$this->assertFalse( get_option( 'sample_plugin_migrated' ) );
		$ran = get_option( 'sample_plugin_ran_migrations', array() );
		$this->assertNotContains( '2024_01_01_000000_sample_migration', $ran );
	}

	/**
	 * An invalid migration file keeps the product version pending for a retry.
	 */
	public function test_invalid_migration_file_keeps_version_pending() {
		// Create a temp directory with an invalid migration file.
		$tmp_dir = sys_get_temp_dir() . '/sdk_migrator_test_' . uniqid();
		mkdir( $tmp_dir ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.directory_mkdir
		// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_file_put_contents
		file_put_contents( $tmp_dir . '/2024_01_01_000000_bad_migration.php', '<?php return "not a migration";' );

		add_filter(
			'sample_plugin_sdk_migrations_path',
			function() use ( $tmp_dir ) {
				return $tmp_dir;
			}
		);

		$migrator = $this->get_migrator();
		$migrator->run_pending(); // Should not throw or record anything.

		$ran = get_option( 'sample_plugin_ran_migrations', array() );
		$this->assertEmpty( $ran );
		$this->assertFalse( get_option( 'sample_plugin_migrated_version' ) );

		// Cleanup.
		unlink( $tmp_dir . '/2024_01_01_000000_bad_migration.php' ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_unlink
		rmdir( $tmp_dir ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.directory_rmdir
	}

	/**
	 * A valid empty directory completes the current product version.
	 */
	public function test_empty_migrations_directory_marks_version_complete() {
		$tmp_dir = sys_get_temp_dir() . '/sdk_migrator_test_' . uniqid();
		mkdir( $tmp_dir ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.directory_mkdir

		add_filter(
			'sample_plugin_sdk_migrations_path',
			function() use ( $tmp_dir ) {
				return $tmp_dir;
			}
		);

		$migrator = $this->get_migrator();
		$migrator->run_pending();

		$this->assertEquals( '1.1.1', get_option( 'sample_plugin_migrated_version' ) );

		rmdir( $tmp_dir ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.directory_rmdir
	}

	/**
	 * A failed migration is unrecorded and retries on the next request.
	 */
	public function test_failed_migration_retries_on_next_request() {
		$tmp_dir = sys_get_temp_dir() . '/sdk_migrator_test_' . uniqid();
		mkdir( $tmp_dir ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.directory_mkdir

		$source = <<<'PHP'
<?php
return new class extends \ThemeisleSDK\Modules\Abstract_Migration {
	public function up() {
		$attempts = (int) get_option( 'sample_plugin_failure_attempts', 0 );
		update_option( 'sample_plugin_failure_attempts', $attempts + 1 );
		throw new \RuntimeException( 'Intentional migration failure.' );
	}
};
PHP;
		// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_file_put_contents
		file_put_contents( $tmp_dir . '/2024_01_01_000000_failing_migration.php', $source );

		add_filter(
			'sample_plugin_sdk_migrations_path',
			function() use ( $tmp_dir ) {
				return $tmp_dir;
			}
		);

		$migrator = $this->get_migrator();
		$migrator->run_pending();

		$this->assertEquals( 1, get_option( 'sample_plugin_failure_attempts' ) );
		$this->assertFalse( get_option( 'sample_plugin_migrated_version' ) );
		$this->assertEmpty( get_option( 'sample_plugin_ran_migrations', array() ) );

		$migrator->run_pending();

		$this->assertEquals( 2, get_option( 'sample_plugin_failure_attempts' ) );
		$this->assertFalse( get_option( 'sample_plugin_migrated_version' ) );

		unlink( $tmp_dir . '/2024_01_01_000000_failing_migration.php' ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_unlink
		rmdir( $tmp_dir ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.directory_rmdir
	}

	/**
	 * Only one migrator instance can hold the product lock at a time.
	 */
	public function test_migration_lock_prevents_concurrent_execution() {
		$first  = $this->get_migrator();
		$second = $this->get_migrator();

		$acquire = new ReflectionMethod( 'ThemeisleSDK\\Modules\\Migrator', 'acquire_lock' );
		$release = new ReflectionMethod( 'ThemeisleSDK\\Modules\\Migrator', 'release_lock' );
		$acquire->setAccessible( true );
		$release->setAccessible( true );

		$this->assertTrue( $acquire->invoke( $first ) );
		$this->assertFalse( $acquire->invoke( $second ) );

		$release->invoke( $first );
		$this->assertTrue( $acquire->invoke( $second ) );
		$release->invoke( $second );
	}

	/**
	 * An expired database lock can be replaced by a later request.
	 */
	public function test_expired_migration_lock_is_recovered() {
		global $wpdb;

		$migrator = $this->get_migrator();
		$expired  = wp_json_encode(
			array(
				'token'   => 'expired-token',
				'expires' => time() - 1,
			)
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, %s)",
				'sample_plugin_migration_lock',
				$expired,
				'no'
			)
		);

		$acquire = new ReflectionMethod( 'ThemeisleSDK\\Modules\\Migrator', 'acquire_lock' );
		$release = new ReflectionMethod( 'ThemeisleSDK\\Modules\\Migrator', 'release_lock' );
		$acquire->setAccessible( true );
		$release->setAccessible( true );

		$this->assertTrue( $acquire->invoke( $migrator ) );
		$release->invoke( $migrator );
	}
}
