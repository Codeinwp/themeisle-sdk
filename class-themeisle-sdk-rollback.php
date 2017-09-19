<?php
/**
 * The rollback class for ThemeIsle SDK
 *
 * @package     ThemeIsleSDK
 * @subpackage  Rollback
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 * @since       1.0.0
 */
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'ThemeIsle_SDK_Rollback' ) ) :
	/**
	 * Rollback for ThemeIsle SDK.
	 */
	class ThemeIsle_SDK_Rollback {

		/**
		 * @var ThemeIsle_SDK_Product $product Themeisle Product.
		 */
		protected $product;


		/**
		 * ThemeIsle_SDK_Rollback constructor.
		 *
		 * @param ThemeIsle_SDK_Product $product_object Product Object.
		 */
		public function __construct( $product_object ) {
			if ( $product_object instanceof ThemeIsle_SDK_Product ) {
				$this->product      = $product_object;
			}
			if ( 'plugin' === $this->product->get_type() && $this->product->can_rollback() ) {
				$this->show_link();
				$this->add_hooks();
			}
		}

		/**
		 * Set the rollback hook. Strangely, this does not work if placed in the ThemeIsle_SDK_Rollback class, so it is being called from there instead.
		 *
		 */
		public function add_hooks(){
			add_action( 'admin_post_' . $this->product->get_key() . '_rollback', array( $this, 'start_rollback' ) );
		}

		/**
		 * If product can be rolled back, show the link to rollback.
		 *
		 * @return string The link to rollback.
		 */
		private function show_link() {
			add_filter( 'plugin_action_links_' . plugin_basename( $this->product->get_basefile() ), array( $this, 'add_rollback_link' ) );
		}

		/**
		 * Show the rollback links in the plugin page.
		 *
		 * @return array The links.
		 */
		public function add_rollback_link( $links ) {
			$version	= $this->product->get_rollback();
			$links[]	= '<a href="' . wp_nonce_url( admin_url( 'admin-post.php?action=' . $this->product->get_key() . '_rollback' ), $this->product->get_key() . '_rollback' ) . '">' . sprintf( apply_filters( $this->product->get_key() . '_rollback_label',  'Rollback to v%s' ), $version['version'] ) . '</a>';
			return $links;
		}

		/**
		 * Start the rollback operation.
		 *
		 */
		public function start_rollback() {
			if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], $this->product->get_key() . '_rollback' ) ) {
				wp_nonce_ays( '' );
			}

			$rollback			= $this->product->get_rollback();
			$plugin_transient 	= get_site_transient( 'update_plugins' );
			$plugin_folder    	= $this->product->get_slug();
			$plugin_file      	= $this->product->get_file();
			$version          	= $rollback['version'];
			$temp_array 		= array(
				'slug'        => $plugin_folder,
				'new_version' => $version,
				'package'     => $rollback['url'],
			);

			$temp_object = (object) $temp_array;
			$plugin_transient->response[ $plugin_folder . '/' . $plugin_file ] = $temp_object;
			set_site_transient( 'update_plugins', $plugin_transient );

			$transient = get_transient( $this->product->get_key() . '_warning_rollback' );

			if ( false === $transient )	{
				set_transient( $this->product->get_key() . '_warning_rollback', 'in progress', 30 );
				require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
				$title = sprintf( apply_filters( $this->product->get_key() . '_rollback_message', 'Rolling back %s to %s' ), $this->product->get_name(), $version );
				$plugin = $plugin_folder . '/' . $plugin_file;
				$nonce = 'upgrade-plugin_' . $plugin;
				$url = 'update.php?action=upgrade-plugin&plugin=' . urlencode( $plugin );
				$upgrader_skin = new Plugin_Upgrader_Skin( compact( 'title', 'nonce', 'url', 'plugin' ) );
				$upgrader = new Plugin_Upgrader( $upgrader_skin );
				$upgrader->upgrade( $plugin );
				delete_transient( $this->product->get_key() . '_warning_rollback' );
				wp_die( '', $title, array( 'response' => 200 ) );
			}
		}
	}
endif;
