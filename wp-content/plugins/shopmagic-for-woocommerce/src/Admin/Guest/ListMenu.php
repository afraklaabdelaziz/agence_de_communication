<?php

namespace WPDesk\ShopMagic\Admin\Guest;

use ShopMagicVendor\WPDesk\View\Renderer\SimplePhpRenderer;
use ShopMagicVendor\WPDesk\View\Resolver\DirResolver;
use WPDesk\ShopMagic\Admin\TableList\AdminListPage;
use WPDesk\ShopMagic\Automation\AutomationPostType;
use WPDesk\ShopMagic\Helper\CapabilitiesCheckTrait;

/**
 * Admin guest list.
 */
final class ListMenu {
	use CapabilitiesCheckTrait;

	const SLUG = 'guests';

	/** @return void */
	public function hooks() {
		add_action(
			'admin_menu',
			function () {
				$allowed_capability = $this->allowed_capability();
				if ( $allowed_capability ) {
					add_submenu_page(
						AutomationPostType::POST_TYPE_MENU_URL,
						esc_html__( 'Guests', 'shopmagic-for-woocommerce' ),
						esc_html__( 'Guests', 'shopmagic-for-woocommerce' ),
						$allowed_capability,
						self::SLUG,
						[ $this, 'render_page_action' ]
					);
				}
			}
		);
	}

	/**
	 * @param int|null $automation_id Optional id to generate url with automation filter
	 *
	 * @return string
	 */
	public static function get_url( $automation_id = null ) {
		$params = [
			'page' => self::SLUG,
		];
		if ( $automation_id !== null ) {
			$params['form_filter[automation_id]'] = (int) $automation_id;
		}

		return AutomationPostType::get_url() . '&' . http_build_query( $params );
	}

	/**
	 * @internal
	 * @return void
	 */
	public function render_page_action() {
		$guest_table = new TableList();
		$guest_table->prepare_items();

		$renderer = ( new SimplePhpRenderer( new DirResolver( __DIR__ . DIRECTORY_SEPARATOR . 'templates' ) ) );
		$renderer->output_render( 'table', [ 'guest_table' => $guest_table ] );
	}
}
