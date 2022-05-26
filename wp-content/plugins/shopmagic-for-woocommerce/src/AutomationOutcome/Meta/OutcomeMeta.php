<?php

namespace WPDesk\ShopMagic\AutomationOutcome\Meta;

use WPDesk\ShopMagic\Helper\WordPressFormatHelper;

/**
 * Representation of Outcome Logs single entry.
 */
final class OutcomeMeta implements \WPDesk\ShopMagic\Database\Abstraction\DAO\Item {

	/** @var int */
	private $id;

	/** @var \DateTimeInterface */
	private $created_date;

	/** @var string */
	private $note;

	/** @var string[] */
	private $context;

	/**
	 * @param int                $id
	 * @param \DateTimeInterface $created_date
	 * @param string             $note
	 * @param string[]           $context
	 */
	public function __construct(
		int $id,
		\DateTimeInterface $created_date,
		string $note,
		array $context
	) {
		$this->id           = $id;
		$this->created_date = $created_date;
		$this->note         = $note;
		$this->context      = $context;
	}

	public function get_changed_fields(): array {
		return [];
	}

	public function has_changed(): bool {
		return false;
	}

	public function set_last_inserted_id( int $id ) {
		$this->id = $id;
	}

	/** @return array{id: string, created_date: string, note: string, context: string} */
	public function normalize(): array {
		$result = get_object_vars( $this );

		$result['context']      = json_encode( $this->context );
		$result['created_date'] = WordPressFormatHelper::date_as_mysql( $this->created_date );

		return $result;
	}

	public function get_id(): int {
		return $this->id;
	}

	public function get_created_date(): \DateTimeInterface {
		return $this->created_date;
	}

	public function get_note(): string {
		return $this->note;
	}

	/** @return string[] */
	public function get_context(): array {
		return $this->context;
	}

}
