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

namespace ThemeisleSDK\Modules;

use ThemeisleSDK\Common\Abstract_Module;
use ThemeisleSDK\Loader;
use ThemeisleSDK\Product;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Uninstall feedback module for ThemeIsle SDK.
 */
class Uninstall_Feedback extends Abstract_Module {
	/**
	 * How many seconds before the deactivation window is triggered for themes?
	 *
	 * @var int Number of days.
	 */
	const AUTO_TRIGGER_DEACTIVATE_WINDOW_SECONDS = 3;
	/**
	 * How many days before the deactivation window pops up again for the theme?
	 *
	 * @var int Number of days.
	 */
	const PAUSE_DEACTIVATE_WINDOW_DAYS = 100;
	/**
	 * Where to send the data.
	 *
	 * @var string Endpoint url.
	 */
	const FEEDBACK_ENDPOINT = 'https://api.themeisle.com/tracking/uninstall';

	/**
	 * Default options for plugins.
	 *
	 * @var array $options_plugin The main options list for plugins.
	 */
	private $options_plugin = array(
		'id3' => array(
			'id'   => 3,
			'type' => 'text',

		),
		'id4' => array(
			'type' => 'textarea',
			'id'   => 4,
		),
		'id5' => array(
			'id'   => 5,
			'type' => 'textarea',
		),
		'id6' => array(
			'type' => 'textarea',
			'id'   => 6,
		),
	);
	/**
	 * Default options for theme.
	 *
	 * @var array $options_theme The main options list for themes.
	 */
	private $options_theme = array(
		'id7'  => array(
			'id' => 7,
		),
		'id8'  => array(
			'type' => 'text',
			'id'   => 8,
		),
		'id9'  => array(
			'id'   => 9,
			'type' => 'text',
		),
		'id10' => array(

			'title' => '',
			'id'    => 10,
		),
	);
	/**
	 * Default other option.
	 *
	 * @var array $other The other option
	 */
	private $other = array(
		'id999' => array(
			'id'   => 999,
			'type' => 'textarea',
		),
	);

	/**
	 * Loads the additional resources
	 */
	public function load_resources() {
		$screen = get_current_screen();

		if ( ! $screen || ! in_array( $screen->id, array( 'theme-install', 'plugins' ) ) ) {
			return;
		}

		$this->add_feedback_popup_style();

		if ( $this->product->get_type() === 'theme' ) {
			$this->add_theme_feedback_drawer_js();
			$this->render_theme_feedback_popup();

			return;
		}
		$this->add_plugin_feedback_popup_js();
		$this->render_plugin_feedback_popup();
	}

	/**
	 * Render theme feedback drawer.
	 */
	private function render_theme_feedback_popup() {
		$heading              = str_replace( '{theme}', $this->product->get_name(), Loader::$labels['uninstall']['heading_theme'] );
		$button_submit        = apply_filters( $this->product->get_key() . '_feedback_deactivate_button_submit', Loader::$labels['uninstall']['submit'] );
		$options              = $this->options_theme;
		$options              = $this->randomize_options( apply_filters( $this->product->get_key() . '_feedback_deactivate_options', $options ) );
		$info_disclosure_link = '<a href="#" class="info-disclosure-link">' . apply_filters( $this->product->get_slug() . '_themeisle_sdk_info_collect_cta', Loader::$labels['uninstall']['cta_info'] ) . '</a>';

		$options += $this->other;

		?>
		<div class="ti-theme-uninstall-feedback-drawer ti-feedback">
			<div class="popup--header">
				<h5><?php echo wp_kses( $heading, array( 'span' => true ) ); ?></h5>
				<?php $this->do_popup_header_after_heading_action( 'theme' ); ?>
				<button class="toggle"><span>&times;</span></button>
			</div><!--/.popup--header-->
			<div class="popup--body">
				<?php $this->render_options_list( $options ); ?>
			</div><!--/.popup--body-->
			<div class="popup--footer">
				<div class="actions">
					<?php
					echo wp_kses_post( $info_disclosure_link );
					echo wp_kses_post( $this->get_disclosure_labels() );
					echo '<div class="buttons">';
					echo get_submit_button( //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, Function has an internal sanitization.
						$button_submit,
						'secondary',
						$this->product->get_key() . 'ti-deactivate-yes',
						false,
						array(
							'data-after-text' => $button_submit,
							'disabled'        => true,
						)
					);
					echo '</div>';
					?>
				</div><!--/.actions-->
			</div><!--/.popup--footer-->
		</div>
		<?php
	}

	/**
	 * Add feedback styles.
	 */
	private function add_feedback_popup_style() {
		?>
		<style>
			.ti-feedback {
				background: #fff;
				max-width: 400px;
				z-index: 10000;
				box-shadow: 0 0 15px -5px rgba(0, 0, 0, .5);
				transition: all .3s ease-out;
			}


			.ti-feedback .popup--header {
				position: relative;
				background-color: #23A1CE;
			}

			.ti-feedback .popup--header h5 {
				margin: 0;
				font-size: 16px;
				padding: 15px;
				color: #fff;
				font-weight: 600;
				text-align: center;
				letter-spacing: .3px;
			}

			.ti-feedback .popup--body {
				padding: 15px;
			}

			.ti-feedback .popup--form {
				margin: 0;
				font-size: 13px;
			}

			.ti-feedback .popup--form input[type="radio"] {
			<?php echo is_rtl() ? 'margin: 0 0 0 10px;' : 'margin: 0 10px 0 0;'; ?>
			}

			.ti-feedback .popup--form input[type="radio"]:checked ~ textarea {
				display: block;
			}

			.ti-feedback .popup--form textarea {
				width: 100%;
				margin: 10px 0 0;
				display: none;
				max-height: 150px;
			}

			.ti-feedback li {
				display: flex;
				align-items: center;
				margin-bottom: 15px;
				flex-wrap: wrap;
			}

			.ti-feedback li label {
				max-width: 90%;
			}

			.ti-feedback li:last-child {
				margin-bottom: 0;
			}

			/* Support Hub: asking support for help, under the reasons it can help with. */
			.ti-feedback .ti-feedback-support {
				display: none;
				width: 100%;
				margin-top: 6px;
				font-size: 12px;
				color: #646970;
			}

			.ti-feedback .popup--form input[type="radio"]:checked ~ .ti-feedback-support {
				display: block;
			}

			.ti-feedback .ti-feedback-support p {
				margin: 0;
				font-size: 12px;
			}

			/* Where to reply, once support is asked: one line in a quiet box. */
			.ti-feedback .ti-feedback-contact-fields {
				display: none;
				position: relative;
				padding: 6px 10px 10px;
				padding-inline-end: 28px;
				background: #f6f7f7;
				border-radius: 3px;
			}

			.ti-feedback .ti-feedback-support.is-contacting .ti-feedback-help {
				display: none;
			}

			.ti-feedback .ti-feedback-support.is-contacting .ti-feedback-contact-fields {
				display: block;
			}

			.ti-feedback .ti-feedback-contact-close {
				position: absolute;
				top: 6px;
				inset-inline-end: 6px;
				padding: 2px 4px;
				border: 0;
				background: none;
				color: #787c82;
				font-size: 16px;
				line-height: 1;
				cursor: pointer;
			}

			.ti-feedback .ti-feedback-contact-close:hover {
				color: #1d2327;
			}

			.ti-feedback .ti-feedback-reply {
				display: flex;
				align-items: center;
				min-height: 24px;
			}

			.ti-feedback .ti-feedback-reply-address {
				font-weight: 600;
				word-break: break-all;
			}

			.ti-feedback .ti-feedback-reply-edit {
				display: none;
				flex: 1;
				align-items: center;
				max-width: 100%;
			}

			.ti-feedback .ti-feedback-support.is-editing .ti-feedback-reply-text {
				display: none;
			}

			.ti-feedback .ti-feedback-support.is-editing .ti-feedback-reply-edit {
				display: flex;
			}

			.ti-feedback .ti-feedback-reply-edit input[type="email"] {
				flex: 1;
				min-width: 0;
				min-height: 24px;
				height: 24px;
				margin: 0;
				margin-inline-start: 6px;
				padding: 0 6px;
				font-size: 12px;
				line-height: 1;
			}

			.ti-feedback .ti-feedback-keep {
				display: block;
				margin-top: 6px;
			}

			.ti-feedback .ti-feedback-error {
				margin: 6px 0 0;
				color: #d63638;
			}

			.ti-feedback .ti-feedback-error:empty {
				display: none;
			}

			.ti-feedback .ti-feedback-sent-message {
				display: none;
				padding: 20px 15px;
				text-align: center;
			}

			.ti-feedback .ti-feedback-sent-message p {
				margin: 5px 0 0;
			}

			.ti-feedback.ti-feedback-sent .ti-feedback-sent-message {
				display: block;
			}

			.ti-feedback.ti-feedback-sent .popup--body,
			.ti-feedback.ti-feedback-sent .popup--footer {
				display: none;
			}

			.ti-feedback .popup--footer {
				padding: 0 15px 15px;
			}

			.ti-feedback .actions {
				display: flex;
				flex-wrap: wrap;
			}

			.info-disclosure-link {
				width: 100%;
				margin-bottom: 15px;
			}

			.ti-feedback .info-disclosure-content {
				max-height: 0;
				overflow: hidden;
				width: 100%;
				transition: .3s ease;
			}

			.ti-feedback .info-disclosure-content.active {
				max-height: 500px;
			}

			.ti-feedback .info-disclosure-content p {
				margin: 0;
			}

			.ti-feedback .info-disclosure-content ul {
				margin: 10px 0;
				border-radius: 3px;
			}

			.ti-feedback .info-disclosure-content ul li {
				display: flex;
				align-items: center;
				justify-content: space-between;
				margin-bottom: 0;
				padding: 5px 0;
				border-bottom: 1px solid #ccc;
			}

			.ti-feedback .buttons {
				display: flex;
				width: 100%;
			}

			.ti-feedback .buttons input:last-child {
			<?php echo is_rtl() ? 'margin-right: auto;' : 'margin-left: auto;'; ?>
			}

			.ti-theme-uninstall-feedback-drawer {
				border-top-left-radius: 5px;
				position: fixed;
				top: 100%;
				right: 15px;
			}

			.ti-theme-uninstall-feedback-drawer.active {
				transform: translateY(-100%);
			}

			.ti-theme-uninstall-feedback-drawer .popup--header {
				border-top-left-radius: 5px;
			}

			.ti-theme-uninstall-feedback-drawer .popup--header .toggle {
				position: absolute;
				padding: 3px 0;
				width: 30px;
				top: -26px;
				right: 0;
				cursor: pointer;
				border-top-left-radius: 5px;
				border-top-right-radius: 5px;
				font-size: 20px;
				background-color: #23A1CE;
				color: #fff;
				border: none;
				line-height: 20px;
			}

			.ti-theme-uninstall-feedback-drawer .toggle span {
				margin: 0;
				display: inline-block;
			}

			.ti-theme-uninstall-feedback-drawer:not(.active) .toggle span {
				transform: rotate(45deg);
			}

			.ti-theme-uninstall-feedback-drawer .popup--header .toggle:hover {
				background-color: #1880a5;
			}


			.ti-plugin-uninstall-feedback-popup .popup--header:before {
				content: "";
				display: block;
				position: absolute;
				top: 50%;
				transform: translateY(-50%);
			<?php
			echo is_rtl() ?
			'right: -10px;
			border-top: 20px solid transparent;
			border-left: 20px solid #23A1CE;
			border-bottom: 20px solid transparent;' :
			'left: -10px;
			border-top: 20px solid transparent;
			border-right: 20px solid #23A1CE;
			border-bottom: 20px solid transparent;';
			?>
			}

			.ti-plugin-uninstall-feedback-popup {
				display: none;
				position: absolute;
				white-space: normal;
				width: 400px;
			<?php echo is_rtl() ? 'right: calc( 100% + 15px );' : 'left: calc( 100% + 15px );'; ?> top: -15px;
			}

			.ti-plugin-uninstall-feedback-popup.sending-feedback .popup--body i {
				animation: rotation 2s infinite linear;
				display: block;
				float: none;
				align-items: center;
				width: 100%;
				margin: 0 auto;
				height: 100%;
				background: transparent;
				padding: 0;
			}

			.ti-plugin-uninstall-feedback-popup.sending-feedback .popup--body i:before {
				padding: 0;
				background: transparent;
				box-shadow: none;
				color: #b4b9be
			}


			.ti-plugin-uninstall-feedback-popup.active {
				display: block;
			}

			tr[data-plugin^="<?php echo esc_attr( $this->product->get_slug() ); ?>"] .deactivate {
				position: relative;
			}

			body.ti-feedback-open .ti-feedback-overlay {
				content: "";
				display: block;
				background-color: rgba(0, 0, 0, 0.5);
				top: 0;
				bottom: 0;
				right: 0;
				left: 0;
				z-index: 10000;
				position: fixed;
			}

			@media (max-width: 768px) {
				.ti-plugin-uninstall-feedback-popup {
					position: fixed;
					max-width: 100%;
					margin: 0 auto;
					left: 50%;
					top: 50px;
					transform: translateX(-50%);
				}

				.ti-plugin-uninstall-feedback-popup .popup--header:before {
					display: none;
				}
			}
		</style>
		<?php
	}

	/**
	 * Theme feedback drawer JS.
	 */
	private function add_theme_feedback_drawer_js() {
		$key = $this->product->get_key();
		?>
		<script type="text/javascript" id="ti-deactivate-js">
			(function ($) {
				$(document).ready(function () {
					setTimeout(function () {
						$('.ti-theme-uninstall-feedback-drawer').addClass('active');
					}, <?php echo absint( self::AUTO_TRIGGER_DEACTIVATE_WINDOW_SECONDS * 1000 ); ?> );

					$('.ti-theme-uninstall-feedback-drawer .toggle').on('click', function (e) {
						e.preventDefault();
						$('.ti-theme-uninstall-feedback-drawer').toggleClass('active');
					});

					$('.info-disclosure-link').on('click', function (e) {
						e.preventDefault();
						$('.info-disclosure-content').toggleClass('active');
					});

					<?php $this->print_support_fields_js( '.ti-theme-uninstall-feedback-drawer' ); ?>

					$('.ti-theme-uninstall-feedback-drawer input[type="radio"]').on('change', function () {
						var radio = $(this);
						if (radio.parent().find('textarea').not('[data-optional]').length > 0 &&
							radio.parent().find('textarea').val().length === 0) {
							$('#<?php echo esc_attr( $key ); ?>ti-deactivate-yes').attr('disabled', 'disabled');
							radio.parent().find('textarea').on('keyup', function (e) {
								if ($(this).val().length === 0) {
									$('#<?php echo esc_attr( $key ); ?>ti-deactivate-yes').attr('disabled', 'disabled');
								} else {
									$('#<?php echo esc_attr( $key ); ?>ti-deactivate-yes').removeAttr('disabled');
								}
							});
						} else {
							$('#<?php echo esc_attr( $key ); ?>ti-deactivate-yes').removeAttr('disabled');
						}
					});

					$('#<?php echo esc_attr( $key ); ?>ti-deactivate-yes').on('click', function (e) {
						e.preventDefault();
						e.stopPropagation();

						var selectedOption = $(
							'.ti-theme-uninstall-feedback-drawer input[name="ti-deactivate-option"]:checked');
						var data = {
							'action': '<?php echo esc_attr( $key ) . '_uninstall_feedback'; ?>',
							'nonce': '<?php echo esc_attr( wp_create_nonce( (string) __CLASS__ ) ); ?>',
							'id': selectedOption.parent().attr('ti-option-id'),
							'msg': selectedOption.parent().find('textarea').val(),
							'type': 'theme',
							'key': '<?php echo esc_attr( $key ); ?>'
						};
						// Support Hub: with support asked, the comment goes to them too, with where to reply.
						var support = selectedOption.parent().find('.ti-feedback-support');
						if (support.hasClass('is-contacting')) {
							// Support needs something to answer.
							if ($.trim(data.msg) === '') {
								selectedOption.parent().find('textarea').trigger('focus');
								return;
							}
							var email = support.find('input[type="email"]')[0];
							if (!email.checkValidity()) {
								support.addClass('is-editing');
								email.reportValidity();
								return;
							}
							data.support = 1;
							data.email = email.value;
						}
						$.post(ajaxurl, data);
						$('.ti-theme-uninstall-feedback-drawer').fadeOut();
					});
				});
			})(jQuery);

		</script>
		<?php
		do_action( $this->product->get_key() . '_uninstall_feedback_after_js' );
	}

	/**
	 * Fires after the popup header heading, allowing products to output extra markup.
	 *
	 * @param string $context Popup context. Either `theme` or `plugin`.
	 */
	private function do_popup_header_after_heading_action( $context ) {
		do_action(
			$this->product->get_key() . '_uninstall_feedback_popup_header_after_heading',
			$this->product,
			$context
		);
	}

	/**
	 * Render the options list.
	 *
	 * @param array $options the options for the feedback form.
	 */
	private function render_options_list( $options ) {
		$key             = $this->product->get_key();
		$inputs_row_map  = [
			'text'     => 1,
			'textarea' => 2,
		];
		$support_reasons = $this->support_reasons();
		?>
		<ul class="popup--form">
			<?php
			foreach ( $options as $idx => $attributes ) {
				$title       = Loader::$labels['uninstall']['options'][ $idx ]['title'];
				$placeholder = array_key_exists( 'placeholder', Loader::$labels['uninstall']['options'][ $idx ] ) ? Loader::$labels['uninstall']['options'][ $idx ]['placeholder'] : '';
				$has_support = in_array( (int) $attributes['id'], $support_reasons, true );
				?>
				<li ti-option-id="<?php echo esc_attr( $attributes['id'] ); ?>">
					<input type="radio" name="ti-deactivate-option"
						   id="<?php echo esc_attr( $key . $attributes['id'] ); ?>">
					<label for="<?php echo esc_attr( $key . $attributes['id'] ); ?>">
						<?php echo esc_attr( str_replace( '{theme}', $this->product->get_name(), $title ) ); ?>
					</label>
					<?php
					if ( array_key_exists( 'type', $attributes ) ) {

						echo '<textarea width="100%" rows="' . esc_attr( $inputs_row_map[ $attributes['type'] ] ) . '" name="comments" placeholder="' . esc_attr( $placeholder ) . '"></textarea>';
					} elseif ( $has_support ) {
						// Support needs something to answer; the comment stays optional, as the reason had no box before.
						echo '<textarea width="100%" rows="2" name="comments" data-optional="1" placeholder="' . esc_attr( $placeholder ) . '"></textarea>';
					}
					if ( $has_support ) {
						$this->render_support_fields();
					}
					?>
				</li>
			<?php } ?>
		</ul>
		<?php
	}

	/**
	 * The reasons support can help with: empty unless the product has the Support Hub.
	 *
	 * @return list<int>
	 */
	private function support_reasons() {
		return class_exists( 'ThemeisleSDK\Modules\Support_Hub' ) ? Support_Hub::uninstall_support_reasons( $this->product ) : array();
	}

	/**
	 * Render the Support Hub part of a reason support can help with: a line
	 * offering to contact support which, once clicked, becomes where they
	 * will reply and, for plugins, the way to keep the plugin active.
	 */
	private function render_support_fields() {
		$labels = Loader::$labels['uninstall']['support'];
		$user   = wp_get_current_user();
		$email  = $user ? $user->user_email : '';
		?>
		<div class="ti-feedback-support">
			<p class="ti-feedback-help">
				<?php echo esc_html( $labels['help'] ); ?>
				<a href="#" class="ti-feedback-contact"><?php echo esc_html( $labels['contact'] ); ?></a>
			</p>
			<div class="ti-feedback-contact-fields">
				<button type="button" class="ti-feedback-contact-close" aria-label="<?php echo esc_attr( $labels['cancel'] ); ?>">&times;</button>
				<p class="ti-feedback-reply">
					<span class="ti-feedback-reply-text">
						<?php echo wp_kses( sprintf( $labels['reply'], '<span class="ti-feedback-reply-address">' . esc_html( $email ) . '</span>' ), array( 'span' => array( 'class' => true ) ) ); ?>
						&middot;
						<a href="#" class="ti-feedback-reply-change"><?php echo esc_html( $labels['change'] ); ?></a>
					</span>
					<label class="ti-feedback-reply-edit">
						<span><?php echo esc_html( $labels['email'] ); ?></span>
						<?php // A dotted domain and 6 characters at least, as is_email() requires: the browser refuses what the server would. ?>
						<input type="email" required minlength="6" pattern="[^@\s]+@[^@\s]+\.[^@\s]+" title="<?php echo esc_attr( $labels['email_invalid'] ); ?>" value="<?php echo esc_attr( $email ); ?>">
					</label>
				</p>
				<?php if ( $this->product->is_plugin() ) { ?>
					<button type="button" class="button button-small ti-feedback-keep"><?php echo esc_html( $labels['keep_active'] ); ?></button>
				<?php } ?>
			</div>
			<p class="ti-feedback-error" role="alert"></p>
		</div>
		<?php
	}

	/**
	 * Print the JS the popup and the drawer share: opening and closing the
	 * support block, and changing where to reply.
	 *
	 * @param string $root Selector of the popup or the drawer.
	 */
	private function print_support_fields_js( $root ) {
		?>
		$('<?php echo esc_attr( $root ); ?>').on('click', '.ti-feedback-contact', function (e) {
						e.preventDefault();
						var support = $(this).closest('.ti-feedback-support').addClass('is-contacting');
						// No address on the account: ask for one right away.
						support.toggleClass('is-editing', support.find('input[type="email"]').val() === '').trigger('ti-support-change');
					}).on('click', '.ti-feedback-contact-close', function (e) {
						e.preventDefault();
						$(this).closest('.ti-feedback-support').removeClass('is-contacting is-editing').trigger('ti-support-change');
					}).on('click', '.ti-feedback-reply-change', function (e) {
						e.preventDefault();
						$(this).closest('.ti-feedback-support').addClass('is-editing').find('input[type="email"]').trigger('focus');
					}).on('change', '.ti-feedback-support input[type="email"]', function () {
						// A valid address goes back to one line of text.
						if (this.checkValidity()) {
							$(this).closest('.ti-feedback-support').removeClass('is-editing').find('.ti-feedback-reply-address').text(this.value);
						}
					});
		<?php
	}

	/**
	 * Render the confirmation shown when a message was sent and the plugin stays active.
	 */
	private function render_support_sent() {
		// A variable, not the call: PHP 5.4 cannot parse empty() on a function result.
		$support_reasons = $this->support_reasons();
		if ( empty( $support_reasons ) ) {
			return;
		}
		$labels = Loader::$labels['uninstall']['support'];
		?>
		<div class="ti-feedback-sent-message" role="status">
			<strong><?php echo esc_html( $labels['sent_title'] ); ?></strong>
			<p><?php echo esc_html( $labels['sent_text'] ); ?></p>
		</div>
		<?php
	}

	/**
	 * Render plugin feedback popup.
	 */
	private function render_plugin_feedback_popup() {
		$button_cancel        = apply_filters( $this->product->get_key() . '_feedback_deactivate_button_cancel', Loader::$labels['uninstall']['button_cancel'] );
		$button_submit        = apply_filters( $this->product->get_key() . '_feedback_deactivate_button_submit', Loader::$labels['uninstall']['button_submit'] );
		$options              = $this->randomize_options( apply_filters( $this->product->get_key() . '_feedback_deactivate_options', $this->options_plugin ) );
		$info_disclosure_link = '<a href="#" class="info-disclosure-link">' . apply_filters( $this->product->get_slug() . '_themeisle_sdk_info_collect_cta', Loader::$labels['uninstall']['cta_info'] ) . '</a>';

		$options += $this->other;
		?>
		<div class="ti-plugin-uninstall-feedback-popup ti-feedback"
			 id="<?php echo esc_attr( $this->product->get_slug() . '_uninstall_feedback_popup' ); ?>">
			<div class="popup--header">
				<h5><?php echo wp_kses( Loader::$labels['uninstall']['heading_plugin'], array( 'span' => true ) ); ?> </h5>
				<?php $this->do_popup_header_after_heading_action( 'plugin' ); ?>
			</div><!--/.popup--header-->
			<div class="popup--body">
				<?php $this->render_options_list( $options ); ?>
			</div><!--/.popup--body-->
			<?php $this->render_support_sent(); ?>
			<div class="popup--footer">
				<div class="actions">
					<?php
					echo wp_kses_post( $info_disclosure_link );
					echo wp_kses_post( $this->get_disclosure_labels() );
					echo '<div class="buttons">';
					echo get_submit_button( //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, Function internals are escaped.
						$button_cancel,
						'secondary',
						$this->product->get_key() . 'ti-deactivate-no',
						false
					);
					echo get_submit_button( //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, Function internals are escaped.
						$button_submit,
						'primary',
						$this->product->get_key() . 'ti-deactivate-yes',
						false,
						array(
							'data-after-text' => $button_submit,
							'disabled'        => true,
						)
					);
					echo '</div>';
					?>
				</div><!--/.actions-->
			</div><!--/.popup--footer-->
		</div>

		<?php
	}

	/**
	 * Add plugin feedback popup JS
	 */
	private function add_plugin_feedback_popup_js() {
		$popup_id = '#' . $this->product->get_slug() . '_uninstall_feedback_popup';
		$key      = $this->product->get_key();
		?>
		<script type="text/javascript" id="ti-deactivate-js">
			(function ($) {
				$(document).ready(function () {
					var targetElement = 'tr[data-plugin^="<?php echo esc_attr( $this->product->get_slug() ); ?>/"] span.deactivate a';
					var $deactivateLink = $(targetElement);
					var redirectUrl = $deactivateLink.attr('href');
					if ($('.ti-feedback-overlay').length === 0) {
						$('body').prepend('<div class="ti-feedback-overlay"></div>');
					}
					$('<?php echo esc_attr( $popup_id ); ?> ').appendTo($deactivateLink.parent());

					$deactivateLink.on('click', function (e) {
						e.preventDefault();
						$('<?php echo esc_attr( $popup_id ); ?> ').addClass('active');
						$('body').addClass('ti-feedback-open');
						$('.ti-feedback-overlay').on('click', function () {
							$('<?php echo esc_attr( $popup_id ); ?> ').removeClass('active');
							$('body').removeClass('ti-feedback-open');
						});
					});

					$('<?php echo esc_attr( $popup_id ); ?> .info-disclosure-link').on('click', function (e) {
						e.preventDefault();
						$(this).parent().find('.info-disclosure-content').toggleClass('active');
					});

					$('<?php echo esc_attr( $popup_id ); ?> input[type="radio"]').on('change', function () {
						var radio = $(this);
						if (radio.parent().find('textarea').length > 0 &&
							radio.parent().find('textarea').val().length === 0) {
							$('<?php echo esc_attr( $popup_id ); ?> #<?php echo esc_attr( $key ); ?>ti-deactivate-yes').attr('disabled', 'disabled');
							radio.parent().find('textarea').on('keyup', function (e) {
								if ($(this).val().length === 0) {
									$('<?php echo esc_attr( $popup_id ); ?> #<?php echo esc_attr( $key ); ?>ti-deactivate-yes').attr('disabled', 'disabled');
								} else {
									$('<?php echo esc_attr( $popup_id ); ?> #<?php echo esc_attr( $key ); ?>ti-deactivate-yes').removeAttr('disabled');
								}
							});
						} else {
							$('<?php echo esc_attr( $popup_id ); ?> #<?php echo esc_attr( $key ); ?>ti-deactivate-yes').removeAttr('disabled');
						}
					});

					$('<?php echo esc_attr( $popup_id ); ?> #<?php echo esc_attr( $key ); ?>ti-deactivate-no').on('click', function (e) {
						e.preventDefault();
						e.stopPropagation();
						// Disabled while a message to support is on its way.
						if ($(this).is(':disabled')) {
							return;
						}
						$deactivateLink.unbind('click');
						$('body').removeClass('ti-feedback-open');
						$('<?php echo esc_attr( $popup_id ); ?>').remove();
						if (redirectUrl !== '') {
							location.href = redirectUrl;
						}
					});

					// Support Hub: asking support for help under the reasons it can help with.
					var $popup = $('<?php echo esc_attr( $popup_id ); ?>');
					var $submit = $popup.find('#<?php echo esc_attr( $key ); ?>ti-deactivate-yes');
					var $skip = $popup.find('#<?php echo esc_attr( $key ); ?>ti-deactivate-no');
					var submitText = $submit.val();
					var sendText = $('<textarea/>').html('<?php echo esc_js( Loader::$labels['uninstall']['support']['button_send'] ); ?>').text();
					var sendError = '<?php echo esc_js( Loader::$labels['uninstall']['support']['error'] ); ?>';
					var sending = false;
					<?php $this->print_support_fields_js( $popup_id ); ?>

					var supportFields = function () {
						return $popup.find('input[name="ti-deactivate-option"]:checked').parent().find('.ti-feedback-support');
					};
					// The button says what it will do: with support asked, the comment is sent to them too.
					var refreshSubmit = function () {
						$submit.val(supportFields().hasClass('is-contacting') ? sendText : submitText);
					};
					$popup.find('input[type="radio"]').on('change', refreshSubmit);
					$popup.on('ti-support-change', refreshSubmit);
					// What the form sends. Null when support is asked and the reply address is not valid.
					var feedbackData = function () {
						var selectedOption = $popup.find('input[name="ti-deactivate-option"]:checked');
						var data = {
							'action': '<?php echo esc_attr( $key ) . '_uninstall_feedback'; ?>',
							'nonce': '<?php echo esc_attr( wp_create_nonce( (string) __CLASS__ ) ); ?>',
							'id': selectedOption.parent().attr('ti-option-id'),
							'msg': selectedOption.parent().find('textarea').val(),
							'type': 'plugin',
							'key': '<?php echo esc_attr( $key ); ?>'
						};
						var support = supportFields();
						if (support.hasClass('is-contacting')) {
							var email = support.find('input[type="email"]')[0];
							if (!email.checkValidity()) {
								support.addClass('is-editing');
								email.reportValidity();
								return null;
							}
							data.support = 1;
							data.email = email.value;
						}
						return data;
					};
					var closeSent = function () {
						if (!$popup.hasClass('ti-feedback-sent')) {
							return;
						}
						$popup.removeClass('ti-feedback-sent active');
						$('body').removeClass('ti-feedback-open');
						$popup.find('input[type="radio"]').prop('checked', false);
						$popup.find('.ti-feedback-support').removeClass('is-contacting is-editing');
						$popup.find('textarea').val('');
						$submit.attr('disabled', 'disabled');
						refreshSubmit();
					};
					$('.ti-feedback-overlay').on('click', closeSent);

					// "Send and keep the plugin active": send the message, confirm, close. Nothing is deactivated.
					$popup.on('click', '.ti-feedback-keep', function (e) {
						e.preventDefault();
						var support = $(this).closest('.ti-feedback-support');
						var comment = support.parent().find('textarea');
						// Support needs something to answer.
						if ($.trim(comment.val()) === '') {
							comment.trigger('focus');
							return;
						}
						var data = sending ? null : feedbackData();
						if (data === null) {
							return;
						}
						data.keep_active = 1;
						sending = true;
						// Nothing else leaves the popup while the message is on its way: no deactivation behind it.
						var $buttons = $(this).add($submit).add($skip).add(support.find('.ti-feedback-contact-close')).attr('disabled', 'disabled');
						var $error = support.find('.ti-feedback-error').text('');
						$.post(ajaxurl, data).done(function (response) {
							if (response && response.success) {
								$popup.addClass('ti-feedback-sent');
								setTimeout(closeSent, 4000);
								return;
							}
							$error.text(response && response.data && response.data.message ? response.data.message : sendError);
						}).fail(function () {
							$error.text(sendError);
						}).always(function () {
							sending = false;
							$buttons.removeAttr('disabled');
						});
					});

					$('<?php echo esc_attr( $popup_id ); ?> #<?php echo esc_attr( $key ); ?>ti-deactivate-yes').on('click', function (e) {
						e.preventDefault();
						e.stopPropagation();
						var data = sending ? null : feedbackData();
						if (data === null) {
							return;
						}
						$deactivateLink.unbind('click');
						$.ajax({
							type: 'POST',
							url: ajaxurl,
							data: data,
							complete() {
								$('body').removeClass('ti-feedback-open');
								$('<?php echo esc_attr( $popup_id ); ?>').remove();
								if (redirectUrl !== '') {
									location.href = redirectUrl;
								}
							},
							beforeSend() {
								$('<?php echo esc_attr( $popup_id ); ?>').addClass('sending-feedback');
								$('<?php echo esc_attr( $popup_id ); ?> .popup--footer').remove();
								$('<?php echo esc_attr( $popup_id ); ?> .popup--body').html('<i class="dashicons dashicons-update-alt"></i>');
							}
						});
					});
				});
			})(jQuery);

		</script>
		<?php
		do_action( $this->product->get_key() . '_uninstall_feedback_after_js' );
	}

	/**
	 * Get the disclosure labels markup.
	 *
	 * @return string
	 */
	private function get_disclosure_labels() {
		$disclosure_new_labels = apply_filters( $this->product->get_slug() . '_themeisle_sdk_disclosure_content_labels', [], $this->product );
		$disclosure_labels     = array_merge(
			[
				'title' => Loader::$labels['uninstall']['disclosure']['title'],
				'items' => [
					sprintf( Loader::$labels['uninstall']['disclosure']['version'], '<strong>', ucwords( $this->product->get_type() ), '</strong>', '<code>', $this->product->get_version(), '</code>' ),
					sprintf( Loader::$labels['uninstall']['disclosure']['website'], '<strong>', '</strong>', '<code>', get_site_url(), '</code>' ),
					sprintf( Loader::$labels['uninstall']['disclosure']['usage'], '<strong>', '</strong>', '<code>', ( time() - $this->product->get_install_time() ), 's</code>' ),
					sprintf( Loader::$labels['uninstall']['disclosure']['reason'], '<strong>', '</strong>', '<i>', '</i>' ),
					sprintf( Loader::$labels['uninstall']['disclosure']['diagnostics'], '<strong>', '</strong>' ),
				],
			],
			$disclosure_new_labels
		);
		// With the Support Hub, a message to support carries where to reply, the plan and the site info.
		$support_reasons = $this->support_reasons();
		if ( ! empty( $support_reasons ) ) {
			$disclosure_labels['title']   = Loader::$labels['uninstall']['disclosure']['title_support'];
			$disclosure_labels['items'][] = sprintf( Loader::$labels['uninstall']['disclosure']['email'], '<strong>', '</strong>', '<i>', '</i>' );
			$disclosure_labels['items'][] = sprintf( Loader::$labels['uninstall']['disclosure']['plan'], '<strong>', '</strong>', '<i>', '</i>' );
			$disclosure_labels['items'][] = sprintf( Loader::$labels['uninstall']['disclosure']['site_info'], '<strong>', '</strong>' );
		}

		$info_disclosure_content = '<div class="info-disclosure-content"><p>' . wp_kses_post( $disclosure_labels['title'] ) . '</p><ul>';
		foreach ( $disclosure_labels['items'] as $disclosure_item ) {
			$info_disclosure_content .= sprintf( '<li>%s</li>', wp_kses_post( $disclosure_item ) );
		}
		$info_disclosure_content .= '</ul></div>';

		return $info_disclosure_content;
	}

	/**
	 * Randomizes the options array.
	 *
	 * @param array $options The options array.
	 */
	public function randomize_options( $options ) {
		$new  = array();
		$keys = array_keys( $options );
		shuffle( $keys );

		foreach ( $keys as $key ) {
			$new[ $key ] = $options[ $key ];
		}

		return $new;
	}

	/**
	 * Called when the deactivate button is clicked.
	 */
	public function post_deactivate() {
		check_ajax_referer( (string) __CLASS__, 'nonce' );

		$this->post_deactivate_or_cancel();

		if ( empty( $_POST['id'] ) ) {
			wp_send_json_success();

			return;
		}
		$id      = sanitize_key( $_POST['id'] );
		$comment = isset( $_POST['msg'] ) ? sanitize_textarea_field( wp_unslash( $_POST['msg'] ) ) : '';
		$has_hub = class_exists( 'ThemeisleSDK\Modules\Support_Hub' );
		// "Contact support" clicked, under a reason that offers it.
		$contact = $has_hub
			&& isset( $_POST['support'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['support'] ) )
			&& in_array( (int) $id, Support_Hub::uninstall_support_reasons( $this->product ), true );
		// Kept as typed: a sanitizer would turn a mistyped address into another valid one. Support_Hub::send() validates it.
		$email = isset( $_POST['email'] ) ? trim( (string) wp_unslash( $_POST['email'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		$keep_active = $contact
			&& $this->product->is_plugin()
			&& isset( $_POST['keep_active'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['keep_active'] ) );
		if ( $keep_active ) {
			// Nothing is uninstalled, so nothing is recorded as an uninstall: only the message goes out.
			$support = Support_Hub::send_uninstall_comment( $this->product, (int) $id, $comment, $email, true );
			if ( null === $support ) {
				wp_send_json_error( array( 'message' => Loader::$labels['support_hub']['error_empty'] ) );
			}
			if ( is_wp_error( $support ) ) {
				wp_send_json_error( array( 'message' => $support->get_error_message() ) );
			}
			wp_send_json_success();
		}

		$sent = $this->call_api(
			array(
				'type'    => 'deactivate',
				'id'      => $id,
				'comment' => $comment,
			)
		);
		// After "Contact support", the comment also opens a support conversation.
		// Best effort: the deactivation goes ahead whatever support answers.
		if ( $contact ) {
			Support_Hub::send_uninstall_comment( $this->product, (int) $id, $comment, $email );
		}
		// Tells the caller whether the feedback endpoint took the reason.
		if ( ! $sent ) {
			wp_send_json_error();
		}
		wp_send_json_success();
	}

	/**
	 * Called when the deactivate/cancel button is clicked.
	 */
	private function post_deactivate_or_cancel() {
		if ( ! isset( $_POST['type'] ) || ! isset( $_POST['key'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing, Nonce already present in caller function.
			return;
		}
		if ( 'theme' !== $_POST['type'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing, Nonce already present in caller function.
			return;
		}

		set_transient( 'ti_sdk_pause_' . sanitize_text_field( $_POST['key'] ), true, self::PAUSE_DEACTIVATE_WINDOW_DAYS * DAY_IN_SECONDS );//phpcs:ignore WordPress.Security.NonceVerification.Missing, Nonce already present in caller function.

	}

	/**
	 * Calls the API
	 *
	 * @param array $attributes The attributes of the post body.
	 *
	 * @return bool Is the request succesfull?
	 */
	protected function call_api( $attributes ) {
		$slug                      = $this->product->get_slug();
		$version                   = $this->product->get_version();
		$attributes['slug']        = $slug;
		$attributes['version']     = $version;
		$attributes['url']         = get_site_url();
		$attributes['active_time'] = ( time() - $this->product->get_install_time() );

		/**
		 * Piggyback the compact crash summary on the same request, regardless
		 * of the logging consent — disclosed in the survey data collection
		 * notice. Only crashes attributed to this product are ever stored.
		 */
		if ( class_exists( 'ThemeisleSDK\Modules\Crash_Reporter' ) ) {
			$crashes = Crash_Reporter::get_uninstall_summary( $this->product );
			if ( ! empty( $crashes ) ) {
				$attributes['crashes'] = wp_json_encode( $crashes );
			}
		}

		$attributes = apply_filters( 'themeisle_sdk_uninstall_feedback_data', $attributes, $this->product );

		$response = wp_remote_post(
			self::FEEDBACK_ENDPOINT,
			array(
				'body' => $attributes,
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}
		$code = (int) wp_remote_retrieve_response_code( $response );

		return $code >= 200 && $code < 300;
	}

	/**
	 * Should we load this object?.
	 *
	 * @param Product $product Product object.
	 *
	 * @return bool Should we load the module?
	 */
	public function can_load( $product ) {
		if ( $this->is_from_partner( $product ) ) {
			return false;
		}
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return true;
		}
		if ( $product->is_theme() && ( false !== get_transient( 'ti_sdk_pause_' . $product->get_key(), false ) ) ) {
			return false;
		}

		global $pagenow;

		if ( ! isset( $pagenow ) || empty( $pagenow ) ) {
			return false;
		}

		if ( $product->is_plugin() && 'plugins.php' !== $pagenow ) {
			return false;

		}
		if ( $product->is_theme() && 'theme-install.php' !== $pagenow ) {
			return false;
		}

		return true;
	}

	/**
	 * Loads module hooks.
	 *
	 * @param Product $product Product details.
	 *
	 * @return Uninstall_Feedback Current module instance.
	 */
	public function load( $product ) {

		if ( apply_filters( $product->get_key() . '_hide_uninstall_feedback', false ) ) {
			return;
		}

		$this->product = $product;
		add_action( 'admin_head', array( $this, 'load_resources' ) );
		add_action( 'wp_ajax_' . $this->product->get_key() . '_uninstall_feedback', array( $this, 'post_deactivate' ) );

		return $this;
	}
}
