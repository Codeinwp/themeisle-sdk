<?php
/**
 * Plugin Name:       Themeisle SDK
 * Description:       Themeisle SDK QA version.
 * Version:           1.0.0
 * Author:            ThemeIsle
 * Author URI:        https://themeisle.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       sample-plugin
 * WordPress Available:  no
 * Requires License:    no
 */

require_once 'load.php';

function _add_product( $products ) {
	$products[] = __FILE__;

	return $products;
}

add_filter( 'themeisle_sdk_products', '_add_product' );
add_filter( 'themeisle_sdk_promo_debug', '__return_true' );
add_filter( 'themeisle_sdk_load_promotions', function ( $promos ) {
	$promos[] = 'otter';

	return $promos;
} );
