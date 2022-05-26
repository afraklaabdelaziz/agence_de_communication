<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\Admin\Automation;

interface ListAction {

	/**
	 * @param string[] $actions
	 * @param \WP_Post $post
	 *
	 * @return string[]
	 */
	public function add_action( array $actions, \WP_Post $post ): array;

	/**
	 * @return void
	 */
	public function handle_action();

}
