<?php
/**
 * The promotions model class for ThemeIsle SDK
 *
 * Here's how to hook it in your plugin: add_filter( 'menu_icons_load_promotions', function() { return array( 'otter' ); } );
 *
 * @package     ThemeIsleSDK
 * @subpackage  Modules
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 * @since       1.0.0
 */

namespace ThemeisleSDK\Modules;

use ThemeisleSDK\Common\Abstract_Module;
use ThemeisleSDK\Product;
use ThemeisleSDK\Promotions\Performance;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Promotions module for ThemeIsle SDK.
 */
class Promotions extends Abstract_Module {
	/**
	 * Fetched feeds items.
	 *
	 * @var array Feed items.
	 */
	private $promotions_to_load = array();

	/**
	 * Should we load this module.
	 *
	 * @param Product $product Product object.
	 *
	 * @return bool
	 */
	public function can_load( $product ) {
		if ( $this->is_from_partner( $product ) ) {
			return false;
		}

		$this->promotions_to_load   = apply_filters( $product->get_key() . '_load_promotions', array() );
		$this->promotions_to_load[] = 'optimole';

		if ( 0 === count( $this->promotions_to_load ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Registers the hooks.
	 *
	 * @param Product $product Product to load.
	 *
	 * @return Promotions Module instance.
	 */
	public function load( $product ) {
		if ( 0 === count( $this->promotions_to_load ) ) {
			return;
		}

		if ( ! $this->is_writeable() || ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		$this->product = $product;

		add_action( 'init', array( $this, 'register_settings' ), 99 );
		add_action( 'admin_init', array( $this, 'register_reference' ), 99 );


		if ( in_array( 'otter', $this->promotions_to_load, true )
		     && false === apply_filters( 'themeisle_sdk_load_promotions_otter', false )
		     && ! ( defined( 'OTTER_BLOCKS_VERSION' ) || $this->is_plugin_installed( 'otter-blocks' ) )
		     && version_compare( get_bloginfo( 'version' ), '5.8', '>=' ) ) {
			add_filter( 'themeisle_sdk_load_promotions_otter', '__return_true' );

			if ( false !== $this->show_otter_promotion() ) {
				add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
			}
		}

		if ( in_array( 'optimole', $this->promotions_to_load, true )
		     && false === apply_filters( 'themeisle_sdk_load_promotions_optimole', false )
		     && ! ( defined( 'OPTML_VERSION' ) || $this->is_plugin_installed( 'optimole-wp' ) )
		) {
			add_filter( 'themeisle_sdk_load_promotions_optimole', '__return_true' );
			$this->load_optimole_promotions();
		}

		return $this;
	}

	public function load_optimole_promotions() {
		require_once 'Promotions/Performance.php';

		new Performance( $this->product, $this->get_sdk_uri() );
	}

	/**
	 * Register plugin reference.
	 *
	 * @return void
	 */
	public function register_reference() {

		$reference_key = ! isset( $_GET['reference_key'] ) ? '' : sanitize_key( $_GET['reference_key'] );
		if ( empty( $reference_key ) ) {
			return;
		}
		if ( get_option( 'otter_reference_key', false ) !== false ) {
			return;
		}
		update_option( 'otter_reference_key', $reference_key );
	}

	/**
	 * Register Settings
	 *
	 * @since   1.2.0
	 * @access  public
	 */
	public function register_settings() {
		register_setting(
			'themeisle_sdk_settings',
			'themeisle_sdk_promotions_otter',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'show_in_rest'      => true,
				'default'           => '{}',
			)
		);

		register_setting(
			'themeisle_sdk_settings',
			'themeisle_sdk_promotions_otter_installed',
			array(
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
				'show_in_rest'      => true,
				'default'           => false,
			)
		);

		register_setting(
			'themeisle_sdk_settings',
			'themeisle_sdk_promotions_optimole',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'show_in_rest'      => true,
				'default'           => '{}',
			)
		);

		register_setting(
			'themeisle_sdk_settings',
			'themeisle_sdk_promotions_optimole_installed',
			array(
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
				'show_in_rest'      => true,
				'default'           => false,
			)
		);
	}

	/**
	 * Get the Otter Blocks plugin status.
	 *
	 * @param string $plugin Plugin slug.
	 *
	 * @return string
	 */
	private function is_plugin_installed( $plugin ) {
		static $allowed_keys = [
			'otter-blocks' => 'otter-blocks/otter-blocks.php',
			'optimole-wp'  => 'optimole-wp/optimole-wp.php'
		];
		if ( ! isset( $allowed_keys[ $plugin ] ) ) {
			return false;
		}
		if ( file_exists( WP_CONTENT_DIR . '/plugins/' . $allowed_keys[ $plugin ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get status of Otter promotion message.
	 *
	 * @return mixed
	 */
	public function show_otter_promotion() {
		$promotions = array(
			'blocks_css',
			'blocks_animation',
			'blocks_conditions',
		);

		$option = json_decode( get_option( 'themeisle_sdk_promotions_otter', '{}' ), true );

		if ( 0 === count( $option ) ) {
			return 'blocks-css';
		}

		if ( isset( $option['blocks-css'] ) && ! isset( $option['blocks-animation'] ) && $option['blocks-css'] < strtotime( '-7 days' ) ) {
			return 'blocks-animation';
		}

		if ( isset( $option['blocks-animation'] ) && ! isset( $option['blocks-conditions'] ) && $option['blocks-animation'] < strtotime( '-7 days' ) ) {
			return 'blocks-conditions';
		}

		return false;
	}

	/**
	 * Check if the path is writable.
	 *
	 * @return boolean
	 * @access  public
	 */
	public function is_writeable() {

		include_once ABSPATH . 'wp-admin/includes/file.php';
		$filesystem_method = get_filesystem_method();

		if ( 'direct' === $filesystem_method ) {
			return true;
		}

		return false;
	}

	/**
	 * Load Gutenberg editor assets.
	 *
	 * @access  public
	 */
	public function enqueue_editor_assets() {
		global $themeisle_sdk_max_path;

		$themeisle_sdk_path = dirname( $themeisle_sdk_max_path );
		$themeisle_sdk_src  = $this->get_sdk_uri();

		$asset_file = include $themeisle_sdk_path . '/themeisle-sdk/assets/js/build/index.asset.php';

		wp_enqueue_script(
			'themeisle-sdk-otter-promotions',
			$themeisle_sdk_src . 'themeisle-sdk/assets/js/build/index.js',
			array_merge( $asset_file['dependencies'], [ 'updates' ] ),
			$asset_file['version'],
			true
		);

		$option = get_option( 'themeisle_sdk_promotions_otter', '{}' );

		wp_localize_script(
			'themeisle-sdk-otter-promotions',
			'themeisleSDKPromotions',
			array(
				'product'          => $this->product->get_name(),
				'assets'           => $themeisle_sdk_src . 'themeisle-sdk/assets/images/',
				'showPromotion'    => $this->show_otter_promotion(),
				'promotions_otter' => $option,
				'activationUrl'    => esc_url(
					add_query_arg(
						array(
							'plugin_status' => 'all',
							'paged'         => '1',
							'action'        => 'activate',
							'reference_key' => $this->product->get_key(),
							'plugin'        => rawurlencode( 'otter-blocks/otter-blocks.php' ),
							'_wpnonce'      => wp_create_nonce( 'activate-plugin_otter-blocks/otter-blocks.php' ),
						),
						admin_url( 'plugins.php' )
					)
				),
			)
		);
	}

	/**
	 * Get the SDK base url.
	 *
	 * @return string
	 */
	private function get_sdk_uri() {
		global $themeisle_sdk_max_path;

		if ( $this->product->is_plugin() ) {
			return plugins_url( '/', $themeisle_sdk_max_path );
		};

		return get_template_directory_uri() . '/vendor/codeinwp/themeisle-sdk/';
	}
}
