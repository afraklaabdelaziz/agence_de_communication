<?php

namespace WPDesk\ShopMagic\Automation;

/**
 * Enables automation to run.
 */
interface Runner {

	/**
	 * Runner needs to be triggered and start the process of setup, validation and execution of automation.
	 *
	 * @return void
	 */
	public function run();

	/**
	 * Needs accessible automation for external processing.
	 *
	 * @return Automation
	 */
	public function get_automation(): Automation;

}
