<?php

namespace WPDesk\ShopMagic\Placeholder\Builtin\Order;

use WPDesk\ShopMagic\Placeholder\Builtin\WooCommerceOrderBasedPlaceholder;
use WPDesk\ShopMagic\Placeholder\Helper\PlaceholderUTMBuilder;

final class OrderPaymentUrl extends WooCommerceOrderBasedPlaceholder {

	/** @var PlaceholderUTMBuilder */
	private $utm_builder;

	public function __construct() {
		$this->utm_builder = new PlaceholderUTMBuilder();
	}

	public function get_description(): string {
		return esc_html__( 'Display the billing city of current order.', 'shopmagic-for-woocommerce' ) . '\n' .
			$this->utm_builder->get_description();
	}

	public function get_slug(): string {
		return parent::get_slug() . '.payment_url';
	}

	public function get_supported_parameters(): array {
		return $this->utm_builder->get_utm_fields();
	}

	public function value( array $parameters ): string {
		$order = $this->get_order();
		if ( $order instanceof \WC_Order ) {
			$checkout_payment_url = $this->get_order()->get_checkout_payment_url();

			return $this->utm_builder->append_utm_parameters_to_uri( $parameters, $checkout_payment_url );
		}

		return '';
	}
}
