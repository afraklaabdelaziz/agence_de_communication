<?php


namespace WPDesk\ShopMagic\Guest;

use WPDesk\ShopMagic\Exception\CannotCreateGuestException;

/**
 * Can create guest.
 *
 * @TODO: refactor to use database abstraction in 3.0
 *
 * @package WPDesk\ShopMagic\Business
 */
class GuestFactory {

	/** @var GuestDAO */
	private $repository;

	const ORDER_GUEST_FOREIGN_META_KEY = 'shopmagic_guest_id';

	public static function generate_tracking_key(): string {
		return md5( uniqid( 'sm_', true ) );
	}

	/**
	 * @param GuestDAO $repository
	 */
	public function __construct( GuestDAO $repository ) {
		$this->repository = $repository;
	}

	/**
	 * @param \WC_Abstract_Order $order
	 *
	 * @return true
	 */
	public function order_has_guest( \WC_Abstract_Order $order ) {
		$id = $order->get_user_id();

		return $id === 0;
	}

	/**
	 * @param \WC_Order $order
	 * @param string|null $id Id can be null if guest is not in the db.
	 *
	 * @return Guest
	 */
	private function create_from_order( \WC_Order $order, $id = null ) {
		$email   = $order->get_billing_email();
		$created = $order->get_date_created();
		if ( ! $created instanceof \DateTimeInterface ) {
			$created = new \DateTime();
		}

		if ( ! is_null( $id ) ) {
			$guest = $this->repository->get_by_id($id);
			$metadata = $guest->get_all_metadata();
		}

		$metadata['first_name']        = $order->get_billing_first_name();
		$metadata['last_name']         = $order->get_billing_last_name();
		$metadata['billing_company']   = $order->get_billing_company();
		$metadata['billing_phone']     = $order->get_billing_phone();
		$metadata['billing_address_1'] = $order->get_billing_address_1();
		$metadata['billing_address_2'] = $order->get_billing_address_2();
		$metadata['billing_country']   = $order->get_billing_country();
		$metadata['billing_city']      = $order->get_billing_city();
		$metadata['billing_state']     = $order->get_billing_state();
		$metadata['billing_postcode']  = $order->get_billing_postcode();

		$metadata['shipping_company']   = $order->get_shipping_company();
		$metadata['shipping_address_1'] = $order->get_shipping_address_1();
		$metadata['shipping_address_2'] = $order->get_shipping_address_2();
		$metadata['shipping_country']   = $order->get_shipping_country();
		$metadata['shipping_city']      = $order->get_shipping_city();
		$metadata['shipping_state']     = $order->get_shipping_state();
		$metadata['shipping_postcode']  = $order->get_shipping_postcode();

		return new Guest( $id, $email, self::generate_tracking_key(), $created, $created, $metadata );
	}

	/**
	 * Connects order to ShopMagic guest user. Silently fails if can't.
	 *
	 * @param int $order_id
	 * @param int $guest_id
	 */
	public function touch_order( $order_id, $guest_id ) {
		if ( is_int( $order_id ) && $order_id > 0 && is_int( $guest_id ) ) {
			update_post_meta( $order_id, self::ORDER_GUEST_FOREIGN_META_KEY, $guest_id );
		}
	}

	/**
	 * Returns guest created from order data. Also checks db if that guest already exists and
	 * if so then choose the newest guest.
	 *
	 * warning: This means that the returned guest can be different from saved in the db even if id exists.
	 *
	 * @param \WC_Abstract_Order $order
	 *
	 * @return Guest
	 */
	public function create_from_order_and_db( \WC_Abstract_Order $order ) {
		if ( $order instanceof \WC_Order_Refund ) {
			$order = wc_get_order( $order->get_parent_id() );
		}
		if ( $order instanceof \WC_Order ) {
			$email = $order->get_billing_email();

			try {
				$guest         = $this->repository->get_by_email( $email );
				$order_created = $order->get_date_created();
				if ( $order_created !== null && $guest->get_created()->getOffset() > $order_created->getOffset() ) {
					return $guest;
				}

				return $this->create_from_order( $order, $guest->get_id() );
			} catch ( CannotCreateGuestException $e ) {
				return $this->create_from_order( $order );
			}
		}
		throw new CannotCreateGuestException( 'WC_Order_Refund has no valid parent' );
	}

	/**
	 * @param $email
	 *
	 * @return Guest
	 */
	public function create_from_email_and_db( $email ) {
		try {
			return $this->repository->get_by_email( $email );
		} catch ( CannotCreateGuestException $e ) {
			return new Guest( null, $email, self::generate_tracking_key(), new \DateTime(), new \DateTime(), [] );
		}
	}
}
