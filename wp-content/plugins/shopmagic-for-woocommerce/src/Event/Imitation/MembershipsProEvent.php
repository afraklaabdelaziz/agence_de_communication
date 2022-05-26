<?php

namespace WPDesk\ShopMagic\Event\Imitation;

use WPDesk\ShopMagic\Admin\Automation\AutomationFormFields\ProEventInfoField;
use WPDesk\ShopMagic\Event\EventFactory2;
use WPDesk\ShopMagic\Event\ImitationCommonEvent;

/**
 * Event that never fires and only shows info about PRO upgrades.
 */
final class MembershipsProEvent extends ImitationCommonEvent {

	public function get_name(): string {
		return __( '[PRO] Membership Status Changed', 'shopmagic-for-woocommerce' );
	}

	public function get_description(): string {
		return '';
	}

	public function get_group_slug(): string {
		return EventFactory2::GROUP_MEMBERSHIPS;
	}

	public function get_fields(): array {
		ob_start();
		include __DIR__ . '/templates/membership-event-description.php';
		$description = ob_get_clean();

		return [
			( new ProEventInfoField() )
				->set_description( $description )
				->add_class( 'notice' )
				->add_class( 'notice-info' ),
		];
	}
}
