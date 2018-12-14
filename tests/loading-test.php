<?php
/**
 * `loading` test.
 *
 * @package ThemeIsleSDK
 */

/**
 * Test sdk loading.
 */
class Sdk_Loading_Test extends WP_UnitTestCase {
	/**
	 * Test if the SDK is loading properly and version is exported.
	 */
	public function test_version_exists() {
		global $themeisle_sdk_max_version;
		$this->assertTrue( isset( $themeisle_sdk_max_version ) );
		$this->assertTrue( version_compare( '0.0.0', $themeisle_sdk_max_version, '<' ) );
	}

}
