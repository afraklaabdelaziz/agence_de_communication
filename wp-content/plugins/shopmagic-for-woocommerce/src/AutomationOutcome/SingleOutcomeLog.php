<?php

namespace WPDesk\ShopMagic\AutomationOutcome;

class SingleOutcomeLog {

	/** @var \DateTimeImmutable */
	private $created_date;

	/** @var string */
	private $note;

	/** @var array */
	private $context;

	/**
	 * SingleOutcomeLog constructor.
	 *
	 * @param \DateTimeImmutable $created_date
	 * @param string $note
	 * @param array $context
	 */
	public function __construct( \DateTimeImmutable $created_date, string $note, array $context ) {
		$this->created_date = $created_date;
		$this->note         = $note;
		$this->context      = $context;
	}

	/**
	 * @return \DateTimeImmutable
	 */
	public function get_created_date(): \DateTimeImmutable {
		return $this->created_date;
	}

	/**
	 * @return string
	 */
	public function get_note(): string {
		return $this->note;
	}

	/**
	 * @return array
	 */
	public function get_context(): array {
		return $this->context;
	}
}
