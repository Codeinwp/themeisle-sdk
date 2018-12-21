<?php
/**
 * Translate feature test.
 *
 * @package ThemeIsleSDK
 */

/**
 * Test translate feature.
 */
class Translate_Test extends WP_UnitTestCase {


	public function test_product_partner_module_loading() {

		$file = dirname( __FILE__ ) . '/sample_products/sample_theme_external/style.css';

		\ThemeisleSDK\Loader::add_product( $file );

		$modules = \ThemeisleSDK\Common\Module_Factory::get_modules_map();

		$this->assertArrayHasKey( 'sample_theme_external', $modules );
		$modules['sample_theme_external'] = array_filter(
			$modules['sample_theme_external'],
			function ( $value ) {
				return ( get_class( $value ) === 'ThemeisleSDK\\Modules\\Translate' );
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

		$this->assertFalse( ( new \ThemeisleSDK\Modules\Translate() )->can_load( $product ) );

	}

	public function test_load_normal_english() {

		$file = dirname( __FILE__ ) . '/sample_products/sample_theme/style.css';

		$product = new \ThemeisleSDK\Product( $file );

		$this->assertFalse( ( new \ThemeisleSDK\Modules\Translate() )->can_load( $product ) );

	}

	public function test_load_non_english() {
		add_filter(
			'locale',
			function () {
				return 'fy';
			}
		);
		$file = dirname( __FILE__ ) . '/sample_products/sample_theme/style.css';

		$product = new \ThemeisleSDK\Product( $file );

		$this->assertTrue( ( new \ThemeisleSDK\Modules\Translate() )->can_load( $product ) );

		$this->assertInstanceOf( 'ThemeisleSDK\\Modules\\Translate', ( new \ThemeisleSDK\Modules\Translate() )->load( $product ) );

	}


}
