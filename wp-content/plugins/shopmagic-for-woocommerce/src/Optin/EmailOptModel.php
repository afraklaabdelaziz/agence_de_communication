<?php


namespace WPDesk\ShopMagic\Optin;

/**
 * OutIn/OptOut email model.
 *
 * @package WPDesk\ShopMagic\Optin
 */
class EmailOptModel {

	/** @var string */
	private $email;

	/** @var OptInModel[] */
	private $optins;

	/** @var OptOutModel[] */
	private $optouts;

	/**
	 * @param string $email
	 * @param OptInModel[] $optins
	 * @param OptOutModel[] $optouts
	 */
	public function __construct( $email, array $optins, array $optouts ) {
		$this->email   = $email;
		$this->optins  = $optins;
		$this->optouts = $optouts;
	}

	/**
	 * @param int $type_id
	 *
	 * @return bool
	 */
	public function is_opted_in( $type_id ) {
		foreach ( $this->optins as $optin ) {
			if ( $optin->get_list_id() === $type_id ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param int $type_id
	 *
	 * @return bool
	 */
	public function is_opted_out( $type_id ) {
		foreach ( $this->optouts as $optout ) {
			if ( $optout->get_list_id() === $type_id ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @return string
	 */
	public function get_email() {
		return $this->email;
	}

	/**
	 * @return OptInModel[]
	 */
	public function get_optins() {
		return $this->optins;
	}

	/**
	 * @return OptOutModel[]
	 */
	public function get_optouts() {
		return $this->optouts;
	}
}
