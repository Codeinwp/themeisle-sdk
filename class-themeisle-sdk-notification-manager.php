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

        const NOTIFICATION_INTERVAL_HOURS       = 1;

		/**
		 * ThemeIsle_SDK_Notification_Manager constructor.
		 *
		 * @param ThemeIsle_SDK_Product $product_object Product Object.
		 * @param array                 $callbacks the objects that will be called when a notification is due
		 */
		public function __construct( $product_object, $callbacks )
        {
			if ( $product_object instanceof ThemeIsle_SDK_Product && $product_object->is_wordpress_available() && $callbacks && is_array( $callbacks ) ) {
                $instances      = get_option( 'ti_sdk_notifications', array() );
				foreach ( $callbacks as $instance ) {
                     $instances[ $product_object->get_key() . get_class( $instance ) ]   = $instance;
				}
                update_option( 'ti_sdk_notifications', $instances );
                error_log('__construct ti_sdk_notifications = ' . print_r($instances,true));
			}

            $this->setup_hooks();
		}

        private function setup_hooks()
        {
            add_action( 'admin_head', array( $this, 'show_notification' );
        }

        private function show_notification()
        {
            $last       = get_option( 'ti_sdk_notification_last', array() );
                error_log('show_notification ti_sdk_notification_last = ' . print_r($last,true));

            if ( ! $last || ( is_array( $last ) && time() - $last['time'] > self::NOTIFICATION_INTERVAL_HOURS ) ) {
                $last_class     = $last ? $last['class'] : null;
                $instances      = get_option( 'ti_sdk_notifications' );
                if ( $instances ) {
                    $keys       = array_keys( $instances );
                    $values     = array_values( $instances );
                    $index      = $last ? array_search( $last_class, $keys ) : -1;
error_log("index $index");
                    $now_class  = $index >= count( $values ) ? $values[ 0 ] ? $values[ $index + 1 ];
error_log("now_class " . print_r($now_class,true));
                    $last_class->hide_notification();
                    if ( $now_class->show_notification() ) {
                        update_option( 'ti_sdk_notification_last', array( 'time' => time(), 'class' => $now_class ) );
                error_log('show_notification ti_sdk_notification_last set to = ' . print_r(array( 'time' => time(), 'class' => $now_class ),true));
                    }
                }
            }
        }
	}
endif;
