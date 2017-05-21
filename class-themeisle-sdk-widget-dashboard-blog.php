<?php
/**
 * The blog dashboard model class for ThemeIsle SDK
 *
 * @package     ThemeIsleSDK
 * @subpackage  Widgets
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 * @since       1.0.0
 */
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'ThemeIsle_SDK_Widget_Dashboard_Blog' ) ) :
	/**
	 * Blog dashboard widget model for ThemeIsle SDK.
	 */
	class ThemeIsle_SDK_Widget_Dashboard_Blog extends ThemeIsle_SDK_Widget {

		/**
		 * @var array instance The instances.
		 */
		private static $_instance;

		/**
		 * ThemeIsle_SDK_Widget_Dashboard_Blog constructor.
		 *
		 * @param ThemeIsle_SDK_Product $product_object The product object.
		 */
		public function __construct( $product_object ) {
			self::$_instance        = $this;
			parent::__construct( $product_object );
		}

		/**
		 * Returns the singleton instance
		 */
		public static function instance() {
			return self::$_instance;
		}

		/**
		 * Registers the hooks
		 */
		public function setup_hooks_child() {
			$already_showing        = get_transient( (string) __CLASS__ );
			if ( $already_showing ) {
				return;
			}

			set_transient( (string) __CLASS__, true, 10 );
			$this->setup_vars();
			add_action( 'wp_dashboard_setup', array( &$this, 'add_widget' ) );
			add_action( 'wp_network_dashboard_setup', array( &$this, 'add_widget' ) );
		}

		/**
		 * Setup class variables
		 */
		function setup_vars() {
			$this->dashboard_name = apply_filters( 'themeisle_sdk_dashboard_widget_name', 'WordPress Guides/Tutorials' );
			$this->feeds          = apply_filters( 'themeisle_sdk_dashboard_widget_feeds', array(
				'https://themeisle.com/blog/feed'
			) );
			$abs                  = untrailingslashit( ( dirname( __FILE__ ) ) );
			$parts                = str_replace( untrailingslashit( ABSPATH ), '', $abs );
			$parts                = explode( DIRECTORY_SEPARATOR, $parts );
			$parts                = array_filter( $parts );
			$this->script_url     = site_url() . '/' . implode( '/', $parts );
		}

		/**
		 * Add widget to the dashboard
		 *
		 * @return string|void
		 */
		function add_widget() {
			global $wp_meta_boxes;
			if ( isset( $wp_meta_boxes['dashboard']['normal']['core']['themeisle'] ) ) {
				return;
			}
			// Load SimplePie Instance
			$feed = fetch_feed( $this->feeds );
			// TODO report error when is an error loading the feed
			if ( is_wp_error( $feed ) ) {
				return '';
			}

			$feed->enable_cache( true );
			$feed->enable_order_by_date( true );
			$feed->set_cache_class( 'WP_Feed_Cache' );
			$feed->set_file_class( 'WP_SimplePie_File' );
			$feed->set_cache_duration( apply_filters( 'wp_feed_cache_transient_lifetime', 7200, $this->feeds ) );
			do_action_ref_array( 'wp_feed_options', array( $feed, $this->feeds ) );
			$feed->strip_comments( true );
			$feed->strip_htmltags( array(
				'base',
				'blink',
				'body',
				'doctype',
				'embed',
				'font',
				'form',
				'frame',
				'frameset',
				'html',
				'iframe',
				'input',
				'marquee',
				'meta',
				'noscript',
				'object',
				'param',
				'script',
				'style',
			) );
			$feed->init();
			$feed->handle_content_type();
			$items = $feed->get_items( 0, 5 );
			foreach ( (array) $items as $item ) {
				$this->items[] = array(
					'title' => $item->get_title(),
					'date'  => $item->get_date( 'U' ),
					'link'  => $item->get_permalink(),
				);
			}
			wp_add_dashboard_widget( 'themeisle', $this->dashboard_name, array(
				&$this,
				'render_dashboard_widget',
			) );
		}

		/**
		 * Render widget content
		 */
		function render_dashboard_widget() {
			?>
			<style type="text/css">
				#themeisle h2.hndle {
					background-image: url(<?php echo $this->script_url; ?>/logo.png);
					background-repeat: no-repeat;
					background-position: 90% 50%;
					background-size: 29px;
				}

				.ti-dw-feed-item {
					display: flex;
					align-items: center;
				}

				.ti-dw-feed-item a {
					float: left;
					width: 89.9%;
				}

				.ti-dw-feed-item .ti-dw-day-container {
					width: 100%;
					letter-spacing: 3px;
					display: block;
				}

				.ti-dw-feed-item .ti-dw-month-container {

					width: 100%;
					display: block;
					font-weight: 600;
					padding: 0px;
					margin-top: -6px;
					text-transform: uppercase;
					font-size: 10px;
					letter-spacing: 1px;
				}

				.ti-dw-feed-item .ti-dw-date-container {
					float: left;
					min-height: 30px;
					margin-right: 0.1%;
					width: 10%;
					text-align: center;
				}

			</style>
			<ul>
				<?php
				foreach ( $this->items as $item ) {
					?>
					<li class="ti-dw-feed-item"><span class="ti-dw-date-container"><span
									class="ti-dw-day-container"><?php echo date( 'd', $item['date'] ); ?></span> <span
									class="ti-dw-month-container"><?php echo substr( date( 'M', $item['date'] ), 0, 3 ); ?></span></span><a
								href="<?php echo add_query_arg(
									array(
										'utm_campaign' => 'feed',
										'utm_medium'   => 'dashboard_widget',
								), $item['link'] ); ?>" target="_blank"><?php echo $item['title']; ?></a>
						<div class="clear"></div>
					</li>
					<?php
				}

					$recommend_plugin       = apply_filters( 'ti_dw_recommend_plugin', array() );
				if ( $recommend_plugin && is_array( $recommend_plugin ) ) {
					add_thickbox();
				?>
				<p>
				<span class="ti-dw-recommend-plugin"><?php _e( 'Popular Plugin' );?>: </span>
				<?php echo $recommend_plugin['name'];?> (<a class="thickbox open-plugin-details-modal" href="<?php echo admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $recommend_plugin['slug'] . '&TB_iframe=true&width=600&height=500' );?>"><?php _e( 'Install' );?></a>)
				</p>

				<?php
				}
				?>
			</ul>

			<?php

		}
	}
endif;

if ( ! function_exists( 'themeisle_dashboard_widget' ) ) {
	/**
	 * The helper method to run the class
	 *
	 * @return the instance.
	 */
	function themeisle_dashboard_widget() {
		return ThemeIsle_SDK_Widget_Dashboard_Blog::instance();
	}
}

themeisle_dashboard_widget();
