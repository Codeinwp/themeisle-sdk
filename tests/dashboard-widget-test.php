<?php
/**
 * Dashboard widget related tests.
 *
 * @package ThemeIsleSDK
 */

/**
 * Test Dashboard widget class.
 */
class Dashboard_Widget_Test extends WP_UnitTestCase {

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
				return ( get_class( $value ) === 'ThemeisleSDK\\Modules\\Dashboard_widget' );
			}
		);
		$this->assertEquals( count( $modules['sample_theme_external'] ), 0 );

	}

	/**
	 * Test if dashboard widget is disabled on partners.
	 */
	public function test_dashboard_widget_can_load_partner() {

		$file    = dirname( __FILE__ ) . '/sample_products/sample_theme_external/style.css';
		$product = new \ThemeisleSDK\Product( $file );

		$this->assertFalse( ( new \ThemeisleSDK\Modules\Dashboard_Widget() )->can_load( $product ) );

	}

	/**
	 * Test if dashboard widget is disabled on partners.
	 */
	public function test_dashboard_widget_can_load_regular() {

		$file    = dirname( __FILE__ ) . '/sample_products/sample_theme/style.css';
		$product = new \ThemeisleSDK\Product( $file );

		$this->assertTrue( ( new \ThemeisleSDK\Modules\Dashboard_Widget() )->can_load( $product ) );
		$this->assertInstanceOf( 'ThemeisleSDK\\Modules\\Dashboard_Widget', ( new \ThemeisleSDK\Modules\Dashboard_Widget() )->load( $product ) );

	}

}
