<?php

namespace WPDesk\ShopMagic\DataSharing;

/**
 * Can provide list of items and render them one by one.
 *
 * @package WPDesk\ShopMagic\DataSharing
 */
interface RenderableItemWithPaginationProvider extends RenderableItemProvider {
	/**
	 * Can provide some unknown items that can be iterated.
	 *
	 * @param int $page Indexed from 1
	 * @param int $page_size
	 *
	 * @return \Iterator
	 */
	public function get_items_page( int $page, int $page_size ): \Iterator;

	public function get_maximum_possible_count(): int;
}
