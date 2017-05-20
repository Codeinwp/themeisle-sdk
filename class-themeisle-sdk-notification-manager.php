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
			if ( $product_object instanceof ThemeIsle_SDK_Product && $callbacks && is_array( $callbacks ) ) {
                $instances      = get_option( 'ti_sdk_notifications', array() );
				foreach ( $callbacks as $instance ) {
                    $instances[ $product_object->get_key() . get_class( $instance ) ]   = $instance;
				}
                update_option( 'ti_sdk_notifications', $instances );
			}

            $this->setup_hooks();
		}

        private function setup_hooks()
        {
            add_action( 'admin_head', array( $this, 'show_notification' ) );
        }

        function show_notification()
        {
            $last       = get_option( 'ti_sdk_notification_last', array() );
            $instances      = get_option( 'ti_sdk_notifications' );

            if ( ! $last || ( is_array( $last ) && time() - $last['time'] > self::NOTIFICATION_INTERVAL_HOURS * HOUR_IN_SECONDS ) ) {
                $last_key       = $last ? $last['key'] : null;
                if ( $instances ) {
                    $keys       = array_keys( $instances );
                    $values     = array_values( $instances );
                    $index      = $last ? array_search( $last_key, $keys ) : -1;
                    $now_class  = $index >= count( $values ) - 1 ? $values[ 0 ] : $values[ $index + 1 ];
                    $now_key    = $index >= count( $keys ) - 1 ? $keys[ 0 ] : $keys[ $index + 1 ];
                    if ( $last_key ) {
                        $last_class = $instances[ $last_key ];
                        $last_class->hide_notification();
                        unset( $instances[ $last_key ] );
                    }
                    $already_showing    = get_transient( 'ti_sdk_notification_showing' );
                    if ( $now_key !== $already_showing && $now_class->show_notification() ) {
                        set_transient( 'ti_sdk_notification_showing', $now_key, 10 );
                        update_option( 'ti_sdk_notification_last', array( 'time' => time(), 'key' => $now_key ) );
                    }
                }
            } elseif ( $last && $instances ) {
                $now_key    = $last['key'];
                $now_class  = $instances[ $now_key ];
                $already_showing    = get_transient( 'ti_sdk_notification_showing' );
                if ( $now_key !== $already_showing && $now_class->show_notification() ) {
                    set_transient( 'ti_sdk_notification_showing', $now_key, 10 );
                    update_option( 'ti_sdk_notification_last', array( 'time' => time(), 'key' => $now_key ) );
                }
            }
        }
	}
endif;
