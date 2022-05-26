<?php

namespace WPDesk\ShopMagic\DataSharing;

/**
 * Object can announce that it requires some data with ::get_required_data_domains.
 * Object will get these data from ::set_provided_data.
 *
 * @package WPDesk\ShopMagic\DataSharing
 */
interface DataReceiver {
	/**
	 * This object required instances of these classes.
	 *
	 * @return string[] Set of class names that object requires.
	 */
	public function get_required_data_domains();

	/**
	 * Set data corresponding go ::get_required_data_domains.
	 *
	 * @param array $data Required format is [ classname => data, .. ]
	 *
	 * @return void
	 */
	public function set_provided_data( array $data);
}
