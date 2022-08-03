<?php
/**
 * The upsells model class for ThemeIsle SDK
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

        if ( in_array( 'otter', $this->upsells_to_load ) ) {
            add_action( 'init', array( $this, 'register_settings' ), 99 );
            add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_otter_assets' ) );
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
				'type'         => 'object',
				'description'  => __( 'Otter Upsells.', 'otter-blocks' ),
				'show_in_rest'      => array(
					'schema' => array(
						'type'       => 'object',
						'properties' => array(
							'blocks_css' => array(
								'type' => 'array',
							),
							'blocks_animation' => array(
								'type' => 'array',
							),
							'blocks_conditions' => array(
								'type' => 'array',
							),
						),
					),
				),
				'default'           => array(
					'blocks_css' => array(),
					'blocks_animation' => array(),
					'blocks_conditions' => array(),
				),
			)
		);
    }

	/**
	 * Load Gutenberg editor assets.
	 *
	 * @since   1.0.0
	 * @access  public
	 */
	public function enqueue_editor_assets() {
		$asset_file = include $themeisle_sdk_path . '/assets/js/build/index.asset.php';

		wp_enqueue_script(
			'themeisle-sdk-otter-upsells',
			$themeisle_sdk_src . 'assets/js/build/index.js',
			$asset_file['dependencies'],
			$asset_file['version'],
			true
		);
	}
}
