<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\Event\Imitation;

use WPDesk\ShopMagic\Admin\Automation\AutomationFormFields\ProEventInfoField;
use WPDesk\ShopMagic\Event\EventFactory2;
use WPDesk\ShopMagic\Event\ImitationCommonEvent;
use function Composer\Autoload\includeFile;

/**
 * Fake event for free users.
 *
 * @package WPDesk\ShopMagic\Event\Imitation
 */
final class ManualTriggerProEvent extends ImitationCommonEvent {

	public function get_name(): string {
		return __( '[PRO] Order Manual Trigger', 'shopmagic-for-woocommerce' );
	}

	public function get_description(): string {
		return '';
	}

	public function get_group_slug(): string {
		return EventFactory2::GROUP_ORDERS;
	}

	/** @return \ShopMagicVendor\WPDesk\Forms\Field[] */
	public function get_fields(): array {
		ob_start();
		include __DIR__ . '/templates/manual-trigger-description.php';
		$description = ob_get_clean();
		return [
			( new ProEventInfoField() )
				->set_description( $description )
				->add_class( 'notice' )
				->add_class( 'notice-info' ),
		];
	}

}
