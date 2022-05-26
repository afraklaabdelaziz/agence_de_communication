<?php

namespace WPDesk\ShopMagic\AutomationOutcome\Meta;

use DateTimeImmutable;
use WPDesk\ShopMagic\Database\Abstraction\DAO\Item;

/**
 * Creates singular outcome log item.
 */
final class OutcomeMetaFactory implements \WPDesk\ShopMagic\Database\Abstraction\DAO\ItemFactory {

	public function create_null(): Item {
		return new OutcomeMeta(
			0,
			new DateTimeImmutable(),
			'',
			[]
		);
	}

	/**
	 * @param array{id: string|null, created: string|null, note: string|null, context: string|null} $data
	 *
	 * @return Item
	 * @throws \Exception
	 */
	public function create_item( array $data ): Item {
		return new OutcomeMeta(
			(int) $data['id'],
			new DateTimeImmutable( $data['created'] ?: 'now' ),
			(string) $data['note'],
			json_decode( $data['context'] ?: '' )
		);
	}
}
