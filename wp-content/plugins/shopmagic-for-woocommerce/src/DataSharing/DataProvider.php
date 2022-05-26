<?php

namespace WPDesk\ShopMagic\DataSharing;

/**
 *
 * @package WPDesk\ShopMagic\DataSharing
 */
interface DataProvider extends \JsonSerializable {
	/**
	 * List of classes that an provider can provide.
	 *
	 * @return string[]
	 */
	public function get_provided_data_domains();

	/**
	 * Object instances promised in get_provided_data_domains.
	 *
	 * @TODO: use provided data object
	 *
	 * object[]
	 */
	public function get_provided_data();
}
