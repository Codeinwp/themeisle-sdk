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
	public function setUp() : void {
		parent::setUp();
		$this->author_id = $this->factory->user->create( array( 'role' => 'editor' ) );
	}

	/**
	 * Tear down.
	 * Remove the user.
	 *
	 * @return void
	 */
	public function tearDown() : void {
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

	/**
	 * Test the promotion dissallow filter and the promotion loading without it.
	 *
	 * @return void
	 */
	public function testPromotionDisallowFilter() {
		wp_set_current_user( 1 );
		$file    = dirname( __FILE__ ) . '/sample_products/sample_plugin/plugin_file.php';
		$product = new \ThemeisleSDK\Product( $file );

		$promotions = new \ThemeisleSDK\Modules\Promotions();

		$this->assertTrue( $promotions->can_load( $product ) );
		$promotions->load( $product );

		set_current_screen( 'edit-post' );
		$current_screen = get_current_screen();
		$current_screen->is_block_editor( true );

		$promotions->load_available();
		$promotions->enqueue();

		global $wp_scripts; // phpcs:ignore
		$data                     = $wp_scripts->get_data( 'ti-sdk-promo', 'data' );
		$prev_data                = $data;
		$data                     = str_replace( 'var themeisleSDKPromotions = ', '', $data );
		$data                     = substr( $data, 0, -1 );
		$themeisle_sdk_promotions = json_decode( $data, true );

		$this->assertEquals( 'Sample plugin.', $themeisle_sdk_promotions['product'] );
		$this->assertTrue( ! empty( $themeisle_sdk_promotions['showPromotion'] ) );

		add_filter(
			'sample_plugin_dissallowed_promotions',
			function () {
				return [
					'om-editor',
					'om-image-block',
					'om-attachment',
					'om-media',
					'om-elementor',
					'blocks-css',
					'blocks-animation',
					'blocks-conditions',
					'rop-posts',
					'ppom',
					'sparks-wishlist',
					'sparks-announcement',
					'sparks-product-review',
				];
			}
		);

		$promotions = new \ThemeisleSDK\Modules\Promotions();

		$this->assertTrue( $promotions->can_load( $product ) );
		$promotions->load( $product );
		$promotions->load_available();
		$promotions->enqueue();

		global $wp_scripts; // phpcs:ignore
		$data = $wp_scripts->get_data( 'ti-sdk-promo', 'data' );
		// we remove the previous enqueued data to only use the new one.
		$data                     = str_replace( $prev_data, '', $data );
		$data                     = str_replace( 'var themeisleSDKPromotions = ', '', $data );
		$data                     = substr( $data, 0, -1 );
		$themeisle_sdk_promotions = json_decode( $data, true );

		$this->assertTrue( empty( $themeisle_sdk_promotions['showPromotion'] ) ); // This should be empty as we filter all promotions.
	}
}
