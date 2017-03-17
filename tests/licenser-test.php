<?php
/**
 * Licenser feature test.
 *
 * @package ThemeIsleSDK
 */

/**
 * Test licenser feature.
 */
class Licenser_Test extends WP_UnitTestCase {


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
			[ $this, 'filter_value' ]
		);
		$this->assertCount( 1, $modules['sample_theme_external'] );

	}

	private function filter_value( $value ) {
		return ( get_class( $value ) === 'ThemeisleSDK\\Modules\\Licenser' );
	}

	/**
	 * Test product from partner loading.
	 */
	public function test_licenser_product_loading() {

		$file = dirname( __FILE__ ) . '/sample_products/sample_theme/style.css';

		\ThemeisleSDK\Loader::add_product( $file );

		$modules = \ThemeisleSDK\Common\Module_Factory::get_modules_map();

		$this->assertArrayHasKey( 'sample_theme', $modules );
		$this->assertGreaterThan( 0, count( $modules['sample_theme'] ) );

	}

	/**
	 * Test if licenser is disabled on partners.
	 */
	public function test_licenser_can_load_partner() {

		$file    = dirname( __FILE__ ) . '/sample_products/sample_theme_external/style.css';
		$product = new \ThemeisleSDK\Product( $file );

		$this->assertTrue( ( new \ThemeisleSDK\Modules\Licenser() )->can_load( $product ) );

	}


	/**
	 * Test if licenser should load for admins.
	 */
	public function test_licenser_can_load() {

		$file    = dirname( __FILE__ ) . '/sample_products/sample_pro_plugin/plugin_file.php';
		$product = new \ThemeisleSDK\Product( $file );

		$this->assertTrue( ( new \ThemeisleSDK\Modules\Licenser() )->can_load( $product ) );
		$this->assertInstanceOf( 'ThemeisleSDK\\Modules\\Licenser', ( new \ThemeisleSDK\Modules\Licenser() )->load( $product ) );

	}

	/**
	 * Test the product version is refreshed after an in-request plugin update.
	 */
	public function test_update_check_refreshes_product_version() {
		$directory = trailingslashit( get_temp_dir() ) . 'themeisle-sdk-updated-product-' . wp_generate_uuid4();
		$file      = trailingslashit( $directory ) . 'plugin.php';

		$this->assertTrue( wp_mkdir_p( $directory ) );
		$this->assertNotFalse( $this->write_plugin_file( $file, '1.0.0' ) );

		$product  = new \ThemeisleSDK\Product( $file );
		$licenser = ( new \ThemeisleSDK\Modules\Licenser() )->load( $product );
		$plugin   = $product->get_slug() . '/' . $product->get_file();
		$data     = (object) array(
			'response'  => array(),
			'no_update' => array(),
		);

		// Arm the delayed update check before the plugin files are replaced.
		$licenser->pre_set_site_transient_update_plugins_filter( $data );
		$this->assertNotFalse( $this->write_plugin_file( $file, '2.0.0' ) );

		// Reproduce a response left in the update bucket during the same request.
		$data->response[ $plugin ] = (object) array(
			'new_version' => '2.0.0',
		);

		$mock_api_response = static function () {
			return array(
				'headers'  => array(),
				'body'     => wp_json_encode(
					array(
						'new_version' => '2.0.0',
					)
				),
				'response' => array(
					'code'    => 200,
					'message' => 'OK',
				),
				'cookies'  => array(),
				'filename' => null,
			);
		};

		add_filter( 'pre_http_request', $mock_api_response, 10, 3 );
		try {
			$data = $licenser->pre_set_site_transient_update_plugins_filter( $data );
		} finally {
			remove_filter( 'pre_http_request', $mock_api_response, 10 );
			unlink( $file ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_unlink
			rmdir( $directory ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.directory_rmdir
		}

		$this->assertSame( '2.0.0', $product->get_version() );
		$this->assertArrayNotHasKey( $plugin, $data->response );
		$this->assertArrayHasKey( $plugin, $data->no_update );
	}

	/**
	 * Write a temporary premium plugin file.
	 *
	 * @param string $file    Plugin file path.
	 * @param string $version Plugin version.
	 *
	 * @return false|int
	 */
	private function write_plugin_file( $file, $version ) {
		// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_file_put_contents
		return file_put_contents(
			$file,
			sprintf(
				"<?php\n/**\n * Plugin Name: Test Updated Product\n * Version: %s\n * Author: ThemeIsle\n * Author URI: https://themeisle.com\n * WordPress Available: no\n * Requires License: yes\n */\n",
				$version
			)
		);
	}


}
