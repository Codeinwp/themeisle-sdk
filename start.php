<?php
/**
 * File responsible for sdk files loading.
 *
 * @package     ThemeIsleSDK
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1.0
 */

namespace ThemeisleSDK;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$products      = apply_filters( 'themeisle_sdk_products', array() );
$path          = dirname( __FILE__ );
$files_to_load = array(
	'Loader.php',
);

foreach ( $files_to_load as $file ) {
	$file_path = $path . '/src/' . $file;

	if ( is_readable( $file_path ) ) {
		require_once $file_path;
	}
}

foreach ( $products as $product ) {
	Loader::add_product( $product );
}
