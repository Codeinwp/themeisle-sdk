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
		add_action( $this->product->get_key() . '_upsell_products', array( $this, 'render_products_box' ), 10, 3 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Render products box content.
	 *
	 * @param array $plugins_list - list of useful plugins (in slug => nicename format).
	 * @param array $themes_list - list of useful themes (in slug => nicename format).
	 * @param array $preferences - list of preferences.
	 */
	function render_products_box( $plugins_list, $themes_list, $preferences = array() ) {
		if ( empty( $plugins_list ) && empty( $themes_list ) ) {
			return;
		}

		if ( ! empty( $plugins_list ) && ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		if ( ! empty( $themes_list ) && ! current_user_can( 'install_themes' ) ) {
			return;
		}

		if ( ! empty( $plugins_list ) ) {
			echo '<div class="upsell-product">';

			foreach ( $plugins_list as $plugin => $nicename ) {
				$current_plugin = $this->call_plugin_api( $plugin );

				$name = empty( $nicename ) ? $current_plugin->name : $nicename;

				$image = $current_plugin->banners['low'];
				if ( isset( $preferences['image'] ) && 'icon' === $preferences['image'] ) {
					$image = $current_plugin->icons['1x'];
				}

				echo '<div class="plugin_box">';
				echo '<img class="plugin-banner" src="' . $image . '">';
				echo '<div class="title-action-wrapper">';
				echo '<span class="plugin-name">' . esc_html( $name ) . '</span>';
				if ( ! isset( $preferences['description'] ) || ( isset( $preferences['description'] ) && $preferences['description'] ) ) {
					echo '<span class="plugin-desc">' . esc_html( $current_plugin->short_description ) . '</span>';
				}
				echo '</div>';
				echo '<div class="plugin-box-footer">';
				echo '<div class="button-wrap">';
				echo self::get_button_html( $plugin );
				echo '</div>';
				echo '<div class="version-wrapper"><span class="version">v' . esc_html( $current_plugin->version ) . '</span></div>';
				echo '</div>';
				echo '</div>';
			}

			echo '</div>';
		}

		if ( ! empty( $themes_list ) ) {
			echo '<div class="upsell-product">';

			foreach ( $themes_list as $slug => $nicename ) {
				$theme = $this->call_theme_api( $slug );
				if ( ! $theme ) {
					continue;
				}

				$url = add_query_arg(
					array(
						'theme' => $theme->slug,
					),
					network_admin_url( 'theme-install.php' )
				);

				$name = empty( $nicename ) ? $theme->name : $nicename;

				echo '<div class="plugin_box">';
				echo '<img class="plugin-banner" src="' . $theme->screenshot_url . '">';
				echo '<div class="title-action-wrapper">';
				echo '<span class="plugin-name">' . esc_html( $name ) . '</span>';
				if ( ! isset( $preferences['description'] ) || ( isset( $preferences['description'] ) && $preferences['description'] ) ) {
					echo '<span class="plugin-desc">' . esc_html( $theme->description ) . '</span>';
				}
				echo '</div>';
				echo '<div class="plugin-box-footer">';
				echo '<div class="button-wrap">';
				echo '<a class="button" href="' . esc_url( $url ) . '">' . esc_html__( 'Install' ) . '</a>';
				echo '</div>';
				echo '<div class="version-wrapper"><span class="version">v' . esc_html( $theme->version ) . '</span></div>';
				echo '</div>';
				echo '</div>';
			}

			echo '</div>';
		}
	}

	/**
	 * Call theme api
	 *
	 * @param string $slug theme slug.
	 *
	 * @return array|mixed|object
	 */
	private function call_theme_api( $slug ) {
		$products = wp_remote_get(
			'https://api.wordpress.org/themes/info/1.1/?action=query_themes&request[theme]=' . $slug . '&request[per_page]=1'
		);
		$products = json_decode( wp_remote_retrieve_body( $products ) );
		if ( is_object( $products ) ) {
			return $products->themes[0];
		}
		return null;
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
	 * Generate action button html.
	 *
	 * @param string $slug     plugin slug.
	 * @param array  $settings button settings.
	 *
	 * @return string
	 */
	private static function get_button_html( $slug, $settings = array() ) {
		$button   = '';
		$redirect = '';
		if ( ! empty( $settings ) && array_key_exists( 'redirect', $settings ) ) {
			$redirect = $settings['redirect'];
		}
		$state = self::check_plugin_state( $slug );
		if ( empty( $slug ) ) {
			return '';
		}

		$additional = '';

		if ( $state === 'deactivate' ) {
			$additional = ' action_button active';
		}

		$button .= '<div class=" plugin-card-' . esc_attr( $slug ) . esc_attr( $additional ) . '" style="padding: 8px 0 5px;">';

		$plugin_link_suffix = self::get_plugin_path( $slug );

		$nonce = add_query_arg(
			array(
				'action'        => 'activate',
				'plugin'        => rawurlencode( $plugin_link_suffix ),
				'plugin_status' => 'all',
				'paged'         => '1',
				'_wpnonce'      => wp_create_nonce( 'activate-plugin_' . $plugin_link_suffix ),
			),
			network_admin_url( 'plugins.php' )
		);
		switch ( $state ) {
			case 'install':
				$button .= '<a data-redirect="' . esc_url( $redirect ) . '" data-slug="' . esc_attr( $slug ) . '" class="install-now ti-install-plugin button  " href="' . esc_url( $nonce ) . '" data-name="' . esc_attr( $slug ) . '" aria-label="Install ' . esc_attr( $slug ) . '">' . __( 'Install and activate' ) . '</a>';
				break;

			case 'activate':
				$button .= '<a  data-redirect="' . esc_url( $redirect ) . '" data-slug="' . esc_attr( $slug ) . '" class="activate-now button button-primary" href="' . esc_url( $nonce ) . '" aria-label="Activate ' . esc_attr( $slug ) . '">' . esc_html__( 'Activate' ) . '</a>';
				break;

			case 'deactivate':
				$nonce = add_query_arg(
					array(
						'action'        => 'deactivate',
						'plugin'        => rawurlencode( $plugin_link_suffix ),
						'plugin_status' => 'all',
						'paged'         => '1',
						'_wpnonce'      => wp_create_nonce( 'deactivate-plugin_' . $plugin_link_suffix ),
					),
					network_admin_url( 'plugins.php' )
				);

				$button .= '<a  data-redirect="' . esc_url( $redirect ) . '" data-slug="' . esc_attr( $slug ) . '" class="deactivate-now button" href="' . esc_url( $nonce ) . '" data-name="' . esc_attr( $slug ) . '" aria-label="Deactivate ' . esc_attr( $slug ) . '">' . esc_html__( 'Deactivate' ) . '</a>';
				break;

			case 'enable_cpt':
				$url     = admin_url( 'admin.php?page=jetpack#/settings' );
				$button .= '<a  data-redirect="' . esc_url( $redirect ) . '" class="button" href="' . esc_url( $url ) . '">' . esc_html__( 'Activate' ) . ' ' . esc_html__( 'Jetpack Portfolio' ) . '</a>';
				break;
		}// End switch().
		$button .= '</div>';

		return $button;
	}

	/**
	 * Check plugin state.
	 *
	 * @param string $slug - plugin slug.
	 *
	 * @return bool
	 */
	private static function check_plugin_state( $slug ) {

		$plugin_link_suffix = self::get_plugin_path( $slug );

		if ( file_exists( ABSPATH . 'wp-content/plugins/' . $plugin_link_suffix ) ) {
			$needs = is_plugin_active( $plugin_link_suffix ) ? 'deactivate' : 'activate';
			if ( $needs === 'deactivate' && ! post_type_exists( 'portfolio' ) && $slug === 'jetpack' ) {
				return 'enable_cpt';
			}

			return $needs;
		} else {
			return 'install';
		}
	}


	/**
	 * Get plugin path based on plugin slug.
	 *
	 * @param string $slug - plugin slug.
	 *
	 * @return string
	 */
	private static function get_plugin_path( $slug ) {

		switch ( $slug ) {
			case 'mailin':
				return $slug . '/sendinblue.php';
				break;
			case 'wpforms-lite':
				return $slug . '/wpforms.php';
				break;
			case 'intergeo-maps':
			case 'visualizer':
			case 'translatepress-multilingual':
				return $slug . '/index.php';
				break;
			case 'beaver-builder-lite-version':
				return $slug . '/fl-builder.php';
				break;
			case 'adblock-notify-by-bweb':
				return $slug . '/adblock-notify.php';
				break;
			default:
				return $slug . '/' . $slug . '.php';
		}
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

		wp_register_script( 'ti-plugin-install', $this->product->get_base_url( 'vendor/codeinwp/themeisle-sdk/assets/js/plugin-install.js' ), array( 'jquery' ), Loader::get_version(), true );

		wp_localize_script(
			'ti-plugin-install',
			'tiPluginInstall',
			array(
				'activating' => esc_html__( 'Activating ' ),
			)
		);

		wp_enqueue_script( 'plugin-install' );
		wp_enqueue_script( 'updates' );
		wp_enqueue_script( 'ti-plugin-install' );
	}
}
