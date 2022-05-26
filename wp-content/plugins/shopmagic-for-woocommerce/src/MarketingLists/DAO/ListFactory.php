<?php

namespace WPDesk\ShopMagic\MarketingLists\DAO;

use DateTimeImmutable;
use WPDesk\ShopMagic\CommunicationList\CommunicationListPersistence;
use WPDesk\ShopMagic\Database\Abstraction\DAO\Item;

final class ListFactory implements \WPDesk\ShopMagic\Database\Abstraction\DAO\ItemFactory {

	public function create_null(): Item {
		return new ListDTO(
			0,
			0,
			'',
			true,
			true,
			new DateTimeImmutable(),
			new DateTimeImmutable()
		);
	}

	/**
	 * @param array{id: numeric-string, list_id: numeric-string, email: string, created: string, updated: string, type: numeric-string, active: numeric-string} $data
	 *
	 * @return Item
	 * @throws \Exception
	 */
	public function create_item( array $data ): Item {
		return new ListDTO(
			absint( $data['id'] ),
			absint( $data['list_id'] ),
			$data['email'],
			$data['active'] === '1',
			$data['type'] === '1',
			new DateTimeImmutable( $data['created'] ),
			new DateTimeImmutable( $data['updated'] )
		);
	}

	public function create_for_email_and_list( string $email, int $list_id ): Item {
		return new ListDTO(
			0,
			$list_id,
			$email,
			true,
			( new CommunicationListPersistence( $list_id ) )->get( CommunicationListPersistence::FIELD_TYPE_KEY ),
			new DateTimeImmutable(),
			new DateTimeImmutable()
		);
	}
}
