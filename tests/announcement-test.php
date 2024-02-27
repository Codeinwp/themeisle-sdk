<?php
/**
 * Announcements module feature test.
 *
 * @package ThemeIsleSDK
 */

/**
 * Test Announcements feature.
 */
class Announcements_Test extends WP_UnitTestCase {
	protected static $admin_id;

	/**
	 * WP Instance setup.
	 */
	public static function wpSetUpBeforeClass( $factory ) {
		self::$admin_id = $factory->user->create(
			array(
				'role' => 'administrator',
			)
		);
		wp_set_current_user( self::$admin_id );
	}

	public static function wpTearDownAfterClass() {
		self::delete_user( self::$admin_id );
	}

	public function test_announcements_module_loading() {
		$file = dirname( __FILE__ ) . '/sample_products/sample_theme/style.css';
		\ThemeisleSDK\Loader::add_product( $file );
		$modules = \ThemeisleSDK\Common\Module_Factory::get_modules_map();
		$this->assertArrayHasKey( 'sample_theme', $modules );
		$modules['sample_theme'] = array_filter(
			$modules['sample_theme'],
			[ $this, 'filter_value' ]
		);
		$this->assertCount( 0, $modules['sample_theme'] );
	}

	private function filter_value( $value ) {
		return ( get_class( $value ) === 'ThemeisleSDK\\Modules\\Announcements' );
	}

	/**
	 * Test if module can load a product.
	 */
	public function test_announcement_product_loading() {

		$file = dirname( __FILE__ ) . '/sample_products/sample_theme/style.css';

		\ThemeisleSDK\Loader::add_product( $file );

		$modules = \ThemeisleSDK\Common\Module_Factory::get_modules_map();

		$this->assertArrayHasKey( 'sample_theme', $modules );
		$this->assertGreaterThan( 0, count( $modules['sample_theme'] ) );

	}

	public function test_announcement_event_black_friday() {
		$module = new \ThemeisleSDK\Modules\Announcements();

		$announcements = $module->get_announcements_for_plugins();

		$this->assertArrayHasKey( 'black_friday', $announcements );

		// The event should not be active before the event start date.
		$module->time = '2024-10-10 00:00:00';
		$this->assertFalse( $module->is_active( $announcements['black_friday'] ) );
	 
		// The event should not be active after the event end date.
		$module->time = '2024-12-4 00:00:00';
		$this->assertFalse( $module->is_active( $announcements['black_friday'] ) );

		// The event should be active between the event start and end date.
		$module->time = '2024-11-28 00:00:00';
		$this->assertTrue( $module->is_active( $announcements['black_friday'] ) );
	}

	public function test_announcement_without_end_date() {
		$module = new \ThemeisleSDK\Modules\Announcements();

		$dates = array(
			'start' => '2024-11-28 00:00:00',
		);

		// The event should not be active before the event start date.
		$module->time = '2024-10-10 00:00:00';
		$this->assertFalse( $module->is_active( $dates ) );

		// The event should be active after the event start date.
		$module->time = '2024-11-28 00:00:01';
		$this->assertTrue( $module->is_active( $dates ) );
	}

	public function test_announcement_without_start_date() {
		$module = new \ThemeisleSDK\Modules\Announcements();

		$dates = array(
			'end' => '2024-11-28 00:00:00',
		);

		// The event should be active before the event end date.
		$module->time = '2024-11-27 23:59:59';
		$this->assertTrue( $module->is_active( $dates ) );

		// The event should not be active after the event end date.
		$module->time = '2024-11-28 00:00:01';
		$this->assertFalse( $module->is_active( $dates ) );
	}

	public function test_announcement_without_start_and_end_date() {
		$module = new \ThemeisleSDK\Modules\Announcements();

		$dates = array();

		// The event should not be active without start and end date.
		$module->time = '2024-11-27 23:59:59';
		$this->assertFalse( $module->is_active( $dates ) );
	}
}
