<?php

namespace WPDesk\ShopMagic\AutomationOutcome;

class SingleOutcome {

	/** @var string */
	private $execution_id;

	/** @var SingleOutcomeLog[] */
	private $logs;

	/** @var \DateTimeImmutable */
	private $update_date;

	/** @var bool|null When null then not finished yet */
	private $success;

	/**
	 * @param string $execution_id
	 * @param SingleOutcomeLog[] $logs
	 * @param \DateTimeImmutable $update_date
	 * @param bool|null $success
	 */
	public function __construct( string $execution_id, array $logs, \DateTimeImmutable $update_date, bool $success ) {
		$this->execution_id = $execution_id;
		$this->logs         = $logs;
		$this->update_date  = $update_date;
		$this->success      = $success;
	}

	/**
	 * @return string
	 */
	public function get_execution_id(): string {
		return $this->execution_id;
	}

	/**
	 * @return SingleOutcomeLog[]
	 */
	public function get_logs(): array {
		return $this->logs;
	}

	/**
	 * @return \DateTimeImmutable
	 */
	public function get_update_date(): \DateTimeImmutable {
		return $this->update_date;
	}

	/**
	 * @return bool|null
	 */
	public function get_success(): bool {
		return $this->success;
	}
}
