<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\MarketingLists\DAO;

use Psr\Log\LoggerInterface;
use WPDesk\ShopMagic\Customer\CustomerDAO;
use WPDesk\ShopMagic\Database\Abstraction\DAO;
use WPDesk\ShopMagic\Database\DatabaseSchema;
use WPDesk\ShopMagic\Event\Builtin\CustomerOptedIn;
use WPDesk\ShopMagic\Event\Builtin\CustomerOptedOut;
use WPDesk\ShopMagic\Exception\CannotProvideItemException;
use WPDesk\ShopMagic\Exception\CustomerNotFound;
use WPDesk\ShopMagic\Exception\SubscriptionNotFound;

/**
 * DAO for marketing lists table.
 */
final class ListTable extends \WPDesk\ShopMagic\Database\Abstraction\AbstractSingleTable {

	/** @var DAO\ItemFactory */
	private $factory;

	/** @var CustomerDAO */
	private $customer_dao;

	public function __construct( DAO\ItemFactory $factory, LoggerInterface $logger = null, CustomerDAO $customer_dao = null ) {
		parent::__construct( $logger );
		$this->factory      = $factory;
		$this->customer_dao = $customer_dao ?? new CustomerDAO();
	}

	protected function get_name(): string {
		return DatabaseSchema::get_marketing_lists_table_name();
	}

	protected function get_factory(): DAO\ItemFactory {
		return $this->factory;
	}

	protected function get_primary_key(): array {
		return [ 'id' ];
	}

	protected function get_columns(): array {
		return [
			'id',
			'list_id',
			'email',
			'active',
			'type',
			'created',
			'updated',
		];
	}

	/**
	 * @param int $list_id
	 *
	 * @return DAO\Collection<ListDTO>
	 */
	public function get_list_subscribers( int $list_id ): DAO\Collection {
		return $this->get_all( [ 'list_id' => $list_id ] );
	}

	public function get_subscribed_to_list( string $email, int $list_id ): DAO\Item {
		try {
			return $this->get_single_by_where(
				[
					'email'   => $email,
					'list_id' => $list_id,
				]
			);
		} catch ( CannotProvideItemException $e ) {
			throw new SubscriptionNotFound( sprintf( esc_html__( 'User %1$s is not subscribed to list %2$d', 'shopmagic-for-woocommerce' ), $email, $list_id ) );
		}
	}

	public function is_subscribed_to_list( string $email, int $list_id ): bool {
		return $this->get_count(
			[
				'email'   => $email,
				'list_id' => $list_id,
				'active'  => '1',
			]
		) > 0;
	}

	/**
	 * Although it's not required, this method expects that we already have customer in our system.
	 * Otherwise, subscription data will contain only customer email, without further possibility to enrich them.
	 *
	 * @since 2.37.6
	 */
	public function subscribe( string $email, int $target_list ): bool {
		if ( $this->is_subscribed_to_list( $email, $target_list ) ) {
			return false;
		}

		try {
			/** @var \WPDesk\ShopMagic\MarketingLists\DAO\ListDTO $customer_status */
			$customer_status = $this->get_subscribed_to_list( $email, $target_list );
		} catch ( SubscriptionNotFound $e ) {
			/** @var \WPDesk\ShopMagic\MarketingLists\DAO\ListDTO $customer_status */
			$customer_status = $this->factory->create_for_email_and_list( $email, $target_list );
		}

		$customer_status->set_active( true );
		return $this->save( $customer_status );
	}

	/**
	 * @param ListDTO $item
	 *
	 * @return bool
	 */
	public function save( DAO\Item $item ): bool {
		$result = parent::save( $item );

		if ( $result ) {
			$this->trigger_update_event( $item );
		}

		return $result;
	}

	/** @return void */
	private function trigger_update_event( ListDTO $item ) {
		try {
			$event_args = [
				$this->customer_dao->find_by_email( $item->get_email() ),
				$item->get_list_id(),
			];

			if ( $item->is_active() ) {
				CustomerOptedIn::trigger( $event_args );
			} else {
				CustomerOptedOut::trigger( $event_args );
			}
		} catch ( CustomerNotFound $e ) {
			if ( $this->logger ) {
				$this->logger->error( sprintf( 'Cannot trigger event. Customer %s not found.', $item->get_email() ) );
			}
		}
	}
}
