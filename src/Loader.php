<?php
/**
 * The main loader class for ThemeIsle SDK
 *
 * @package     ThemeIsleSDK
 * @subpackage  Loader
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 * @since       1.0.0
 */

namespace ThemeisleSDK;

use ThemeisleSDK\Common\Module_Factory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Singleton loader for ThemeIsle SDK.
 */
final class Loader {
	/**
	 * Singleton instance.
	 *
	 * @var Loader instance The singleton instance
	 */
	private static $instance;
	/**
	 * Current loader version.
	 *
	 * @var string $version The class version.
	 */
	private static $version = '2.0.0';
	/**
	 * Holds registered products.
	 *
	 * @var array The products which use the SDK.
	 */
	private static $products = [];
	/**
	 * Holds available modules to load.
	 *
	 * @var array The modules which SDK will be using.
	 */
	private static $available_modules = [
		'logger',
		'licenser',
		'uninstall_feedback',
		'dashboard_widget',
		'rollback',
	];

	/**
	 * Initialize the sdk logic.
	 */
	public static function init() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Loader ) ) {
			self::$instance = new Loader();
			$modules        = array_merge( self::$available_modules, apply_filters( 'themeisle_sdk_modules', [] ) );
			foreach ( $modules as $key => $module ) {
				if ( ! class_exists( 'ThemeisleSDK\\Modules\\' . ucwords( $module ) ) ) {
					unset( $modules[ $key ] );
				}
			}
			self::$available_modules = $modules;
		}
	}

	/**
	 * Register product into SDK.
	 *
	 * @param string $base_file The product base file.
	 *
	 * @return Loader The singleton object.
	 */
	public static function add_product( $base_file ) {

		if ( ! is_readable( $base_file ) ) {
			return self::$instance;
		}

		$product = new Product( $base_file );

		Module_Factory::attach( $product, self::get_modules() );

		self::$products[ $product->get_slug() ] = $product;
		//
		// $notifications = array();
		// Based on the WordPress Available file header we enable the logger or not.
		// if ( ! $product_object->is_wordpress_available() && apply_filters( $product_object->get_key() . '_enable_licenser', true ) === true ) {
		// $licenser = new ThemeIsle_SDK_Licenser( $product_object );
		// $licenser->enable();
		// }
		//
		// $logger = new ThemeIsle_SDK_Logger( $product_object );
		// if ( $product_object->is_logger_active() ) {
		// $logger->enable();
		// } else {
		// $notifications[] = $logger;
		// }
		//
		// $feedback = new ThemeIsle_SDK_Feedback_Factory( $product_object, $product_object->get_feedback_types() );
		//
		// $instances = $feedback->get_instances();
		// if ( array_key_exists( 'review', $instances ) ) {
		// $notifications[] = $instances['review'];
		// }
		// if ( array_key_exists( 'translate', $instances ) ) {
		// $notifications[] = $instances['translate'];
		// }
		// new ThemeIsle_SDK_Notification_Manager( $product_object, $notifications );
		// if ( ! $product_object->is_external_author() ) {
		// new ThemeIsle_SDK_Widgets_Factory( $product_object, $product_object->get_widget_types() );
		// }
		// if ( ! $product_object->is_external_author() ) {
		// new ThemeIsle_SDK_Rollback( $product_object );
		// }
		//
		// new ThemeIsle_SDK_Endpoints( $product_object );
		return self::$instance;
	}

	/**
	 * Get all registered modules by the SDK.
	 *
	 * @return array Modules available.
	 */
	public static function get_modules() {
		return self::$available_modules;
	}

	/**
	 * Get all products using the SDK.
	 *
	 * @return array Products available.
	 */
	public static function get_products() {
		return self::$products;
	}


}
