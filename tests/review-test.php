<?php
/**
 * Review feature test.
 *
 * @package ThemeIsleSDK
 */

/**
 * Test review feature.
 */
class Review_Test extends WP_UnitTestCase {


	public function test_product_partner_module_loading() {

		$file = dirname( __FILE__ ) . '/sample_products/sample_theme_external/style.css';

		\ThemeisleSDK\Loader::add_product( $file );

		$modules = \ThemeisleSDK\Common\Module_Factory::get_modules_map();

		$this->assertArrayHasKey( 'sample_theme_external', $modules );
		$modules['sample_theme_external'] = array_filter(
			$modules['sample_theme_external'],
			function ( $value ) {
				return ( get_class( $value ) === 'ThemeisleSDK\\Modules\\Review' );
			}
		);
		$this->assertEquals( count( $modules['sample_theme_external'] ), 0 );

	}

	public function test_product_loading() {

		$file = dirname( __FILE__ ) . '/sample_products/sample_theme/style.css';

		\ThemeisleSDK\Loader::add_product( $file );

		$modules = \ThemeisleSDK\Common\Module_Factory::get_modules_map();

		$this->assertArrayHasKey( 'sample_theme', $modules );
		$this->assertGreaterThan( 0, count( $modules['sample_theme'] ) );

	}

	public function test_can_load_partner() {

		$file    = dirname( __FILE__ ) . '/sample_products/sample_theme_external/style.css';
		$product = new \ThemeisleSDK\Product( $file );

		$this->assertFalse( ( new \ThemeisleSDK\Modules\Review() )->can_load( $product ) );

	}

	public function test_load_normal() {

		$file = dirname( __FILE__ ) . '/sample_products/sample_theme/style.css';

		$product = new \ThemeisleSDK\Product( $file );

		$this->assertTrue( ( new \ThemeisleSDK\Modules\Review() )->can_load( $product ) );
		$this->assertInstanceOf( 'ThemeisleSDK\\Modules\\Review', ( new \ThemeisleSDK\Modules\Review() )->load( $product ) );

	}


}
