<?php
/**
 * Featured Plugins module tests.
 *
 * @package ThemeIsleSDK
 */


/**
 * Mock the plugins_api function.
 *
 * @param string       $action The API function being performed.
 * @param array|object $args   Plugin API arguments.
 *
 * @return object
 */
function plugins_api( $action, $args ) {
	return mock_plugin_api_results();
}

/**
 * Mock the plugin API results.
 *
 * @return object
 */
function mock_plugin_api_results() {
	$featured_plugin = array(
		'name'                     => 'Featured Plugin',
		'slug'                     => 'featured-plugin',
		'version'                  => '7.2.0',
		'author'                   => 'PHPUnit Featured Plugin',
		'author_profile'           => 'https://example.com/featured-plugin',
		'requires'                 => '6.3',
		'tested'                   => '6.5',
		'requires_php'             => '7.0',
		'requires_plugins'         => array(),
		'rating'                   => 80,
		'ratings'                  => array(
			5 => 3,
			4 => 0,
			3 => 0,
			2 => 0,
			1 => 1,
		),
		'num_ratings'              => 4,
		'support_threads'          => 1,
		'support_threads_resolved' => 0,
		'active_installs'          => 6000,
		'downloaded'               => 316410,
		'last_updated'             => '2024-03-11 9:17pm GMT',
		'added'                    => '2021-02-11',
		'homepage'                 => '',
		'short_description'        => 'Short Desc',
		'description'              => 'Long Desc',
		'download_link'            => 'https://example.com/plugin/featured-plugin.7.2.0.zip',
		'tags'                     => array(
			'auto-update'    => 'auto-update',
			'failure'        => 'failure',
			'feature-plugin' => 'feature-plugin',
			'update'         => 'update',
		),
		'donate_link'              => '',
		'icons'                    => array(
			'1x'  => 'https://example.com/featured-plugin/assets/icon.svg?rev=2787335',
			'svg' => 'https://example.com/featured-plugin/assets/icon.svg?rev=2787335',
		),
	);

	$results = array( $featured_plugin );

	$api_result          = new stdClass();
	$api_result->info    = new stdClass();
	$api_result->plugins = $results;
	$api_result->info    = array( 'results' => 1 );

	return $api_result;
}

/**
 * Test Featured Plugins loading.
 */
class Featured_Plugins_Test extends WP_UnitTestCase {

	private static $admin_id;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$admin_id = $factory->user->create(
			array(
				'role' => 'administrator',
			)
		);

		wp_set_current_user( self::$admin_id );
	}

	public static function wpTearDownAfterClass() {
		self::delete_user( self::$admin_id );
	}

	/**
	 * Utility method to change the value of a protected property.
	 *
	 * @param \ThemeisleSDK\Product $object The object.
	 * @param string                $property The property name.
	 * @param mixed                 $new_value The new value.
	 *
	 * @return void
	 * @throws ReflectionException Throws an exception if the property does not exist.
	 */
	private function set_protected_property( $object, $property, $new_value ) {
		$reflection = new ReflectionClass( $object );
		$property   = $reflection->getProperty( $property );
		$property->setAccessible( true );
		$property->setValue( $object, $new_value );
	}

	/**
	 * Test plugin not loading without config.
	 */
	public function test_plugin_not_loading_if_not_pro() {
		$plugin         = dirname( __FILE__ ) . '/sample_products/sample_plugin/plugin_file.php';
		$plugin_product = new \ThemeisleSDK\Product( $plugin );

		$this->set_protected_property( $plugin_product, 'wordpress_available', true );

		$this->assertFalse( ( new \ThemeisleSDK\Modules\Featured_Plugins() )->can_load( $plugin_product ) );
	}

	/**
	 * Test plugin loading for pro.
	 */
	public function test_plugin_loading_for_pro() {
		$plugin         = dirname( __FILE__ ) . '/sample_products/sample_pro_plugin/plugin_file.php';
		$plugin_product = new \ThemeisleSDK\Product( $plugin );

		$this->assertTrue( ( new \ThemeisleSDK\Modules\Featured_Plugins() )->can_load( $plugin_product ) );
	}

	/**
	 * Test plugin not loading for slugs that contain pro as part of a word. Eg. Product.
	 */
	public function test_plugin_loading_for_words_w_pro() {
		$plugin         = dirname( __FILE__ ) . '/sample_products/sample_pro_plugin/plugin_file.php';
		$plugin_product = new \ThemeisleSDK\Product( $plugin );

		$this->set_protected_property( $plugin_product, 'wordpress_available', true );

		$this->assertFalse( ( new \ThemeisleSDK\Modules\Featured_Plugins() )->can_load( $plugin_product ) );
	}

	/**
	 * Test plugin not loading for pro if disabled.
	 */
	public function test_plugin_not_loading_for_pro_disabled() {
		$plugin         = dirname( __FILE__ ) . '/sample_products/sample_pro_plugin/plugin_file.php';
		$plugin_product = new \ThemeisleSDK\Product( $plugin );

		add_filter( 'themeisle_sdk_disable_featured_plugins', '__return_true' );

		$this->assertFalse( ( new \ThemeisleSDK\Modules\Featured_Plugins() )->can_load( $plugin_product ) );
	}

	/**
	 * Test the filter is added.
	 */
	public function test_plugins_api_result_filter_added() {
		wp_set_current_user( self::$admin_id );
		$plugin         = dirname( __FILE__ ) . '/sample_products/sample_pro_plugin/plugin_file.php';
		$plugin_product = new \ThemeisleSDK\Product( $plugin );

		$module = new \ThemeisleSDK\Modules\Featured_Plugins();
		$module->load( $plugin_product );

		$this->assertTrue( (bool) has_filter( 'plugins_api_result', [ $module, 'filter_plugin_api_results' ] ) );
	}

	/**
	 * Test the filter is not added if already registered.
	 */
	public function test_plugins_api_will_not_add_filter_if_marked_as_registered() {
		wp_set_current_user( self::$admin_id );
		$plugin         = dirname( __FILE__ ) . '/sample_products/sample_pro_plugin/plugin_file.php';
		$plugin_product = new \ThemeisleSDK\Product( $plugin );

		add_filter( 'themeisle_sdk_plugin_api_filter_registered', '__return_true' );

		$module = new \ThemeisleSDK\Modules\Featured_Plugins();
		$module->load( $plugin_product );

		$this->assertFalse( (bool) has_filter( 'plugins_api_result', [ $module, 'filter_plugin_api_results' ] ) );

		add_filter( 'themeisle_sdk_plugin_api_filter_registered', '__return_false' );

		$module->load( $plugin_product );
		$this->assertTrue( (bool) has_filter( 'plugins_api_result', [ $module, 'filter_plugin_api_results' ] ) );
	}

	/**
	 * Test that even if a previous filter mutates the result type properties, the plugin API filter still works.
	 */
	public function test_plugins_api_result_filter() {
		wp_set_current_user( self::$admin_id );
		$plugin         = dirname( __FILE__ ) . '/sample_products/sample_pro_plugin/plugin_file.php';
		$plugin_product = new \ThemeisleSDK\Product( $plugin );

		$module = new \ThemeisleSDK\Modules\Featured_Plugins();
		$module->load( $plugin_product );

		$api_result       = plugins_api( 'query_plugins', array() );
		$args             = new stdClass();
		$args->page       = 1;
		$args->per_page   = 36;
		$args->browse     = 'featured';
		$args->wp_version = '6.4';

		$filtered_api_result = apply_filters( 'plugins_api_result', $api_result, 'query_plugins', $args );
		$this->assertEquals( 1, count( $filtered_api_result->plugins ) );

		// Mutate the plugins property to be an object.
		add_filter(
			'plugins_api_result',
			function( $results, $action, $args ) {
				$results->plugins = (object) $results->plugins;
				return $results;
			},
			9,
			3
		);

		// This should also pass if the result type properties are mutated.
		$filtered_api_result = apply_filters( 'plugins_api_result', $api_result, 'query_plugins', $args );
		$this->assertEquals( 1, count( $filtered_api_result->plugins ) );


		// Mutate a plugin from list to be an object.
		add_filter(
			'plugins_api_result',
			function( $results, $action, $args ) {
				$plugin           = $results->plugins[0];
				$plugin['name']   = 'Optimole';
				$plugin['slug']   = 'optimole-wp';
				$plugins          = $results->plugins;
				$plugins[]        = (object) $plugin;
				$results->plugins = $plugins;

				return $results;
			},
			11,
			3
		);

		// This should also pass if the plugin array contains a object within the list.
		$filtered_api_result = apply_filters( 'plugins_api_result', $api_result, 'query_plugins', $args );
		$this->assertEquals( 2, count( $filtered_api_result->plugins ) );
	}

	/**
	 * Test LMS plugin is prepended when searching for LMS-related terms.
	 */
	public function test_lms_plugin_is_prepended_on_lms_search() {
		wp_set_current_user( self::$admin_id );
		$plugin         = dirname( __FILE__ ) . '/sample_products/sample_pro_plugin/plugin_file.php';
		$plugin_product = new \ThemeisleSDK\Product( $plugin );

		$module = $this->getMockBuilder( '\ThemeisleSDK\Modules\Featured_Plugins' )
			->onlyMethods( [ 'get_plugins_filtered_from_author' ] )
			->getMock();

		$lms_plugin = (object) [
			'name'    => 'Masteriyo',
			'slug'    => 'learning-management-system',
			'version' => '1.0.0',
			'author'  => 'masteriyo',
		];

		$module->method( 'get_plugins_filtered_from_author' )
			->willReturn( [ $lms_plugin ] );

		// Including a duplicate LMS plugin to test deduplication.
		$existing_plugin      = (object) [
			'name'    => 'Other Plugin',
			'slug'    => 'other-plugin',
			'version' => '2.0.0',
			'author'  => 'someone',
		];
		$duplicate_lms_plugin = (object) [
			'name'    => 'Masteriyo',
			'slug'    => 'learning-management-system',
			'version' => '1.0.0',
			'author'  => 'masteriyo',
		];

		$plugins = [ $existing_plugin, $duplicate_lms_plugin ];

		$args = (object) [
			'search' => 'best lms plugin',
			'page'   => 1,
		];

		$result = $module->filter_plugin_api_results(
			(object) [
				'plugins' => $plugins,
				'info'    => [ 'results' => 10 ],
			],
			'query_plugins',
			$args
		);

		$this->assertEquals( 'learning-management-system', $result->plugins[0]->slug, 'LMS plugin should be prepended.' );
		$this->assertEquals( 'other-plugin', $result->plugins[1]->slug, 'Other plugin should follow.' );
		// Ensure no duplicate LMS plugin exists.
		$lms_count = 0;
		foreach ( $result->plugins as $plugin ) {
			if ( isset( $plugin->slug ) && $plugin->slug === 'learning-management-system' ) {
				$lms_count++;
			}
		}
		$this->assertEquals( 1, $lms_count, 'There should be only one LMS plugin in the results.' );
	}

	/**
	 * Test LMS plugin is prepended for the expanded LMS search keywords.
	 *
	 * @dataProvider lms_search_keyword_provider
	 *
	 * @param string $search Search query.
	 */
	public function test_lms_plugin_is_prepended_on_expanded_lms_search_keywords( $search ) {
		wp_set_current_user( self::$admin_id );

		$module = $this->getMockBuilder( '\ThemeisleSDK\Modules\Featured_Plugins' )
			->onlyMethods( [ 'get_plugins_filtered_from_author' ] )
			->getMock();

		$lms_plugin = (object) [
			'name'    => 'Masteriyo',
			'slug'    => 'learning-management-system',
			'version' => '1.0.0',
			'author'  => 'masteriyo',
		];

		$module->method( 'get_plugins_filtered_from_author' )
			->willReturn( [ $lms_plugin ] );

		$result = $module->filter_plugin_api_results(
			(object) [
				'plugins' => array(),
				'info'    => [ 'results' => 10 ],
			],
			'query_plugins',
			(object) [
				'search' => $search,
				'page'   => 1,
			]
		);

		$this->assertEquals( 'learning-management-system', $result->plugins[0]->slug, 'LMS plugin should be prepended.' );
	}

	/**
	 * Test LMS plugin is prepended in search even if another LMS plugin is active.
	 */
	public function test_lms_search_prepend_ignores_active_lms_plugins() {
		wp_set_current_user( self::$admin_id );

		$active_plugins = get_option( 'active_plugins', array() );
		update_option( 'active_plugins', array_unique( array_merge( (array) $active_plugins, array( 'tutor/tutor.php' ) ) ) );

		try {
			$module = $this->getMockBuilder( '\ThemeisleSDK\Modules\Featured_Plugins' )
				->onlyMethods( [ 'get_plugins_filtered_from_author' ] )
				->getMock();

			$lms_plugin = (object) [
				'name'    => 'Masteriyo',
				'slug'    => 'learning-management-system',
				'version' => '1.0.0',
				'author'  => 'masteriyo',
			];

			$module->method( 'get_plugins_filtered_from_author' )
				->willReturn( [ $lms_plugin ] );

			$result = $module->filter_plugin_api_results(
				(object) [
					'plugins' => array(),
					'info'    => [ 'results' => 10 ],
				],
				'query_plugins',
				(object) [
					'search' => 'training',
					'page'   => 1,
				]
			);

			$this->assertEquals( 'learning-management-system', $result->plugins[0]->slug, 'LMS plugin should be prepended.' );
		} finally {
			update_option( 'active_plugins', $active_plugins );
		}
	}

	/**
	 * Test LMS plugin is not prepended for partial search keyword matches.
	 */
	public function test_lms_plugin_is_not_prepended_on_partial_search_keyword_matches() {
		wp_set_current_user( self::$admin_id );

		$module = $this->getMockBuilder( '\ThemeisleSDK\Modules\Featured_Plugins' )
			->onlyMethods( [ 'get_plugins_filtered_from_author' ] )
			->getMock();

		$module->expects( $this->never() )
			->method( 'get_plugins_filtered_from_author' );

		$plugin = (object) [
			'name'    => 'Other Plugin',
			'slug'    => 'other-plugin',
			'version' => '1.0.0',
			'author'  => 'someone',
		];

		$result = $module->filter_plugin_api_results(
			(object) [
				'plugins' => array( $plugin ),
				'info'    => [ 'results' => 10 ],
			],
			'query_plugins',
			(object) [
				'search' => 'classic discourse',
				'page'   => 1,
			]
		);

		$this->assertEquals( 'other-plugin', $result->plugins[0]->slug, 'LMS plugin should not be prepended.' );
	}

	/**
	 * Test featured results are prepended without trimming the original results.
	 */
	public function test_featured_results_are_prepended_without_trimming() {
		wp_set_current_user( self::$admin_id );

		$module = $this->get_module_with_mocked_featured_plugins();

		$existing = (object) [
			'name' => 'Existing Plugin',
			'slug' => 'existing-plugin',
		];

		$result = $module->filter_plugin_api_results(
			(object) [
				'plugins' => [ $existing ],
				'info'    => [ 'results' => 1 ],
			],
			'query_plugins',
			(object) [
				'page'       => 1,
				'per_page'   => 36,
				'browse'     => 'featured',
				'wp_version' => '6.4',
			]
		);

		$this->assertCount( 3, $result->plugins, 'No original result should be trimmed.' );
		$this->assertEquals( 'optimole-wp', $result->plugins[0]->slug, 'Featured plugins should be prepended.' );
		$this->assertEquals( 'otter-blocks', $result->plugins[1]->slug, 'Featured plugins should be prepended.' );
		$this->assertEquals( 'existing-plugin', $result->plugins[2]->slug, 'Original results should follow the featured ones.' );
		$this->assertEquals( 3, $result->info['results'], 'Results count should include the injected plugins.' );
	}

	/**
	 * Test featured results are deduplicated when already present in the original list.
	 */
	public function test_featured_results_dedup_existing_entries() {
		wp_set_current_user( self::$admin_id );

		$module = $this->get_module_with_mocked_featured_plugins();

		$existing_optimole = (object) [
			'name' => 'Optimole',
			'slug' => 'optimole-wp',
		];
		$existing_other    = (object) [
			'name' => 'Other Plugin',
			'slug' => 'other-plugin',
		];

		$result = $module->filter_plugin_api_results(
			(object) [
				'plugins' => [ $existing_optimole, $existing_other ],
				'info'    => [ 'results' => 2 ],
			],
			'query_plugins',
			(object) [
				'page'       => 1,
				'per_page'   => 36,
				'browse'     => 'featured',
				'wp_version' => '6.4',
			]
		);

		$this->assertCount( 3, $result->plugins, 'Duplicate featured plugins should be removed from the original list.' );
		$this->assertEquals( 'optimole-wp', $result->plugins[0]->slug, 'Featured plugins should be prepended.' );
		$this->assertEquals( 'otter-blocks', $result->plugins[1]->slug, 'Featured plugins should be prepended.' );
		$this->assertEquals( 'other-plugin', $result->plugins[2]->slug, 'Original results should follow the featured ones.' );
		$this->assertEquals( 3, $result->info['results'], 'Results count should reflect the deduplicated list.' );
	}

	/**
	 * Test featured plugins are not injected on secondary pages.
	 */
	public function test_featured_results_not_injected_on_secondary_pages() {
		wp_set_current_user( self::$admin_id );

		$module = $this->getMockBuilder( '\ThemeisleSDK\Modules\Featured_Plugins' )
			->onlyMethods( [ 'get_plugins_filtered_from_author' ] )
			->getMock();

		$module->expects( $this->never() )
			->method( 'get_plugins_filtered_from_author' );

		$existing = (object) [
			'name' => 'Existing Plugin',
			'slug' => 'existing-plugin',
		];

		$result = $module->filter_plugin_api_results(
			(object) [
				'plugins' => [ $existing ],
				'info'    => [ 'results' => 40 ],
			],
			'query_plugins',
			(object) [
				'page'       => 2,
				'per_page'   => 36,
				'browse'     => 'featured',
				'wp_version' => '6.4',
			]
		);

		$this->assertCount( 1, $result->plugins, 'Secondary pages should not receive injected plugins.' );
		$this->assertEquals( 'existing-plugin', $result->plugins[0]->slug );
		$this->assertEquals( 40, $result->info['results'], 'Results count should not change on secondary pages.' );
	}

	/**
	 * Test the results count is bumped when the LMS plugin is added on search.
	 */
	public function test_lms_search_adjusts_results_count() {
		wp_set_current_user( self::$admin_id );

		$module = $this->getMockBuilder( '\ThemeisleSDK\Modules\Featured_Plugins' )
			->onlyMethods( [ 'get_plugins_filtered_from_author' ] )
			->getMock();

		$lms_plugin = (object) [
			'name' => 'Masteriyo',
			'slug' => 'learning-management-system',
		];

		$module->method( 'get_plugins_filtered_from_author' )
			->willReturn( [ $lms_plugin ] );

		$args = (object) [
			'search' => 'best lms plugin',
			'page'   => 1,
		];

		// The LMS plugin is added on top: count goes up by one.
		$result = $module->filter_plugin_api_results(
			(object) [
				'plugins' => array(),
				'info'    => [ 'results' => 10 ],
			],
			'query_plugins',
			$args
		);
		$this->assertEquals( 11, $result->info['results'], 'Results count should be bumped when the LMS plugin is added.' );

		// The LMS plugin replaces an existing copy: count stays the same.
		$result = $module->filter_plugin_api_results(
			(object) [
				'plugins' => [ clone $lms_plugin ],
				'info'    => [ 'results' => 10 ],
			],
			'query_plugins',
			$args
		);
		$this->assertEquals( 10, $result->info['results'], 'Results count should not change when the LMS plugin replaces a duplicate.' );
	}

	/**
	 * Test that empty author results are cached to avoid repeated remote requests.
	 */
	public function test_empty_author_results_are_cached() {
		wp_set_current_user( self::$admin_id );

		$module = new \ThemeisleSDK\Modules\Featured_Plugins();

		$module->filter_plugin_api_results(
			(object) [
				'plugins' => array(),
				'info'    => [ 'results' => 0 ],
			],
			'query_plugins',
			(object) [
				'page'       => 1,
				'per_page'   => 36,
				'browse'     => 'featured',
				'wp_version' => '6.4',
			]
		);

		// The mocked plugins_api returns no matching slugs, so the filtered lists are empty and should still be cached.
		$this->assertSame( array(), get_transient( 'themeisle_sdk_featured_plugins_Optimole' ), 'Empty Optimole results should be cached.' );
		$this->assertSame( array(), get_transient( 'themeisle_sdk_featured_plugins_Themeisle' ), 'Empty Themeisle results should be cached.' );
	}

	/**
	 * Build a module mock returning one Optimole and one Themeisle featured plugin.
	 *
	 * @return \ThemeisleSDK\Modules\Featured_Plugins
	 */
	private function get_module_with_mocked_featured_plugins() {
		$module = $this->getMockBuilder( '\ThemeisleSDK\Modules\Featured_Plugins' )
			->onlyMethods( [ 'get_plugins_filtered_from_author' ] )
			->getMock();

		$optimole = (object) [
			'name' => 'Optimole',
			'slug' => 'optimole-wp',
		];
		$otter    = (object) [
			'name' => 'Otter Blocks',
			'slug' => 'otter-blocks',
		];

		$module->method( 'get_plugins_filtered_from_author' )
			->willReturnCallback(
				function( $args, $filter_slugs = [], $author = 'Themeisle' ) use ( $optimole, $otter ) {
					$results = [
						'Optimole'  => [ $optimole ],
						'Themeisle' => [ $otter ],
					];

					return isset( $results[ $author ] ) ? $results[ $author ] : [];
				}
			);

		return $module;
	}

	/**
	 * LMS search keyword provider.
	 *
	 * @return array
	 */
	public function lms_search_keyword_provider() {
		return array(
			array( 'best lms plugin' ),
			array( 'learn platform' ),
			array( 'course builder' ),
			array( 'courses builder' ),
			array( 'learning platform' ),
			array( 'academy' ),
			array( 'training' ),
			array( 'student portal' ),
			array( 'students portal' ),
			array( 'quiz maker' ),
		);
	}

	/**
	 * Test Easy MCP plugin is prepended when searching for AI/MCP-related terms.
	 */
	public function test_easy_mcp_plugin_is_prepended_on_ai_search() {
		wp_set_current_user( self::$admin_id );

		$module = $this->getMockBuilder( '\ThemeisleSDK\Modules\Featured_Plugins' )
			->onlyMethods( [ 'get_plugins_filtered_from_author' ] )
			->getMock();

		$easy_mcp_plugin = (object) [
			'name'    => 'Easy MCP AI',
			'slug'    => 'easy-mcp-ai',
			'version' => '1.0.0',
			'author'  => 'easymcpai',
		];

		$module->method( 'get_plugins_filtered_from_author' )
			->willReturn( [ $easy_mcp_plugin ] );

		// Including a duplicate Easy MCP plugin to test deduplication.
		$existing_plugin           = (object) [
			'name'    => 'Other Plugin',
			'slug'    => 'other-plugin',
			'version' => '2.0.0',
			'author'  => 'someone',
		];
		$duplicate_easy_mcp_plugin = (object) [
			'name'    => 'Easy MCP AI',
			'slug'    => 'easy-mcp-ai',
			'version' => '1.0.0',
			'author'  => 'easymcpai',
		];

		$result = $module->filter_plugin_api_results(
			(object) [
				'plugins' => [ $existing_plugin, $duplicate_easy_mcp_plugin ],
				'info'    => [ 'results' => 10 ],
			],
			'query_plugins',
			(object) [
				'search' => 'best mcp plugin',
				'page'   => 1,
			]
		);

		$this->assertEquals( 'easy-mcp-ai', $result->plugins[0]->slug, 'Easy MCP plugin should be prepended.' );
		$this->assertEquals( 'other-plugin', $result->plugins[1]->slug, 'Other plugin should follow.' );
		$easy_mcp_count = 0;
		foreach ( $result->plugins as $plugin ) {
			if ( isset( $plugin->slug ) && $plugin->slug === 'easy-mcp-ai' ) {
				$easy_mcp_count++;
			}
		}
		$this->assertEquals( 1, $easy_mcp_count, 'There should be only one Easy MCP plugin in the results.' );
	}

	/**
	 * Test Easy MCP plugin is prepended for the AI/MCP search keywords.
	 *
	 * @dataProvider ai_search_keyword_provider
	 *
	 * @param string $search Search query.
	 */
	public function test_easy_mcp_plugin_is_prepended_on_ai_search_keywords( $search ) {
		$this->assertTrue( \ThemeisleSDK\Modules\Featured_Plugins::matches_ai_search_keywords( $search ) );
		wp_set_current_user( self::$admin_id );

		$module = $this->getMockBuilder( '\ThemeisleSDK\Modules\Featured_Plugins' )
			->onlyMethods( [ 'get_plugins_filtered_from_author' ] )
			->getMock();

		$easy_mcp_plugin = (object) [
			'name'    => 'Easy MCP AI',
			'slug'    => 'easy-mcp-ai',
			'version' => '1.0.0',
			'author'  => 'easymcpai',
		];

		$module->method( 'get_plugins_filtered_from_author' )
			->willReturn( [ $easy_mcp_plugin ] );

		$result = $module->filter_plugin_api_results(
			(object) [
				'plugins' => array(),
				'info'    => [ 'results' => 10 ],
			],
			'query_plugins',
			(object) [
				'search' => $search,
				'page'   => 1,
			]
		);

		$this->assertEquals( 'easy-mcp-ai', $result->plugins[0]->slug, 'Easy MCP plugin should be prepended.' );
	}

	/**
	 * Test Easy MCP plugin is not prepended for unrelated or partial keyword matches.
	 *
	 * @dataProvider non_ai_search_keyword_provider
	 *
	 * @param string $search Search query.
	 */
	public function test_easy_mcp_plugin_is_not_prepended_without_search_keyword_matches( $search ) {
		$this->assertFalse( \ThemeisleSDK\Modules\Featured_Plugins::matches_ai_search_keywords( $search ) );
		wp_set_current_user( self::$admin_id );

		$module = $this->getMockBuilder( '\ThemeisleSDK\Modules\Featured_Plugins' )
			->onlyMethods( [ 'get_plugins_filtered_from_author' ] )
			->getMock();

		$module->expects( $this->never() )
			->method( 'get_plugins_filtered_from_author' );

		$plugin = (object) [
			'name'    => 'Other Plugin',
			'slug'    => 'other-plugin',
			'version' => '1.0.0',
			'author'  => 'someone',
		];

		$result = $module->filter_plugin_api_results(
			(object) [
				'plugins' => array( $plugin ),
				'info'    => [ 'results' => 10 ],
			],
			'query_plugins',
			(object) [
				'search' => $search,
				'page'   => 1,
			]
		);

		$this->assertEquals( 'other-plugin', $result->plugins[0]->slug, 'Easy MCP plugin should not be prepended.' );
	}

	/**
	 * Build a module whose author queries return the given per-author results.
	 *
	 * @param array $results Author => plugins list map (missing authors return none).
	 *
	 * @return \ThemeisleSDK\Modules\Featured_Plugins
	 */
	private function get_module_with_mocked_author_results( $results ) {
		$module = $this->getMockBuilder( '\ThemeisleSDK\Modules\Featured_Plugins' )
			->onlyMethods( [ 'get_plugins_filtered_from_author' ] )
			->getMock();

		$module->method( 'get_plugins_filtered_from_author' )
			->willReturnCallback(
				function( $args, $filter_slugs = [], $author = 'Themeisle' ) use ( $results ) {
					return isset( $results[ $author ] ) ? $results[ $author ] : [];
				}
			);

		return $module;
	}

	/**
	 * Test Easy MCP is appended after the existing injected plugins on WordPress 6.9+.
	 */
	public function test_easy_mcp_appended_in_featured_tab_on_wp_69() {
		global $wp_version;
		$original_wp_version = $wp_version;
		$wp_version          = '6.9';

		try {
			wp_set_current_user( self::$admin_id );

			$module = $this->get_module_with_mocked_author_results(
				[
					'Optimole'  => [
						(object) [
							'name' => 'Optimole',
							'slug' => 'optimole-wp',
						],
					],
					'Themeisle' => [
						(object) [
							'name' => 'Otter Blocks',
							'slug' => 'otter-blocks',
						],
					],
					'easymcpai' => [
						(object) [
							'name' => 'Easy MCP AI',
							'slug' => 'easy-mcp-ai',
						],
					],
				]
			);

			$result = $module->filter_plugin_api_results(
				(object) [
					'plugins' => [
						(object) [
							'name' => 'Existing Plugin',
							'slug' => 'existing-plugin',
						],
					],
					'info'    => [ 'results' => 1 ],
				],
				'query_plugins',
				(object) [
					'page'       => 1,
					'per_page'   => 36,
					'browse'     => 'featured',
					'wp_version' => '6.9',
				]
			);

			$this->assertCount( 4, $result->plugins );
			$this->assertEquals( 'optimole-wp', $result->plugins[0]->slug );
			$this->assertEquals( 'otter-blocks', $result->plugins[1]->slug );
			$this->assertEquals( 'easy-mcp-ai', $result->plugins[2]->slug, 'Easy MCP should be appended after the existing injected plugins.' );
			$this->assertEquals( 'existing-plugin', $result->plugins[3]->slug );
			$this->assertEquals( 4, $result->info['results'] );
		} finally {
			$wp_version = $original_wp_version;
		}
	}

	/**
	 * Test Easy MCP is not injected in the Featured tab below WordPress 6.9.
	 */
	public function test_easy_mcp_not_in_featured_tab_below_wp_69() {
		global $wp_version;
		$original_wp_version = $wp_version;
		$wp_version          = '6.8';

		try {
			wp_set_current_user( self::$admin_id );

			$module = $this->get_module_with_mocked_author_results(
				[
					'Optimole'  => [
						(object) [
							'name' => 'Optimole',
							'slug' => 'optimole-wp',
						],
					],
					'Themeisle' => [
						(object) [
							'name' => 'Otter Blocks',
							'slug' => 'otter-blocks',
						],
					],
					'easymcpai' => [
						(object) [
							'name' => 'Easy MCP AI',
							'slug' => 'easy-mcp-ai',
						],
					],
				]
			);

			$result = $module->filter_plugin_api_results(
				(object) [
					'plugins' => [],
					'info'    => [ 'results' => 0 ],
				],
				'query_plugins',
				(object) [
					'page'       => 1,
					'per_page'   => 36,
					'browse'     => 'featured',
					'wp_version' => '6.8',
				]
			);

			foreach ( $result->plugins as $plugin ) {
				$this->assertNotEquals( 'easy-mcp-ai', $plugin->slug, 'Easy MCP should not be injected below WordPress 6.9.' );
			}
			$this->assertCount( 2, $result->plugins );
		} finally {
			$wp_version = $original_wp_version;
		}
	}

	/**
	 * Test the injected featured list is deduplicated by slug across author groups.
	 */
	public function test_featured_injected_authors_deduped_by_slug() {
		global $wp_version;
		$original_wp_version = $wp_version;
		$wp_version          = '6.9';

		try {
			wp_set_current_user( self::$admin_id );

			$otter  = (object) [
				'name' => 'Otter Blocks',
				'slug' => 'otter-blocks',
			];
			$module = $this->get_module_with_mocked_author_results(
				[
					'Optimole'  => [
						(object) [
							'name' => 'Optimole',
							'slug' => 'optimole-wp',
						],
					],
					'Themeisle' => [ $otter ],
					'easymcpai' => [ $otter ],
				]
			);

			$result = $module->filter_plugin_api_results(
				(object) [
					'plugins' => [],
					'info'    => [ 'results' => 0 ],
				],
				'query_plugins',
				(object) [
					'page'       => 1,
					'per_page'   => 36,
					'browse'     => 'featured',
					'wp_version' => '6.9',
				]
			);

			$this->assertCount( 2, $result->plugins, 'Duplicate slugs across author groups should be removed.' );
			$this->assertEquals( 'optimole-wp', $result->plugins[0]->slug );
			$this->assertEquals( 'otter-blocks', $result->plugins[1]->slug );
		} finally {
			$wp_version = $original_wp_version;
		}
	}

	/**
	 * Test the LMS prepend keeps precedence on searches matching both keyword sets.
	 */
	public function test_lms_prepend_wins_on_dual_keyword_search() {
		wp_set_current_user( self::$admin_id );

		$module = $this->getMockBuilder( '\ThemeisleSDK\Modules\Featured_Plugins' )
			->onlyMethods( [ 'get_plugins_filtered_from_author' ] )
			->getMock();

		$lms_plugin = (object) [
			'name'    => 'Masteriyo',
			'slug'    => 'learning-management-system',
			'version' => '1.0.0',
			'author'  => 'masteriyo',
		];

		$module->expects( $this->once() )
			->method( 'get_plugins_filtered_from_author' )
			->willReturn( [ $lms_plugin ] );

		$result = $module->filter_plugin_api_results(
			(object) [
				'plugins' => array(),
				'info'    => [ 'results' => 10 ],
			],
			'query_plugins',
			(object) [
				'search' => 'ai course',
				'page'   => 1,
			]
		);

		$this->assertEquals( 'learning-management-system', $result->plugins[0]->slug, 'LMS plugin should keep the top slot.' );
		foreach ( $result->plugins as $plugin ) {
			$this->assertNotEquals( 'easy-mcp-ai', isset( $plugin->slug ) ? $plugin->slug : '', 'Easy MCP plugin should not stack on a dual-keyword search.' );
		}
	}

	/**
	 * AI/MCP search keyword provider.
	 *
	 * @return array
	 */
	public function ai_search_keyword_provider() {
		return array(
			array( 'mcp server' ),
			array( 'model context protocol' ),
			array( 'ai assistant' ),
			array( 'ai' ),
			array( 'WordPress AI' ),
			array( 'WordPress AI assistant' ),
			array( 'connect claude' ),
			array( 'chatgpt for wordpress' ),
			array( 'llm tools' ),
			array( 'Connect CLAUDE' ),
			array( 'CHATGPT for WordPress' ),
			array( 'LLM tools' ),
			array( 'AI-powered' ),
			array( 'WordPress/MCP' ),
			array( '(Claude)' ),
			array( 'connect Model Context Protocol tools' ),
			array( 'claudecode' ),
			array( 'chatgpt4' ),
			array( 'ConnectClaudeCode' ),
			array( 'myCHATGPT4plugin' ),
			array( 'llms' ),
			array( 'LLMs.txt' ),
			array( 'wp-mcp' ),
		);
	}

	/**
	 * Unrelated searches where AI, MCP or LLM must not match inside another word.
	 *
	 * @return array
	 */
	public function non_ai_search_keyword_provider() {
		return array(
			array( 'contact forms' ),
			array( 'email' ),
			array( 'mailchimp' ),
			array( 'maintenance mode' ),
			array( 'paid memberships' ),
			array( 'domain mapping' ),
			array( 'email campaign maintenance' ),
			array( 'order fulfillment' ),
			array( 'installment payments' ),
			array( 'myMCPserver' ),
			array( 'myLLMtools' ),
		);
	}

}
