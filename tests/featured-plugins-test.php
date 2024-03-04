<?php
/**
 * Featured Plugins module tests.
 *
 * @package ThemeIsleSDK
 */

/**
 * Test Featured Plugins loading.
 */
class Featured_Plugins_Test extends WP_UnitTestCase {

	private static $admin_id;

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

	/**
	 * Test plugin not loading without config.
	 */
	public function test_plugin_not_loading_if_not_pro() {
		$plugin         = dirname( __FILE__ ) . '/sample_products/sample_plugin/plugin_file.php';
		$plugin_product = new \ThemeisleSDK\Product( $plugin );

		$this->assertFalse( ( new \ThemeisleSDK\Modules\Featured_Plugins() )->can_load( $plugin_product ) );
	}

	/**
	 * Test plugin loading for pro.
	 */
	public function test_plugin_loading_for_pro() {
		$plugin         = dirname( __FILE__ ) . '/sample_products/sample_pro_plugin/plugin_file.php';
		$plugin_product = new \ThemeisleSDK\Product( $plugin );

		$this->assertTrue( ( new \ThemeisleSDK\Modules\Featured_Plugins() )->can_load( $plugin_product ) );
	}

	/**
	 * Test plugin not loading for pro if disabled.
	 */
	public function test_plugin_not_loading_for_pro_disabled() {
		$plugin         = dirname( __FILE__ ) . '/sample_products/sample_pro_plugin/plugin_file.php';
		$plugin_product = new \ThemeisleSDK\Product( $plugin );

		add_filter( 'themeisle_sdk_disable_featured_plugins', '__return_true' );

		$this->assertFalse( ( new \ThemeisleSDK\Modules\Featured_Plugins() )->can_load( $plugin_product ) );
	}

	/**
	 * Test the filter is added.
	 */
	public function test_plugins_api_result_filter_added() {
		wp_set_current_user( self::$admin_id );
		$plugin         = dirname( __FILE__ ) . '/sample_products/sample_pro_plugin/plugin_file.php';
		$plugin_product = new \ThemeisleSDK\Product( $plugin );

		$module = new \ThemeisleSDK\Modules\Featured_Plugins();
		$module->load( $plugin_product );

		$this->assertTrue( (bool) has_filter( 'plugins_api_result', [ $module, 'filter_plugin_api_results' ] ) );
	}

}
