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
		 * Return the locale path.
		 *
		 * @param string $locale Locale code.
		 *
		 * @return string Locale path.
		 */
		private function get_locale_paths( $locale ) {
			if ( empty( $locale ) ) {
				return '';
			}
			$locales = array(
				'pt_PT_ao90'     => 'pt/ao90',
				'de_CH_informal' => 'de-ch/informal',
				'nl_NL_formal'   => 'nl/formal',
				'de_DE_formal'   => 'de/formal',
				'af'             => 'af',
				'ak'             => 'ak',
				'am'             => 'am',
				'ar'             => 'ar',
				'arq'            => 'arq',
				'ary'            => 'ary',
				'as'             => 'as',
				'ast'            => 'ast',
				'az'             => 'az',
				'azb'            => 'azb',
				'az_TR'          => 'az-tr',
				'ba'             => 'ba',
				'bal'            => 'bal',
				'bcc'            => 'bcc',
				'bel'            => 'bel',
				'bg_BG'          => 'bg',
				'bn_BD'          => 'bn',
				'bo'             => 'bo',
				'bre'            => 'br',
				'bs_BA'          => 'bs',
				'ca'             => 'ca',
				'ceb'            => 'ceb',
				'ckb'            => 'ckb',
				'co'             => 'co',
				'cs_CZ'          => 'cs',
				'cy'             => 'cy',
				'da_DK'          => 'da',
				'de_DE'          => 'de',
				'de_CH'          => 'de-ch',
				'dv'             => 'dv',
				'dzo'            => 'dzo',
				'el'             => 'el',
				'art_xemoji'     => 'art-xemoji',
				'en_US'          => 'en',
				'en_AU'          => 'en-au',
				'en_CA'          => 'en-ca',
				'en_GB'          => 'en-gb',
				'en_NZ'          => 'en-nz',
				'en_ZA'          => 'en-za',
				'eo'             => 'eo',
				'es_ES'          => 'es',
				'es_AR'          => 'es-ar',
				'es_CL'          => 'es-cl',
				'es_CO'          => 'es-co',
				'es_CR'          => 'es-cr',
				'es_GT'          => 'es-gt',
				'es_MX'          => 'es-mx',
				'es_PE'          => 'es-pe',
				'es_PR'          => 'es-pr',
				'es_VE'          => 'es-ve',
				'et'             => 'et',
				'eu'             => 'eu',
				'fa_IR'          => 'fa',
				'fa_AF'          => 'fa-af',
				'fuc'            => 'fuc',
				'fi'             => 'fi',
				'fo'             => 'fo',
				'fr_FR'          => 'fr',
				'fr_BE'          => 'fr-be',
				'fr_CA'          => 'fr-ca',
				'frp'            => 'frp',
				'fur'            => 'fur',
				'fy'             => 'fy',
				'ga'             => 'ga',
				'gd'             => 'gd',
				'gl_ES'          => 'gl',
				'gn'             => 'gn',
				'gsw'            => 'gsw',
				'gu'             => 'gu',
				'hat'            => 'hat',
				'hau'            => 'hau',
				'haw_US'         => 'haw',
				'haz'            => 'haz',
				'he_IL'          => 'he',
				'hi_IN'          => 'hi',
				'hr'             => 'hr',
				'hu_HU'          => 'hu',
				'hy'             => 'hy',
				'id_ID'          => 'id',
				'ido'            => 'ido',
				'is_IS'          => 'is',
				'it_IT'          => 'it',
				'ja'             => 'ja',
				'jv_ID'          => 'jv',
				'ka_GE'          => 'ka',
				'kab'            => 'kab',
				'kal'            => 'kal',
				'kin'            => 'kin',
				'kk'             => 'kk',
				'km'             => 'km',
				'kn'             => 'kn',
				'ko_KR'          => 'ko',
				'kir'            => 'kir',
				'lb_LU'          => 'lb',
				'li'             => 'li',
				'lin'            => 'lin',
				'lo'             => 'lo',
				'lt_LT'          => 'lt',
				'lv'             => 'lv',
				'me_ME'          => 'me',
				'mg_MG'          => 'mg',
				'mk_MK'          => 'mk',
				'ml_IN'          => 'ml',
				'mlt'            => 'mlt',
				'mn'             => 'mn',
				'mr'             => 'mr',
				'mri'            => 'mri',
				'ms_MY'          => 'ms',
				'my_MM'          => 'mya',
				'ne_NP'          => 'ne',
				'nb_NO'          => 'nb',
				'nl_NL'          => 'nl',
				'nl_BE'          => 'nl-be',
				'nn_NO'          => 'nn',
				'oci'            => 'oci',
				'ory'            => 'ory',
				'os'             => 'os',
				'pa_IN'          => 'pa',
				'pl_PL'          => 'pl',
				'pt_BR'          => 'pt-br',
				'pt_PT'          => 'pt',
				'ps'             => 'ps',
				'rhg'            => 'rhg',
				'ro_RO'          => 'ro',
				'roh'            => 'roh',
				'ru_RU'          => 'ru',
				'rue'            => 'rue',
				'rup_MK'         => 'rup',
				'sah'            => 'sah',
				'sa_IN'          => 'sa-in',
				'scn'            => 'scn',
				'si_LK'          => 'si',
				'sk_SK'          => 'sk',
				'sl_SI'          => 'sl',
				'sna'            => 'sna',
				'snd'            => 'snd',
				'so_SO'          => 'so',
				'sq'             => 'sq',
				'sq_XK'          => 'sq-xk',
				'sr_RS'          => 'sr',
				'srd'            => 'srd',
				'su_ID'          => 'su',
				'sv_SE'          => 'sv',
				'sw'             => 'sw',
				'syr'            => 'syr',
				'szl'            => 'szl',
				'ta_IN'          => 'ta',
				'ta_LK'          => 'ta-lk',
				'tah'            => 'tah',
				'te'             => 'te',
				'tg'             => 'tg',
				'th'             => 'th',
				'tir'            => 'tir',
				'tl'             => 'tl',
				'tr_TR'          => 'tr',
				'tt_RU'          => 'tt',
				'tuk'            => 'tuk',
				'twd'            => 'twd',
				'tzm'            => 'tzm',
				'ug_CN'          => 'ug',
				'uk'             => 'uk',
				'ur'             => 'ur',
				'uz_UZ'          => 'uz',
				'vi'             => 'vi',
				'wa'             => 'wa',
				'xho'            => 'xho',
				'xmf'            => 'xmf',
				'yor'            => 'yor',
				'zh_CN'          => 'zh-cn',
				'zh_HK'          => 'zh-hk',
				'zh_TW'          => 'zh-tw',
			);

			$slug = isset( $locales[ $locale ] ) ? $locales[ $locale ] : '';
			if ( empty( $slug ) ) {
				return '';
			}
			if ( strpos( $slug, '/' ) === false ) {
				$slug .= '/default';
			}
			$url = 'https://translate.wordpress.org/projects/wp-' . $this->product->get_type() . 's/' . $this->product->get_slug() . '/dev/' . $slug . '?filters%5Bstatus%5D=untranslated&sort%5Bby%5D=random';

			return $url;
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
			$html = $this->get_html( $this->product->get_key() );

			if ( $html ) {
				echo '<div class="notice notice-success is-dismissible" id="' . $id . '" ><div class="themeisle-translate-box">' . $html . '</div></div>';
			}
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
		 * @param   string $key The product key.
		 *
		 * @return  void|string Html code of the notification.
		 */
		function get_html( $key ) {
			$lang = get_user_locale();
			if ( 'en_US' === $lang ) {
				return;
			}
			$array = explode( '_', $lang );
			require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );

			$languages = translations_api(
				$this->product->get_type() . 's',
				array(
					'slug'    => $this->product->get_slug(),
					'version' => $this->product->get_version(),
				)
			);

			if ( ! isset( $languages['translations'] ) ) {
				return;
			}
			$languages = $languages['translations'];
			$available = wp_list_pluck( $languages, 'language' );
			if ( in_array( $lang, $available ) ) {
				return;
			}
			$link    = $this->get_locale_paths( $lang );
			$heading = apply_filters( $this->product->get_key() . '_feedback_translate_heading', $this->heading );
			$heading = str_replace(
				array( '{product}' ),
				trim( str_replace( 'Lite', '', apply_filters( $this->product->get_key() . '_friendly_name', $this->product->get_name() ) ) ), $heading
			);

			$message = apply_filters( $this->product->get_key() . '_feedback_translation_no', 'Transations not available' );

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
