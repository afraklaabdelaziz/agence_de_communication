<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\Filter;

use WPDesk\ShopMagic\Event\EventFactory2;

/**
 * Base class for filters showing info about PRO upgrade.
 *
 * @package WPDesk\ShopMagic\Filter
 */
abstract class ImitationCommonFilter implements Filter {

	public function get_required_data_domains(): array {
		return [];
	}

	final public function update_fields_data( \Psr\Container\ContainerInterface $data ) {
	}

	final public function get_description(): string {
		return '';
	}

	// @phpstan-ignore-next-line
	final public function set_provided_data( array $data ) {
	}

	abstract public function get_name(): string;

	public function get_group_slug(): string {
		return EventFactory2::GROUP_PRO;
	}

	final public function passed(): bool {
		return false;
	}
}
