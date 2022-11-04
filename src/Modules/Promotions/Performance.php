<?php

namespace ThemeisleSDK\Promotions;

class Performance {
	private $enqueued = false;
	private $sdk_uri;
	private $product;

	public function __construct( $product, $sdk_uri ) {
		$this->sdk_uri = $sdk_uri;
		$this->product = $product;
		$this->run_actions();
	}

	private function run_actions() {
		add_action( 'admin_notices', array( $this, 'render_optimole_dash_notice' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_optimole_editor_assets' ) );
		add_filter( 'attachment_fields_to_edit', array( $this, 'edit_attachment' ), 10, 2 );
		add_action( 'elementor/editor/after_enqueue_scripts', array( $this, 'enqueue_elementor_assets' ) );
	}


	public function render_optimole_dash_notice() {
		$screen = get_current_screen();

		if ( ! isset( $screen->id ) || $screen->id !== 'upload' ) {
			return false;
		}

		$this->enqueue_optimole_assets( 'admin' );

		echo '<div id="ti-optml-notice" class="notice notice-info ti-sdk-om-notice"></div>';
	}

	private function enqueue_optimole_assets( $type ) {
		$allowed = [ 'admin', 'editor', 'elementor' ];

		if ( ! in_array( $type, $allowed, true ) ) {
			return;
		}

		global $themeisle_sdk_max_path;
		$handle            = 'ti-sdk-optml-' . $type;
		$themeisle_sdk_src = $this->sdk_uri;
		$asset_file        = require $themeisle_sdk_max_path . '/assets/js/build/optimole-' . $type . '.asset.php';
		$user              = wp_get_current_user();

		wp_register_script( $handle, $themeisle_sdk_src . 'assets/js/build/optimole-' . $type . '.js', $asset_file['dependencies'], $asset_file['version'], true );
		wp_localize_script( $handle, 'tiSdkData', [
			'logo'  => esc_url( $themeisle_sdk_src . '/assets/images/optimole-logo.svg' ),
			'title' => esc_html( sprintf( __( 'Recommended by %s', 'textdomain' ), $this->product->get_name() ) ),
			'email' => $user->user_email,
			'option' => get_option( 'themeisle_sdk_promotions_optimole', '{}' )
		] );
		wp_enqueue_script( $handle );
		if ( $this->enqueued === true ) {
			return;
		}
		$this->enqueued = true;
		wp_enqueue_style( $handle, $themeisle_sdk_src . 'assets/js/build/style-optimole-admin.css', [ 'wp-components' ], $asset_file['version'] );
	}


	public function edit_attachment( $fields, $post ) {
		if ( $post->post_type !== 'attachment' ) {
			return $fields;
		}

		if ( ! isset( $post->post_mime_type ) || strpos( $post->post_mime_type, 'image' ) === false ) {
			return $fields;
		}

		$meta = wp_get_attachment_metadata( $post->ID );
		$size = $meta['filesize'];

		if ( isset( $meta['filesize'] ) && $meta['filesize'] < 200000 ) {
			return $fields;
		}

		$fields['optimole'] = array(
			'input' => 'html',
			'html'  => '<div id="ti-optml-notice-attachment" class="notice notice-info ti-sdk-om-notice"></div>',
		);

		return $fields;
	}

	public function enqueue_optimole_editor_assets() {
		$this->enqueue_optimole_assets( 'editor' );
	}

	public function enqueue_elementor_assets() {
		$this->enqueue_optimole_assets( 'elementor' );
	}
}