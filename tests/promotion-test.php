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
	 * Product.
	 *
	 * @var \ThemeisleSDK\Product
	 */
	private $product;

	/**
	 * Original blogdescription value, saved before each test.
	 *
	 * @var string $original_blogdescription
	 */
	private $original_blogdescription;

	/**
	 * Set up.
	 * Create a test user.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		$this->author_id                = $this->factory->user->create( array( 'role' => 'editor' ) );
		$this->original_blogdescription = get_option( 'blogdescription', '' );
		update_option( 'blogdescription', '' );
		delete_transient( 'tisdk_page_title_signals_v1' );
		delete_transient( 'tisdk_lms_page_title_signal_v1' );
	}


	/**
	 * Tear down.
	 * Remove the user.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		parent::tearDown();
		wp_delete_user( $this->author_id, true );
		update_option( 'blogdescription', $this->original_blogdescription );
		delete_transient( 'tisdk_page_title_signals_v1' );
		delete_transient( 'tisdk_lms_page_title_signal_v1' );
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
	public function testPromotionLoading() {
		$this->setup_screen();

		$promotions = new \ThemeisleSDK\Modules\Promotions();
		$product    = $this->get_product();
		$this->assertTrue( $promotions->can_load( $product ) );
		$promotions->load( $product );
		$promotions->load_available();
		$promotions->enqueue();

		global $wp_scripts; // phpcs:ignore
		$data                     = $wp_scripts->get_data( 'ti-sdk-promo', 'data' );
		$data                     = str_replace( 'var themeisleSDKPromotions = ', '', $data );
		$data                     = substr( $data, 0, - 1 );
		$themeisle_sdk_promotions = json_decode( $data, true );

		$this->assertEquals( 'Sample plugin.', $themeisle_sdk_promotions['product'] );
		$this->assertTrue( ! empty( $themeisle_sdk_promotions['showPromotion'] ) );
	}

	public function testPromotionDisallowFilter() {
		$this->setup_screen();

		add_filter(
			'sample_plugin_dissallowed_promotions',
			function () {
				return [
					'om-editor',
					'om-image-block',
					'om-attachment',
					'blocks-css',
					'blocks-animation',
					'blocks-conditions',
				];
			}
		);

		$promotions = new \ThemeisleSDK\Modules\Promotions();
		$product    = $this->get_product();

		$this->assertTrue( $promotions->can_load( $product ) );
		$promotions->load( $product );
		$promotions->load_available();
		$promotions->enqueue();

		global $wp_scripts; // phpcs:ignore
		$data                     = $wp_scripts->get_data( 'ti-sdk-promo', 'data' );
		$data                     = str_replace( 'var themeisleSDKPromotions = ', '', $data );
		$data                     = substr( $data, 0, - 1 );
		$themeisle_sdk_promotions = json_decode( $data, true );

		$this->assertTrue( empty( $themeisle_sdk_promotions['showPromotion'] ) ); // This should be empty as we filter all promotions.
	}

	public function testWfpPromoNotShown() {
		wp_set_current_user( 1 );
		set_current_screen( 'plugin-install' );

		$promotions = new \ThemeisleSDK\Modules\Promotions();
		$product    = $this->get_product();
		$this->assertTrue( $promotions->can_load( $product ) );
		$promotions->load( $product );
		$promos = $promotions->promotions;

		$this->assertNotContains( 'wp-full-pay-plugins-install', $promos );
	}

	public function testWfpPromoShown() {
		$this->factory->post->create(
			array(
				'post_type'   => 'page',
				'post_author' => 1,
				'post_title'  => 'Donate',
				'post_status' => 'publish',
			)
		);

		wp_set_current_user( 1 );
		set_current_screen( 'plugin-install' );

		$promotions = new \ThemeisleSDK\Modules\Promotions();
		$product    = $this->get_product();
		$this->assertTrue( $promotions->can_load( $product ) );

		$promotions->load( $product );
		$promotions->load_available();

		$promos = $promotions->promotions;

		$this->assertContains( 'wp-full-pay-plugins-install', $promos );
	}

	public function testMasteriyoPromoShown() {
		update_option( 'blogdescription', 'Courses' );

		wp_set_current_user( 1 );
		set_current_screen( 'plugin-install' );

		$promotions = new \ThemeisleSDK\Modules\Promotions();
		$product    = $this->get_product();
		$this->assertTrue( $promotions->can_load( $product ) );

		$promotions->load( $product );
		$promotions->load_available();

		$promos = $promotions->promotions;

		$this->assertContains( 'masteriyo-plugins-install', $promos );

	}

	public function testMasteriyoPromoShownForExactLmsTagline() {
		update_option( 'blogdescription', 'LMS platform' );

		wp_set_current_user( 1 );
		set_current_screen( 'plugin-install' );

		$promotions = new \ThemeisleSDK\Modules\Promotions();
		$product    = $this->get_product();
		$this->assertTrue( $promotions->can_load( $product ) );

		$promotions->load( $product );
		$promotions->load_available();

		$promos = $promotions->promotions;

		$this->assertContains( 'masteriyo-plugins-install', $promos );
	}

	public function testMasteriyoPromoShownForPageTitle() {
		$this->factory->post->create(
			array(
				'post_type'   => 'page',
				'post_author' => 1,
				'post_title'  => 'Student Training',
				'post_status' => 'publish',
			)
		);

		wp_set_current_user( 1 );
		set_current_screen( 'plugin-install' );

		$promotions = new \ThemeisleSDK\Modules\Promotions();
		$product    = $this->get_product();
		$this->assertTrue( $promotions->can_load( $product ) );

		$promotions->load( $product );
		$promotions->load_available();

		$promos = $promotions->promotions;

		$this->assertContains( 'masteriyo-plugins-install', $promos );
	}

	public function testMasteriyoPromoNotShownForPartialLmsTaglineMatch() {
		update_option( 'blogdescription', 'Films' );

		wp_set_current_user( 1 );
		set_current_screen( 'plugin-install' );

		$promotions = new \ThemeisleSDK\Modules\Promotions();
		$product    = $this->get_product();
		$this->assertTrue( $promotions->can_load( $product ) );

		$promotions->load( $product );
		$promotions->load_available();

		$promos = $promotions->promotions;

		$this->assertNotContains( 'masteriyo-plugins-install', $promos );
	}

	public function testMasteriyoPromoNotShownForProblematicPartialTaglineMatches() {
		update_option( 'blogdescription', 'Classic Discourse' );

		wp_set_current_user( 1 );
		set_current_screen( 'plugin-install' );

		$promotions = new \ThemeisleSDK\Modules\Promotions();
		$product    = $this->get_product();
		$this->assertTrue( $promotions->can_load( $product ) );

		$promotions->load( $product );
		$promotions->load_available();

		$promos = $promotions->promotions;

		$this->assertNotContains( 'masteriyo-plugins-install', $promos );
	}

	public function testMasteriyoPromoNotShownForPartialLmsPageTitleMatch() {
		$this->factory->post->create(
			array(
				'post_type'   => 'page',
				'post_author' => 1,
				'post_title'  => 'Films',
				'post_status' => 'publish',
			)
		);

		wp_set_current_user( 1 );
		set_current_screen( 'plugin-install' );

		$promotions = new \ThemeisleSDK\Modules\Promotions();
		$product    = $this->get_product();
		$this->assertTrue( $promotions->can_load( $product ) );

		$promotions->load( $product );
		$promotions->load_available();

		$promos = $promotions->promotions;

		$this->assertNotContains( 'masteriyo-plugins-install', $promos );
	}

	public function testMasteriyoPromoNotShownForProblematicPartialPageTitleMatches() {
		$this->factory->post->create(
			array(
				'post_type'   => 'page',
				'post_author' => 1,
				'post_title'  => 'Classic Discourse',
				'post_status' => 'publish',
			)
		);

		wp_set_current_user( 1 );
		set_current_screen( 'plugin-install' );

		$promotions = new \ThemeisleSDK\Modules\Promotions();
		$product    = $this->get_product();
		$this->assertTrue( $promotions->can_load( $product ) );

		$promotions->load( $product );
		$promotions->load_available();

		$promos = $promotions->promotions;

		$this->assertNotContains( 'masteriyo-plugins-install', $promos );
	}

	public function testMasteriyoPromoConditionsSkipPageLookupOutsidePluginInstall() {
		$this->factory->post->create(
			array(
				'post_type'   => 'page',
				'post_author' => 1,
				'post_title'  => 'Student Training',
				'post_status' => 'publish',
			)
		);

		$this->setup_screen();

		$promotions = new \ThemeisleSDK\Modules\Promotions();
		$product    = $this->get_product();
		$this->assertTrue( $promotions->can_load( $product ) );

		$this->assertFalse( get_transient( 'tisdk_page_title_signals_v1' ) );
		$this->assertFalse( get_transient( 'tisdk_lms_page_title_signal_v1' ) );
	}

	public function testMasteriyoPromoNotShownWhenLmsPluginActive() {
		update_option( 'blogdescription', 'Courses' );

		$active_plugins = get_option( 'active_plugins', array() );
		update_option( 'active_plugins', array_unique( array_merge( (array) $active_plugins, array( 'tutor/tutor.php' ) ) ) );

		try {
			wp_set_current_user( 1 );
			set_current_screen( 'plugin-install' );

			$promotions = new \ThemeisleSDK\Modules\Promotions();
			$product    = $this->get_product();
			$this->assertTrue( $promotions->can_load( $product ) );

			$promotions->load( $product );
			$promotions->load_available();

			$promos = $promotions->promotions;

			$this->assertNotContains( 'masteriyo-plugins-install', $promos );
		} finally {
			update_option( 'active_plugins', $active_plugins );
		}
	}

	public function testMasteriyoPromoSkipsPageLookupWhenLmsPluginActive() {
		$this->factory->post->create(
			array(
				'post_type'   => 'page',
				'post_author' => 1,
				'post_title'  => 'Student Training',
				'post_status' => 'publish',
			)
		);

		$active_plugins = get_option( 'active_plugins', array() );
		update_option( 'active_plugins', array_unique( array_merge( (array) $active_plugins, array( 'tutor/tutor.php' ) ) ) );

		try {
			wp_set_current_user( 1 );
			set_current_screen( 'plugin-install' );

			$promotions = new \ThemeisleSDK\Modules\Promotions();
			$product    = $this->get_product();
			$this->assertTrue( $promotions->can_load( $product ) );

			$promotions->load( $product );
			$promotions->load_available();

			$promos = $promotions->promotions;

			$this->assertNotContains( 'masteriyo-plugins-install', $promos );
			$this->assertFalse( get_transient( 'tisdk_lms_page_title_signal_v1' ) );
		} finally {
			update_option( 'active_plugins', $active_plugins );
		}
	}

	public function testVisualizerBlockDirectorySuggestionForChartQueries() {
		$promotions = new class() extends \ThemeisleSDK\Modules\Promotions {
			public function call_plugin_api( $slug ) {
				return (object) array(
					'name'                => 'Visualizer',
					'short_description'   => 'Charts and graphs.',
					'author'              => '<a href="https://themeisle.com">ThemeIsle</a>',
					'rating'              => 100,
					'num_ratings'         => 12,
					'active_installs'     => 10000,
					'author_block_rating' => 100,
					'author_block_count'  => 1,
					'icons'               => array(
						'1x' => 'https://ps.w.org/visualizer/assets/icon-128x128.png',
						'2x' => 'https://ps.w.org/visualizer/assets/icon-256x256.png',
					),
					'last_updated'        => '2026-01-01 00:00:00',
				);
			}

			public function is_plugin_installed( $plugin ) {
				return false;
			}
		};

		$args     = (object) array( 'block' => 'charts' );
		$response = (object) array(
			'plugins' => array(
				array(
					'slug' => 'some-other-plugin',
				),
			),
		);

		$result = $promotions->inject_visualizer_block_directory_suggestion( $response, 'query_plugins', $args );

		$this->assertSame( 'visualizer', $result->plugins[0]['slug'] );
		$this->assertSame( 'visualizer/chart', $result->plugins[0]['blocks'][0]['name'] );
	}

	public function testVisualizerBlockDirectorySuggestionForVisualizerQueries() {
		$promotions = new class() extends \ThemeisleSDK\Modules\Promotions {
			public function call_plugin_api( $slug ) {
				return (object) array(
					'name'              => 'Visualizer',
					'short_description' => 'Charts and graphs.',
					'author'            => '<a href="https://themeisle.com">ThemeIsle</a>',
					'rating'            => 100,
					'num_ratings'       => 12,
					'active_installs'   => 10000,
					'icons'             => array(),
					'last_updated'      => '2026-01-01 00:00:00',
				);
			}

			public function is_plugin_installed( $plugin ) {
				return false;
			}
		};

		$args   = (object) array( 'block' => 'visualizer' );
		$result = $promotions->inject_visualizer_block_directory_suggestion( (object) array( 'plugins' => array() ), 'query_plugins', $args );

		$this->assertCount( 1, $result->plugins );
		$this->assertSame( 'visualizer', $result->plugins[0]['slug'] );
	}

	public function testVisualizerOnboardingIsSuppressedOnNewBlockEditorRequests() {
		global $pagenow;

		$pagenow           = 'post-new.php';
		$_GET['post_type'] = 'post';

		$promotions = new \ThemeisleSDK\Modules\Promotions();

		$this->assertFalse( $promotions->suppress_visualizer_onboarding_in_editor( true ) );
	}

	public function testVisualizerBlockEditorShimIsEnqueued() {
		global $pagenow, $wp_scripts;

		$pagenow           = 'post-new.php';
		$_GET['post_type'] = 'post';
		wp_register_script( 'visualizer-gutenberg-block', '', array(), '1.0.0', true );
		wp_enqueue_script( 'visualizer-gutenberg-block' );

		$promotions = new \ThemeisleSDK\Modules\Promotions();
		$promotions->enqueue_visualizer_block_editor_shim();

		$this->assertTrue( wp_script_is( 'ti-sdk-visualizer-editor-shim', 'enqueued' ) );
		$inline_scripts = $wp_scripts->registered['ti-sdk-visualizer-editor-shim']->extra['after'] ?? array();
		$this->assertStringContainsString(
			'window.google.visualization.Version',
			implode( "\n", $inline_scripts )
		);
	}

	private function get_product() {
		$file = dirname( __FILE__ ) . '/sample_products/sample_plugin/plugin_file.php';

		$product = $this->getMockBuilder( \ThemeisleSDK\Product::class )
						->setConstructorArgs( [ $file ] )
						->setMethods( [ 'get_install_time' ] )
						->getMock();

		$product->method( 'get_install_time' )
				->willReturn( time() - ( 4 * DAY_IN_SECONDS ) );

		return $product;
	}


	private function setup_screen() {
		wp_set_current_user( 1 );

		set_current_screen( 'edit-post' );
		$screen = get_current_screen();
		$screen->is_block_editor( true );
	}

}
