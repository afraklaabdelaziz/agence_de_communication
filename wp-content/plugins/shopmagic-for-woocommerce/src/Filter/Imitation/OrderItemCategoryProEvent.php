<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\Filter\Imitation;

use WPDesk\ShopMagic\Admin\Automation\AutomationFormFields\ProEventInfoField;
use WPDesk\ShopMagic\Event\EventFactory2;
use WPDesk\ShopMagic\Filter\ImitationCommonFilter;

/**
 * Fake filter for pro encouragement.
 *
 * @package WPDesk\ShopMagic\Filter\Imitation
 */
final class OrderItemCategoryProEvent extends ImitationCommonFilter {

	/** @return string[] */
	public function get_required_data_domains(): array {
		return [ \WC_Order::class ];
	}

	public function get_group_slug(): string {
		return EventFactory2::GROUP_ORDERS;
	}

	public function get_name(): string {
		return __( '[PRO] Order - Item Categories', 'shopmagic-for-woocommerce' );
	}

	public function get_fields(): array {
		ob_start();
		include __DIR__ . '/templates/order-item-description.php';
		$description = ob_get_clean();

		return [
			( new ProEventInfoField() )
				->set_description( $description )
				->add_class( 'notice' )
				->add_class( 'notice-info' ),
		];
	}

}
