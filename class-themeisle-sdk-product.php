<?php
/**
 * The product model class for ThemeIsle SDK
 *
 * @package     ThemeIsleSDK
 * @subpackage  Product
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 * @since       1.0.0
 *
 */
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'ThemeIsle_SDK_Product' ) ) :
	class ThemeIsle_SDK_Product {
		/**
		 * @var string $slug THe product slug.
		 */
		private $slug;
		/**
		 * @var string $basefile The file with headers.
		 */
		private $basefile;

		public function __construct( $basefile ) {
			if ( ! empty( $basefile ) ) {
				$this->basefile = $basefile;
				$this->setup_slug();
			}
		}
		public function setup_slug(){
			$dir = basename($this->basefile);
			die($dir);
		}
		public function get_basefile() {
			return $this->basefile;
		}


	}
endif;