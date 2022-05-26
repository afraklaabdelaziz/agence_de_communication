<?php

namespace WPDesk\ShopMagic\Customer;

/**
 * Customer from given data.
 *
 * @package WPDesk\ShopMagic\Customer
 */
final class CustomerDTO implements Customer2 {

	/** @var bool */
	private $is_guest;

	/** @var string|int */
	private $id;

	/** @var string */
	private $username;

	/** @var string */
	private $first_name;

	/** @var string */
	private $last_name;

	/** @var string */
	private $full_name;

	/** @var string */
	private $email;

	/** @var string */
	private $phone;

	/** @var string */
	private $language;

	public function __construct( $is_guest, $id, $username, $first_name, $last_name, $full_name, $email, $phone, string $language = null ) {
		$this->is_guest   = $is_guest;
		$this->id         = $id;
		$this->username   = $username;
		$this->first_name = $first_name;
		$this->last_name  = $last_name;
		$this->full_name  = $full_name;
		$this->email      = $email;
		$this->phone      = $phone;
		$this->language   = $language;
	}

	/**
	 * @return bool
	 */
	public function is_guest() {
		return $this->is_guest;
	}

	/**
	 * @return string
	 */
	public function get_id() {
		return (string) $this->id;
	}

	/**
	 * @return string
	 */
	public function get_username() {
		return $this->username;
	}

	/**
	 * @return string
	 */
	public function get_first_name() {
		return $this->first_name;
	}

	/**
	 * @return string
	 */
	public function get_last_name() {
		return $this->last_name;
	}

	/**
	 * @return string
	 */
	public function get_full_name() {
		return $this->full_name;
	}

	/**
	 * @return string
	 */
	public function get_email() {
		return $this->email;
	}

	/**
	 * @return string
	 */
	public function get_phone() {
		return $this->phone;
	}

	public function get_language(): string {
		return $this->language;
	}
}
