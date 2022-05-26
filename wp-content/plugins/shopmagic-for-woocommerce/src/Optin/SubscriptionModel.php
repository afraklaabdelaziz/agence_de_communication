<?php


namespace WPDesk\ShopMagic\Optin;

use WPDesk\ShopMagic\Helper\WordPressFormatHelper;

/**
 * Base model for optnin/out.
 *
 * @package WPDesk\ShopMagic\Optin
 */
abstract class SubscriptionModel {

	/** @var int */
	protected $list_id;

	/** @var \DateTimeImmutable */
	protected $created;

	/** @var string */
	protected $name;

	/**
	 * @param int $list_id
	 * @param string $name
	 * @param \DateTimeImmutable $created
	 */
	public function __construct( $list_id, $name, \DateTimeImmutable $created ) {
		$this->list_id = $list_id;
		$this->name    = $name;
		$this->created = $created;
	}

	/**
	 * @return int
	 */
	public function get_list_id() {
		return $this->list_id;
	}

	/**
	 * @return \DateTimeImmutable
	 */
	public function get_created() {
		return $this->created;
	}

	/**
	 * @return string
	 */
	public function get_created_as_string() {
		return WordPressFormatHelper::format_wp_datetime( $this->get_created() );
	}

	/**
	 * @return string
	 */
	public function get_list_name() {
		return $this->name;
	}
}
