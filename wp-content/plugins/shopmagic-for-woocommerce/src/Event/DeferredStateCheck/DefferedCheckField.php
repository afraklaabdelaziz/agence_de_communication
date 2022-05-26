<?php

namespace WPDesk\ShopMagic\Event\DeferredStateCheck;

use WPDesk\ShopMagic\FormField\Field\CheckboxField;

/**
 * Field to use in events that want to recheck status before run.
 *
 * @package WPDesk\ShopMagic\Event
 */
class DefferedCheckField extends CheckboxField {
	const NAME = 'check_defer';

	public function __construct() {
		parent::__construct();

		$this
			->set_name( self::NAME )
			->set_label( __( 'Recheck order status before run', 'shopmagic-for-woocommerce' ) )
			->set_description_tip(
				__(
					'Useful for delayed automations. Ensures the status hasn\'t changed since initial event.',
					'shopmagic-for-woocommerce'
				)
			);
	}
}
