<?php
/**
 * The feedback model class for ThemeIsle SDK
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
if ( ! class_exists( 'ThemeIsle_SDK_Feedback' ) ) :
	/**
	 * Feedback model for ThemeIsle SDK.
	 */
	abstract class ThemeIsle_SDK_Feedback {
		/**
		 * @var ThemeIsle_SDK_Product $product Themeisle Product.
		 */
		protected $product;

		/**
		 * @var string $feedback_url Url where to send the feedback
		 */
		private $feedback_url = 'http://localhost:81/wp-json/__pirate_feedback_/v1/feedback';

		/**
		 * ThemeIsle_SDK_Feedback constructor.
		 *
		 * @param ThemeIsle_SDK_Product $product_object Product Object.
		 */
		public function __construct( $product_object ) {
			if ( $product_object instanceof ThemeIsle_SDK_Product ) {
				$this->product      = $product_object;
			}
            $this->setup_hooks();
		}

        public function setup_hooks() {
            $this->setup_hooks_child();
        }

        protected function call_api( $attributes ) {
            $slug               = $this->product->get_slug();
            $attributes['slug'] = $slug;

            wp_remote_post( $this->feedback_url, array( 'body' => $attributes ) );
        }

        function randomize_options( $options ) {
            $new    = array();
            $keys   = array_keys( $options );
            shuffle( $keys );

            foreach ( $keys as $key ) {
                $new[ $key ] = $options[ $key ];
            }

            return $new;
        }

        protected abstract function setup_hooks_child();

	}
endif;
