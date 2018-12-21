<?php
/**
 * Endpoint feature test.
 *
 * @package ThemeIsleSDK
 */

/**
 * Test endpoint feature.
 */
class Endpoint_Test extends WP_UnitTestCase {


	/**
	 * Test product from partner loading.
	 */
	public function test_product_partner_module_loading() {

		$file = dirname( __FILE__ ) . '/sample_products/sample_theme_external/style.css';

		\ThemeisleSDK\Loader::add_product( $file );

		$modules = \ThemeisleSDK\Common\Module_Factory::get_modules_map();

		$this->assertArrayHasKey( 'sample_theme_external', $modules );
		$modules['sample_theme_external'] = array_filter(
			$modules['sample_theme_external'],
			function ( $value ) {
				return ( get_class( $value ) === 'ThemeisleSDK\\Modules\\Endpoint' );
			}
		);
		$this->assertCount( 1, $modules['sample_theme_external'] );

	}

	/**
	 * Test product from partner loading.
	 */
	public function test_endpoint_product_loading() {

		$file = dirname( __FILE__ ) . '/sample_products/sample_theme/style.css';

		\ThemeisleSDK\Loader::add_product( $file );

		$modules = \ThemeisleSDK\Common\Module_Factory::get_modules_map();

		$this->assertArrayHasKey( 'sample_theme', $modules );
		$this->assertGreaterThan( 0, count( $modules['sample_theme'] ) );

	}

	/**
	 * Test if endpoint is disabled on partners.
	 */
	public function test_endpoint_can_load_partner() {

		$file    = dirname( __FILE__ ) . '/sample_products/sample_theme_external/style.css';
		$product = new \ThemeisleSDK\Product( $file );

		$this->assertTrue( ( new \ThemeisleSDK\Modules\Endpoint() )->can_load( $product ) );

	}


	/**
	 * Test if endpoint should load for admins.
	 */
	public function test_endpoint_can_load() {

		$file    = dirname( __FILE__ ) . '/sample_products/sample_plugin/plugin_file.php';
		$product = new \ThemeisleSDK\Product( $file );

		$this->assertTrue( ( new \ThemeisleSDK\Modules\Endpoint() )->can_load( $product ) );
		$this->assertInstanceOf( 'ThemeisleSDK\\Modules\\Endpoint', ( new \ThemeisleSDK\Modules\Endpoint() )->load( $product ) );

	}


}
