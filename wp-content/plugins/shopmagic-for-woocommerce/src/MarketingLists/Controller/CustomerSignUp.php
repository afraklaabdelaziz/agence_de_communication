<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\MarketingLists\Controller;

use Psr\Log\LoggerAwareTrait;
use WPDesk\ShopMagic\CommunicationList\CommunicationListRepository;
use WPDesk\ShopMagic\Exception\CannotProvideItemException;
use WPDesk\ShopMagic\MarketingLists\DAO\ListTable;

final class CustomerSignUp {
	use LoggerAwareTrait;

	/** @var CommunicationListRepository */
	private $lists_repository;

	/** @var ListTable */
	private $subscriptions;

	public function __construct( ListTable $list_table, CommunicationListRepository $lists_repository ) {
		$this->subscriptions    = $list_table;
		$this->lists_repository = $lists_repository;
	}

	/** @return void */
	public function hooks() {
		add_action(
			'woocommerce_checkout_order_processed',
			function( $_, $__, $order ) {
				$this->save_checkout_optins( $order );
			},
			20,
			3
		);
		add_action( 'user_register', [ $this, 'optin_to_softs' ] );
	}

	/**
	 * @return void
	 * @internal
	 */
	public function optin_to_softs( int $user_id ) {
		$user = get_user_by( 'id', $user_id );
		if ( ! $user instanceof \WP_User ) {
			return;
		}

		foreach ( $this->lists_repository->get_soft_optin_communication_types() as $opt_out ) {
			$this->subscriptions->subscribe( $user->user_email, $opt_out->get_id() );
		}
	}

	/** @return void */
	private function save_checkout_optins( \WC_Order $order ) {
		foreach ( $this->lists_repository->get_all() as $type ) {
			if ( isset( $_POST['shopmagic_optin'][ $type->get_id() ] ) &&
				 $_POST['shopmagic_optin'][ $type->get_id() ] === 'yes' &&
				 ! $this->subscriptions->is_subscribed_to_list( $order->get_billing_email(), $type->get_id() )
			) {
				$this->subscriptions->subscribe( $order->get_billing_email(), $type->get_id() );
			}

			if ( $type->is_opt_out() ) {
				try {
					$this->subscriptions->get_subscribed_to_list( $order->get_billing_email(), $type->get_id() );
				} catch ( CannotProvideItemException $e ) {
					$this->subscriptions->subscribe( $order->get_billing_email(), $type->get_id() );
				}
			}
		}
	}

}
