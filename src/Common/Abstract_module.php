<?php
/**
 * The abstract class for module definition.
 *
 * @package     ThemeIsleSDK
 * @subpackage  Loader
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 * @since       3.0.0
 */

namespace ThemeisleSDK\Common;

use ThemeisleSDK\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Abstract_Module.
 *
 * @package ThemeisleSDK\Common
 */
abstract class Abstract_Module {
	/**
	 * Product which use the module.
	 *
	 * @var Product $product Product object.
	 */
	protected $product = null;

	/**
	 * Can load the module for the selected product.
	 *
	 * @param Product $product Product data.
	 *
	 * @return bool Should load module?
	 */
	abstract public function can_load( $product );

	/**
	 * Bootstrap the module.
	 *
	 * @param Product $product Product object.
	 */
	abstract public function load( $product );

	/**
	 * Check if the product is from partner.
	 *
	 * @param Product $product Product data.
	 *
	 * @return bool Is product from partner.
	 */
	public function is_from_partner( $product ) {

		foreach ( Module_Factory::$domains as $partner_domain ) {
			if ( strpos( $product->get_store_url(), $partner_domain ) !== false ) {
				return true;
			}
		}

		return array_key_exists( $product->get_slug(), Module_Factory::$slugs );
	}

	/**
	 * Wrapper for wp_remote_get on VIP environments.
	 *
	 * @param string $url Url to check.
	 * @param array  $args Option params.
	 *
	 * @return array|\WP_Error
	 */
	public function safe_get( $url, $args = array() ) {
		return function_exists( 'vip_safe_wp_remote_get' )
			? vip_safe_wp_remote_get( $url )
			: wp_remote_get( //phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_remote_get_wp_remote_get, Already used.
				$url,
				$args
			);
	}

	/**
	 * Get the SDK base url.
	 *
	 * @return string
	 */
	public function get_sdk_uri() {
		global $themeisle_sdk_max_path;

		/**
		 * $themeisle_sdk_max_path can point to the theme when the theme version is higher.
		 * hence we also need to check that the path does not point to the theme else this will break the URL.
		 * References: https://github.com/Codeinwp/neve-pro-addon/issues/2403
		 */
		if ( $this->product->is_plugin() && false === strpos( $themeisle_sdk_max_path, get_template_directory() ) ) {
			return plugins_url( '/', $themeisle_sdk_max_path . '/themeisle-sdk/' );
		};

		return get_template_directory_uri() . '/vendor/codeinwp/themeisle-sdk/';
	}

	/**
	 * Call plugin api
	 *
	 * @param string $slug plugin slug.
	 *
	 * @return array|mixed|object
	 */
	public function call_plugin_api( $slug ) {
		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		$call_api = get_transient( 'ti_plugin_info_' . $slug );

		if ( false === $call_api ) {
			$call_api = plugins_api(
				'plugin_information',
				array(
					'slug'   => $slug,
					'fields' => array(
						'downloaded'        => false,
						'rating'            => false,
						'description'       => false,
						'short_description' => true,
						'donate_link'       => false,
						'tags'              => false,
						'sections'          => true,
						'homepage'          => true,
						'added'             => false,
						'last_updated'      => false,
						'compatibility'     => false,
						'tested'            => false,
						'requires'          => false,
						'downloadlink'      => false,
						'icons'             => true,
						'banners'           => true,
					),
				)
			);
			set_transient( 'ti_plugin_info_' . $slug, $call_api, 30 * MINUTE_IN_SECONDS );
		}

		return $call_api;
	}
}
