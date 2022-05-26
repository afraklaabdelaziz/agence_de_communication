<?php

namespace WPDesk\ShopMagic\Action;

use WPDesk\ShopMagic\Automation\Automation;
use WPDesk\ShopMagic\Event\Event;

/**
 * Main reason of action existence is a possibility of performing some action. This is a definition of that capability.
 *
 * @package WPDesk\ShopMagic\Action
 */
interface ActionLogic {
	/**
	 * Execute the action job.
	 *
	 * @param Automation $automation Action context. This action is a part of this automation.
	 * @param Event $event Action context. This action is executed because this event has occurred.
	 *
	 * @return bool Action can provide info about successful execution.
	 */
	public function execute( Automation $automation, Event $event);
}
