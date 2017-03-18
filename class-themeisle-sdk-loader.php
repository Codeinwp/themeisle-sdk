<?php
/**
 * The main loader class for ThemeIsle SDK
 *
 * @package     ThemeIsleSDK
 * @subpackage  Loader
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 * @since       1.0.0
 *
 */
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'ThemeIsle_SDK_Loader' ) ) :
	final class ThemeIsle_SDK_Loader {
		/**
		 * @var ThemeIsle_SDK_Loader instance The singleton instance
		 */
		private static $instance;
		/**
		 * @var string $version The class version.
		 */
		private static $version = '1.0.0';
		/**
		 * @var array The products which use the SDK.
		 */
		private static $products;

		/**
		 * Register product into SDK.
		 *
		 * @param string $basefile The product basefile.
		 *
		 * @return ThemeIsle_SDK_Loader The singleton object.
		 */
		public static function init_product( $basefile ) {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof ThemeIsle_SDK_Loader ) ) {
				self::$instance = new ThemeIsle_SDK_Loader;
				self::$instance->setup_hooks();
			}
			$product_object                                = new ThemeIsle_SDK_Product( $basefile );
			self::$products[ $product_object->get_slug() ] = $product_object;

			return self::$instance;
		}

		public function setup_hooks() {
			add_filter( 'extra_plugin_headers', array( $this, 'add_extra_headers' ) );
			add_filter( 'extra_theme_headers', array( $this, 'add_extra_headers' ) );
		}

		function add_extra_headers( $headers ) {

			if ( ! in_array( 'Requires License', $headers ) ) {
				$headers[] = 'Requires License';
			}

			if ( ! in_array( 'WordPress Available', $headers ) ) {
				$headers[] = 'WordPress Available';
			}

			return $headers;
		}

	}
endif;