<?php

namespace WPDesk\ShopMagic\Automation\Validator;

use WPDesk\ShopMagic\Automation\Automation;
use WPDesk\ShopMagic\Automation\AutomationValidator;
use WPDesk\ShopMagic\Event\NullEvent;

/**
 * Validates, if automation has real event and at least one action.
 */
final class FullyConfiguredValidator extends AutomationValidator {

	/** @var Automation */
	private $automation;

	public function __construct( Automation $automation ) {
		$this->automation = $automation;
	}

	public function valid(): bool {
		if ( $this->automation->get_event() instanceof NullEvent ) {
			return false;
		}

		if ( count( $this->automation->get_actions() ) <= 0 ) {
			return false;
		}

		return parent::valid();
	}
}
