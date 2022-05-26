<?php

namespace WPDesk\ShopMagic\Event;

use WPDesk\ShopMagic\Automation\Automation;
use WPDesk\ShopMagic\Filter\FilterLogic;

interface EventFactory2 {
	const GROUP_USERS        = 'users';
	const GROUP_CARTS        = 'carts';
	const GROUP_ORDERS       = 'orders';
	const GROUP_SUBSCRIPTION = 'subscriptions';
	const GROUP_MEMBERSHIPS  = 'memberships';
	const GROUP_PRO          = 'pro';
	const GROUP_FORMS        = 'forms';
	const GROUP_AUTOMATION = 'automation';

	/**
	 * @param string           $slug
	 * @param Automation  $automation Deprecated.
	 * @param FilterLogic $filters Deprecated.
	 *
	 * @return Event
	 */
	public function create_event( string $slug, Automation $automation, FilterLogic $filters ): Event;

	/**
	 * @return Event[]
	 */
	public function get_event_list(): array;

	/**
	 * @param string $group_id
	 *
	 * @return string
	 */
	public function event_group_name( string $group_id ): string;

	/**
	 * @param string $slug
	 *
	 * @return Event
	 */
	public function get_event( string $slug ): Event;

}
