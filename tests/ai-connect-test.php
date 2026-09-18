<?php
/**
 * AI Connect module tests.
 *
 * @package ThemeIsleSDK
 */

use ThemeisleSDK\Modules\Ai_Connect;
use ThemeisleSDK\Product;

/**
 * Test the "Connect your AI agent" module.
 */
class Ai_Connect_Test extends WP_UnitTestCase {

	const META = array(
		'notice_cases' => array( 'optimize new uploads', 'purge cached images', 'offload originals to the cloud' ),
		'prompts'      => array( 'Show me my delivery settings.', 'Purge the images in the "Spring campaign" folder.' ),
		'abilities'    => array( 'sample/get-settings', 'sample/purge-cache' ),
	);

	/**
	 * An administrator who may install and activate plugins.
	 *
	 * @var int
	 */
	private $admin;

	public function set_up() {
		parent::set_up();
		Ai_Connect::reset();
		$this->admin = self::factory()->user->create( array( 'role' => 'administrator' ) );
		if ( is_multisite() ) {
			grant_super_admin( $this->admin );
		}
		wp_set_current_user( $this->admin );
		set_current_screen( 'plugins' );
	}

	public function tear_down() {
		Ai_Connect::reset();
		remove_all_filters( 'sample_plugin_ai_connect_metadata' );
		remove_all_filters( 'sample_theme_ai_connect_metadata' );
		remove_all_filters( 'plugin_row_meta' );
		remove_all_actions( 'in_admin_header' );
		remove_all_actions( 'admin_notices' );
		remove_all_actions( 'admin_footer' );
		update_option( 'active_plugins', array() );
		delete_option( Ai_Connect::ABILITIES_OPTION );
		delete_option( 'sample_plugin_install' );
		delete_option( 'sample_theme_install' );
		set_current_screen( 'front' );
		parent::tear_down();
	}

	/** Product reads its install time when it is constructed, so the age is set first. */
	private function plugin( $installed_ago = WEEK_IN_SECONDS ) {
		update_option( 'sample_plugin_install', time() - $installed_ago );
		return new Product( dirname( __FILE__ ) . '/sample_products/sample_plugin/plugin_file.php' );
	}

	private function theme( $installed_ago = WEEK_IN_SECONDS ) {
		update_option( 'sample_theme_install', time() - $installed_ago );
		return new Product( dirname( __FILE__ ) . '/sample_products/sample_theme/style.css' );
	}

	private function opt_in( $product, $meta = self::META ) {
		add_filter(
			$product->get_key() . '_ai_connect_metadata',
			function () use ( $meta ) {
				return $meta;
			}
		);
	}

	private function loaded( $product ) {
		$module = new Ai_Connect();
		$this->assertTrue( $module->can_load( $product ) );
		return $module->load( $product );
	}

	private function notice( $module ) {
		ob_start();
		$module->render_notice();
		return ob_get_clean();
	}

	// -- opt-in -------------------------------------------------------------

	public function test_not_loading_without_metadata() {
		$this->assertFalse( ( new Ai_Connect() )->can_load( $this->plugin() ) );
		$this->assertFalse( ( new Ai_Connect() )->can_load( $this->theme() ) );
	}

	public function test_loading_with_metadata() {
		$this->opt_in( $this->plugin() );
		$this->opt_in( $this->theme() );
		$this->assertTrue( ( new Ai_Connect() )->can_load( $this->plugin() ) );
		$this->assertTrue( ( new Ai_Connect() )->can_load( $this->theme() ) );
	}

	/**
	 * @dataProvider unusable_metadata
	 */
	public function test_not_loading_with_unusable_metadata( $meta ) {
		$this->opt_in( $this->plugin(), $meta );
		$this->assertFalse( ( new Ai_Connect() )->can_load( $this->plugin() ) );
	}

	public function unusable_metadata() {
		return array(
			'not an array' => array( 'yes' ),
			'empty'        => array( array() ),
			'no prompts'   => array( array( 'notice_cases' => array( 'do a thing' ) ) ),
			'no cases'     => array( array( 'prompts' => array( 'Do a thing.' ) ) ),
			'blank values' => array( array( 'notice_cases' => array( ' ', 7 ), 'prompts' => array( '', null ) ) ),
		);
	}

	public function test_not_loading_outside_the_admin() {
		$this->opt_in( $this->plugin() );
		set_current_screen( 'front' );
		$this->assertFalse( ( new Ai_Connect() )->can_load( $this->plugin() ) );
	}

	/** With Easy MCP active there is nothing to offer: no notice, no row link, no modal. */
	public function test_not_loading_when_the_connector_is_active() {
		$this->opt_in( $this->plugin() );
		update_option( 'active_plugins', array( Ai_Connect::CONNECTOR_FILE ) );
		$this->assertFalse( ( new Ai_Connect() )->can_load( $this->plugin() ) );
	}

	public function test_metadata_is_sanitized_and_bounded() {
		$data = Ai_Connect::sanitize_metadata(
			array(
				'name'         => ' <b>Optimole</b> ',
				'notice_cases' => array( 'one', '<b>two</b>', 'three', 'four' ),
				'prompts'      => array( 'a', 'b', 'c', 'd', 'e', 'f' ),
				'abilities'    => array( 'optimole/purge-image-cache', 'Not An Ability', 'wp_delete_post', '../x/y', 'optimole/get-offload-job' ),
			),
			$this->plugin()
		);
		$this->assertSame( 'Optimole', $data['name'] );
		$this->assertSame( array( 'one', 'two', 'three' ), $data['notice_cases'] );
		$this->assertCount( 5, $data['prompts'] );
		$this->assertSame( array( 'optimole/purge-image-cache', 'optimole/get-offload-job' ), $data['abilities'] );
	}

	public function test_the_name_defaults_to_the_products_own() {
		$data = Ai_Connect::sanitize_metadata( self::META, $this->plugin() );
		$this->assertSame( $this->plugin()->get_friendly_name(), $data['name'] );
	}

	// -- who sees it ---------------------------------------------------------

	public function test_only_users_who_can_install_and_activate_plugins() {
		$this->assertTrue( Ai_Connect::user_can_connect() );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );
		$this->assertFalse( Ai_Connect::user_can_connect() );
		wp_set_current_user( 0 );
		$this->assertFalse( Ai_Connect::user_can_connect() );
	}

	public function test_row_link_is_added_to_the_products_own_row_only() {
		$product = $this->plugin();
		$this->opt_in( $product );
		$module = $this->loaded( $product );
		$own    = $module->add_row_meta( array( 'View details' ), plugin_basename( $product->get_basefile() ) );
		$this->assertCount( 2, $own );
		$this->assertStringContainsString( 'data-ti-ai-connect="' . $product->get_key() . '"', $own[1] );
		$this->assertStringContainsString( 'Connect with your AI agent', $own[1] );
		$this->assertSame( array( 'View details' ), $module->add_row_meta( array( 'View details' ), 'akismet/akismet.php' ) );
	}

	public function test_row_link_is_hidden_from_users_who_cannot_install_plugins() {
		$product = $this->plugin();
		$this->opt_in( $product );
		$module = $this->loaded( $product );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );
		$this->assertSame( array(), $module->add_row_meta( array(), plugin_basename( $product->get_basefile() ) ) );
	}

	// -- the notice ----------------------------------------------------------

	public function test_notice_waits_a_day_after_install() {
		$young = $this->plugin( HOUR_IN_SECONDS );
		$this->opt_in( $young );
		$this->assertSame( '', $this->notice( $this->loaded( $young ) ) );

		Ai_Connect::reset();
		$product = $this->plugin( DAY_IN_SECONDS + 60 );
		$html    = $this->notice( $this->loaded( $product ) );
		$this->assertStringContainsString( 'Manage ' . $product->get_friendly_name() . ' with your AI agent.', $html );
		$this->assertStringContainsString( 'Ask Claude, ChatGPT or Cursor to optimize new uploads, purge cached images or offload originals to the cloud.', $html );
		$this->assertStringContainsString( 'is-dismissible', $html );
	}

	public function test_notice_reads_well_with_one_or_two_use_cases() {
		$product = $this->plugin();
		$this->opt_in( $product, array( 'notice_cases' => array( 'purge the cache' ), 'prompts' => array( 'Purge it.' ) ) );
		$this->assertStringContainsString( 'Ask Claude, ChatGPT or Cursor to purge the cache.', $this->notice( $this->loaded( $product ) ) );
	}

	public function test_notice_is_only_on_the_plugins_list_and_internal_pages() {
		$product = $this->plugin();
		$this->opt_in( $product );
		$module = $this->loaded( $product );

		set_current_screen( 'dashboard' );
		$this->assertSame( '', $this->notice( $module ), 'Not on unrelated admin screens.' );

		do_action( 'themeisle_internal_page', $product->get_slug(), 'dashboard' );
		$html = $this->notice( $module );
		$this->assertStringContainsString( 'data-ti-ai-notice', $html, 'On the product\'s own page.' );
		$this->assertStringContainsString( '.notice:not(.ti-ai-notice)', $html, 'And there it hides the other notices.' );
	}

	public function test_a_product_that_passes_its_basename_as_the_slug_is_still_recognised() {
		$product = $this->plugin();
		$this->opt_in( $product );
		$module = $this->loaded( $product );
		set_current_screen( 'dashboard' );
		do_action( 'themeisle_internal_page', $product->get_slug() . '/plugin_file.php', 'dashboard' );
		$this->assertStringContainsString( 'data-ti-ai-notice', $this->notice( $module ) );
	}

	public function test_the_notice_survives_a_product_that_clears_admin_notices() {
		$product = $this->plugin();
		$this->opt_in( $product );
		$module = $this->loaded( $product );
		remove_all_actions( 'admin_notices' ); // What Orbit Fox does on load-{$hook} of its dashboard.
		do_action( 'in_admin_header' );
		ob_start();
		do_action( 'admin_notices' );
		$this->assertSame( 1, substr_count( ob_get_clean(), 'data-ti-ai-notice' ) );
	}

	public function test_the_notice_is_never_printed_twice() {
		$product = $this->plugin();
		$this->opt_in( $product );
		$this->loaded( $product );
		do_action( 'in_admin_header' );
		ob_start();
		do_action( 'admin_notices' );
		$this->assertSame( 1, substr_count( ob_get_clean(), 'data-ti-ai-notice' ) );
	}

	public function test_other_notices_are_left_alone_on_the_plugins_list() {
		$product = $this->plugin();
		$this->opt_in( $product );
		$this->assertStringNotContainsString( '<style>', $this->notice( $this->loaded( $product ) ) );
	}

	public function test_an_internal_page_of_a_product_that_did_not_opt_in_shows_nothing() {
		$product = $this->plugin();
		$this->opt_in( $product );
		$module = $this->loaded( $product );
		set_current_screen( 'dashboard' );
		do_action( 'themeisle_internal_page', 'some-other-product', 'dashboard' );
		$this->assertSame( '', $this->notice( $module ) );
	}

	public function test_dismissal_is_per_user_not_per_site() {
		$product = $this->plugin();
		$this->opt_in( $product );
		$module = $this->loaded( $product );

		update_user_meta( $this->admin, Ai_Connect::DISMISSED_KEY, time() );
		$this->assertSame( '', $this->notice( $module ) );

		$other = self::factory()->user->create( array( 'role' => 'administrator' ) );
		if ( is_multisite() ) {
			grant_super_admin( $other );
		}
		wp_set_current_user( $other );
		$this->assertStringContainsString( 'data-ti-ai-notice', $this->notice( $module ), 'Another administrator has not seen it yet.' );
	}

	public function test_notice_is_hidden_from_users_who_cannot_install_plugins() {
		$product = $this->plugin();
		$this->opt_in( $product );
		$module = $this->loaded( $product );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );
		$this->assertSame( '', $this->notice( $module ) );
	}

	public function test_several_products_render_one_notice_and_one_modal() {
		$plugin = $this->plugin( 2 * WEEK_IN_SECONDS );
		$theme  = $this->theme( WEEK_IN_SECONDS );
		$this->opt_in( $plugin );
		$this->opt_in( $theme, array( 'name' => 'Sample Theme', 'notice_cases' => array( 'change the header' ), 'prompts' => array( 'Make the header sticky.' ) ) );
		$this->loaded( $plugin );
		$this->loaded( $theme );

		ob_start();
		do_action( 'admin_notices' );
		$html = ob_get_clean();
		$this->assertSame( 1, substr_count( $html, 'data-ti-ai-notice' ) );
		$this->assertStringContainsString( 'Manage ' . $plugin->get_friendly_name(), $html, 'The product installed first speaks on the plugins list.' );

		ob_start();
		do_action( 'admin_footer' );
		$this->assertSame( 1, substr_count( ob_get_clean(), 'id="ti-ai-connect"' ) );
	}

	// -- links ---------------------------------------------------------------

	public function test_connect_links_mirror_easy_mcp() {
		update_option( 'blogname', 'Tom & Co' );
		$links = Ai_Connect::connect_links( 'https://example.org/wp-json/easy-mcp-ai/v1/mcp' );
		$this->assertSame( array( 'claude', 'chatgpt', 'cursor' ), array_keys( $links ) );
		$this->assertSame(
			'https://claude.ai/customize/connectors?modal=add-custom-connector&connectorName=Tom%20%26%20Co%20WordPress&connectorUrl=https%3A%2F%2Fexample.org%2Fwp-json%2Feasy-mcp-ai%2Fv1%2Fmcp',
			$links['claude']
		);
		$this->assertSame( 'https://chatgpt.com/plugins#settings/Connectors?create-connector=true&redirectAfter=%2Fplugins', $links['chatgpt'], 'ChatGPT cannot be pre-filled.' );
		$this->assertStringStartsWith( 'cursor://anysphere.cursor-deeplink/mcp/install?name=Tom%20%26%20Co%20WordPress&config=', $links['cursor'] );
		parse_str( (string) wp_parse_url( $links['cursor'], PHP_URL_QUERY ), $query );
		$this->assertSame( array( 'url' => 'https://example.org/wp-json/easy-mcp-ai/v1/mcp' ), json_decode( base64_decode( $query['config'] ), true ) );
	}

	// -- abilities -----------------------------------------------------------

	public function test_enabling_abilities_adds_without_removing() {
		update_option( Ai_Connect::ABILITIES_OPTION, array( 'other/kept' ) );
		$added = Ai_Connect::enable_abilities( array( 'sample/get-settings', 'other/kept' ) );
		// Without the Abilities API (WP < 6.9) the names cannot be checked, so they are taken as given.
		if ( ! function_exists( 'wp_get_abilities' ) ) {
			$this->assertSame( array( 'sample/get-settings' ), $added );
			$this->assertSame( array( 'other/kept', 'sample/get-settings' ), get_option( Ai_Connect::ABILITIES_OPTION ) );
		} else {
			$this->assertSame( array(), $added, 'Names that are not registered abilities are never enabled.' );
			$this->assertSame( array( 'other/kept' ), get_option( Ai_Connect::ABILITIES_OPTION ) );
		}
	}

	public function test_enabling_abilities_needs_manage_options() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );
		$this->assertSame( array(), Ai_Connect::enable_abilities( array( 'sample/get-settings' ) ) );
		$this->assertFalse( get_option( Ai_Connect::ABILITIES_OPTION ) );
	}

	public function test_a_corrupt_stored_list_is_replaced_not_fatal() {
		update_option( Ai_Connect::ABILITIES_OPTION, 'not-a-list' );
		Ai_Connect::enable_abilities( array() );
		$this->assertSame( 'not-a-list', get_option( Ai_Connect::ABILITIES_OPTION ), 'Nothing to add means nothing is written.' );
	}

	// -- ajax ----------------------------------------------------------------

	public function test_already_active_connector_is_not_installed_again() {
		$product = $this->plugin();
		$this->opt_in( $product );
		$module = $this->loaded( $product );
		update_option( 'active_plugins', array( Ai_Connect::CONNECTOR_FILE ) );
		$this->assertTrue( $module->install_and_activate() );
	}

	public function test_labels_cover_every_string_the_ui_uses() {
		$labels = \ThemeisleSDK\Loader::$labels['ai_connect'];
		foreach ( array( 'row_link', 'notice_title', 'notice_text', 'cases_join', 'notice_button', 'eyebrow', 'title', 'lead', 'enable', 'enabling', 'enabled', 'url_label', 'copy', 'copied', 'close', 'connect_heading', 'connect_claude', 'connect_chatgpt', 'connect_cursor', 'hint_off', 'hint_on', 'prompts_off', 'prompts_on', 'error_permission', 'error_install' ) as $key ) {
			$this->assertNotEmpty( $labels[ $key ], $key );
		}
		// Copy decisions: vendor-neutral, and no install wording in the UI.
		$all = strtolower( implode( ' ', $labels ) );
		$this->assertSame( 0, preg_match( '/\\b(our|my) ai\\b/', $all ) );
		foreach ( array( 'row_link', 'notice_title', 'notice_text', 'notice_button', 'eyebrow', 'title', 'lead', 'enable', 'hint_off', 'hint_on' ) as $key ) {
			$this->assertStringNotContainsString( 'install', strtolower( $labels[ $key ] ), $key );
		}
	}
}
