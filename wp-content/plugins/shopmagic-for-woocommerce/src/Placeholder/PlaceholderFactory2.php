<?php

namespace WPDesk\ShopMagic\Placeholder;

use WPDesk\ShopMagic\DataSharing\DataProvider;

interface PlaceholderFactory2 {
	public function create_placeholder( DataProvider $provider, string $slug ): Placeholder;

	public function is_placeholder_available( DataProvider $provider, string $slug ): bool;

	public function get_placeholder_by_slug( string $slug ): Placeholder;

	/**
	 * @param DataProvider $provider
	 *
	 * @return Placeholder[]
	 */
	public function get_placeholder_list_to_handle( DataProvider $provider ): array;

	public function append_placeholder( Placeholder $placeholder );

	/**
	 * @return string[]
	 *
	 * @TODO: uncomment in 3.0(!)
	 */
	// public function get_possible_placeholder_slugs(): array; phpcs:ignore
}
