<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\Helper;

/**
 * Trait helpful for determining user access basing on capabilities.
 * Able to poll different capabilities and return the name of first matching.
 */
trait CapabilitiesCheckTrait {

	/** @var string[] */
	private $allowed_capabilities = [ 'manage_options', 'edit_others_shop_orders' ];

	/** @return string|false */
	private function allowed_capability() {
		foreach ( $this->allowed_capabilities as $capability ) {
			if ( current_user_can( $capability ) ) {
				return $capability;
			}
		}

		return false;
	}

}
