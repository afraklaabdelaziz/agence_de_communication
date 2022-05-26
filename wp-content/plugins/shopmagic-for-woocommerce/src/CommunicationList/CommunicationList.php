<?php

namespace WPDesk\ShopMagic\CommunicationList;

/**
 * @package WPDesk\ShopMagic\CommunicationList
 */
final class CommunicationList {
	const TYPE_OPTIN  = 'opt_in';
	const TYPE_OPTOUT = 'opt_out';

	/** @var int */
	private $id;

	/** @var string */
	private $name;

	/** @var string */
	private $type;

	/** @var bool */
	private $checkout_available;

	/** @var string */
	private $checkbox_label;

	/** @var string */
	private $checkbox_description;

	/**
	 * CommunicationType constructor.
	 *
	 * @param int $id
	 * @param string $name
	 * @param string $type One of [TYPE_OPTIN, TYPE_OPTOUT]
	 * @param bool $checkout_available
	 * @param string $checkbox_label
	 * @param string $checkbox_description
	 */
	public function __construct( $id, $name, $type, $checkout_available, $checkbox_label, $checkbox_description ) {
		$this->id                   = $id;
		$this->name                 = $name;
		$this->type                 = $type;
		$this->checkout_available   = $checkout_available;
		$this->checkbox_label       = $checkbox_label;
		$this->checkbox_description = $checkbox_description;
	}

	/**
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * @return string One of [TYPE_OPTIN, TYPE_OPTOUT]
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * @return bool
	 */
	public function is_opt_out() {
		return $this->get_type() === self::TYPE_OPTOUT;
	}

	/**
	 * @return bool
	 */
	public function is_checkout_available() {
		return $this->checkout_available;
	}

	/**
	 * @return string
	 */
	public function get_checkbox_label() {
		return $this->checkbox_label;
	}

	/**
	 * @return string
	 */
	public function get_checkbox_description() {
		return $this->checkbox_description;
	}
}
