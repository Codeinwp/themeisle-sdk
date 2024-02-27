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


}
