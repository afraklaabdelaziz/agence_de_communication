<?php

namespace WPDesk\ShopMagic\Placeholder\Builtin\Order;

use WPDesk\ShopMagic\Placeholder\Builtin\WooCommerceOrderBasedPlaceholder;
use WPDesk\ShopMagic\Placeholder\Helper\DateFormatHelper;

final class OrderDateCreated extends WooCommerceOrderBasedPlaceholder {

	/** @var \WPDesk\ShopMagic\Placeholder\Helper\DateFormatHelper */
	private $date_format_helper;

	public function __construct() {
		$this->date_format_helper = new DateFormatHelper();
	}

	public function get_description(): string {
		return esc_html__( 'Display the date of creating order.', 'shopmagic-for-woocommerce' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_slug() {
		return parent::get_slug() . '.date_created';
	}

	/**
	 * @inheritDoc
	 */
	public function get_supported_parameters() {
		return $this->date_format_helper->get_supported_parameters();
	}

	/**
	 * @param array $parameters
	 *
	 * @return string
	 */
	public function value( array $parameters ) {
		return $this->date_format_helper->format_date( $this->get_order()->get_date_created(), $parameters );
	}
}
