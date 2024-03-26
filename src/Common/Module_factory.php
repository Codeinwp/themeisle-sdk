<?php
/**
 * The module factory class.
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
 * Class Job_Factory
 *
 * @package ThemeisleSDK\Common
 */
class Module_Factory {
	/**
	 * Partners slugs.
	 *
	 * @var array $SLUGS Partners product slugs.
	 */
	public static $slugs = [
		'zermatt'         => true,
		'neto'            => true,
		'olsen'           => true,
		'benson'          => true,
		'romero'          => true,
		'carmack'         => true,
		'puzzle'          => true,
		'broadsheet'      => true,
		'girlywp'         => true,
		'veggie'          => true,
		'zeko'            => true,
		'maishawp'        => true,
		'didi'            => true,
		'liber'           => true,
		'medicpress-pt'   => true,
		'adrenaline-pt'   => true,
		'consultpress-pt' => true,
		'legalpress-pt'   => true,
		'gympress-pt'     => true,
		'readable-pt'     => true,
		'bolts-pt'        => true,
	];
	/**
	 * Partners domains.
	 *
	 * @var array $DOMAINS Partners domains.
	 */
	public static $domains = [
		'proteusthemes.com',
		'anarieldesign.com',
		'prothemedesign.com',
		'cssigniter.com',
	];
	/**
	 * Map which contains all the modules loaded for each product.
	 *
	 * @var array Mapping array.
	 */
	private static $modules_attached = [];

	/**
	 * Load availabe modules for the selected product.
	 *
	 * @param Product $product Loaded product.
	 * @param array   $modules List of modules.
	 */
	public static function attach( $product, $modules ) {

		if ( ! isset( self::$modules_attached[ $product->get_slug() ] ) ) {
			self::$modules_attached[ $product->get_slug() ] = [];
		}

		foreach ( $modules as $module ) {
			$class = 'ThemeisleSDK\\Modules\\' . ucwords( $module, '_' );
			/**
			 * Module object.
			 *
			 * @var Abstract_Module $module_object Module instance.
			 */
			$module_object = new $class( $product );

			// This will load the module if the product is in beta. This way free licensed products will receive updates via the store.
			$allow_if_beta = $product->is_beta();

			if ( ! $module_object->can_load( $product ) && $allow_if_beta === false ) {
				continue;
			}
			self::$modules_attached[ $product->get_slug() ][ $module ] = $module_object->load( $product );
		}
	}

	/**
	 * Products/Modules loaded map.
	 *
	 * @return array Modules map.
	 */
	public static function get_modules_map() {
		return self::$modules_attached;
	}
}
