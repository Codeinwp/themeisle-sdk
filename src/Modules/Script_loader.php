<?php
/**
 * The dependency model class for ThemeIsle SDK
 *
 * @package     ThemeIsleSDK
 * @subpackage  Modules
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 * @since       3.3
 */

namespace ThemeisleSDK\Modules;

use ThemeisleSDK\Common\Abstract_Module;
use ThemeisleSDK\Product;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Script loader module for ThemeIsle SDK.
 */
class Script_Loader extends Abstract_Module {
	/**
	 * Check if we should load the module for this product.
	 *
	 * @param Product $product Product to load the module for.
	 *
	 * @return bool Should we load ?
	 */
	public function can_load( $product ) {
		if ( $this->is_from_partner( $product ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Load module logic.
	 *
	 * @param Product $product Product to load.
	 *
	 * @return Dependancy Module object.
	 */
	public function load( $product ) {
		$this->product = $product;
		$this->setup_actions();
		return $this;
	}

	/**
	 * Setup actions. Once for all products.
	 */
	private function setup_actions() {

		if ( apply_filters( 'themeisle_sdk_script_setup', false ) ) {
			return;
		}

		add_filter( 'themeisle_sdk_dependency_script_handler', [ $this, 'get_script_handler' ], 10, 1 );
		add_action( 'themeisle_sdk_dependency_enqueue_script', [ $this, 'enqueue_script' ], 10, 1 );

		add_filter( 'themeisle_sdk_script_setup', '__return_true' );

		add_action( 'themeisle_internal_page', [ $this, 'load_survey_for_product' ], 10, 2 );
	}

	/**
	 * Load survey for product using internal pages.
	 *
	 * @param string $product_slug Product slug.
	 * @param string $page_slug    Page slug.
	 */
	public function load_survey_for_product( $product_slug, $page_slug ) {
		$data = apply_filters( 'themeisle-sdk/survey/' . $product_slug, [], $page_slug );

		if ( empty( $data ) || ! is_array( $data ) ) {
			return;
		}

		$handler = $this->get_script_handler( 'survey' );
		$this->load_survey( $handler, $data );
	}

	/**
	 * Get the script handler.
	 * 
	 * @param string $slug The slug of the script.
	 * 
	 * @return string The script handler. Empty if slug is not a string or not implemented.
	 */
	public function get_script_handler( $slug ) {
		if ( ! is_string( $slug ) ) {
			return '';
		}
		
		if ( 'tracking' !== $slug && 'survey' !== $slug && 'banner' !== $slug ) {
			return '';
		}

		return apply_filters( 'themeisle_sdk_dependency_script_handler_name', 'themeisle_sdk_' . $slug . '_script', $slug );
	}

	/**
	 * Enqueue the script.
	 *
	 * @param string $slug The slug of the script.
	 */
	public function enqueue_script( $slug ) {
		$handler = apply_filters( 'themeisle_sdk_dependency_script_handler', $slug );
		if ( empty( $handler ) ) {
			return;
		}
		
		if ( 'tracking' === $slug ) {
			$this->load_tracking( $handler );
		} elseif ( 'survey' === $slug ) {
			$this->load_survey( $handler );
		} elseif ( 'banner' === $slug ) {
			$this->load_banner( $handler );
		}
	}

	/**
	 * Load the survey script.
	 * 
	 * @param string $handler The script handler.
	 * @param array  $data The survey data.
	 * 
	 * @return void
	 */
	public function load_survey( $handler, $data = array() ) {
		global $themeisle_sdk_max_path;
		$asset_file = require $themeisle_sdk_max_path . '/assets/js/build/survey/survey_deps.asset.php';

		wp_enqueue_script(
			$handler,
			$this->get_sdk_uri() . 'assets/js/build/survey/survey_deps.js',
			$asset_file['dependencies'],
			$asset_file['version'],
			true
		);

		$data = array_replace_recursive( $this->get_survey_common_data(), $data );

		wp_localize_script( $handler, 'tsdk_survey_data', $data );
	}

	/**
	 * Get the common data in the Formbrick survey format.
	 * 
	 * @return array
	 */
	public function get_survey_common_data() {
		$language            = apply_filters( 'themeisle_sdk_current_lang', get_user_locale() );
		$available_languages = [
			'de_DE'        => 'de',
			'de_DE_formal' => 'de',
		];
		$lang_code           = isset( $available_languages[ $language ] ) ? $available_languages[ $language ] : 'en';

		$url_parts = wp_parse_url( apply_filters( 'themeisle_sdk_current_site_url', get_site_url() ) );
		$clean_url = str_replace( 'www.', '', $url_parts['host'] );
		if ( isset( $url_parts['path'] ) ) {
			$clean_url .= $url_parts['path'];
		}
		$user_id = 'u_' . hash( 'crc32b', $clean_url ); 

		return [
			'userId'     => $user_id,
			'attributes' => [
				'language' => $lang_code,
			],
		];
	}

	/**
	 * Load the tracking script.
	 * 
	 * @param string $handler The script handler.
	 * 
	 * @return void
	 */
	public function load_tracking( $handler ) {
		global $themeisle_sdk_max_path;
		$asset_file = require $themeisle_sdk_max_path . '/assets/js/build/tracking/tracking.asset.php';

		wp_enqueue_script(
			$handler,
			$this->get_sdk_uri() . 'assets/js/build/tracking/tracking.js',
			$asset_file['dependencies'],
			$asset_file['version'],
			true
		);
	}

	/**
	 * Load the banner script.
	 * 
	 * @param string $handler The script handler.
	 * 
	 * @return void
	 */
	public function load_banner( $handler ) {
		global $themeisle_sdk_max_path;
		$asset_file = require $themeisle_sdk_max_path . '/assets/js/build/banner/banner.asset.php';

		wp_enqueue_script(
			$handler,
			$this->get_sdk_uri() . 'assets/js/build/banner/banner.js',
			$asset_file['dependencies'],
			$asset_file['version'],
			true
		);

		wp_enqueue_style(
			$handler . '_style',
			$this->get_sdk_uri() . 'assets/css/banner.css',
			[],
			$asset_file['version']
		);
	}
}
