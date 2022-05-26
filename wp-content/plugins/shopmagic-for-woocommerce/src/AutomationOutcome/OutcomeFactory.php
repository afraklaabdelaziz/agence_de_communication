<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\AutomationOutcome;

use DateTimeImmutable;
use WPDesk\ShopMagic\AutomationOutcome\Meta\OutcomeMetaFactory;
use WPDesk\ShopMagic\Customer\CustomerFactory;
use WPDesk\ShopMagic\Database\Abstraction\DAO\Item;
use WPDesk\ShopMagic\Database\Abstraction\DAO\ItemFactory;
use WPDesk\ShopMagic\Exception\CannotCreateGuestException;
use WPDesk\ShopMagic\Guest\Guest;

/**
 * Creates single outcome representation.
 */
final class OutcomeFactory implements ItemFactory {

	/** @var CustomerFactory */
	private $customer_factory;

	public function __construct( CustomerFactory $customer_factory ) {
		$this->customer_factory = $customer_factory;
	}

	public function create_null(): Item {
		return new Outcome(
			0,
			'',
			0,
			'',
			0,
			'',
			$this->customer_factory->create_null(),
			'',
			false,
			false,
			new DateTimeImmutable(),
			new DateTimeImmutable()
		);
	}

	/**
	 * @param array{id: string|null, execution_id: string, automation_id: string, automation_name: string, action_index: string, action_name: string, customer_id: string|null, guest_id: string|null, customer_email: string, success: string, finished: string, created: string, updated: string} $data
	 *
	 * @return Item
	 * @throws \Exception
	 */
	public function create_item( array $data ): Item {
		if ( ! empty( $data['customer_id'] ) && strpos( $data['customer_id'], CustomerFactory::GUEST_ID_PREFIX ) === false ) {
			$customer = $this->customer_factory->create_from_user( new \WP_User( $data['customer_id'] ) );
		} else {
			try {
				if ( is_string( $data['guest_id'] ) ) {
					$data['guest_id'] = ltrim( $data['guest_id'] ?? '', CustomerFactory::GUEST_ID_PREFIX );
				}
				$customer = $this->customer_factory->create_from_guest_id( (int) $data['guest_id'] );
			} catch ( CannotCreateGuestException $e ) {
				$customer = $this->customer_factory->create_null();
			}
		}

		return new Outcome(
			(int) $data['id'],
			$data['execution_id'],
			(int) $data['automation_id'],
			$data['automation_name'],
			(int) $data['action_index'],
			$data['action_name'],
			$customer,
			$data['customer_email'],
			$data['success'] === '1',
			$data['finished'] === '1',
			new DateTimeImmutable( $data['created'] ?: 'now' ),
			new DateTimeImmutable( $data['updated'] ?: 'now' )
		);
	}
}
