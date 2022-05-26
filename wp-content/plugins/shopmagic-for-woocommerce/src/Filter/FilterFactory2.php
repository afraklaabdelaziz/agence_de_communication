<?php

namespace WPDesk\ShopMagic\Filter;

use WPDesk\ShopMagic\DataSharing\DataProvider;
use WPDesk\ShopMagic\DataSharing\DataReceiver;

interface FilterFactory2 {
	/**
	 * @return Filter[]
	 */
	public function get_filter_list(): array;

	/**
	 * @param string $slug
	 *
	 * @return Filter
	 */
	public function create_filter( string $slug ): Filter;

	/**
	 * @param DataProvider $provider
	 *
	 * @return Filter[]
	 */
	public function get_filter_list_to_handle( DataProvider $provider ): array;

	/**
	 * @param string $slug
	 *
	 * @return Filter
	 */
	public function get_filter( string $slug ): Filter;
}
