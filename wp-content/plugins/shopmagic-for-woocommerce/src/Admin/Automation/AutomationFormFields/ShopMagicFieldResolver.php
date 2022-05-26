<?php

namespace WPDesk\ShopMagic\Admin\Automation\AutomationFormFields;

use ShopMagicVendor\WPDesk\View\Resolver\DirResolver;

/**
 * Small wrapper abstracting the access to form field templates.
 * Useful for external plugins using fields.
 */
final class ShopMagicFieldResolver extends DirResolver {
	public function __construct() {
		parent::__construct( __DIR__ . '/field-templates' );
	}

}
