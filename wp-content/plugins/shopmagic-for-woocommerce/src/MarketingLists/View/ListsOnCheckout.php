<?php

namespace WPDesk\ShopMagic\MarketingLists\View;

use WPDesk\ShopMagic\CommunicationList\CommunicationListRepository;
use WPDesk\ShopMagic\Frontend\FrontRenderer;
use WPDesk\ShopMagic\MarketingLists\DAO\ListFactory;
use WPDesk\ShopMagic\MarketingLists\DAO\ListTable;

/**
 * Communication type info for customer. Optins/optouts.
 */
final class ListsOnCheckout {

	/** @var ListTable */
	private $list_table;

	public function __construct( ListTable $list_table = null ) {
		$this->list_table = $list_table ?? new ListTable( new ListFactory() );
	}

	/** @return void */
	public function hooks() {
		add_action( 'woocommerce_checkout_after_terms_and_conditions', [ $this, 'render_after_terms_optins' ] );
	}

	private function get_email(): string {
		$session_data = WC()->session->get( 'customer' );
		if ( isset( $session_data['email'] ) ) {
			return sanitize_email( $session_data['email'] );
		}

		return '';
	}

	/** @return void */
	public function render_after_terms_optins() {
		global $wpdb;
		$renderer = new FrontRenderer();
		$ct_repo  = new CommunicationListRepository( $wpdb );
		foreach ( $ct_repo->get_checkout_communication_types() as $type ) {
			$renderer->output_render(
				'checkout_optin',
				[
					'type'     => $type,
					'opted_in' => $this->list_table->is_subscribed_to_list( $this->get_email(), $type->get_id() ),
				]
			);
		}
	}
}
