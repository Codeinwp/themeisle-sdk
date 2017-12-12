<?php
/**
 * The class that exposes endpoints.
 *
 * @package     ThemeIsleSDK
 * @subpackage  Endpoints
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 * @since       1.0.0
 */
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'ThemeIsle_SDK_Endpoints' ) ) :
	/**
	 * Expose endpoints loader for ThemeIsle SDK.
	 */
	final class ThemeIsle_SDK_Endpoints {


		const SDK_ENDPOINT			= 'themeisle-sdk';
		const SDK_ENDPOINT_VERSION	= 1;
		const PRODUCT_SPECIFIC		= false;

		/**
		 * @var ThemeIsle_SDK_Product $product Themeisle Product.
		 */
		protected $product;

		/**
		 * ThemeIsle_SDK_Endpoints constructor.
		 *
		 * @param ThemeIsle_SDK_Product $product_object Product Object.
		 */
		public function __construct( $product_object ) {
			if ( $product_object instanceof ThemeIsle_SDK_Product ) {
				$this->product = $product_object;
			}
			$this->setup_endpoints();
		}

		private function setup_endpoints() {
			global $wp_version;
			if ( version_compare( $wp_version, '4.4', '<' ) ) {
				return;
			}

			$this->setup_rest();
		}

		private function setup_rest() {
			add_action( 'rest_api_init', array( $this, 'rest_register' ) );
		}

		/**
		 * Registers the endpoints
		 */
		function rest_register() {
			register_rest_route(
				self::SDK_ENDPOINT . '/v' . self::SDK_ENDPOINT_VERSION,
				'/checksum/(?P<slug>.*)/',
				array(
					'methods'  => 'GET',
					'callback' => array( $this, 'checksum' ),
				)
			);
		}

		/**
		 * The checksum endpoint.
		 *
		 * @param WP_REST_Request $data the request.
		 *
		 * @return WP_REST_Response Response or the error
		 */
		function checksum( WP_REST_Request $data ) {
			if ( self::PRODUCT_SPECIFIC ) {
				$params	= $this->validate_params( $data, array( 'slug' ) );
			}

			$files	= array();
			switch ( $this->product->get_type() ) {
				case 'plugin':
					$files	= array( '/' );
					break;
				case 'theme':
					$files	= array( 'style.css', 'functions.php' );
					break;
			}
		}

		private function generate_diff( $files ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			WP_Filesystem();
			global $wp_filesystem;

			$path			= str_replace( ABSPATH, $wp_filesystem->abspath(), plugin_dir_path( dirname( $this->basefile ) ) );
			$addons_path    = trailingslashit( $plugin_path ) . '/includes/addons/';
			$files  = $wp_filesystem->dirlist( $addons_path, false, true );
			if ( ! $files ) {
				return;
			}

		}

		/**
		 * Validates the parameters to the API
		 *
		 * @param WP_REST_Request $data the request.
		 * @param array           $params the parameters to validate.
		 *
		 * @return array of parameter name=>value
		 */
		private function validate_params( WP_REST_Request $data, $params ) {
			$collect = array();
			foreach ( $params as $param ) {
				$value = sanitize_text_field( $data[ $param ] );
				if ( empty( $value ) ) {
					return new WP_Error(
						'themeisle_' . $param . '_invalid', sprintf( __( 'Invalid %', 'themeisle-sdk' ), $param ), array(
							'status' => 403,
						)
					);
				} else {
					$collect[ $param ] = $value;
				}
			}

			return $collect;
		}

	}
 }