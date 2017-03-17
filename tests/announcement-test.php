<?php
/**
 * Unit tests for the Announcements module.
 *
 * @package ThemeIsleSDK
 */

use ThemeisleSDK\Loader;
use ThemeisleSDK\Modules\Announcements;
use ThemeisleSDK\Product;

class Announcement_Test extends WP_UnitTestCase {
	protected static $admin_id;
	/** @var Announcements */
	private $announcements;

	public static function wpSetUpBeforeClass( \WP_UnitTest_Factory $factory ) {
		self::$admin_id = $factory->user->create( [ 'role' => 'administrator' ] );
	}

	public static function wpTearDownAfterClass() {
		self::delete_user( self::$admin_id );
	}

	public function set_up() {
		parent::set_up();
		wp_set_current_user( self::$admin_id );
		$this->announcements = new Announcements();
	}

	public function tear_down() {
		remove_all_filters( 'themeisle_sdk_current_date' );
		remove_all_filters( 'themeisle_sdk_current_time' );
		remove_all_filters( 'themeisle_sdk_blackfriday_data' );

		$this->restore_loader_products_if_stashed();

		$this->set_private_static_property( Announcements::class, 'notice_loaded', false );
		$this->set_private_static_property( Announcements::class, 'meta_link_loaded', false );

		parent::tear_down();
	}

	/** @var array<string, Product>|null */
	private $loader_products_backup = null;

	/**
	 * @return Product
	 */
	private function get_sample_plugin_product() {
		$file = dirname( __FILE__ ) . '/sample_products/sample_plugin/plugin_file.php';
		return new Product( $file );
	}

	/**
	 * @return Product
	 */
	private function get_sample_theme_product() {
		$file = dirname( __FILE__ ) . '/sample_products/sample_theme/style.css';
		return new Product( $file );
	}

	private function bind_announcements_product( Product $product ) {
		$this->set_private_property( $this->announcements, 'product', $product );
	}

	private function force_product_install_timestamp( Product $product, $timestamp ) {
		$this->set_private_property( $product, 'install', $timestamp );
	}

	/**
	 *
	 * Stashes the current products in the loader and sets the new products.
	 *
	 * @param array<string, Product> $products Products to set in the loader.
	 */
	private function stash_and_set_loader_products( array $products ) {
		$ref  = new \ReflectionClass( Loader::class );
		$prop = $ref->getProperty( 'products' );
		$prop->setAccessible( true );
		if ( null === $this->loader_products_backup ) {
			$this->loader_products_backup = $prop->getValue( null );
		}
		$prop->setValue( null, $products );
	}

	private function restore_loader_products_if_stashed() {
		if ( null === $this->loader_products_backup ) {
			return;
		}
		$ref  = new \ReflectionClass( Loader::class );
		$prop = $ref->getProperty( 'products' );
		$prop->setAccessible( true );
		$prop->setValue( null, $this->loader_products_backup );
		$this->loader_products_backup = null;
	}

	/**
	 * Set a private property on an object using reflection.
	 *
	 * @param object $object The object.
	 * @param string $property_name The property name.
	 * @param mixed  $value The value to set.
	 */
	private function set_private_property( $object, $property_name, $value ) {
		$ref  = new \ReflectionClass( $object );
		$prop = $ref->getProperty( $property_name );
		$prop->setAccessible( true );
		$prop->setValue( $object, $value );
	}

	/**
	 * Set a private static property using reflection.
	 *
	 * @param string $class_name The class name.
	 * @param string $property_name The property name.
	 * @param mixed  $value The value to set.
	 */
	private function set_private_static_property( $class_name, $property_name, $value ) {
		$ref  = new \ReflectionClass( $class_name );
		$prop = $ref->getProperty( $property_name );
		$prop->setAccessible( true );
		$prop->setValue( $value );
	}

	/**
	 * @param string $date_string Date in format 'YYYY-MM-DD'.
	 */
	private function set_current_date( $date_string ) {
		add_filter(
			'themeisle_sdk_current_date',
			static function () use ( $date_string ) {
				return new DateTime( $date_string );
			}
		);
	}

	/**
	 * @param array $data Black Friday configuration data.
	 */
	private function set_blackfriday_data( array $data ) {
		add_filter(
			'themeisle_sdk_blackfriday_data',
			static function () use ( $data ) {
				return $data;
			}
		);
	}

	public function test_can_show_notice_without_dismiss() {
		$today = new DateTime( '2025-03-10' );
		delete_user_meta( self::$admin_id, 'themeisle_sdk_dismissed_notice_black_friday' );
		$this->assertTrue(
			$this->announcements->can_show_notice( $today, self::$admin_id ),
			'Notice should show when user has not dismissed.'
		);
	}

	public function test_can_show_notice_when_dismissed_same_year() {
		$today = new DateTime( '2025-03-10' );
		update_user_meta(
			self::$admin_id,
			'themeisle_sdk_dismissed_notice_black_friday',
			strtotime( '2025-01-01' )
		);
		$this->assertFalse(
			$this->announcements->can_show_notice( $today, self::$admin_id ),
			'Notice should not show when dismissed this year.'
		);
	}

	public function test_get_black_friday_dates_and_duration() {
		$start = $this->announcements->get_start_date( new DateTime( '2025-01-01' ) );
		$this->assertEquals( '2025-11-24', $start->format( 'Y-m-d' ) );
		
		$end = $this->announcements->get_end_date( $start );
		$this->assertEquals( '2025-12-01 23:59:59', $end->format( 'Y-m-d H:i:s' ) );

		// check sale window boundaries
		$this->assertFalse( $this->announcements->is_black_friday_sale( new DateTime( '2025-11-23' ) ) );
		$this->assertTrue( $this->announcements->is_black_friday_sale( new DateTime( '2025-11-24' ) ) );
		$this->assertTrue( $this->announcements->is_black_friday_sale( new DateTime( '2025-11-28' ) ) );
		$this->assertTrue( $this->announcements->is_black_friday_sale( new DateTime( '2025-12-01' ) ) );
		$this->assertFalse( $this->announcements->is_black_friday_sale( new DateTime( '2025-12-02' ) ) );
	}

	public function test_get_remaining_time_for_event() {
		$base = new DateTime( '2025-12-01 10:00:00' );
		add_filter(
			'themeisle_sdk_current_date',
			function() use ( $base ) {
				return $base;
			}
		);

		$end  = new DateTime( '2025-12-02 00:00:00' );
		$diff = $this->announcements->get_remaining_time_for_event( $end );
		$this->assertNotEmpty( $diff );
		$this->assertStringContainsString( 'hour', $diff );
	}

	public function test_load_announcements_registers_hooks_during_sale() {
		add_filter(
			'themeisle_sdk_current_date',
			function() {
				return new DateTime( '2025-11-25' );
			}
		);

		$product = $this->get_sample_plugin_product();
		// Install time must be on the same timeline as themeisle_sdk_current_date (not real time()),
		// otherwise ( current_timestamp - install ) can be negative when "now" is simulated in the past.
		$this->force_product_install_timestamp(
			$product,
			( new DateTime( '2025-11-01' ) )->getTimestamp()
		);
		$this->bind_announcements_product( $product );

		$this->announcements->load_announcements();
		$this->assertTrue(
			has_action( 'admin_notices', [ $this->announcements, 'black_friday_notice_render' ] ) > 0
		);
		$this->assertTrue(
			has_filter( 'plugin_row_meta', [ $this->announcements, 'add_plugin_meta_links' ] ) > 0
		);
	}

	public function test_load_registers_ajax_dismiss_hook() {
		$product = $this->get_sample_plugin_product();

		$this->announcements->load( $product );

		$this->assertTrue(
			has_action( 'wp_ajax_themeisle_sdk_dismiss_black_friday_notice', [ $this->announcements, 'disable_notification_ajax' ] ) > 0
		);
	}

	public function test_load_announcements_skips_outside_sale() {
		add_filter(
			'themeisle_sdk_current_date',
			function() {
				return new DateTime( '2025-10-15' );
			}
		);

		$this->announcements->load_announcements();
		$this->assertFalse(
			has_action( 'admin_notices', [ $this->announcements, 'black_friday_notice_render' ] )
		);
	}

	public function test_add_plugin_meta_links_returns_unchanged_for_other_plugin_file() {
		$product = $this->get_sample_plugin_product();
		$this->bind_announcements_product( $product );

		$links = [ '<a href="#">Documentation</a>' ];
		$out   = $this->announcements->add_plugin_meta_links( $links, 'unrelated-plugin/unrelated-plugin.php' );

		$this->assertSame( $links, $out );
	}

	public function test_add_plugin_meta_links_returns_unchanged_when_configs_empty() {
		$this->set_blackfriday_data( [] );

		$product = $this->get_sample_plugin_product();
		$this->bind_announcements_product( $product );

		$basename = plugin_basename( $product->get_basefile() );
		$links    = [ '<a href="#">Documentation</a>' ];
		$out      = $this->announcements->add_plugin_meta_links( $links, $basename );

		$this->assertSame( $links, $out );
	}

	public function test_add_plugin_meta_links_appends_link_when_slug_config_has_message_and_url() {
		$this->set_current_date( '2025-11-25' );
		$this->set_blackfriday_data(
			[
				'sample_plugin' => [
					'plugin_meta_message' => 'BFCM meta link',
					'sale_url'            => 'https://example.com/sale',
				],
			]
		);

		$product = $this->get_sample_plugin_product();
		$this->bind_announcements_product( $product );

		$basename = plugin_basename( $product->get_basefile() );
		$links    = [ '<a href="#">Documentation</a>' ];
		$out      = $this->announcements->add_plugin_meta_links( $links, $basename );

		$this->assertCount( 2, $out );
		$this->assertSame( $links[0], $out[0] );
		$this->assertStringContainsString( 'themeisle-sale-plugin-meta-link', $out[1] );
		$this->assertStringContainsString( 'BFCM meta link', $out[1] );
		$this->assertStringContainsString( esc_url( 'https://example.com/sale' ), $out[1] );
	}

	public function test_add_plugin_meta_links_only_loads_once_per_request() {
		$this->set_current_date( '2025-11-25' );
		$this->set_blackfriday_data(
			[
				'sample_plugin' => [
					'plugin_meta_message' => 'Plugin sale',
					'sale_url'            => 'https://example.com/plugin-sale',
				],
				'sample_theme'  => [
					'plugin_meta_message' => 'Theme sale',
					'sale_url'            => 'https://example.com/theme-sale',
				],
			]
		);

		$plugin_product = $this->get_sample_plugin_product();
		$theme_product  = $this->get_sample_theme_product();
		$this->stash_and_set_loader_products(
			[
				'sample_plugin' => $plugin_product,
				'sample_theme'  => $theme_product,
			]
		);

		$this->bind_announcements_product( $plugin_product );
		$plugin_basename = plugin_basename( $plugin_product->get_basefile() );
		$plugin_links    = [ '<a href="#">Documentation</a>' ];
		$plugin_out      = $this->announcements->add_plugin_meta_links( $plugin_links, $plugin_basename );

		$this->bind_announcements_product( $theme_product );
		$theme_basename = plugin_basename( $theme_product->get_basefile() );
		$theme_links    = [ '<a href="#">Documentation</a>' ];
		$theme_out      = $this->announcements->add_plugin_meta_links( $theme_links, $theme_basename );

		$this->assertCount( 2, $plugin_out );
		$this->assertStringContainsString( 'Plugin sale', $plugin_out[1] );
		$this->assertSame( $theme_links, $theme_out );
	}

	public function test_add_plugin_meta_links_skips_when_plugin_meta_targets_exclude_current_slug() {
		$this->set_current_date( '2025-11-25' );
		$this->set_blackfriday_data(
			[
				'sample_plugin' => [
					'plugin_meta_message' => 'Wrong product',
					'sale_url'            => 'https://example.com/sale',
					'plugin_meta_targets' => [ 'some_other_slug' ],
				],
			]
		);

		$product = $this->get_sample_plugin_product();
		$this->bind_announcements_product( $product );

		$basename = plugin_basename( $product->get_basefile() );
		$links    = [ '<a href="#">Documentation</a>' ];
		$out      = $this->announcements->add_plugin_meta_links( $links, $basename );

		$this->assertSame( $links, $out );
	}

	public function test_add_plugin_meta_links_uses_config_from_other_product_when_targeted() {
		$this->set_current_date( '2025-11-25' );
		$this->set_blackfriday_data(
			[
				'sample_theme' => [
					'plugin_meta_message' => 'Cross-product meta',
					'sale_url'            => 'https://example.com/cross/',
					'plugin_meta_targets' => [ 'sample_plugin' ],
				],
			]
		);

		$this->stash_and_set_loader_products(
			[
				'sample_plugin' => $this->get_sample_plugin_product(),
				'sample_theme'  => $this->get_sample_theme_product(),
			]
		);

		$product = $this->get_sample_plugin_product();
		$this->bind_announcements_product( $product );

		$basename = plugin_basename( $product->get_basefile() );
		$links    = [];
		$out      = $this->announcements->add_plugin_meta_links( $links, $basename );

		$this->assertCount( 1, $out );
		$this->assertStringContainsString( 'Cross-product meta', $out[0] );
		$this->assertStringContainsString( esc_url( 'https://example.com/cross/' ), $out[0] );
	}

	public function test_add_plugin_meta_links_falls_back_when_own_config_missing_message() {
		$this->set_current_date( '2025-11-25' );
		$this->set_blackfriday_data(
			[
				'sample_plugin' => [
					// Missing plugin_meta_message
					'sale_url' => 'https://example.com/own/',
				],
				'sample_theme'  => [
					'plugin_meta_message' => 'Fallback meta',
					'sale_url'            => 'https://example.com/fallback/',
					'plugin_meta_targets' => [ 'sample_plugin' ],
				],
			]
		);

		$this->stash_and_set_loader_products(
			[
				'sample_plugin' => $this->get_sample_plugin_product(),
				'sample_theme'  => $this->get_sample_theme_product(),
			]
		);

		$product = $this->get_sample_plugin_product();
		$this->bind_announcements_product( $product );

		$basename = plugin_basename( $product->get_basefile() );
		$links    = [];
		$out      = $this->announcements->add_plugin_meta_links( $links, $basename );

		$this->assertCount( 1, $out );
		$this->assertStringContainsString( 'Fallback meta', $out[0] );
		$this->assertStringContainsString( esc_url( 'https://example.com/fallback/' ), $out[0] );
	}

	public function test_add_plugin_meta_links_falls_back_when_own_config_missing_url() {
		$this->set_current_date( '2025-11-25' );
		$this->set_blackfriday_data(
			[
				'sample_plugin' => [
					'plugin_meta_message' => 'Own message',
					// Missing sale_url
				],
				'sample_theme'  => [
					'plugin_meta_message' => 'Fallback meta',
					'sale_url'            => 'https://example.com/fallback/',
					'plugin_meta_targets' => [ 'sample_plugin' ],
				],
			]
		);

		$this->stash_and_set_loader_products(
			[
				'sample_plugin' => $this->get_sample_plugin_product(),
				'sample_theme'  => $this->get_sample_theme_product(),
			]
		);

		$product = $this->get_sample_plugin_product();
		$this->bind_announcements_product( $product );

		$basename = plugin_basename( $product->get_basefile() );
		$links    = [];
		$out      = $this->announcements->add_plugin_meta_links( $links, $basename );

		$this->assertCount( 1, $out );
		$this->assertStringContainsString( 'Fallback meta', $out[0] );
		$this->assertStringContainsString( esc_url( 'https://example.com/fallback/' ), $out[0] );
	}

	public function test_add_plugin_meta_links_skips_fallback_when_cross_product_targets_dont_match() {
		$this->set_current_date( '2025-11-25' );
		$this->set_blackfriday_data(
			[
				'sample_plugin' => [
					// Missing both fields to trigger fallback search
				],
				'sample_theme'  => [
					'plugin_meta_message' => 'Fallback meta',
					'sale_url'            => 'https://example.com/fallback/',
					'plugin_meta_targets' => [ 'some_other_slug' ], // Doesn't match sample_plugin
				],
			]
		);

		$this->stash_and_set_loader_products(
			[
				'sample_plugin' => $this->get_sample_plugin_product(),
				'sample_theme'  => $this->get_sample_theme_product(),
			]
		);

		$product = $this->get_sample_plugin_product();
		$this->bind_announcements_product( $product );

		$basename = plugin_basename( $product->get_basefile() );
		$links    = [ '<a href="#">Documentation</a>' ];
		$out      = $this->announcements->add_plugin_meta_links( $links, $basename );

		$this->assertSame( $links, $out );
	}

	public function test_override_about_us_metadata_returns_unchanged_outside_black_friday() {
		$this->set_current_date( '2025-10-15' );

		$about_data = [
			'has_upgrade_menu' => true,
			'upgrade_link'     => 'https://example.com/regular',
			'upgrade_text'     => 'Get Pro',
		];

		$product = $this->get_sample_plugin_product();
		$this->bind_announcements_product( $product );

		$result = $this->announcements->override_about_us_metadata( $about_data );

		$this->assertSame( $about_data, $result );
		$this->assertSame( 'https://example.com/regular', $result['upgrade_link'] );
		$this->assertSame( 'Get Pro', $result['upgrade_text'] );
	}

	public function test_override_about_us_metadata_returns_empty_about_data() {
		$this->set_current_date( '2025-11-25' );

		$product = $this->get_sample_plugin_product();
		$this->bind_announcements_product( $product );

		$result = $this->announcements->override_about_us_metadata( [] );

		$this->assertSame( [], $result );
	}

	public function test_override_about_us_metadata_returns_non_array_about_data() {
		$this->set_current_date( '2025-11-25' );

		$product = $this->get_sample_plugin_product();
		$this->bind_announcements_product( $product );

		$result = $this->announcements->override_about_us_metadata( null );

		$this->assertNull( $result );
	}

	public function test_override_about_us_metadata_skips_when_no_upgrade_menu() {
		$this->set_current_date( '2025-11-25' );

		$about_data = [
			'has_upgrade_menu' => false,
			'upgrade_link'     => 'https://example.com/regular',
			'upgrade_text'     => 'Get Pro',
		];

		$product = $this->get_sample_plugin_product();
		$this->bind_announcements_product( $product );

		$result = $this->announcements->override_about_us_metadata( $about_data );

		$this->assertSame( $about_data, $result );
	}

	public function test_override_about_us_metadata_skips_when_upgrade_menu_not_boolean_true() {
		$this->set_current_date( '2025-11-25' );

		$about_data = [
			'has_upgrade_menu' => 1, // truthy but not bool true
			'upgrade_link'     => 'https://example.com/regular',
			'upgrade_text'     => 'Get Pro',
		];

		$product = $this->get_sample_plugin_product();
		$this->bind_announcements_product( $product );

		$result = $this->announcements->override_about_us_metadata( $about_data );

		$this->assertSame( $about_data, $result );
	}

	public function test_override_about_us_metadata_skips_when_no_product_config() {
		$this->set_current_date( '2025-11-25' );
		$this->set_blackfriday_data(
			[
				'other_product' => [
					'upgrade_menu_text' => 'Black Friday Sale',
					'sale_url'          => 'https://example.com/bfcm',
				],
			]
		);

		$about_data = [
			'has_upgrade_menu' => true,
			'upgrade_link'     => 'https://example.com/regular',
			'upgrade_text'     => 'Get Pro',
		];

		$product = $this->get_sample_plugin_product();
		$this->bind_announcements_product( $product );

		$result = $this->announcements->override_about_us_metadata( $about_data );

		$this->assertSame( $about_data, $result );
	}

	public function test_override_about_us_metadata_skips_when_config_empty() {
		$this->set_current_date( '2025-11-25' );
		$this->set_blackfriday_data(
			[
				'sample_plugin' => [],
			]
		);

		$about_data = [
			'has_upgrade_menu' => true,
			'upgrade_link'     => 'https://example.com/regular',
			'upgrade_text'     => 'Get Pro',
		];

		$product = $this->get_sample_plugin_product();
		$this->bind_announcements_product( $product );

		$result = $this->announcements->override_about_us_metadata( $about_data );

		$this->assertSame( $about_data, $result );
	}

	public function test_override_about_us_metadata_skips_when_config_not_array() {
		$this->set_current_date( '2025-11-25' );
		$this->set_blackfriday_data(
			[
				'sample_plugin' => 'not-array',
			]
		);

		$about_data = [
			'has_upgrade_menu' => true,
			'upgrade_link'     => 'https://example.com/regular',
			'upgrade_text'     => 'Get Pro',
		];

		$product = $this->get_sample_plugin_product();
		$this->bind_announcements_product( $product );

		$result = $this->announcements->override_about_us_metadata( $about_data );

		$this->assertSame( $about_data, $result );
	}

	public function test_override_about_us_metadata_skips_when_missing_upgrade_menu_text() {
		$this->set_current_date( '2025-11-25' );
		$this->set_blackfriday_data(
			[
				'sample_plugin' => [
					// Missing upgrade_menu_text
					'sale_url' => 'https://example.com/bfcm',
				],
			]
		);

		$about_data = [
			'has_upgrade_menu' => true,
			'upgrade_link'     => 'https://example.com/regular',
			'upgrade_text'     => 'Get Pro',
		];

		$product = $this->get_sample_plugin_product();
		$this->bind_announcements_product( $product );

		$result = $this->announcements->override_about_us_metadata( $about_data );

		$this->assertSame( $about_data, $result );
	}

	public function test_override_about_us_metadata_skips_when_missing_sale_url() {
		$this->set_current_date( '2025-11-25' );
		$this->set_blackfriday_data(
			[
				'sample_plugin' => [
					'upgrade_menu_text' => 'Black Friday Sale',
					// Missing sale_url
				],
			]
		);

		$about_data = [
			'has_upgrade_menu' => true,
			'upgrade_link'     => 'https://example.com/regular',
			'upgrade_text'     => 'Get Pro',
		];

		$product = $this->get_sample_plugin_product();
		$this->bind_announcements_product( $product );

		$result = $this->announcements->override_about_us_metadata( $about_data );

		$this->assertSame( $about_data, $result );
	}

	public function test_override_about_us_metadata_updates_upgrade_text_and_link_during_sale() {
		$this->set_current_date( '2025-11-25' );
		$this->set_blackfriday_data(
			[
				'sample_plugin' => [
					'upgrade_menu_text' => 'Black Friday Sale - 50% Off',
					'sale_url'          => 'https://example.com/bfcm-sale',
				],
			]
		);

		$about_data = [
			'has_upgrade_menu' => true,
			'upgrade_link'     => 'https://example.com/regular',
			'upgrade_text'     => 'Get Pro',
		];

		$product = $this->get_sample_plugin_product();
		$this->bind_announcements_product( $product );

		$result = $this->announcements->override_about_us_metadata( $about_data );

		$this->assertNotSame( $about_data, $result );
		$this->assertSame( 'Black Friday Sale - 50% Off', $result['upgrade_text'] );
		$this->assertSame( 'https://example.com/bfcm-sale', $result['upgrade_link'] );
	}

	public function test_override_about_us_metadata_preserves_other_fields() {
		$this->set_current_date( '2025-11-25' );
		$this->set_blackfriday_data(
			[
				'sample_plugin' => [
					'upgrade_menu_text' => 'Black Friday Sale',
					'sale_url'          => 'https://example.com/bfcm',
				],
			]
		);

		$about_data = [
			'location'         => 'themes.php',
			'logo'             => 'https://example.com/logo.png',
			'has_upgrade_menu' => true,
			'upgrade_link'     => 'https://example.com/regular',
			'upgrade_text'     => 'Get Pro',
			'custom_field'     => 'custom_value',
		];

		$product = $this->get_sample_plugin_product();
		$this->bind_announcements_product( $product );

		$result = $this->announcements->override_about_us_metadata( $about_data );

		$this->assertSame( 'themes.php', $result['location'] );
		$this->assertSame( 'https://example.com/logo.png', $result['logo'] );
		$this->assertTrue( $result['has_upgrade_menu'] );
		$this->assertSame( 'custom_value', $result['custom_field'] );
		$this->assertSame( 'Black Friday Sale', $result['upgrade_text'] );
		$this->assertSame( 'https://example.com/bfcm', $result['upgrade_link'] );
	}
}
