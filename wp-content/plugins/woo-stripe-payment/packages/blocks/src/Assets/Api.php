<?php


namespace PaymentPlugins\Blocks\Stripe\Assets;

use \PaymentPlugins\Blocks\Stripe\Config;

class Api {

	private $config;

	private $asset_registry;

	private $styles = array();

	/**
	 * Api constructor.
	 *
	 * @param Config $config
	 */
	public function __construct( Config $config ) {
		$this->config = $config;
		$this->init();
	}

	private function init() {
		foreach ( array( 'cart', 'checkout' ) as $page ) {
			add_action( "woocommerce_blocks_enqueue_${page}_block_scripts_after", array( $this, 'enqueue_style' ) );
		}
		$this->register_script( 'wc-stripe-blocks-commons', 'build/commons.js' );
	}

	/**
	 * @param string $relative_path
	 *
	 * @return string
	 */
	public function get_asset_url( $relative_path = '' ) {
		$url = $this->config->get_url();
		preg_match( '/^(\.{2}\/)+/', $relative_path, $matches );
		if ( $matches ) {
			foreach ( range( 0, substr_count( $matches[0], '../' ) - 1 ) as $idx ) {
				$url = dirname( $url );
			}
			$relative_path = '/' . substr( $relative_path, strlen( $matches[0] ) );
		}

		return $url . $relative_path;
	}

	public function get_path( $relative_path = '' ) {
		return $this->config->get_path( $relative_path );
	}

	/**
	 * Registers the provided script.
	 *
	 * @param string $handle
	 * @param string $relative_path
	 * @param array $deps
	 */
	public function register_script( $handle, $relative_path, $deps = array() ) {
		$src = $this->get_asset_url( $relative_path );
		// check if there is an assets.php file
		$path = $this->get_path( str_replace( '.js', '.asset.php', $relative_path ) );

		$default_deps = array( 'wc-stripe-blocks-commons' );
		if ( ! in_array( $handle, $default_deps ) ) {
			$deps = wp_parse_args( $deps, $default_deps );
		}
		// dependency file exists so load it.
		if ( file_exists( $path ) ) {
			$dependency = require( $path );
			if ( isset( $dependency['dependencies'] ) ) {
				$deps = wp_parse_args( $deps, $dependency['dependencies'] );
			}
		}
		wp_register_script( $handle, $src, $deps, $this->config->get_version(), true );

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( $handle, 'woo-stripe-payment' );
		}
	}

	public function register_external_script( $handle, $src, $deps, $version, $footer = true ) {
		wp_enqueue_script( $handle, $src, $deps, $version, $footer );
	}

	public function enqueue_style() {
		// always enqueue styles if there are scripts that have been registered.
		if ( ! in_array( 'wc-stripe-block-style', $this->styles ) ) {
			wp_enqueue_style( 'wc-stripe-block-style', $this->get_asset_url( 'build/style.css' ), array(), $this->config->get_version() );
			wp_style_add_data( 'wc-stripe-block-style', 'rtl', 'replace' );
			$this->styles[] = 'wc-stripe-block-style';

			do_action( 'wc_stripe_blocks_enqueue_styles', $this );
		}
	}
}