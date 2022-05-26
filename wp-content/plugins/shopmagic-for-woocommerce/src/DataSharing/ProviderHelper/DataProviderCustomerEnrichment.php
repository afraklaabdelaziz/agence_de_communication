<?php

namespace WPDesk\ShopMagic\DataSharing\ProviderHelper;

use WPDesk\ShopMagic\Customer\Customer;
use WPDesk\ShopMagic\Customer\CustomerFactory;
use WPDesk\ShopMagic\DataSharing\DataProvider;
use WPDesk\ShopMagic\Guest\GuestFactory;
use WPDesk\ShopMagic\Guest\GuestDAO;

/**
 * Using info about currently accessible data can create an additional layer of metadata.
 *
 * @package WPDesk\ShopMagic\DataSharing
 */
class DataProviderCustomerEnrichment implements DataProvider {

	/** @var DataProvider */
	private $provider;

	public function __construct( DataProvider $provider ) {
		$this->provider = $provider;
	}

	/**
	 * @inheritDoc
	 */
	public function get_provided_data_domains() {
		$additional_domains = [];
		if ( $this->can_add_customer() ) {
			$additional_domains[] = Customer::class;
		}

		return $additional_domains;
	}

	private function can_add_customer(): bool {
		$domains = $this->provider->get_provided_data_domains();

		return in_array( \WP_User::class, $domains, true ) && ! in_array( Customer::class, $domains, true );
	}

	/** @return object[] */
	public function get_provided_data(): array {
		$additional_data = [];
		if ( $this->can_add_customer() ) {
			$provided_data = $this->provider->get_provided_data();
			$domains       = $this->provider->get_provided_data_domains();

			$user_exists  = in_array( \WP_User::class, $domains, true ) && $provided_data[ \WP_User::class ] instanceof \WP_User;
			$order_exists = in_array( \WC_Order::class, $domains, true ) && $provided_data[ \WC_Order::class ] instanceof \WC_Abstract_Order;

			$customer_factory = new CustomerFactory();

			if ( $user_exists && $order_exists ) {
				$additional_data[ Customer::class ] = $customer_factory->create_from_user_and_order( $provided_data[ \WP_User::class ], $provided_data[ \WC_Order::class ] );
			} elseif ( $user_exists ) {
				$additional_data[ Customer::class ] = $customer_factory->create_from_user( $provided_data[ \WP_User::class ] );
			} elseif ( $order_exists ) {
				global $wpdb;
				$guest_dao = new GuestDAO( $wpdb );
				$guest     = ( new GuestFactory( $guest_dao ) )->create_from_order_and_db( $provided_data[ \WC_Order::class ] );
				if ( ! $guest->is_saved() ) {
					$guest_dao->save( $guest );
				}
				$additional_data[ Customer::class ] = $customer_factory->create_from_guest( $guest );
			}
		}

		return $additional_data;
	}

	/**
	 * @inheritDoc
	 */
	public function jsonSerialize() {
		$data = $this->get_provided_data();
		if ( isset( $data[ Customer::class ] ) ) {
			$customer = $data[ Customer::class ];
			if ( $customer instanceof Customer ) {
				return [
					'guest'          => $customer->is_guest(),
					'customer_id'    => (string) $customer->get_id(),
					// must be string as guest has non-numeric id and json adds "".
					'customer_email' => $customer->get_email(),
				];
			}
		}

		return [];
	}
}
