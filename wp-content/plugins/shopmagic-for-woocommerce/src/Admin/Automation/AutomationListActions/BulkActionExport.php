<?php


namespace WPDesk\ShopMagic\Admin\Automation\AutomationListActions;

use WPDesk\ShopMagic\Admin\Automation\BulkListAction;
use WPDesk\ShopMagic\Automation\AutomationPostType;
use WPDesk\ShopMagic\Recipe\RecipeExporter;

class BulkActionExport implements BulkListAction {

	/** @return void */
	public function hooks() {
		add_filter( 'bulk_actions-edit-shopmagic_automation', [ $this, 'add_action' ] );
		add_filter( 'handle_bulk_actions-edit-shopmagic_automation', [ $this, 'handle_action' ], 10, 3 );
		add_filter( 'handle_bulk_actions-edit-shopmagic_automation', [ $this, 'handle_action_another' ], 10, 3 );
	}

	public function add_action( array $actions ): array {
		if ( $this->should_add_action() ) {
			$actions['export'] = sprintf(
				'%s',
				esc_html__( 'Export', 'shopmagic-for-woocommerce' )
			);
		}

		return $actions;
	}

	public function handle_action( string $sendback, string $doaction, array $ids ): string {
		if ( $doaction !== 'export' ) {
			return $sendback;
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'You cannot export automation. Check your permissions.', 'shopmagic-for-woocommerce' ) );
		}

		$filename = 'shopmagic-automations-' . date( 'Y-m-d' );

		header( 'Content-Type: application/octet-stream' );
		header( 'Content-disposition: ' . $filename . '.json' );
		header( 'Content-disposition: filename=' . $filename . '.json' );

		exit( json_encode( ( new RecipeExporter() )->get_multiple_recipes( $ids ) ) );
	}

	private function should_add_action(): bool {
		if ( ! current_user_can( 'edit_posts' ) ||
			 ( get_current_screen() instanceof \WP_Screen && get_current_screen()->post_type !== AutomationPostType::TYPE )
		) {
			return false;
		}
		return true;
	}
}
