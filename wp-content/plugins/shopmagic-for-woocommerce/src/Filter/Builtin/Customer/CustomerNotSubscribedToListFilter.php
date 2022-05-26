<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\Filter\Builtin\Customer;

use WPDesk\ShopMagic\CommunicationList\CommunicationListRepository;
use WPDesk\ShopMagic\Filter\Builtin\CustomerFilter;
use WPDesk\ShopMagic\Filter\ComparisionType\SelectManyToManyType;
use WPDesk\ShopMagic\LoggerFactory;
use WPDesk\ShopMagic\MarketingLists\DAO\ListFactory;
use WPDesk\ShopMagic\MarketingLists\DAO\ListTable;

final class CustomerNotSubscribedToListFilter extends CustomerFilter {

	/** @var ListTable */
	private $list;

	public function __construct( ListTable $list = null ) {
		$this->list = $list ?? new ListTable( new ListFactory() );
	}

	public function get_name(): string {
		return esc_html__( 'Customer - Not Subscribed to List', 'shopmagic-for-woocommerce' );
	}

	public function passed(): bool {
		if ( ! $this->is_customer_provided() ) {
			LoggerFactory::get_logger()->warning( 'No customer provided for class ' . __CLASS__ );
			return false;
		}
		$not_subscribed_lists = [];

		foreach ( $this->fields_data->get( SelectManyToManyType::VALUE_KEY ) as $list_id ) {
			if ( ! $this->list->is_subscribed_to_list( $this->get_customer()->get_email(), (int) $list_id ) ) {
				$not_subscribed_lists[] = $list_id;
			}
		}

		return $this->get_type()->passed(
			$this->fields_data->get( SelectManyToManyType::VALUE_KEY ),
			$this->fields_data->get( SelectManyToManyType::CONDITION_KEY ),
			$not_subscribed_lists
		);
	}

	protected function get_type(): SelectManyToManyType {
		return new SelectManyToManyType( CommunicationListRepository::get_lists_as_select_options() );
	}

}
