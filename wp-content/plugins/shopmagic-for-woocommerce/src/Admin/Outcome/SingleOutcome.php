<?php

namespace WPDesk\ShopMagic\Admin\Outcome;

use ShopMagicVendor\WPDesk\View\Renderer\SimplePhpRenderer;
use ShopMagicVendor\WPDesk\View\Resolver\DirResolver;
use WPDesk\ShopMagic\Automation\AutomationPostType;
use WPDesk\ShopMagic\AutomationOutcome\OutcomeInTable;
use WPDesk\ShopMagic\AutomationOutcome\OutcomeReposistory;

/**
 * Single outcome view.
 */
final class SingleOutcome {
	const SLUG = 'shopmagic_single_outcome';

	public function hooks() {
		add_action(
			'admin_menu',
			function () {
				add_submenu_page(
					null,
					__( 'Outcome', 'shopmagic-for-woocommerce' ),
					__( 'Outcomes', 'shopmagic-for-woocommerce' ),
					'manage_options',
					self::SLUG,
					[ $this, 'render_page_action' ]
				);
			}
		);
	}


	public static function get_url( OutcomeInTable $outcome ): string {
		$params = [
			'page' => self::SLUG,
			'id'   => $outcome->get_execution_id(),
		];

		return AutomationPostType::get_url() . '&' . http_build_query( $params );
	}

	/**
	 * @internal
	 */
	public function render_page_action() {
		global $wpdb;

		$repository = new OutcomeReposistory( $wpdb );
		$outcome    = $repository->get_by_id( (int) $_GET['id'] );
		$renderer   = ( new SimplePhpRenderer( new DirResolver( __DIR__ . DIRECTORY_SEPARATOR . 'templates' ) ) );
		echo $renderer->render( 'single-outcome-log', [ 'outcome' => $outcome ] );
	}
}
