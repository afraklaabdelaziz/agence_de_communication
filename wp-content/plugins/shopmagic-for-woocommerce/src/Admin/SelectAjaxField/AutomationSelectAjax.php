<?php

namespace WPDesk\ShopMagic\Admin\SelectAjaxField;

use WPDesk\ShopMagic\Automation\AutomationPostType;
use WPDesk\ShopMagic\FormField\BasicField;

/**
 * Select saved automations for admin view filtering from AJAX form.
 *
 * @package WPDesk\ShopMagic\Admin\SelectAjaxField
 */
class AutomationSelectAjax extends BasicField {

	public function get_template_name(): string {
		return 'automation-select';
	}

	public static function get_ajax_action_name(): string {
		return 'shopmagic_automation';
	}

	/**
	 * @internal
	 * @return void
	 */
	public static function automation_select_ajax() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['term'] ) || ! current_user_can( 'manage_woocommerce' ) ) {
			die;
		}
		$term = sanitize_text_field( wp_unslash( $_GET['term'] ) );
		// phpcs:enable

		$limit = 80;
		if ( 3 > strlen( $term ) ) {
			$limit = 20;
		}

		if ( empty( $term ) ) {
			wp_die();
		}

		$query = new \WP_Query(
			[
				's'              => mb_strtolower( $term, 'UTF-8' ),
				'posts_per_page' => $limit,
				'post_type'      => AutomationPostType::TYPE,
				'post_status'    => 'publish',
				'orderby'        => 'title',
			]
		);

		/** @var \WP_Post[] $automations */
		$automations = $query->get_posts();

		$results = [];
		foreach ( $automations as $automation_post ) {
			$results[ $automation_post->ID ] = get_the_title( $automation_post->ID );
		}

		wp_send_json( $results );
	}

	/**
	 * @return void
	 */
	public static function hooks() {
		add_action( 'wp_ajax_' . self::get_ajax_action_name(), '\WPDesk\ShopMagic\Admin\SelectAjaxField\AutomationSelectAjax::automation_select_ajax' );
	}
}
