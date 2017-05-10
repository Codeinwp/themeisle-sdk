<?php
/**
 * The deactivate feedback model class for ThemeIsle SDK
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
if ( ! class_exists( 'ThemeIsle_SDK_Feedback_Deactivate' ) ) :
	/**
	 * Deactivate feedback model for ThemeIsle SDK.
	 */
	class ThemeIsle_SDK_Feedback_Deactivate extends ThemeIsle_SDK_Feedback {

		/**
		 * @var array $options The main options list
		 */
		private $options    = array(
			'I only needed the plugin for a short period'   => array(
				'id' => 1,
			),
			'The plugin broke my site'                      => array(
				'id' => 2,
			),
			'I found a better plugin'                       => array(
				'id' => 3,
				'type' => 'text',
				'placeholder' => 'What\'s the plugin\'s name?',
			),
			'The plugin suddenly stopped working'           => array(
				'id' => 4,
			),
			'I no longer need the plugin'                   => array(
				'id' => 5,
			),
			'It\'s a temporary deactivation. I\'m just debugging an issue.' => array(
				'id' => 6,
			),
		);

		/**
		 * @var array $other The other option
		 */
		private $other          = array(
			'Other'         => array(
				'id' => 999,
				'type' => 'textarea',
				'placeholder' => 'cmon cmon tell us',
			),
		);

		/**
		 * @var string $heading The heading of the modal
		 */
		private $heading        = 'If you have a moment, please let us know why you are deactivating:';

		/**
		 * @var string $button_submit The text of the deactivate button
		 */
		private $button_submit  = 'Submit &amp; Deactivate';

		/**
		 * @var string $button_cancel The text of the cancel button
		 */
		private $button_cancel  = 'Cancel';

		/**
		 * ThemeIsle_SDK_Feedback_Deactivate constructor.
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
			global $pagenow;
			if ( 'plugins.php' === $pagenow ) {
				add_action( 'admin_head', array( $this, 'load_resources' ) );
			}
			add_action( 'wp_ajax_' . __CLASS__, array( $this, 'post_deactivate' ) );
		}

		/**
		 * Loads the additional resources
		 */
		function load_resources() {
			add_thickbox();

			$id     = $this->product->get_key() . '_deactivate';

			$this->add_css();
			$this->add_js( '#TB_inline?' . apply_filters( $this->product->get_key() . '_feedback_deactivate_attributes', 'width=600&height=550' ) . '&inlineId=' . $id );

			echo '<div id="' . $id . '" style="display:none;" class="themeisle-deactivate-box">' . $this->get_html() . '</div>';
		}

		/**
		 * Loads the css
		 */
		function add_css() {
?>
			<style type="text/css" id="ti-deactivate-css">
				input[name="ti-deactivate-option"] ~ div {
					display: none;
				}
				input[name="ti-deactivate-option"]:checked ~ div {
					display: block;
				}
			</style>
<?php
		}

		/**
		 * Loads the js
		 *
		 * @param string $src The url that will hijack the deactivate button url.
		 */
		function add_js( $src ) {
?>
			<script type="text/javascript" id="ti-deactivate-js">
				(function ($){
					$(document).ready(function(){
						var href = $('tr[data-slug="<?php echo $this->product->get_slug();?>"] span.deactivate a').attr('href');
						$('#ti-deactivate-no').on('click', function(e){
							e.preventDefault();
							e.stopPropagation();
							tb_remove();
						});

						$('ul.ti-list label, ul.ti-list input[name="ti-deactivate-option"]').on('click', function(e){
							$('#ti-deactivate-yes').removeAttr('disabled');
						});

						$('#ti-deactivate-yes').attr('data-ti-action', href).on('click', function(e){
							e.preventDefault();
							e.stopPropagation();
							$.ajax({
								url     : ajaxurl,
								method  : 'post',
								data    : {
									'action'    : '<?php echo __CLASS__;?>',
									'nonce'     : '<?php echo wp_create_nonce( (string) __CLASS__ );?>',
									'id'        : $('input[name="ti-deactivate-option"]:checked').parent().attr('ti-option-id'),
									'msg'       : $('input[name="ti-deactivate-option"]:checked').parent().find('textarea').val()
								},
							});
							location.href = $(this).attr('data-ti-action');
						});
						$('tr[data-slug="<?php echo $this->product->get_slug();?>"] span.deactivate a').attr('href', '<?php echo $src;?>').addClass('thickbox');
					});
				})(jQuery);
			</script>
<?php
		}

		/**
		 * Generates the HTML
		 */
		function get_html() {
			$heading        = apply_filters( $this->product->get_key() . '_feedback_deactivate_heading', $this->heading );
			$options        = $this->randomize_options( apply_filters( $this->product->get_key() . '_feedback_deactivate_options', $this->options ) );
			$button_submit  = apply_filters( $this->product->get_key() . '_feedback_deactivate_button_submit', $this->button_submit );
			$button_cancel  = apply_filters( $this->product->get_key() . '_feedback_deactivate_button_cancel', $this->button_cancel );

			$options        += $this->other;

			$list           = '';
			foreach ( $options as $title => $attributes ) {
				$id         = $attributes['id'];
				$list       .= '<li ti-option-id="' . $id . '"><input type="radio" name="ti-deactivate-option" id="' . $id . '"><label for="' . $id . '">' . __( $title ) . '</label>';
				if ( array_key_exists( 'type', $attributes ) ) {
					$list   .= '<div>';
					$placeholder    = array_key_exists( 'placeholder', $attributes ) ? __( $attributes['placeholder'] ) : '';
					switch ( $attributes['type'] ) {
						case 'text':
							$list   .= '<textarea style="width: 100%" rows="1" name="comments" placeholder="' . $placeholder . '"></textarea>';
							break;
						case 'textarea':
							$list   .= '<textarea style="width: 100%" rows="2" name="comments" placeholder="' . $placeholder . '"></textarea>';
							break;
					}
					$list   .= '</div>';
				}
				$list       .= '</li>';
			}

			return ''
				. '<h3>' . $heading . '</h3>'
				. '<ul class="ti-list">' . $list . '</ul>'
				. '<div class="actions">'
				. get_submit_button( __( $button_submit ), 'secondary', 'ti-deactivate-yes', false, array(
					'disabled' => 'disabled',
				) )
				. get_submit_button( __( $button_cancel ), 'primary', 'ti-deactivate-no', false )
				. '</div>';
		}

		/**
		 * Called when the deactivate button is clicked
		 */
		function post_deactivate() {
			check_ajax_referer( (string) __CLASS__, 'nonce' );

			$this->call_api( array(
				'type' => 'deactivate',
				'id' => $_POST['id'],
				'comment' => isset( $_POST['msg'] ) ? $_POST['msg'] : '',
			) );
		}
	}
endif;
