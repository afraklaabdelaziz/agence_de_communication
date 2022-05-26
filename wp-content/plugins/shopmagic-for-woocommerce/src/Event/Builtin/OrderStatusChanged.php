<?php

namespace WPDesk\ShopMagic\Event\Builtin;

use WPDesk\ShopMagic\Event\DeferredStateCheck\DefferedCheckField;
use WPDesk\ShopMagic\Event\OrderCommonEvent;
use WPDesk\ShopMagic\Event\DeferredStateCheck\SupportsDeferredCheck;
use WPDesk\ShopMagic\FormField\Field\CheckboxField;
use WPDesk\ShopMagic\FormField\Field\SelectField;
use WPDesk\ShopMagic\Helper\WooCommerceStatusHelper;

final class OrderStatusChanged extends OrderCommonEvent implements SupportsDeferredCheck {
	const PARAM_STATUS_FROM = 'order_status_from';
	const PARAM_STATUS_TO   = 'order_status_to';

	/**
	 * @inheritDoc
	 */
	public function get_name() {
		return __( 'Order Status Changed', 'shopmagic-for-woocommerce' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_description() {
		return __( 'Triggered when order status is changed.', 'shopmagic-for-woocommerce' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_fields() {
		$fields = [];

		$fields[] = ( new SelectField() )
			->set_label( __( 'Status changes from', 'shopmagic-for-woocommerce' ) )
			->set_name( self::PARAM_STATUS_FROM )
			->set_placeholder( __( 'Any status', 'shopmagic-for-woocommerce' ) )
			->set_options( wc_get_order_statuses() );

		$fields[] = ( new SelectField() )
			->set_label( __( 'Status changes to', 'shopmagic-for-woocommerce' ) )
			->set_name( self::PARAM_STATUS_TO )
			->set_placeholder( __( 'Any status', 'shopmagic-for-woocommerce' ) )
			->set_options( wc_get_order_statuses() );

		$fields[] = new DefferedCheckField();

		return $fields;
	}

	/**
	 * @inheritDoc
	 */
	public function initialize() {
		add_action( 'woocommerce_order_status_changed', [ $this, 'status_changed' ], 10, 4 );
	}

	/**
	 * Check valid statuses and run actions.
	 *
	 * @param int $order_id
	 * @param string $old_status
	 * @param string $new_status
	 * @param \WC_Order $order
	 */
	public function status_changed( $order_id, $old_status, $new_status, $order ) {
		$this->order = $order;

		$order_status_from = $this->fields_data->get( self::PARAM_STATUS_FROM );
		$order_status_to   = $this->fields_data->get( self::PARAM_STATUS_TO );

		if ( empty( $order_status_from ) || WooCommerceStatusHelper::validate_status_field( $order_status_from, $old_status ) ) {
			if ( empty( $order_status_to ) || WooCommerceStatusHelper::validate_status_field( $order_status_to, $new_status ) ) {
				$this->run_actions();
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function is_event_still_valid() {
		if ( ! $this->fields_data->has( DefferedCheckField::NAME ) || $this->fields_data->get( DefferedCheckField::NAME ) === CheckboxField::VALUE_FALSE ) {
			return true;
		}

		$required_status = $this->fields_data->get( self::PARAM_STATUS_TO );
		return empty( $required_status ) || WooCommerceStatusHelper::validate_status_field( $required_status, $this->get_order()->get_status() );
	}
}
