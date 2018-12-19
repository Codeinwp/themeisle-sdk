<?php
/**
 * The product model class for ThemeIsle SDK
 *
 * @package     ThemeIsleSDK
 * @subpackage  Product
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 * @since       1.0.0
 */

namespace ThemeisleSDK;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Product model for ThemeIsle SDK.
 */
class Product {
	// **
	// * @var string $logger_option Logger option key.
	// */
	// public $logger_option;
	/**
	 * Define plugin type string.
	 */
	const PLUGIN_TYPE = 'plugin';
	/**
	 * Define theme type string.
	 */
	const THEME_TYPE = 'theme';
	/**
	 * If the product has a pro version, contains the pro slug.
	 *
	 * @var string $pro_slug Pro slug, if available.
	 */
	public $pro_slug;
	/**
	 * Current product slug.
	 *
	 * @var string $slug THe product slug.
	 */
	private $slug;
	/**
	 * Product basefile, with the proper metadata.
	 *
	 * @var string $basefile The file with headers.
	 */
	private $basefile;
	/**
	 * Type of the product.
	 *
	 * @var string $type The product type ( plugin | theme ).
	 */
	private $type;
	/**
	 * The file name.
	 *
	 * @var string $file The file name.
	 */
	private $file;
	/**
	 * Product name, fetched from the file headers.
	 *
	 * @var string $name The product name.
	 */
	private $name;
	/**
	 * Product normalized key.
	 *
	 * @var string $key The product ready key.
	 */
	private $key;
	/**
	 * Product store url.
	 *
	 * @var string $store_url The store url.
	 */
	private $store_url;
	/**
	 * Product install timestamp.
	 *
	 * @var int $install The date of install.
	 */
	private $install;
	/**
	 * Product store/author name.
	 *
	 * @var string $store_name The store name.
	 */
	private $store_name;
	/**
	 * Does the product requires license.
	 *
	 * @var bool $requires_license Either user needs to activate it with license.
	 */
	private $requires_license;
	/**
	 * Is the product available on wordpress.org
	 *
	 * @var bool $wordpress_available Either is available on WordPress or not.
	 */
	private $wordpress_available;
	/**
	 * Current version of the product.
	 *
	 * @var string $version The product version.
	 */
	private $version;
	// **
	// * @var string $feedback_types All the feedback types the product supports
	// */
	// private $feedback_types = array();

	/**
	 * ThemeIsle_SDK_Product constructor.
	 *
	 * @param string $basefile Product basefile.
	 */
	public function __construct( $basefile ) {
		if ( ! empty( $basefile ) ) {
			if ( is_readable( $basefile ) ) {
				$this->basefile = $basefile;
				$this->setup_from_path();
				$this->setup_from_fileheaders();
			}
		}
		$install = get_option( $this->get_key() . '_install', 0 );
		if ( 0 === $install ) {
			$install = time();
			update_option( $this->get_key() . '_install', time() );
		}
		$this->install = $install;

		$this->logger_option = $this->get_key() . '_logger_flag';

	}

	/**
	 * Setup props from path.
	 */
	public function setup_from_path() {
		$this->file = basename( $this->basefile );
		$dir        = dirname( $this->basefile );
		$this->slug = basename( $dir );
		$exts       = explode( '.', $this->basefile );
		$ext        = $exts[ count( $exts ) - 1 ];
		if ( 'css' === $ext ) {
			$this->type = 'theme';
		}
		if ( 'php' === $ext ) {
			$this->type = 'plugin';
		}
		$this->key = self::key_ready_name( $this->slug );
	}

	/**
	 * Normalize string.
	 *
	 * @param string $string the String to be normalized for cron handler.
	 *
	 * @return string $name         The normalized string.
	 */
	static function key_ready_name( $string ) {
		return str_replace( '-', '_', strtolower( trim( $string ) ) );
	}

	/**
	 * Setup props from fileheaders.
	 */
	public function setup_from_fileheaders() {
		$file_headers = array(
			'Requires License'    => 'Requires License',
			'WordPress Available' => 'WordPress Available',
			'Pro Slug'            => 'Pro Slug',
			'Version'             => 'Version',
		);
		if ( 'plugin' === $this->type ) {
			$file_headers['Name']       = 'Plugin Name';
			$file_headers['AuthorName'] = 'Author';
			$file_headers['AuthorURI']  = 'Author URI';
		}
		if ( 'theme' === $this->type ) {
			$file_headers['Name']       = 'Theme Name';
			$file_headers['AuthorName'] = 'Author';
			$file_headers['AuthorURI']  = 'Author URI';
		}
		$file_headers = get_file_data( $this->basefile, $file_headers );

		$this->name       = $file_headers['Name'];
		$this->store_name = $file_headers['AuthorName'];
		$this->author_url = $file_headers['AuthorURI'];
		$this->store_url  = $file_headers['AuthorURI'];

		$this->requires_license    = ( 'yes' === $file_headers['Requires License'] ) ? true : false;
		$this->wordpress_available = ( 'yes' === $file_headers['WordPress Available'] ) ? true : false;
		$this->pro_slug            = ! empty( $file_headers['Pro Slug'] ) ? $file_headers['Pro Slug'] : '';
		$this->version             = $file_headers['Version'];
		// if ( $this->require_uninstall_feedback() ) {
		// $this->feedback_types[] = 'deactivate';
		// }
		// if ( $this->is_wordpress_available() ) {
		// $this->feedback_types[] = 'review';
		// $this->feedback_types[] = 'translate';
		// }.
	}

	// **
	// * We require feedback on uninstall.
	// *
	// * @return bool Either we should require feedback on uninstall or not.
	// */
	// public function require_uninstall_feedback() {
	// if ( $this->get_type() == 'theme' && ! $this->is_external_author() ) {
	// return ! get_transient( 'ti_sdk_pause_' . $this->get_key(), false );
	// }
	//
	// if ( $this->get_type() == 'plugin' ) {
	//
	// return true;
	// }
	//
	// return false;
	// }

	/**
	 * Return the product key.
	 *
	 * @return string The product key.
	 */
	public function get_key() {
		return $this->key;
	}

	// **
	// * Check if the product is by external author or not.
	// *
	// * @return bool Either is external author or no.
	// */
	// public function is_external_author() {
	// foreach ( $this->allowed_authors as $author ) {
	// if ( strpos( $this->author_url, $author ) !== false ) {
	// return true;
	// }
	// if ( in_array( $this->get_slug(), $this->allowed_products ) ) {
	// return true;
	// }
	// }
	//
	// return false;
	// }

	/**
	 * Check if the product is either theme or plugin.
	 *
	 * @return string Product type.
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Return if the product is used as a plugin.
	 *
	 * @return bool Is plugin?
	 */
	public function is_plugin() {
		return self::PLUGIN_TYPE === $this->type;
	}

	/**
	 * Return if the product is used as a theme.
	 *
	 * @return bool Is theme ?
	 */
	public function is_theme() {
		return self::THEME_TYPE === $this->type;
	}

	/**
	 * Returns the product slug.
	 *
	 * @return string The product slug.
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * If product is available on wordpress.org or not.
	 *
	 * @return bool Either is wp available or not.
	 */
	public function is_wordpress_available() {
		return $this->wordpress_available;
	}

	/**
	 * The magic var_dump info method.
	 *
	 * @return array Debug info.
	 */
	public function __debugInfo() {
		return array(
			'name'                => $this->name,
			'slug'                => $this->slug,
			'version'             => $this->version,
			'basefile'            => $this->basefile,
			'key'                 => $this->key,
			'type'                => $this->type,
			'store_name'          => $this->store_name,
			'store_url'           => $this->store_url,
			'wordpress_available' => $this->wordpress_available,
			'requires_license'    => $this->requires_license,
		);

	}

	/**
	 * Getter for product version.
	 *
	 * @return string The product version.
	 */
	public function get_version() {
		return $this->version;
	}


	/**
	 * Returns current product license, if available.
	 *
	 * @return string Return license key, if available.
	 */
	public function get_license() {
		$license_data = get_option( $this->get_key() . '_license_data', '' );

		if ( empty( $license_data ) ) {
			return '';
		}
		if ( ! isset( $license_data->key ) ) {
			return '';
		}

		return $license_data->key;
	}

	/**
	 * Return friendly name.
	 *
	 * @return string Friendly name.
	 */
	public function get_friendly_name() {
		$name = apply_filters( $this->get_key() . '_friendly_name', trim( str_replace( 'Lite', '', $this->get_name() ) ) );
		$name = rtrim( $name, '- ()' );

		return $name;
	}

	/**
	 * Getter for product name.
	 *
	 * @return string The product name.
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Either the product requires license or not.
	 *
	 * @return bool Either requires license or not.
	 */
	public function requires_license() {
		return $this->requires_license;
	}

	/**
	 * Returns the Store name.
	 *
	 * @return string Store name.
	 */
	public function get_store_name() {
		return $this->store_name;
	}

	/**
	 * Returns the store url.
	 *
	 * @return string The store url.
	 */
	public function get_store_url() {
		return $this->store_url;
	}

	/**
	 * Returns product basefile, which holds the metaheaders.
	 *
	 * @return string The product basefile.
	 */
	public function get_basefile() {
		return $this->basefile;
	}

	/**
	 * Returns product filename.
	 *
	 * @return string The product filename.
	 */
	public function get_file() {
		return $this->file;
	}
	//
	// **
	// * Returns feedback types.
	// *
	// * @return array The feedback types.
	// */
	// public function get_feedback_types() {
	// return apply_filters( $this->get_key() . '_feedback_types', $this->feedback_types );
	// }
	//
	// **
	// * We log the user website and product version.
	// *
	// * @return bool Either we log the data or not.
	// */
	// public function is_logger_active() {
	// If is not available on WordPress log this automatically.
	// if ( ! $this->is_wordpress_available() ) {
	// return true;
	// } else {
	// $pro_slug = $this->get_pro_slug();
	// if ( ! empty( $pro_slug ) ) {
	//
	// $all_products = ThemeIsle_SDK_Loader::get_products();
	// if ( isset( $all_products[ $pro_slug ] ) ) {
	// return true;
	// }
	// }
	//
	// return ( get_option( $this->get_key() . '_logger_flag', 'no' ) === 'yes' );
	//
	// }
	// }

	/**
	 * Returns the pro slug, if available.
	 *
	 * @return string The pro slug.
	 */
	public function get_pro_slug() {
		return $this->pro_slug;
	}

	/**
	 * Return the install timestamp.
	 *
	 * @return int The install timestamp.
	 */
	public function get_install_time() {
		return $this->install;
	}

}
