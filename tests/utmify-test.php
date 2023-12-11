<?php
/**
 * UTMIFY tests.
 *
 * @package ThemeIsleSDK
 */

/**
 * Test utmify link class.
 */
class Utmify_Test extends WP_UnitTestCase {

	const AFFILIATE_URL = 'https://affiliate/upgrade/url';


	private function register_filter() {
		add_filter(
			'tsdk_utmify_examplecomlink',
			function ( $arguments, $url ) {
				$arguments['new_arg'] = '_affiliate_id_';
				return $arguments;
			},
			11,
			2 
		);
	}


	private function register_filter_url() {
		add_filter(
			'tsdk_utmify_url_examplecomlink',
			function ( $utmify_url, $url ) {
				return self::AFFILIATE_URL;
			},
			11,
			2 
		);
	}


	public function test_utmify_plugin() {


		$file = dirname( __FILE__ ) . '/sample_products/sample_plugin/plugin_file.php';

		$product = new \ThemeisleSDK\Product( $file );

		$link = tsdk_utmify( 'https://example.com/link', 'area', 'location' );

		$this->assertEquals( 'https://example.com/link?utm_source=wpadmin&utm_medium=location&utm_campaign=area&utm_content=examplecomlink', $link );

		$this->register_filter();

		$link = tsdk_utmify( 'https://example.com/link', 'area', 'location' );

		$this->assertEquals( 'https://example.com/link?utm_source=wpadmin&utm_medium=location&utm_campaign=area&utm_content=examplecomlink&new_arg=_affiliate_id_', $link );

		$this->register_filter_url();

		$link = tsdk_utmify( 'https://example.com/link', 'area', 'location' );

		$this->assertEquals( self::AFFILIATE_URL, $link );

	}

}
