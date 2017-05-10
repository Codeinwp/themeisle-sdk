<?php
/**
 * The feedback factory class for ThemeIsle SDK
 *
 * @package     ThemeIsleSDK
 * @subpackage  Feedback
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 * @since       1.0.0
 */
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'ThemeIsle_SDK_Feedback_Factory' ) ) :
	/**
	 * Feedback model for ThemeIsle SDK.
	 */
	class ThemeIsle_SDK_Feedback_Factory {

		/**
		 * ThemeIsle_SDK_Feedback_Factory constructor.
		 *
		 * @param array $feedback_types the feedback types.
		 */
		public function __construct( $product_object, $feedback_types ) {
			if ( $product_object instanceof ThemeIsle_SDK_Product && $feedback_types && is_array( $feedback_types ) ) {
				foreach ( $feedback_types as $type ) {
					$class      = 'ThemeIsle_SDK_Feedback_' . ucwords( $type );
					$instance   = new $class( $product_object );
					$instance->setup_hooks();
				}
			}
		}
	}
endif;
