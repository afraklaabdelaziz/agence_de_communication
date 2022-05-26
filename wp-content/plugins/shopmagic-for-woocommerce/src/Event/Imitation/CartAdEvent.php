<?php

namespace WPDesk\ShopMagic\Event\Imitation;

use WPDesk\ShopMagic\Admin\Automation\AutomationFormFields\ProEventInfoField;
use WPDesk\ShopMagic\Admin\Settings\CartAdSettings;
use WPDesk\ShopMagic\Event\EventFactory2;
use WPDesk\ShopMagic\Event\ImitationCommonEvent;

/**
 * Event that never fires and only shows info about PRO upgrades.
 */
final class CartAdEvent extends ImitationCommonEvent {
	public function get_name(): string {
		return __( 'Cart Abandoned', 'shopmagic-for-woocommerce' );
	}

	public function get_description(): string {
		return '';
	}

	public function get_group_slug(): string {
		return EventFactory2::GROUP_CARTS;
	}

	public function get_fields(): array {
		return [
			( new ProEventInfoField() )
				->set_description( CartAdSettings::render_for_event() )
				->add_class( 'notice' )
				->add_class( 'notice-info' ),
		];
	}
}
