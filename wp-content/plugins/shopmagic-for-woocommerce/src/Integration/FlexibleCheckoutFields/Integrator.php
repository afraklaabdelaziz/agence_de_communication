<?php

namespace WPDesk\ShopMagic\Integration\FlexibleCheckoutFields;

use Psr\Log\LoggerInterface;
use ShopMagicVendor\WPDesk\Notice\Notice;
use WPDesk\ShopMagic\Placeholder\PlaceholderFactory2;

final class Integrator {
	public function integrate_placeholders( PlaceholderFactory2 $factory, LoggerInterface $logger ) {
		add_action(
			'flexible_checkout_fields/init',
			static function ( $integrator ) use ( $factory, $logger ) {
				if ( $integrator instanceof \WPDesk\FCF\Free\Integration\Integrator ) {
					$version = $integrator->get_version();
					if ( version_compare( $version, '1000', '<=' ) ) {
						new Notice(
							__(
								'This version of ShopMagic for WooCommerce is not compatible with currently installed version of Flexible Checkout Fields. Please upgrade ShopMagic to the newest version.',
								'shopmagic-for-woocommerce'
							)
						);

						return;
					}
					$factory->append_placeholder( new Placeholder\OrderCheckoutField( $integrator, $logger ) );
				}
			}
		);
	}
}
