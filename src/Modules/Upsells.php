<?php
/**
 * The upsells model class for ThemeIsle SDK
 *
 * add_filter(
 *  'menu_icons_load_upsells',
 *  function() {
 *   return array( 'otter' );
 *  }
 * );
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

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Upsells module for ThemeIsle SDK.
 */
class Upsells extends Abstract_Module {
	/**
	 * Fetched feeds items.
	 *
	 * @var array Feed items.
	 */
	private $upsells_to_load = array();

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

		$this->upsells_to_load = apply_filters( $product->get_key() . '_load_upsells', array() );

		if ( 0 === count( $this->upsells_to_load ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Registers the hooks.
	 *
	 * @param Product $product Product to load.
	 *
	 * @return Upsells Module instance.
	 */
	public function load( $product ) {
		if ( 0 === count( $this->upsells_to_load ) ) {
			return;
		}

		$this->product = $product;

		if ( in_array( 'otter', $this->upsells_to_load ) && false === apply_filters( 'themeisle_sdk_load_upsells_otter', false ) && ! ( defined( 'OTTER_BLOCKS_VERSION' ) || $this->is_otter_installed() ) ) {
			add_filter( 'themeisle_sdk_load_upsells_otter', '__return_true' );
			add_action( 'init', array( $this, 'register_settings' ), 99 );

			if ( false !== $this->show_otter_upsell() ) {
				add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
			}
		}

		return $this;
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
			'themeisle_sdk_upsells_otter',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'show_in_rest'      => true,
				'default'           => '{}',
			)
		);
	}

	/**
	 * Get the Otter Blocks plugin status.
	 *
	 * @return string
	 */
	private function is_otter_installed() {
		$status = false;

		if ( file_exists( ABSPATH . 'wp-content/plugins/otter-blocks/otter-blocks.php' ) ) {
			return true;
		}

		return $status;
	}

	/**
	 * Get status of Otter upsell message.
	 *
	 * @return mixed
	 */
	public function show_otter_upsell() {
		$upsells = array(
			'blocks_css',
			'blocks_animation',
			'blocks_conditions',
		);

		$option = json_decode( get_option( 'themeisle_sdk_upsells_otter', '{}' ), true );

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
	 * Load Gutenberg editor assets.
	 *
	 * @since   1.0.0
	 * @access  public
	 */
	public function enqueue_editor_assets() {
		global $themeisle_sdk_path, $themeisle_sdk_src;
		$asset_file = include $themeisle_sdk_path . '/assets/js/build/index.asset.php';

		wp_enqueue_script(
			'themeisle-sdk-otter-upsells',
			$themeisle_sdk_src . 'assets/js/build/index.js',
			array_merge( $asset_file['dependencies'], [ 'updates' ] ),
			$asset_file['version'],
			true
		);

		$option = get_option( 'themeisle_sdk_upsells_otter', '{}' );

		wp_localize_script(
			'themeisle-sdk-otter-upsells',
			'themeisleSDKUpsells',
			array(
				'product'       => $this->product->get_name(),
				'assets'        => $themeisle_sdk_src . 'assets/images/',
				'showUpsell'    => $this->show_otter_upsell(),
				'upsells_otter' => $option,
				'activationUrl' => esc_url(
					add_query_arg(
						array(
							'plugin_status' => 'all',
							'paged'         => '1',
							'action'        => 'activate',
							'plugin'        => rawurlencode( 'otter-blocks/otter-blocks.php' ),
							'_wpnonce'      => wp_create_nonce( 'activate-plugin_otter-blocks/otter-blocks.php' ),
						),
						admin_url( 'plugins.php' ) 
					) 
				),
			)
		);
	}
}
