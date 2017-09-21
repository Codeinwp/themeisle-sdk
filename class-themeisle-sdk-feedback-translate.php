<?php
/**
 * The Translate feedback model class for ThemeIsle SDK
 *
 * @package     ThemeIsleSDK
 * @subpackage  Feedback
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 * @since       1.0.0
 */
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'ThemeIsle_SDK_Feedback_Translate' ) ) :
	/**
	 * Translate feedback model for ThemeIsle SDK.
	 */
	class ThemeIsle_SDK_Feedback_Translate extends ThemeIsle_SDK_Feedback {

		/**
		 * @var string $heading The heading of the modal
		 */
		private $heading = 'Translate {product}!';

		/**
		 * @var string $button_cancel The text of the cancel button
		 */
		private $button_cancel = 'No, thanks.';
		/**
		 * @var string $button_already The text of the already did it button
		 */
		private $button_do = 'Ok, I will gladly help.';

		/**
		 * ThemeIsle_SDK_Feedback_Translate constructor.
		 *
		 * @param ThemeIsle_SDK_Product $product_object The product object.
		 */
		public function __construct( $product_object ) {
			parent::__construct( $product_object );
		}

		/**
		 * Registers the hooks
		 */
		public function setup_hooks_child() {
			add_action( 'wp_ajax_' . $this->product->get_key() . __CLASS__, array( $this, 'dismiss' ) );
		}

		/**
		 * Shows the notification
		 */
		function show_notification() {
			if ( ! $this->product->is_wordpress_available() ) {
				$this->disable();

				return false;
			}
			$show = get_option( $this->product->get_key() . '_translate_flag', 'yes' );
			if ( 'no' === $show ) {
				return false;
			}
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );

			return true;
		}

		/**
		 * Shows the admin notice
		 */
		function admin_notices() {
			$id = $this->product->get_key() . '_translate';

			$this->add_css( $this->product->get_key() );
			$this->add_js( $this->product->get_key() );

			echo '<div class="notice notice-success is-dismissible" id="' . $id . '" ><div class="themeisle-translate-box">' . $this->get_html( $this->product->get_key() ) . '</div></div>';
		}

		/**
		 * Loads the css
		 *
		 * @param string $key The product key.
		 */
		function add_css( $key ) {
			?>
			<style type="text/css" id="<?php echo $key; ?>ti-translate-css">
			</style>
			<?php
		}

		/**
		 * Loads the js
		 *
		 * @param string $key The product key.
		 */
		function add_js( $key ) {
			?>
			<script type="text/javascript" id="<?php echo $key; ?>ti-translate-js">
				(function ($) {
					$(document).ready(function () {
						$('#<?php echo $key; ?>_translate').on('click', '.notice-dismiss, .review-dismiss', function (e) {

							$.ajax({
								url: ajaxurl,
								method: "post",
								data: {
									'nonce': '<?php echo wp_create_nonce( (string) __CLASS__ ); ?>',
									'action': '<?php echo $this->product->get_key() . __CLASS__; ?>'
								},
								success: function () {
									$('#<?php echo $key; ?>_translate').hide();
								}
							});
						});
					});
				})(jQuery);
			</script>
			<?php
		}

		/**
		 * Generates the HTML
		 *
		 * @param string $key The product key.
		 */
		function get_html( $key ) {
			$lang	 = defined( 'WP_LANG' ) ? WP_LANG : get_option('WPLANG', 'en_US');
			if ( 'en_US' === $lang ) {
				return;
			}

			$link    = 'https://translate.wordpress.org/projects/wp-' . $this->product->get_type() . 's/' . $this->product->get_slug() . '/dev/default/';
			$heading = apply_filters( $this->product->get_key() . '_feedback_translate_heading', $this->heading );
			$heading = str_replace(
				array( '{product}' ),
				trim( str_replace( 'Lite', '', apply_filters( $this->product->get_key() . '_friendly_name', $this->product->get_name() ) ) ), $heading
			);

			$message	= apply_filters( $this->product->get_key() . '_feedback_translation_no', 'Transations not available' );
			$file		= dirname( $this->product->get_basefile() ) . '/languages' . $this->product->get_slug() . '-' . $lang . '.mo';
			if ( file_exists( $file ) ) {
				// translations available
				$message	= apply_filters( $this->product->get_key() . '_feedback_translation_yes', 'Transations incomplete' );
			}

			$button_cancel = apply_filters( $this->product->get_key() . '_feedback_translate_button_cancel', $this->button_cancel );
			$button_do     = apply_filters( $this->product->get_key() . '_feedback_translate_button_do', $this->button_do );

			return '<div id="' . $this->product->get_key() . '-translate-notification" class="themeisle-sdk-translate-box">'
				   . '<h2>' . $heading . '</h2>'
				   . '<p>' . $message . '</p>'
				   . '<div class="actions">'
				   . '<a href="' . $link . '" target="_blank" class="button button-primary translate-dismiss"> ' . $button_do . '</a>'
				   . get_submit_button( $button_cancel, 'translate-dismiss ' . $this->product->get_key() . '-ti-translate', $this->product->get_key() . 'ti-translate-no', false )
				   . '</div></div>';
		}

		/**
		 * Called when the either button is clicked
		 */
		function dismiss() {
			check_ajax_referer( (string) __CLASS__, 'nonce' );

			$this->disable();
		}

		/**
		 * Disables the notification
		 */
		protected function disable() {
			update_option( $this->product->get_key() . '__translate_flag', 'no' );
		}

		/**
		 * Enables the notification
		 */
		protected function enable() {
			update_option( $this->product->get_key() . '__translate_flag', 'yes' );
		}
	}
endif;
