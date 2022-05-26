<?php

namespace WPDesk\ShopMagic\Admin\Automation\AutomationFormFields;

use WPDesk\ShopMagic\FormField\BasicField;

/**
 * Notice field for items available in pro version.
 *
 * @todo Change name to ProItemInfoField
 * @package WPDesk\ShopMagic\Admin\Automation\AutomationFormFields
 */
final class ProEventInfoField extends BasicField {

	public function __construct() {
		parent::__construct();
		$this->set_name( '' );
	}

	public function get_template_name(): string {
		return 'pro-event-info';
	}

	public function should_override_form_template(): bool {
		return true;
	}
}
