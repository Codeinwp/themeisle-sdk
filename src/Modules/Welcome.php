<?php
/**
 * The welcome model class for ThemeIsle SDK
 *
 * Here's how to hook it in your plugin or theme:
 * ```php
 *      add_filter( '<product_slug>_welcome_metadata', function() {
 *          return [
 *               'is_enabled' => <condition_if_pro_available>,
 *               'cta_link' => tsdk_utmify( 'https://link_to_upgrade.with/?discount=<discountCode>')
 *          ];
 *      } );
 * ```
 *
 * @package     ThemeIsleSDK
 * @subpackage  Modules
 * @copyright   Copyright (c) 2023, Bogdan Preda
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 * @since       1.0.0
 */

namespace ThemeisleSDK\Modules;

// Exit if accessed directly.
use ThemeisleSDK\Common\Abstract_Module;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Promotions module for ThemeIsle SDK.
 */
class Welcome extends Abstract_Module {

	/**
	 * Debug mode.
	 *
	 * @var bool
	 */
	private $debug = false;

	/**
	 * Welcome metadata.
	 *
	 * @var array
	 */
	private $welcome_discounts = array();

	/**
	 * Check that we can load this module.
	 *
	 * @param \ThemeisleSDK\Product $product The product.
	 *
	 * @return bool
	 */
	public function can_load( $product ) {
		$this->debug      = apply_filters( 'themeisle_sdk_welcome_debug', $this->debug );
		$welcome_metadata = apply_filters( $product->get_key() . '_welcome_metadata', array() );

		$is_welcome_enabled = $this->is_welcome_meta_valid( $welcome_metadata );

		if ( $is_welcome_enabled ) {
			$this->welcome_discounts[ $product->get_key() ] = $welcome_metadata;
		}

		return $this->debug || $is_welcome_enabled;
	}

	/**
	 * Check that the metadata is valid and the welcome is enabled.
	 *
	 * @param array $welcome_metadata The metadata to validate.
	 *
	 * @return bool
	 */
	private function is_welcome_meta_valid( $welcome_metadata ) {
		return ! empty( $welcome_metadata ) && isset( $welcome_metadata['is_enabled'] ) && $welcome_metadata['is_enabled'];
	}

	/**
	 * Load the module.
	 *
	 * @param \ThemeisleSDK\Product $product The product.
	 *
	 * @return $this
	 */
	public function load( $product ) {
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		$this->product = $product;
		if ( ! $this->is_time_to_show_welcome() && $this->debug === false ) {
			return;
		}

		add_filter( 'themeisle_sdk_registered_notifications', [ $this, 'add_notification' ], 99, 1 );

		return $this;
	}

	/**
	 * Check if it's time to show the welcome.
	 *
	 * @return bool
	 */
	private function is_time_to_show_welcome() {
		// if 7 days from install have not passed, don't show the welcome.
		if ( $this->product->get_install_time() + 7 * DAY_IN_SECONDS > time() ) {
			return false;
		}

		// if 12 days from install have passed, don't show the welcome ( after 7 days for 5 days ).
		if ( $this->product->get_install_time() + 12 * DAY_IN_SECONDS < time() ) {
			return false;
		}

		return true;
	}

	/**
	 * Add the welcome notification.
	 * Will block all other notifications if a welcome notification is present.
	 *
	 * @return array
	 */
	public function add_notification( $all_notifications ) {
		if ( empty( $this->welcome_discounts ) ) {
			return $all_notifications;
		}

		if ( ! isset( $this->welcome_discounts[ $this->product->get_key() ] ) ) {
			return $all_notifications;
		}

		// filter out the notifications that are not welcome upsells
		// if we arrived here we will have at least one welcome upsell
		$all_notifications = array_filter(
			$all_notifications,
			function( $notification ) {
				return strpos( $notification['id'], '_welcome_upsell_flag' ) !== false;
			} 
		);

		$offer = $this->welcome_discounts[ $this->product->get_key() ];

		$response = [];

		$logos = $this->get_logos();
		$logo  = isset( $logos[ $this->product->get_key() ] ) ? $logos[ $this->product->get_key() ] : '';

		$link = $offer['cta_link'];

		$message = apply_filters( $this->product->get_key() . '_welcome_upsell_message', '<p>You\'ve been using <b>{product}</b> for 7 days now and we appreciate your loyalty! We also want to make sure you\'re getting the most out of our product. That\'s why we\'re offering you a special deal - upgrade to <b>{product} PRO</b> in the next 5 days and receive a discount of <b>up to 30%</b>. <a href="{cta_link}" target="_blank">Upgrade now</a> and unlock all the amazing features of <b>{product} PRO</b>!</p>' );

		$button_submit = apply_filters( $this->product->get_key() . '_feedback_review_button_do', 'Upgrade Now!' );
		$button_cancel = apply_filters( $this->product->get_key() . '_feedback_review_button_cancel', 'No, thanks.' );
		$message       = str_replace(
			[ '{product}', '{cta_link}' ],
			[
				$this->product->get_friendly_name(),
				$link,
			],
			$message
		);

		$all_notifications[] = [
			'id'      => $this->product->get_key() . '_welcome_upsell_flag',
			'message' => $message,
			'img_src' => $logo,
			'ctas'    => [
				'confirm' => [
					'link' => $link,
					'text' => $button_submit,
				],
				'cancel'  => [
					'link' => '#',
					'text' => $button_cancel,
				],
			],
			'type'    => 'info',
		];

		$key        = array_rand( $all_notifications );
		$response[] = $all_notifications[ $key ];

		return $response;
	}


	/**
	 * Get the logos for defined products.
	 *
	 * @return array
	 */
	private function get_logos() {
		$neve_svg   = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEoAAABKCAYAAAAc0MJxAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAK7SURBVHgB7ZtNbhpBEEarmB7JSx+B3MAjQaTs7Bs4R2ARwpKcIOQEdnYIFuQGdk6Ad4kEEtwAjkA2kaP5qXQrTpSVXba6p6tRvQ2DBmmkp55vaqoLNMMVgfIkHVBYqCgmKoqJimKiopioKCYqiomKYqKimKgoJiqKiYpioqKYqCgmKoqJimKiopiEEYVwB0eGgQAYNIOS6jMEWtivp+ARIho8dr6DeGp721fgGQzRMzcd8+p+WuxPRt+6NWVLIuyCJ6pZHx87765ZNWYHngmaUffTN/sM6wt7uIXECR7mTpZdBQUQfoaEae2pV817Y/vxCRKl1fLArqyJveIFIu0hMVqvo6pp/87lVmqyohScLrfKX3lhD28hEeJV5l+Kg70V30IiuRX9Fcblli2MPoBwRLzrlbP+NRIVknNLzEtxOX+9lRzyoroH/0JeYHEqr83iQl5gcSq2H+VCngDdU/EAAhDduKtnvVvTqUSEvPgOp5QORJDGnW+cLPtRmHfra4hEtBVlG4Yf4Zk8hHwUYt56E/N+tTwZbbqQAHEziuC8buokZEUPcwKyPe5qkw3XlyAYKU+9U7tjc/OS3GoLaeXBJB+uvG81+UBcHWX3zsb5cL2TllsiC06XW9JCXmxl/hDyu3z4PVrt9D/iX2EIsisJIZ/KNIsrTm9gvPE6x/Ac0hn7IbjMf9abWLmV1HzU35A3o9U5tExyg2ROFjSwbDu3Up64a7U4TXo00RWndmW1klvHMMN51kZxGmTiDhHHTdP8eOI3C/DLwW5GDPJOuQ0xcYdH93d+N2hr+1zgmeMbnw4gyaFz5kxUFBMVxSTIvh7nqReKUAP5QURlmH0t5709RCD/M5DvXZTeekyCrKiKqoXtH0EMqgaC9KzCzB4EqmViorceExXFREUxUVFMVBQTFcVERTFRUUxUFJPfRE/rd+Zjqc0AAAAASUVORK5CYII=';
		$otter_svg  = 'data:image/png;base64,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';
		$feedzy_svg = 'data:image/png;base64,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';



		return [
			'neve'         => $neve_svg,
			'otter_blocks' => $otter_svg,
			'feedzy'       => $feedzy_svg,
		];
	}

}
