<?php

namespace WPDesk\ShopMagic\Automation\Validator;

use WPDesk\ShopMagic\Automation\AutomationValidator;
use WPDesk\ShopMagic\Filter\FilterLogic;

/**
 * Validates, if filters passing.
 */
final class FiltersValidator extends AutomationValidator {

	/** @var FilterLogic */
	private $filters;

	public function __construct( FilterLogic $filters ) {
		$this->filters = $filters;
	}

	public function valid(): bool {
		$this->filters->set_provided_data( $this->provided_data );
		if ( ! $this->filters->passed() ) {
			return false;
		}

		return parent::valid();
	}

}
