<?php


namespace PaymentPlugins\Blocks\Stripe;

use Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry;
use Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry;
use Automattic\WooCommerce\Blocks\Registry\Container as Container;
use PaymentPlugins\Blocks\Stripe\Payments\PaymentsApi;
use PaymentPlugins\Blocks\Stripe\Payments\CreditCardPayment;
use PaymentPlugins\Blocks\Stripe\Assets\Api as AssetsApi;

class Config {

	private $version;

	private $container;

	private $path;

	private $url;

	/**
	 * Init constructor.
	 *
	 * @param string $version
	 * @param Container $container
	 * @param string $path
	 */
	public function __construct( $version, Container $container, $path ) {
		$this->version   = $version;
		$this->container = $container;
		$this->path      = $path;
		$this->url       = plugin_dir_url( $this->path . DIRECTORY_SEPARATOR . 'src' );
		$this->dependencies();
		$this->register_payment_methods();
	}

	public function get_url( $relative_path = '' ) {
		return $this->url . $relative_path;
	}

	public function get_path( $relative_path ) {
		return trailingslashit( $this->path ) . $relative_path;
	}

	public function get_plugin_path() {
		return stripe_wc()->plugin_path();
	}

	public function get_version() {
		return $this->version;
	}

	private function dependencies() {
		$this->container->register( AssetsApi::class, function ( $container ) {
			return new AssetsApi( $this );
		} );
	}

	/**
	 * Register all of the payment methods to the Container.
	 * @throws \Exception
	 */
	private function register_payment_methods() {
		// register the payments API
		$this->container->register( PaymentsApi::class, function ( Container $container ) {
			return new PaymentsApi( $container, $this, $container->get( AssetDataRegistry::class ) );
		} );
		$this->container->get( PaymentsApi::class );
	}
}