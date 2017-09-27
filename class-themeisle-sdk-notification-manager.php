<?php
/**
 * The notification manager class for ThemeIsle SDK
 *
 * @package     ThemeIsleSDK
 * @subpackage  Notification
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 * @since       1.0.0
 */
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'ThemeIsle_SDK_Notification_Manager' ) ) :
	/**
	 * Notification manager model for ThemeIsle SDK.
	 */
	class ThemeIsle_SDK_Notification_Manager {
		/**
		 * Time between notifications.
		 */
		const NOTIFICATION_INTERVAL_HOURS = 100;
		/**
		 * @var array Notifications for the current product.
		 */
		private $notifications = array();
		/**
		 * @var ThemeIsle_SDK_Product Current product.
		 */
		private $product;
		/**
		 * @var array ThemeIsle_SDK_Feedback Feedbacks available.
		 */
		private $callbacks = array();

		/**
		 * ThemeIsle_SDK_Notification_Manager constructor.
		 *
		 * @param ThemeIsle_SDK_Product $product_object Product Object.
		 * @param array                 $callbacks the objects that will be called when a notification is due.
		 */
		public function __construct( $product_object, $callbacks ) {
			$this->product   = $product_object;
			$this->callbacks = $callbacks;
			$this->setup_hooks();
		}

		/**
		 * Setup the notifications.
		 */
		function setup_notifications() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			// Load the notifications only if we have it installed after the required interval.
			if ( ( time() - $this->product->get_install_time() ) > self::NOTIFICATION_INTERVAL_HOURS * HOUR_IN_SECONDS ) {
				if ( $this->product instanceof ThemeIsle_SDK_Product && $this->callbacks && is_array( $this->callbacks ) ) {
					foreach ( $this->callbacks as $instance ) {
						$this->notifications[ $this->product->get_key() . get_class( $instance ) ] = $instance;
					}
				}
			}
		}

		/**
		 * Setup the internal hooks
		 */
		private function setup_hooks() {
			add_action( 'admin_head', array( $this, 'show_notification' ) );
			add_action( 'admin_init', array( $this, 'setup_notifications' ) );
		}

		/**
		 * Shows the notification
		 */
		function show_notification() {
			$instances = $this->notifications;
			if ( empty( $instances ) ) {
				return;
			}

			$available = array_keys( $instances );
			// backward compatibility for people who already have "expired" notifications, let's not show them again.
			$hidden    = get_option( 'themeisle_sdk_notification_hidden', array() );
			if ( ! empty( $hidden ) ) {
				// get keys of the hidden notifications.
				$hidden_keys    = wp_list_pluck( $hidden, 'key' );
				$available      = array_diff( $available, $hidden_keys );
				if ( empty( $available ) ) {
					return;
				}
			}

			foreach ( $available as $key ) {
				$instance   = $instances[ $key ];
				if ( true === $instance->show_notification() ) {
					// if this notification is going to show, bail.
					break;
				}
			}
		}
	}
endif;
