<?php

namespace WPDesk\ShopMagic\Filter\Builtin\Customer;

use WPDesk\ShopMagic\CommunicationList\CommunicationListRepository;
use WPDesk\ShopMagic\Filter\Builtin\CustomerFilter;
use WPDesk\ShopMagic\Filter\ComparisionType\SelectManyToManyType;
use WPDesk\ShopMagic\LoggerFactory;
use WPDesk\ShopMagic\MarketingLists\DAO\ListDTO;
use WPDesk\ShopMagic\MarketingLists\DAO\ListFactory;
use WPDesk\ShopMagic\MarketingLists\DAO\ListTable;
use WPDesk\ShopMagic\Placeholder\Builtin\Customer\CustomerEmail;

class CustomerListFilter extends CustomerFilter {

	/** @var ListTable */
	private $list;

	public function __construct( ListTable $list = null ) {
		$this->list = $list ?? new ListTable( new ListFactory() );
	}

	public function get_name() {
		return __( 'Customer - Subscribed to List', 'shopmagic-for-woocommerce' );
	}

	public function passed() {
		if ( ! $this->is_customer_provided() ) {
			LoggerFactory::get_logger()->warning( 'No customer provided for class ' . __CLASS__ );
			return false;
		}

		$active_subscriptions = $this->list->get_all(
			[
				'email'  => $this->get_customer()->get_email(),
				'active' => '1',
			]
		);

		$active_ids = [];
		foreach ( $active_subscriptions as $active ) {
			$active_ids[] = $active->get_list_id();
		}

		return $this->get_type()->passed(
			$this->fields_data->get( SelectManyToManyType::VALUE_KEY ),
			$this->fields_data->get( SelectManyToManyType::CONDITION_KEY ),
			$active_ids
		);
	}

	protected function get_type() {
		return new SelectManyToManyType( CommunicationListRepository::get_lists_as_select_options() );
	}

}
