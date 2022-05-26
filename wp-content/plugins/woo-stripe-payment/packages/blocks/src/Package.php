<?php


namespace PaymentPlugins\Blocks\Stripe;


class Package {

	public static function init() {
		self::maybe_load_config();
	}

	/**
	 * @throws \Exception
	 *
	 * Loads the Blocks integration if WooCommerce Blocks is installed as a feature plugin.
	 */
	private static function maybe_load_config() {
		if ( \class_exists( '\Automattic\WooCommerce\Blocks\Package' ) ) {
			if ( \method_exists( '\Automattic\WooCommerce\Blocks\Package', 'feature' ) ) {
				$feature = \Automattic\WooCommerce\Blocks\Package::feature();
				if ( \method_exists( $feature, 'is_feature_plugin_build' ) ) {
					if ( $feature->is_feature_plugin_build() ) {
						self::container()->get( Config::class );
					}
				}
			}
		}
	}

	/**
	 * @return \Automattic\WooCommerce\Blocks\Registry\Container
	 */
	public static function container() {
		static $container;
		if ( ! $container ) {
			$container = \Automattic\WooCommerce\Blocks\Package::container();
			$container->register( Config::class, function ( $container ) {
				return new Config( stripe_wc()->version(), $container, dirname( __DIR__ ) );
			} );
		}

		return $container;
	}


}