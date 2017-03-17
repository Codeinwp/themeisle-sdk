<?php
/**
 * Crash reporter feature test.
 *
 * @package ThemeIsleSDK
 */

use ThemeisleSDK\Modules\Crash_Reporter;
use ThemeisleSDK\Product;

/**
 * Test crash reporter feature.
 */
class Crash_Reporter_Test extends WP_UnitTestCase {

	/**
	 * Sample plugin product.
	 *
	 * @var Product
	 */
	private $product;

	/**
	 * Setup a clean crash reporter state around a sample product.
	 */
	public function setUp(): void {
		parent::setUp();
		Crash_Reporter::reset();
		$this->product = new Product( dirname( __FILE__ ) . '/sample_products/sample_plugin/plugin_file.php' );
		delete_option( $this->product->get_key() . '_crash_data' );
		delete_transient( $this->product->get_key() . '_crash_backoff' );
		wp_clear_scheduled_hook( $this->product->get_key() . '_crash_flush' );
		// Sample plugin is not wordpress-available, so consent defaults to yes; pin it off.
		update_option( $this->product->get_key() . '_logger_flag', 'no' );
	}

	/**
	 * Build a synthetic fatal in a given file.
	 *
	 * @param string $file File the error originates from.
	 * @param int    $line Line of the error.
	 * @param string $message Error message.
	 *
	 * @return array Error shape for Crash_Reporter::capture().
	 */
	private function make_error( $file, $line = 10, $message = 'Call to undefined function foo()' ) {
		return [
			'type'    => E_ERROR,
			'message' => $message,
			'file'    => $file,
			'line'    => $line,
			'trace'   => [],
		];
	}

	/**
	 * Path of a file inside the sample product.
	 *
	 * @param string $relative Relative path.
	 *
	 * @return string Absolute path.
	 */
	private function product_file( $relative = 'inc/broken.php' ) {
		return dirname( $this->product->get_basefile() ) . '/' . $relative;
	}

	public function test_can_load_by_default() {
		$this->assertTrue( ( new Crash_Reporter() )->can_load( $this->product ) );
	}

	public function test_can_load_disabled_by_filter() {
		add_filter( $this->product->get_slug() . '_sdk_enable_crash_reporter', '__return_false' );
		$this->assertFalse( ( new Crash_Reporter() )->can_load( $this->product ) );
		remove_all_filters( $this->product->get_slug() . '_sdk_enable_crash_reporter' );
	}

	public function test_load_returns_instance() {
		$this->assertInstanceOf( 'ThemeisleSDK\Modules\Crash_Reporter', ( new Crash_Reporter() )->load( $this->product ) );
		$this->assertTrue( defined( 'THEMEISLE_SDK_CRASH_HANDLER' ) );
	}

	public function test_capture_stores_product_error() {
		( new Crash_Reporter() )->load( $this->product );

		$this->assertTrue( Crash_Reporter::capture( $this->make_error( $this->product_file() ) ) );

		$data = get_option( $this->product->get_key() . '_crash_data' );
		$this->assertCount( 1, $data['reports'] );
		$report = array_values( $data['reports'] )[0];
		$this->assertSame( 'product:inc/broken.php', $report['file'] );
		$this->assertSame( 'fatal_error', $report['event_type'] );
		$this->assertSame( 1, $report['count'] );
		$this->assertSame( $this->product->get_version(), $report['product_version'] );
	}

	public function test_capture_drops_foreign_error() {
		( new Crash_Reporter() )->load( $this->product );

		$this->assertFalse( Crash_Reporter::capture( $this->make_error( '/srv/www/wp-content/plugins/some-other-plugin/main.php' ) ) );
		$data = get_option( $this->product->get_key() . '_crash_data' );
		$this->assertTrue( empty( $data['reports'] ) );
	}

	public function test_capture_without_registry_drops_everything() {
		$this->assertFalse( Crash_Reporter::capture( $this->make_error( $this->product_file() ) ) );
	}

	public function test_fingerprint_deduplication_bumps_count() {
		( new Crash_Reporter() )->load( $this->product );

		Crash_Reporter::capture( $this->make_error( $this->product_file(), 42 ) );
		Crash_Reporter::capture( $this->make_error( $this->product_file(), 42 ) );

		$data = get_option( $this->product->get_key() . '_crash_data' );
		$this->assertCount( 1, $data['reports'] );
		$this->assertSame( 2, array_values( $data['reports'] )[0]['count'] );
	}

	public function test_message_digits_do_not_break_deduplication() {
		( new Crash_Reporter() )->load( $this->product );

		Crash_Reporter::capture( $this->make_error( $this->product_file(), 42, 'Allowed memory size of 134217728 bytes exhausted' ) );
		Crash_Reporter::capture( $this->make_error( $this->product_file(), 42, 'Allowed memory size of 268435456 bytes exhausted' ) );

		$data = get_option( $this->product->get_key() . '_crash_data' );
		$this->assertCount( 1, $data['reports'] );
	}

	public function test_sanitization_redacts_secrets_and_paths() {
		( new Crash_Reporter() )->load( $this->product );

		$message = 'Failure for john.doe@example.com with key abcdef0123456789abcdef0123456789 in /srv/www/secret/dir/file.php';
		Crash_Reporter::capture(
			[
				'type'    => E_ERROR,
				'message' => $message,
				'file'    => $this->product_file(),
				'line'    => 7,
				'trace'   => [
					[
						'file'     => $this->product_file( 'inc/caller.php' ),
						'line'     => 3,
						'function' => 'do_thing',
						'class'    => 'Sample_Class',
						'type'     => '->',
						'args'     => [ 'sensitive-value' ],
					],
					[
						'file'     => '/srv/www/wp-content/plugins/other/other.php',
						'line'     => 99,
						'function' => 'other_thing',
					],
				],
			]
		);

		$data   = get_option( $this->product->get_key() . '_crash_data' );
		$report = array_values( $data['reports'] )[0];

		$this->assertStringNotContainsString( 'john.doe@example.com', $report['message'] );
		$this->assertStringContainsString( '[email]', $report['message'] );
		$this->assertStringNotContainsString( 'abcdef0123456789abcdef0123456789', $report['message'] );
		$this->assertStringNotContainsString( '/srv/www/secret', $report['message'] );

		$this->assertSame( 'product:inc/caller.php', $report['trace'][0]['file'] );
		$this->assertSame( 'Sample_Class->do_thing', $report['trace'][0]['function'] );
		$this->assertArrayNotHasKey( 'args', $report['trace'][0] );
		$this->assertStringNotContainsString( '/srv/www/wp-content', $report['trace'][1]['file'] );
	}

	public function test_fingerprint_cap_eviction() {
		( new Crash_Reporter() )->load( $this->product );

		for ( $i = 1; $i <= 20; $i ++ ) {
			Crash_Reporter::capture( $this->make_error( $this->product_file(), $i, 'Distinct error nr ' . str_repeat( 'x', $i ) ) );
		}

		$data = get_option( $this->product->get_key() . '_crash_data' );
		$this->assertCount( 15, $data['reports'] );
	}

	public function test_no_flush_scheduled_without_consent() {
		( new Crash_Reporter() )->load( $this->product );
		Crash_Reporter::capture( $this->make_error( $this->product_file() ) );

		$this->assertFalse( wp_next_scheduled( $this->product->get_key() . '_crash_flush' ) );
	}

	public function test_flush_scheduled_with_consent() {
		update_option( $this->product->get_key() . '_logger_flag', 'yes' );
		( new Crash_Reporter() )->load( $this->product );
		Crash_Reporter::capture( $this->make_error( $this->product_file() ) );

		$this->assertNotFalse( wp_next_scheduled( $this->product->get_key() . '_crash_flush' ) );
	}

	public function test_no_flush_scheduled_during_backoff() {
		update_option( $this->product->get_key() . '_logger_flag', 'yes' );
		( new Crash_Reporter() )->load( $this->product );
		// A version-change during load lifts the backoff by design, so the
		// backoff is set afterwards, matching the real sequence: failed send,
		// then a later crash must not re-schedule inside the window.
		set_transient( $this->product->get_key() . '_crash_backoff', true, 600 );
		Crash_Reporter::capture( $this->make_error( $this->product_file() ) );

		$this->assertFalse( wp_next_scheduled( $this->product->get_key() . '_crash_flush' ) );
	}

	public function test_sentinel_records_are_adopted_on_load() {
		update_option(
			$this->product->get_key() . '_crash_data',
			[
				'raw' => [
					[
						'type'    => E_ERROR,
						'message' => 'Bootstrap fatal',
						'file'    => $this->product_file( 'bootstrap.php' ),
						'line'    => 5,
						'time'    => time() - 100,
					],
				],
			]
		);

		( new Crash_Reporter() )->load( $this->product );

		$data = get_option( $this->product->get_key() . '_crash_data' );
		$this->assertArrayNotHasKey( 'raw', $data );
		$this->assertCount( 1, $data['reports'] );
		$this->assertSame( 'product:bootstrap.php', array_values( $data['reports'] )[0]['file'] );
	}

	public function test_uninstall_summary_is_compact_and_product_only() {
		( new Crash_Reporter() )->load( $this->product );

		for ( $i = 1; $i <= 8; $i ++ ) {
			Crash_Reporter::capture(
				[
					'type'    => E_ERROR,
					'message' => str_repeat( 'long message ', 40 ) . $i,
					'file'    => $this->product_file( 'inc/file-' . $i . '.php' ),
					'line'    => $i,
					'trace'   => [
						[
							'file'     => $this->product_file( 'inc/caller.php' ),
							'line'     => 1,
							'function' => 'inner',
						],
						[
							'file'     => '/srv/www/wp-includes/plugin.php',
							'line'     => 2,
							'function' => 'apply_filters',
						],
					],
				]
			);
		}

		$summary = Crash_Reporter::get_uninstall_summary( $this->product );

		$this->assertCount( 5, $summary );
		foreach ( $summary as $entry ) {
			$this->assertLessThanOrEqual( 200, strlen( $entry['message'] ) );
			foreach ( $entry['trace'] as $frame ) {
				$this->assertTrue( 0 === strpos( $frame['file'], 'product:' ) || 0 === strpos( $frame['file'], 'sdk:' ) );
			}
		}
	}

	public function test_uninstall_summary_empty_without_reports() {
		$this->assertSame( [], Crash_Reporter::get_uninstall_summary( $this->product ) );
	}

	public function test_crash_data_option_is_not_autoloaded() {
		( new Crash_Reporter() )->load( $this->product );
		Crash_Reporter::capture( $this->make_error( $this->product_file() ) );

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- the autoload flag is only visible via a direct query.
		$autoload = $wpdb->get_var( $wpdb->prepare( "SELECT autoload FROM $wpdb->options WHERE option_name = %s", $this->product->get_key() . '_crash_data' ) );
		$this->assertContains( $autoload, [ 'no', 'off' ] );
	}

	public function test_uncaught_exception_shape_is_recorded() {
		( new Crash_Reporter() )->load( $this->product );

		Crash_Reporter::capture(
			[
				'type'         => E_ERROR,
				'message'      => 'RuntimeException: something broke',
				'file'         => $this->product_file(),
				'line'         => 12,
				'trace'        => [],
				'is_exception' => true,
			]
		);

		$data = get_option( $this->product->get_key() . '_crash_data' );
		$this->assertSame( 'uncaught_exception', array_values( $data['reports'] )[0]['event_type'] );
	}
}
