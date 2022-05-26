<?php

namespace WPDesk\ShopMagic\Admin\Automation\AutomationFormFields;

use ShopMagicVendor\WPDesk\Forms\Field\BasicField;

/**
 * Frontend time imput.
 */
final class InputTimeField extends BasicField {

	public function get_template_name(): string {
		return 'input-time';
	}

}
