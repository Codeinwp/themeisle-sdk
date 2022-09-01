<?php
/**
 * The compatibilities model class for ThemeIsle SDK
 *
 * @package     ThemeIsleSDK
 * @subpackage  Modules
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 * @since       1.0.0
 */

namespace ThemeisleSDK\Modules;

use ThemeisleSDK\Common\Abstract_Module;
use ThemeisleSDK\Product;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Promotions module for ThemeIsle SDK.
 */
class Compatibilities extends Abstract_Module {


	/**
	 * Should we load this module.
	 *
	 * @param Product $product Product object.
	 *
	 * @return bool
	 */
	public function can_load( $product ) {
		if ( $this->is_from_partner( $product ) ) {
			return false;
		}
		if ( $product->is_theme() && ! current_user_can( 'switch_themes' ) ) {
			return false;
		}

		if ( $product->is_plugin() && ! current_user_can( 'install_plugins' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Registers the hooks.
	 *
	 * @param Product $product Product to load.
	 *
	 * @return Compatibilities Module instance.
	 */
	public function load( $product ) {


		$this->product = $product;

		$compatibilities = apply_filters( 'themeisle_sdk_compatibilities/' . $this->product->get_slug(), [] );
		if ( empty( $compatibilities ) ) {
			return $this;
		}
		$requirement = null;
		foreach ( $compatibilities as $compatibility ) {

			if ( empty( $compatibility['basefile'] ) ) {
				return $this;
			}
			$requirement = new Product( $compatibility['basefile'] );
			if ( ! version_compare( $requirement->get_version(), $compatibility['required'], '<' ) ) {
				return $this;
			}
			break;
		}
		if ( empty( $requirement ) ) {
			return $this;
		}
		add_filter(
			'upgrader_pre_download',
			function ( $return, $package, $upgrader ) use ( $product, $requirement ) {
				/**
				 * Upgrader object.
				 *
				 * @var \WP_Upgrader $upgrader Upgrader object.
				 */
				$should_block = false;
				if ( $product->is_theme()
				 && property_exists( $upgrader, 'skin' )
				 && property_exists( $upgrader->skin, 'theme_info' )
				 && $upgrader->skin->theme_info->template === $product->get_slug() ) {
					$should_block = true;

				}
				if ( ! $should_block && $product->is_plugin()
				 && property_exists( $upgrader, 'skin' )
				 && property_exists( $upgrader->skin, 'plugin_info' )
				 && $upgrader->skin->plugin_info['Name'] === $product->get_name() ) {
					$should_block = true;
				}
				if ( $should_block ) {
					echo( sprintf(
						'New %s version is not compatible with your current version of %s. Please %supdate%s %s %s.',
						esc_attr( $product->get_friendly_name() ),
						esc_attr( $requirement->get_friendly_name() ),
						'<a href="' . esc_url( admin_url( $requirement->is_theme() ? 'themes.php' : 'plugins.php' ) ) . '">',
						'</a>',
						esc_attr( $requirement->get_friendly_name() ),
						esc_attr( $requirement->is_theme() ? 'theme' : 'plugin' )
					) );
					$upgrader->maintenance_mode( false );
					die();
				}

				return $return;
			},
			10,
			3 
		);

		add_action(
			'admin_notices',
			function () use ( $product, $requirement ) {
				echo '<div class="notice notice-error "><p>';
				echo( sprintf(
					'%s is not compatible with your current version of %s. Please %supdate%s %s %s to the latest version.',
					'<strong>' . esc_attr( $product->get_friendly_name() ) . '</strong>',
					'<strong>' . esc_attr( $requirement->get_friendly_name() ) . '</strong>',
					'<a href="' . esc_url( admin_url( $requirement->is_theme() ? 'themes.php' : 'plugins.php' ) ) . '">',
					'</a>',
					'<strong>' . esc_attr( $requirement->get_friendly_name() ) . '</strong>',
					esc_attr( $requirement->is_theme() ? 'theme' : 'plugin' )
				) );
				echo '</p></div>';
			} 
		);

		return $this;
	}

}
