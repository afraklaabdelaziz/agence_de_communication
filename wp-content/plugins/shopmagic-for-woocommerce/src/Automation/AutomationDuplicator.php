<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\Automation;

use WPDesk\ShopMagic\Exception\AutomationNotFound;
use WPDesk\ShopMagic\Exception\CouldNotDuplicateAutomation;

/**
 * Duplicates automation with the use of AutomationPersistence.
 *
 * @package WPDesk\ShopMagic\Automation
 */
final class AutomationDuplicator {

	/**
	 * @throws AutomationNotFound
	 * @throws CouldNotDuplicateAutomation
	 */
	public function duplicate( int $automation_id ): int {
		$automation_to_duplicate = get_post( $automation_id );

		if ( ! $automation_to_duplicate instanceof \WP_Post ) {
			throw new AutomationNotFound( esc_html__( 'Required automation could not be found.', 'shopmagic-for-woocommerce' ) );
		}

		// translators: %s current automation name in context of duplication.
		$new_automation_title = sprintf( esc_html__( '%s (copy)', 'shopmagic-for-woocommerce' ), $automation_to_duplicate->post_title );

		$new_automation_id = wp_insert_post(
			[
				'post_title'   => $new_automation_title,
				'post_content' => '',
				'post_type'    => AutomationPostType::TYPE,
				'post_status'  => 'draft',
			]
		);

		if ( $new_automation_id instanceof \WP_Error ) {
			throw new CouldNotDuplicateAutomation( esc_html__( 'Duplicated automation could not be created.', 'shopmagic-for-woocommerce' ) );
		}

		$duplicated_automation_data = new AutomationPersistence( $automation_id );
		$new_automation_data        = new AutomationPersistence( $new_automation_id );

		$new_automation_data->set_event_data( $duplicated_automation_data->get_event_data(), $duplicated_automation_data->get_event_slug() );
		$new_automation_data->set_actions_data( $duplicated_automation_data->get_actions_data() );
		$new_automation_data->set_filters_data( $duplicated_automation_data->get_filters_data() );

		return $new_automation_id;
	}

}
