<?php

namespace WPDesk\ShopMagic\Admin\Automation\AutomationFormFields;

use ShopMagicVendor\WPDesk\Forms\Field;
use ShopMagicVendor\WPDesk\Forms\Field\BasicField;

final class CompoundField extends BasicField {

	/** @var Field[] */
	private $fields;

	public function __construct( Field ...$fields ) {
		parent::__construct();
		$this->fields = $fields;
	}

	/** @return Field[] */
	public function get_fields(): array {
		return $this->fields;
	}

	public function get_template_name(): string {
		return 'compound-field';
	}

	public function should_override_form_template(): bool {
		return true;
	}

}
