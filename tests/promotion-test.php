<?php
/**
 * Promotion module feature test.
 *
 * @package ThemeIsleSDK
 */

/**
 * Test Promotion feature.
 */
class Promotion_Test extends WP_UnitTestCase {
	/**
	 * Author user ID.
	 *
	 * @var int $author_id
	 */
	private $author_id;

	/**
	 * Set up.
	 * Create a test user.
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->author_id = $this->factory->user->create( array( 'role' => 'editor' ) );
	}

	/**
	 * Tear down.
	 * Remove the user.
	 *
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
		wp_delete_user( $this->author_id, true );
	}

	/**
	 * Test the CSRF protection when setting the reference_key
	 *
	 * @return void
	 */
	public function testCSRFOptionUpdate() {
		$promotions = new \ThemeisleSDK\Modules\Promotions();
		$option_key = 'otter_reference_key';

		$option = get_option( $option_key );
		$this->assertEmpty( $option );

		wp_set_current_user( $this->author_id );

		// Check non-capable users can not update the option.
		$_GET['reference_key'] = 'test';
		$promotions->register_reference();
		$option = get_option( $option_key );
		$this->assertEmpty( $option );

		wp_set_current_user( 1 );

		// Check capable users with invalid nonce can't update the option.
		$promotions->register_reference();
		$option = get_option( $option_key );
		$this->assertEmpty( $option );

		// Check capable users with valid nonce can update the option.
		$plugin           = 'otter-blocks/otter-blocks.php';
		$_GET['plugin']   = rawurlencode( $plugin );
		$_GET['_wpnonce'] = wp_create_nonce( 'activate-plugin_' . $plugin );
		$promotions->register_reference();
		$option = get_option( $option_key );
		$this->assertEquals( 'test', $option );
	}
}
