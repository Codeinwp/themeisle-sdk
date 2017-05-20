<?php
/**
 * The main loader class for ThemeIsle SDK
 *
 * @package     ThemeIsleSDK
 * @subpackage  Logger
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 * @since       1.0.0
 */
if ( ! class_exists( 'ThemeIsle_SDK_Logger' ) ) :
	/**
	 * Class ThemeIsle_SDK_Logger
	 *
	 * Send the statistics to the Themeisle Endpoint
	 */
	/**
	 * Class ThemeIsle_SDK_Logger
	 */
	class ThemeIsle_SDK_Logger {

		/**
		 * @var string $logging_url Url where to send the logs
		 */
		private $logging_url = 'http://mirror.themeisle.com';

		/**
		 * @var ThemeIsle_SDK_Product $product Themeisle Product.
		 */
		private $product;

		/**
		 * @var string $product_cron Cron name handler
		 */
		private $product_cron;

		/**
		 * ThemeIsle_SDK_Logger constructor.
		 *
		 * @param ThemeIsle_SDK_Product $product_object Product Object.
		 */
		public function __construct( $product_object ) {
			if ( $product_object instanceof ThemeIsle_SDK_Product ) {
				$this->product      = $product_object;
				$this->product_cron = $product_object->get_key() . '_log_activity';
			}
			add_action( 'wp_ajax_' . $this->product->get_key() . __CLASS__, array( $this, 'dismiss' ) );
		}


		/**
		 * Start the cron to send the log. It will randomize the interval in order to not send all the logs at the same time.
		 */
		public function enable() {
			if ( ! wp_next_scheduled( $this->product_cron ) ) {
				wp_schedule_single_event( time() + ( rand( 15, 24 ) * 3600 ), $this->product_cron );
			}
			add_action( $this->product_cron, array( $this, 'send_log' ) );
		}

		/**
		 * Send the statistics to the api endpoint
		 */
		public function send_log() {
			wp_remote_post( $this->logging_url, array(
				'method'      => 'POST',
				'timeout'     => 3,
				'redirection' => 5,
				'headers'     => array(
					'X-ThemeIsle-Event' => 'log_site',
				),
				'body'        => array(
					'site'    => get_site_url(),
					'product' => $this->product->get_slug(),
					'version' => $this->product->get_version(),
				),
			) );
		}

        function dismiss() {
			check_ajax_referer( (string) __CLASS__, 'nonce' );

            update_option( $this->product->get_key() . '_logger_flag', $_POST['enable'] );
        }

        function show_notification() {
            $show   = get_option( $this->product->get_key() . '_logger_flag', true );
            if ( true === $show ) {
                error_log("showing logger");
                return true;
            }
            error_log("NOT showing logger");
            return false;
        }

        public function hide_notification() {
            $show   = get_option( $this->product->get_key() . '_logger_flag', true );
            if ( true === $show ) {
                error_log("hiding logger");
                // if the notification was showing and no action was taken, hide it
                update_option( $this->product->get_key() . '_logger_flag', 'no' );
            }
        }

	}
endif;
