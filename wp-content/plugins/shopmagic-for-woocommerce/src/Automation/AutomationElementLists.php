<?php

namespace WPDesk\ShopMagic\Automation;

/**
 * List of names of all events, filters, actions and plugins that the plugin has.
 */
final class AutomationElementLists {

	/** @var string[] */
	private $installed_events;

	/** @var string[] */
	private $installed_filters;

	/** @var string[] */
	private $installed_actions;

	/** @var string[] */
	private $installed_placeholders;

	/**
	 * @param string[] $installed_events
	 * @param string[] $installed_filters
	 * @param string[] $installed_actions
	 * @param string[] $installed_placeholders
	 */
	public function __construct( array $installed_events, array $installed_filters, array $installed_actions, array $installed_placeholders ) {
		$this->installed_events       = $installed_events;
		$this->installed_filters      = $installed_filters;
		$this->installed_actions      = $installed_actions;
		$this->installed_placeholders = $installed_placeholders;
	}

	/** @return string[] */
	public function get_installed_events(): array {
		return $this->installed_events;
	}

	/** @return string[] */
	public function get_installed_filters(): array {
		return $this->installed_filters;
	}

	/** @return string[] */
	public function get_installed_actions(): array {
		return $this->installed_actions;
	}

	/** @return string[] */
	public function get_installed_placeholders(): array {
		return $this->installed_placeholders;
	}

}
