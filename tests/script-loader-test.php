<?php
/**
 * Script loader feature test.
 *
 * @package ThemeIsleSDK
 */

/**
 * Test script logger feature.
 */
class Script_Loader_Test extends WP_UnitTestCase {


	public function test_script_loader_module_loading() {

		$file = dirname( __FILE__ ) . '/sample_products/sample_theme/style.css';

		\ThemeisleSDK\Loader::add_product( $file );

		$modules = \ThemeisleSDK\Common\Module_Factory::get_modules_map();
		
		$this->assertArrayHasKey( 'sample_theme', $modules );
		$modules['sample_theme'] = array_filter(
			$modules['sample_theme'],
			[ $this, 'filter_value' ]
		);
		$this->assertEquals( count( $modules['sample_theme'] ), 1 );

	}

	private function filter_value( $value ) {
		return ! empty( $value ) && ( get_class( $value ) === 'ThemeisleSDK\\Modules\\Script_Loader' );
	}

	public function test_script_loader_product_loading() {

		$file = dirname( __FILE__ ) . '/sample_products/sample_theme/style.css';

		\ThemeisleSDK\Loader::add_product( $file );

		$modules = \ThemeisleSDK\Common\Module_Factory::get_modules_map();

		$this->assertArrayHasKey( 'sample_theme', $modules );
		$this->assertGreaterThan( 0, count( $modules['sample_theme'] ) );

	}

	public function test_script_loader_can_not_load_partner() {

		$file    = dirname( __FILE__ ) . '/sample_products/sample_theme_external/style.css';
		$product = new \ThemeisleSDK\Product( $file );

		$this->assertFalse( ( new \ThemeisleSDK\Modules\Script_Loader() )->can_load( $product ) );
	}

	public function test_script_loader_load_normal() {
		$file = dirname( __FILE__ ) . '/sample_products/sample_theme/style.css';

		$product = new \ThemeisleSDK\Product( $file );

		$this->assertTrue( ( new \ThemeisleSDK\Modules\Script_Loader() )->can_load( $product ) );
		$this->assertInstanceOf( 'ThemeisleSDK\\Modules\\Script_Loader', ( new \ThemeisleSDK\Modules\Script_Loader() )->load( $product ) );
	}

	public function test_script_loader_filters_check() {
		$file = dirname( __FILE__ ) . '/sample_products/sample_theme/style.css';

		$product = new \ThemeisleSDK\Product( $file );
		
		$module = ( new \ThemeisleSDK\Modules\Script_Loader() )->load( $product );
		
		// Check if the hooks are available.
		$this->assertEquals( has_filter( 'themeisle_sdk_dependency_script_handler', [ $module, 'get_script_handler' ] ), 10 );
		$this->assertEquals( has_action( 'themeisle_sdk_dependency_enqueue_script', [ $module, 'enqueue_script' ] ), 10 );
	}

	public function test_multiple_script_loading() {
		$file = dirname( __FILE__ ) . '/sample_products/sample_theme/style.css';

		$product = new \ThemeisleSDK\Product( $file );
		
		/**
		 * When multiple products are loaded, the script loader hooks registration should not be triggered multiple times.
		 */
		( new \ThemeisleSDK\Modules\Script_Loader() )->load( $product );
		( new \ThemeisleSDK\Modules\Script_Loader() )->load( $product );
		( new \ThemeisleSDK\Modules\Script_Loader() )->load( $product );
		
		// Load survey script.
		$handler = apply_filters( 'themeisle_sdk_dependency_script_handler', 'survey' );
		$this->assertNotEmpty( $handler );
		$this->assertTrue( 'themeisle_sdk_survey_script' === $handler );
		do_action( 'themeisle_sdk_dependency_enqueue_script', 'survey' );
		$this->assertTrue( wp_script_is( $handler, 'enqueued' ) );

		// Load tracking script.
		$handler = apply_filters( 'themeisle_sdk_dependency_script_handler', 'tracking' );
		$this->assertNotEmpty( $handler );
		$this->assertTrue( 'themeisle_sdk_tracking_script' === $handler );
		do_action( 'themeisle_sdk_dependency_enqueue_script', 'tracking' );
		$this->assertTrue( wp_script_is( $handler, 'enqueued' ) );

		$this->assertTrue( has_filter( 'themeisle_sdk_script_setup' ) );
	}

	public function test_script_loader_handler_check() {
		$file = dirname( __FILE__ ) . '/sample_products/sample_theme/style.css';

		$product = new \ThemeisleSDK\Product( $file );
		
		( new \ThemeisleSDK\Modules\Script_Loader() )->load( $product );
		
		// Existing dependencies should have a handler.
		$handler = apply_filters( 'themeisle_sdk_dependency_script_handler', 'survey' );
		$this->assertNotEmpty( $handler );

		$handler = apply_filters( 'themeisle_sdk_dependency_script_handler', 'tracking' );
		$this->assertNotEmpty( $handler );

		// Non-existing dependencies should not have a handler.
		$handler = apply_filters( 'themeisle_sdk_dependency_script_handler', 'test' );
		$this->assertEmpty( $handler );
	}

	public function test_script_loader_enqueue_script() {
		$file = dirname( __FILE__ ) . '/sample_products/sample_theme/style.css';

		$product = new \ThemeisleSDK\Product( $file );
		
		( new \ThemeisleSDK\Modules\Script_Loader() )->load( $product );
		
		// Load survey script.
		$handler = apply_filters( 'themeisle_sdk_dependency_script_handler', 'survey' );
		$this->assertNotEmpty( $handler );
		do_action( 'themeisle_sdk_dependency_enqueue_script', 'survey' );
		$this->assertTrue( wp_script_is( $handler, 'enqueued' ) );

		// Load tracking script.
		$handler = apply_filters( 'themeisle_sdk_dependency_script_handler', 'tracking' );
		$this->assertNotEmpty( $handler );
		do_action( 'themeisle_sdk_dependency_enqueue_script', 'tracking' );
		$this->assertTrue( wp_script_is( $handler, 'enqueued' ) );

		// Load test script (it does not exist so it should not be enqueued).
		$handler = apply_filters( 'themeisle_sdk_dependency_script_handler', 'test' );
		$this->assertEmpty( $handler );
		do_action( 'themeisle_sdk_dependency_enqueue_script', 'test' );
		$this->assertFalse( wp_script_is( $handler, 'enqueued' ) );
	}

	/**
	 * Test the load_survey_for_product method.
	 * 
	 * @return void
	 */
	public function test_load_survey_for_product() {
		$file          = dirname( __FILE__ ) . '/sample_products/sample_theme/style.css';
		$product       = new \ThemeisleSDK\Product( $file );
		$script_loader = new \ThemeisleSDK\Modules\Script_Loader();
		
		$script_loader->load_survey_for_product( $product->get_slug(), [] );

		// Verify the survey script is loaded
		$handler = $script_loader->get_script_handler( 'survey' );
		$this->assertNotEmpty( $handler );
		$this->assertTrue( wp_script_is( $handler, 'enqueued' ) );
	}

	/**
	 * Test the get_survey_common_data method.
	 * 
	 * @return void
	 */
	public function test_get_survey_common_data() {
		$script_loader = new \ThemeisleSDK\Modules\Script_Loader();
		
		// Set up test filters
		add_filter(
			'themeisle_sdk_current_lang',
			function() {
				return 'de_DE';
			}
		);

		add_filter(
			'themeisle_sdk_current_site_url',
			function() {
				return 'https://example.com/wordpress';
			}
		);
		
		$data = $script_loader->get_survey_common_data();
		
		// Assert the structure and content of returned data
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'userId', $data );
		$this->assertArrayHasKey( 'attributes', $data );
		$this->assertArrayHasKey( 'language', $data['attributes'] );
		
		// Test German language mapping
		$this->assertEquals( 'de', $data['attributes']['language'] );
		
		// Test userId generation
		$expected_user_id = 'u_' . hash( 'crc32b', 'example.com/wordpress' );
		$this->assertEquals( $expected_user_id, $data['userId'] );
		
		// Test with different language that are not yet supported.
		add_filter(
			'themeisle_sdk_current_lang',
			function() {
				return 'fr_FR';
			}
		);
		$data = $script_loader->get_survey_common_data();
		$this->assertEquals( 'en', $data['attributes']['language'] );
	}

	/**
	 * Test the secret masking filter.
	 * 
	 * @return void
	 */
	public function test_secret_masking() {
		$script_loader = new \ThemeisleSDK\Modules\Script_Loader();

		// Test normal string
		$this->assertEquals( '****test', $script_loader->secret_masking( 'testtest' ) );

		// Test odd length string
		$this->assertEquals( '***test', $script_loader->secret_masking( 'footest' ) );

		// Test empty string
		$this->assertEquals( '', $script_loader->secret_masking( '' ) );

		// Test non-string input
		$this->assertEquals( 123, $script_loader->secret_masking( 123 ) );
		$this->assertEquals( null, $script_loader->secret_masking( null ) );
		$this->assertEquals( [], $script_loader->secret_masking( [] ) );
	}
}
