<?php

declare( strict_types=1 );

namespace WPDesk\ShopMagic\Database\Abstraction\Schema;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Single schema update.
 *
 * @package WPDesk\ShopMagic\Database
 */
abstract class Update implements LoggerAwareInterface {
	use LoggerAwareTrait;

	/**
	 * Returns unique int that marks this update.
	 * Each update must have unique id and each new update must have id higher than the last one.
	 *
	 * @return int Unique id.
	 */
	abstract public static function get_version(): int;

	/**
	 * Do the update.
	 *
	 * @return bool Returns false when fails.
	 */
	abstract public function update( \wpdb $wpdb ): bool;

	/**
	 * Each update should know how to clean after itself when the plugin is uninstalled.
	 * This method does the cleaning.
	 *
	 * @return bool Returns false when fails.
	 */
	public function cleanup( \wpdb $wpdb ): bool {
		return true;
	}
}
