<?php

namespace WPDesk\ShopMagic\Admin\Automation\AutomationFormFields;

use ShopMagicVendor\WPDesk\Forms\Field\BasicField;

/**
 * Can show and populate multiple checkboxes as a field.
 */
final class MultipleCheckboxField extends BasicField {

	public function get_template_name(): string {
		return 'input-multiple-checkbox';
	}

	/** @param string[] $options */
	public function set_options( array $options ): self {
		$this->meta['possible_values'] = $options;
		return $this;
	}
}
