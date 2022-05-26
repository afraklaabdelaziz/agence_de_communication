<?php

namespace WPDesk\ShopMagic\CommunicationList;

/**
 * @package WPDesk\ShopMagic\CommunicationList
 */
final class CommunicationListForTable {

	/**
	 * @var int
	 */
	private $optin_count;

	/**
	 * @var int
	 */
	private $optout_count;

	/** @var string */
	private $type;

	public function __construct( $type, $optin_count, $optout_count ) {
		$this->optin_count  = $optin_count;
		$this->optout_count = $optout_count;
		$this->type         = $type;
	}


	/**
	 * @return int
	 */
	public function get_optin_count() {
		return $this->optin_count;
	}

	/**
	 * @return int
	 */
	public function get_optout_count() {
		return $this->optout_count;
	}


	/**
	 * @return string
	 */
	public function get_type_name() {
		switch ( $this->type ) {
			case CommunicationList::TYPE_OPTIN:
				return __( 'Opt-in', 'shopmagic-for-woocomerce' );
			case CommunicationList::TYPE_OPTOUT:
				return __( 'Opt-out', 'shopmagic-for-woocomerce' );
		}

		return '';
	}
}
