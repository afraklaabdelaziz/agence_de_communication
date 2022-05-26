<?php

namespace WPDesk\ShopMagic\Admin\Queue;

use ShopMagicVendor\WPDesk\View\Renderer\SimplePhpRenderer;
use ShopMagicVendor\WPDesk\View\Resolver\DirResolver;
use WPDesk\ShopMagic\Admin\TableList\AdminListPage;
use WPDesk\ShopMagic\Automation\AutomationPostType;
use WC_Queue_Interface;
use WPDesk\ShopMagic\Helper\CapabilitiesCheckTrait;

/**
 * Admin optin list.
 *
 * @package WPDesk\ShopMagic\Admin\CommunicationType
 */
final class ListMenu {
	use CapabilitiesCheckTrait;

	const SLUG = 'queue';

	/** @var WC_Queue_Interface */
	private $queue;

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

	public function hooks() {
		add_action(
			'admin_menu',
			function () {
				$allowed_capability = $this->allowed_capability();
				if ( $allowed_capability ) {
					add_submenu_page(
						AutomationPostType::POST_TYPE_MENU_URL,
						esc_html__( 'Queue', 'shopmagic-for-woocommerce' ),
						esc_html__( 'Queue', 'shopmagic-for-woocommerce' ),
						$allowed_capability,
						self::SLUG,
						[ $this, 'render_page_action' ]
					);
				}
			}
		);

		add_action(
			'woocommerce_init',
			function () {
				$this->queue = \WC_Queue::instance();
			}
		);
	}

	public function render_page_action() {
		$queue_table = new TableList( $this->queue );
		$queue_table->prepare_items();

		$renderer = ( new SimplePhpRenderer( new DirResolver( __DIR__ . DIRECTORY_SEPARATOR . 'list-templates' ) ) );
		echo $renderer->render( 'table', [ 'queue_table' => $queue_table ] );
	}
}
