<?php

namespace WPDesk\ShopMagic\DataSharing\DataAccess;

/**
 *
 * @package WPDesk\ShopMagic\DataSharing\DataAccess
 */
final class NewUserAccount {

	/** @var string  */
	private $password;

	/**
	 * @param string $password
	 */
	public function __construct( $password ) {
		$this->password = $password;
	}

	/**
	 * @return string
	 */
	public function get_password() {
		return $this->password;
	}
}
