<?php

namespace WPDesk\ShopMagic\Automation;

use WPDesk\ShopMagic\DataSharing\Traits\DataReceiverAsProtectedField;

/**
 * Base class in chain of request for validating current automation.
 */
class AutomationValidator {
	use DataReceiverAsProtectedField;

	/** @var AutomationValidator */
	private $next;

	final public function add_validator( AutomationValidator $validator ): AutomationValidator {
		$this->next = $validator;
		return $validator;
	}

	public function valid(): bool {
		if ( $this->next === null ) {
			return true;
		}
		$this->next->set_provided_data( $this->provided_data );
		return $this->next->valid();
	}
}
