<?php

namespace WPDesk\ShopMagic\AutomationOutcome;

interface OutcomeInformationRepository {
	/**
	 * How many outcomes this client has for given automation.
	 *
	 * @param int $automation_id
	 * @param string $customer_id
	 *
	 * @return int
	 */
	public function get_done_automation_count_for_customer( int $automation_id, string $customer_id ): int;

	/**
	 * How many outcomes this client has for given automation in given period of time.
	 *
	 * @param int $automation_id
	 * @param string $customer_id
	 * @param int $in_days
	 *
	 * @return int
	 */
	public function get_done_automation_count_with_time( int $automation_id, string $customer_id, int $in_days ): int;
}
