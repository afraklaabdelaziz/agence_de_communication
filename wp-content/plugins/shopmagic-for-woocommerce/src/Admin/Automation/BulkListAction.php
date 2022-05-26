<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\Admin\Automation;

interface BulkListAction {

	/**
	 * @param string[] $actions
	 *
	 * @return string[]
	 */
	public function add_action( array $actions ): array;

	/**
	 * @param int[] $ids
	 */
	public function handle_action( string $sendback, string $doaction, array $ids ): string;

}
