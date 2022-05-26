<?php

namespace WPDesk\ShopMagic\Admin\Automation\AutomationListActions;

use WPDesk\ShopMagic\Admin\Automation\ListAction;
use WPDesk\ShopMagic\Automation\AutomationPostType;
use WPDesk\ShopMagic\Recipe\RecipeExporter;

class ActionExport implements ListAction {

	const ACTION_NAME = 'export_single_automation';

	/** @return void */
	public function hooks() {
		add_filter( 'post_row_actions', [ $this, 'add_action' ], 10, 2 );
		add_action( 'admin_action_' . self::ACTION_NAME, [ $this, 'handle_action' ] );
	}

	public function add_action( array $actions, \WP_Post $post ): array {
		if ( $this->should_add_action() ) {
			$export_url = $this->get_export_url( $post->ID );

			$actions['export'] = sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url( $export_url ),
				esc_html__( 'Export', 'shopmagic-for-woocommerce' )
			);
		}

		return $actions;
	}

	public function handle_action() {
		$post_id = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;
		check_admin_referer( self::ACTION_NAME . $post_id );
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'You cannot export automation. Check your permissions.', 'shopmagic-for-woocommerce' ) );
		}

		$filename = 'shopmagic-automations' . date( 'Y-m-d' );

		header( 'Content-Type: application/octet-stream' );
		header( 'Content-disposition: ' . $filename . '.json' );
		header( 'Content-disposition: filename=' . $filename . '.json' );

		exit( json_encode( ( new RecipeExporter() )->get_as_recipe( $post_id ) ) );
	}

	private function should_add_action(): bool {
		if ( ! current_user_can( 'edit_posts' ) ||
			( get_current_screen() instanceof \WP_Screen && get_current_screen()->post_type !== AutomationPostType::TYPE )
		) {
			return false;
		}
		return true;
	}

	private function get_export_url( int $id ): string {
		return wp_nonce_url(
			add_query_arg(
				[
					'action' => self::ACTION_NAME,
					'id'     => $id,
				],
				admin_url( 'admin.php' )
			),
			self::ACTION_NAME . $id
		);
	}
}
