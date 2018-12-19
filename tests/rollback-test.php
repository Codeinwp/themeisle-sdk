<?php
/**
 * Rollback feature test.
 *
 * @package ThemeIsleSDK
 */

/**
 * Test Rollback feature.
 */
class Rollback_Test extends WP_UnitTestCase {

	/**
	 * Test product from partner loading.
	 */
	public function test_product_partner_module_loading() {

		$file = dirname( __FILE__ ) . '/sample_products/sample_theme_external/style.css';

		\ThemeisleSDK\Loader::add_product( $file );

		$modules = \ThemeisleSDK\Common\Module_Factory::get_modules_map();

		$this->assertArrayHasKey( 'sample_theme_external', $modules );
		$this->assertEquals( count( $modules['sample_theme_external'] ), 0 );

	}
	/**
	 * Test product from partner loading.
	 */
	public function test_rollback_product_loading() {

		$file = dirname( __FILE__ ) . '/sample_products/sample_theme/style.css';

		\ThemeisleSDK\Loader::add_product( $file );

		$modules = \ThemeisleSDK\Common\Module_Factory::get_modules_map();

		$this->assertArrayHasKey( 'sample_theme', $modules );
		$this->assertGreaterThan( 0, count( $modules['sample_theme'] ) );

	}

	/**
	 * Test if rollback is disabled on partners.
	 */
	public function test_rollback_can_load_partner() {

		$file    = dirname( __FILE__ ) . '/sample_products/sample_theme_external/style.css';
		$product = new \ThemeisleSDK\Product( $file );

		$this->assertFalse( ( new \ThemeisleSDK\Modules\Dashboard_Widget() )->can_load( $product ) );

	}

	/**
	 * Test if rollback is disabled on partners.
	 */
	public function test_rollback_can_load_regular() {

		$file    = dirname( __FILE__ ) . '/sample_products/sample_theme/style.css';
		$product = new \ThemeisleSDK\Product( $file );

		$this->assertTrue( ( new \ThemeisleSDK\Modules\Dashboard_Widget() )->can_load( $product ) );

	}


}
