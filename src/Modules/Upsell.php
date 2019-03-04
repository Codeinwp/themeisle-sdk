<?php
/**
 * The class that exposes hooks for upsell.
 *
 * @package     ThemeIsleSDK
 * @subpackage  Rollback
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 * @since       1.0.0
 */

namespace ThemeisleSDK\Modules;

// Exit if accessed directly.
use ThemeisleSDK\Common\Abstract_Module;
use ThemeisleSDK\Loader;
use ThemeisleSDK\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Expose endpoints for ThemeIsle SDK.
 */
class Upsell extends Abstract_Module {


	/**
	 * Load module logic.
	 *
	 * @param Product $product Product to load.
	 */
	public function load( $product ) {
		$this->product = $product;
		$this->setup_hooks();

		return $this;
	}

	/**
	 * Check if we should load the module for this product.
	 *
	 * @param Product $product Product data.
	 *
	 * @return bool Should we load the module?
	 */
	public function can_load( $product ) {
		return true;
	}

	/**
	 * Setup endpoints.
	 */
	private function setup_hooks() {
		add_action( $this->product->get_key() . '_upsell_products', array( $this, 'render_products_box' ), 10, 4 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Render products box content.
	 *
	 * @param array $plugins_list - list of useful plugins (in slug => nicename format).
	 * @param array $themes_list - list of useful themes (in slug => nicename format).
	 * @param array $string - list of translated strings.
	 * @param array $preferences - list of preferences.
	 */
	function render_products_box( $plugins_list, $themes_list, $strings, $preferences = array() ) {
		if ( empty( $plugins_list ) && empty( $themes_list ) ) {
			return;
		}

		if ( ! empty( $plugins_list ) && ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		if ( ! empty( $themes_list ) && ! current_user_can( 'install_themes' ) ) {
			return;
		}

		add_thickbox();

		if ( ! empty( $plugins_list ) ) {
			$list = $this->get_plugins( $plugins_list, $preferences );

			if ( has_action( $this->product->get_key() . '_upsell_products_plugin_template' ) ) {
				do_action( $this->product->get_key() . '_upsell_products_plugin_template', $list, $strings, $preferences );
			} else {
				echo '<div class="upsell-product">';

				foreach ( $list as $current_plugin ) {
					echo '<div class="plugin_box">';
					echo '<img class="plugin-banner" src="' . $current_plugin->custom_image . '">';
					echo '<div class="title-action-wrapper">';
					echo '<span class="plugin-name">' . esc_html( $current_plugin->custom_name ) . '</span>';
					if ( ! isset( $preferences['description'] ) || ( isset( $preferences['description'] ) && $preferences['description'] ) ) {
						echo '<span class="plugin-desc">' . esc_html( $current_plugin->short_description ) . '</span>';
					}
					echo '</div>';
					echo '<div class="plugin-box-footer">';
					echo '<a class="button thickbox open-plugin-details-modal" href="' . esc_url( $current_plugin->custom_url ) . '">' . esc_html( $strings['install'] ) . '</a>';
					echo '</div>';
					echo '<div class="version-wrapper"><span class="version">v' . esc_html( $current_plugin->version ) . '</span></div>';
					echo '</div>';
				}

				echo '</div>';
			}
		}

		if ( ! empty( $themes_list ) ) {
			$list = $this->get_themes( $themes_list, $preferences );

			if ( has_action( $this->product->get_key() . '_upsell_products_theme_template' ) ) {
				do_action( $this->product->get_key() . '_upsell_products_theme_template', $list, $strings, $preferences );
			} else {
				echo '<div class="upsell-product">';

				foreach ( $list as $theme ) {
					echo '<div class="plugin_box">';
					echo '<img class="plugin-banner" src="' . $theme->screenshot_url . '">';
					echo '<div class="title-action-wrapper">';
					echo '<span class="plugin-name">' . esc_html( $theme->custom_name ) . '</span>';
					if ( ! isset( $preferences['description'] ) || ( isset( $preferences['description'] ) && $preferences['description'] ) ) {
						echo '<span class="plugin-desc">' . esc_html( $theme->description ) . '</span>';
					}
					echo '</div>';
					echo '<div class="plugin-box-footer">';
					echo '<div class="button-wrap">';
					echo '<a class="button" href="' . esc_url( $theme->custom_url ) . '">' . esc_html( $strings['install'] ) . '</a>';
					echo '</div>';
					echo '<div class="version-wrapper"><span class="version">v' . esc_html( $theme->version ) . '</span></div>';
					echo '</div>';
				}

				echo '</div>';
			}
		}
	}

	/**
	 * Collect all the information for the plugins list.
	 *
	 * @param array $plugins_list - list of useful plugins (in slug => nicename format).
	 * @param array $preferences - list of preferences.
	 *
	 * @return array
	 */
	private function get_plugins( $plugins_list, $preferences ) {
		$list = array();
		foreach ( $plugins_list as $plugin => $nicename ) {
			$current_plugin = $this->call_plugin_api( $plugin );

			$name = empty( $nicename ) ? $current_plugin->name : $nicename;

			$image = $current_plugin->banners['low'];
			if ( isset( $preferences['image'] ) && 'icon' === $preferences['image'] ) {
				$image = $current_plugin->icons['1x'];
			}

			$url = add_query_arg(
				array(
					'tab'       => 'plugin-information',
					'plugin'    => $current_plugin->slug,
					'TB_iframe' => true,
					'width'     => 600,
					'height'    => 500,
				),
				network_admin_url( 'plugin-install.php' )
			);

			$current_plugin->custom_url   = $url;
			$current_plugin->custom_name  = $name;
			$current_plugin->custom_image = $image;

			$list[] = $current_plugin;
		}
		return $list;
	}

	/**
	 * Collect all the information for the themes list.
	 *
	 * @param array $themes_list - list of useful themes (in slug => nicename format).
	 * @param array $preferences - list of preferences.
	 *
	 * @return array
	 */
	private function get_themes( $themes_list, $preferences ) {
		$list = array();
		foreach ( $themes_list as $slug => $nicename ) {
			$theme = $this->call_theme_api( $slug );
			if ( ! $theme ) {
				continue;
			}

			$url = add_query_arg(
				array(
					'theme'     => $theme->slug,
					'TB_iframe' => true,
					'width'     => 600,
					'height'    => 500,
				),
				network_admin_url( 'theme-install.php' )
			);

			$name = empty( $nicename ) ? $theme->name : $nicename;

			$theme->custom_url  = $url;
			$theme->custom_name = $name;

			$list[] = $theme;
		}
		return $list;
	}

	/**
	 * Call theme api
	 *
	 * @param string $slug theme slug.
	 *
	 * @return array|mixed|object
	 */
	private function call_theme_api( $slug ) {
		$theme = get_transient( 'ti_theme_info_' . $slug );

		if ( false !== $theme ) {
			return $theme;
		}

		$products = wp_remote_get(
			'https://api.wordpress.org/themes/info/1.1/?action=query_themes&request[theme]=' . $slug . '&request[per_page]=1'
		);
		$products = json_decode( wp_remote_retrieve_body( $products ) );
		if ( is_object( $products ) ) {
			$theme = $products->themes[0];
			set_transient( 'ti_theme_info_' . $slug, $theme, 6 * HOUR_IN_SECONDS );
		}
		return $theme;
	}

	/**
	 * Call plugin api
	 *
	 * @param string $slug plugin slug.
	 *
	 * @return array|mixed|object
	 */
	private function call_plugin_api( $slug ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

		$call_api = get_transient( 'ti_plugin_info_' . $slug );

		if ( false === $call_api ) {
			$call_api = plugins_api(
				'plugin_information',
				array(
					'slug'   => $slug,
					'fields' => array(
						'downloaded'        => false,
						'rating'            => false,
						'description'       => false,
						'short_description' => true,
						'donate_link'       => false,
						'tags'              => false,
						'sections'          => true,
						'homepage'          => true,
						'added'             => false,
						'last_updated'      => false,
						'compatibility'     => false,
						'tested'            => false,
						'requires'          => false,
						'downloadlink'      => false,
						'icons'             => true,
						'banners'           => true,
					),
				)
			);
			set_transient( 'ti_plugin_info_' . $slug, $call_api, 30 * MINUTE_IN_SECONDS );
		}

		return $call_api;
	}

	/**
	 * Load css and scripts for the plugin upsell page.
	 */
	public function enqueue() {
		$screen = get_current_screen();

		if ( ! isset( $screen->id ) ) {
			return;
		}

		if ( false === apply_filters( $this->product->get_key() . '_enqueue_upsell', false, $screen->id ) ) {
			return;
		}

		wp_enqueue_style( 'ti-plugin-style', $this->product->get_base_url( 'vendor/codeinwp/themeisle-sdk/assets/css/upsell.css' ), array(), Loader::get_version() );

		wp_enqueue_script( 'plugin-install' );
	}
}
