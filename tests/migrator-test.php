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
		delete_option( 'sample_plugin_migrated' );
		delete_option( 'sample_plugin_skippable_ran' );
		delete_option( 'sample_plugin_version' );
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
	 * The load() method returns the migrator instance and registers admin_init hook.
	 */
	public function test_load_returns_self() {
		$product  = new \ThemeisleSDK\Product( $this->plugin_file );
		$migrator = new \ThemeisleSDK\Modules\Migrator();
		$result   = $migrator->load( $product );
		$this->assertInstanceOf( 'ThemeisleSDK\\Modules\\Migrator', $result );
	}

	/**
	 * The run_pending() method does nothing when no upgrade action was fired this request.
	 */
	public function test_run_pending_does_nothing_without_upgrade_action() {
		$this->register_migrations_path();

		// Pre-seed the stored version to match the plugin version so the Product
		// constructor does not fire themeisle_sdk_update_sample_plugin.
		update_option( 'sample_plugin_version', '1.1.1' );

		$product  = new \ThemeisleSDK\Product( $this->plugin_file );
		$migrator = new \ThemeisleSDK\Modules\Migrator();
		$migrator->load( $product );

		$migrator->run_pending();

		$this->assertFalse( get_option( 'sample_plugin_migrated' ) );
		$this->assertEmpty( get_option( 'sample_plugin_ran_migrations', array() ) );
	}

	/**
	 * The run_pending() method does nothing when no migrations path is registered.
	 */
	public function test_run_pending_no_path_does_nothing() {
		$migrator = $this->get_migrator();
		$migrator->run_pending();
		$this->assertFalse( get_option( 'sample_plugin_ran_migrations' ) );
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
	 * A file that does not return an Abstract_Migration instance is safely skipped.
	 */
	public function test_invalid_migration_file_is_skipped() {
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

		// Cleanup.
		unlink( $tmp_dir . '/2024_01_01_000000_bad_migration.php' ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_unlink
		rmdir( $tmp_dir ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.directory_rmdir
	}
}
