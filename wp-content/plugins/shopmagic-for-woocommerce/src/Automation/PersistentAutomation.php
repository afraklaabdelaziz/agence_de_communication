<?php

namespace WPDesk\ShopMagic\Automation;

/**
 * Can store Automation.
 */
interface PersistentAutomation {

	/**
	 * @return string Automation needs name.
	 */
	public function get_automation_name(): string;

	/**
	 * @return string Every automation has single event.
	 */
	public function get_event_slug(): string;

	/**
	 * @return array<string, int|string> Event settings are stored in an array.
	 */
	public function get_event_data(): array;

	/**
	 * @return array Actions settings are stored in an array.
	 */
	public function get_actions_data(): array;

	/**
	 * @return array Filters settings are stored in an array.
	 */
	public function get_filters_data(): array;
}
