<?php

namespace WPDesk\ShopMagic\AutomationOutcome;

use WPDesk\ShopMagic\Customer\Customer;

class OutcomeInTable {

	/** @var string */
	private $execution_id;

	/** @var int */
	private $automation_id;

	/** @var string */
	private $automation_name;

	/** @var Customer */
	private $customer;

	/** @var string */
	private $customer_email;

	/** @var string */
	private $action_name;

	/** @var \DateTimeImmutable */
	private $update_date;

	/** @var bool|null When null then not finished yet */
	private $success;

	/** @var bool */
	private $has_logs;

	/**
	 * OutcomeInTable constructor.
	 *
	 * @param string $execution_id
	 * @param int $automation_id
	 * @param string $automation_name
	 * @param Customer $customer
	 * @param string $customer_email
	 * @param string $action_name
	 * @param bool|null $success
	 * @param \DateTimeImmutable $update_date
	 * @param bool $has_logs
	 */
	public function __construct( $execution_id, $automation_id, $automation_name, Customer $customer, $customer_email, $action_name, $success, \DateTimeImmutable $update_date, $has_logs ) {
		$this->execution_id    = $execution_id;
		$this->automation_id   = $automation_id;
		$this->automation_name = $automation_name;
		$this->customer        = $customer;
		$this->customer_email  = $customer_email;
		$this->action_name     = $action_name;
		$this->success         = $success;
		$this->update_date     = $update_date;
		$this->has_logs        = $has_logs;
	}

	/**
	 * @return string
	 */
	public function get_execution_id() {
		return $this->execution_id;
	}

	/**
	 * @return int
	 */
	public function get_automation_id() {
		return $this->automation_id;
	}

	/**
	 * @return string
	 */
	public function get_automation_name() {
		return $this->automation_name;
	}

	/**
	 * @return Customer
	 */
	public function get_customer() {
		return $this->customer;
	}

	/**
	 * @return string
	 */
	public function get_customer_email() {
		return $this->customer_email;
	}

	/**
	 * @return string
	 */
	public function get_action_name() {
		return $this->action_name;
	}

	/**
	 * @return \DateTimeImmutable
	 */
	public function get_update_date() {
		return $this->update_date;
	}

	/**
	 * @return bool|null
	 */
	public function get_success() {
		return $this->success;
	}

	/**
	 * @return bool
	 */
	public function has_logs() {
		return $this->has_logs;
	}
}
