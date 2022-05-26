<?php

namespace WPDesk\ShopMagic\Admin\Queue;

use ShopMagicVendor\WPDesk\PluginBuilder\Plugin\Hookable;

final class CancelQueueAction implements Hookable {
	public function hooks() {
		add_action( 'wp_ajax_' . self::get_action_name(), [ $this, 'cancel_action' ] );
	}

	/**
	 * @internal
	 */
	public function cancel_action() {
		$as_id = (int) $_REQUEST['id'];
		if ( wp_verify_nonce( sanitize_key( $_REQUEST['noonce'] ), $as_id ) ) {
			// Can't find proper method in WC_Queue_Interface to cancel by id.
			\ActionScheduler::store()->cancel_action( $as_id );
		}
		wp_send_json(
			[
				'result' => 'OK',
			]
		);
	}

	private static function get_action_name(): string {
		return 'shopmagic_cancel_queue';
	}

	public static function get_url( $as_action ): string {
		$params = [
			'action' => self::get_action_name(),
			'id'     => $as_action['index'],
			'noonce' => wp_create_nonce( (int) $as_action['index'] ),
		];

		return admin_url( 'admin-ajax.php' ) . '?' . http_build_query( $params );
	}
}
