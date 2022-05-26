<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\Admin\MarketingLists;

use WPDesk\ShopMagic\Admin\TableList\AbstractTableList;
use WPDesk\ShopMagic\Admin\TableList\AdminListPage;
use WPDesk\ShopMagic\Automation\AutomationPostType;

/**
 * Admin page for Subscribers list.
 */
final class ListPage extends AdminListPage {

	/** @var string */
	protected $post_type = 'shopmagic_automation';

	/** @var string */
	protected $page = 'optins';

	/** @var bool */
	protected $show_in_menu = false;

	public function __construct( AbstractTableList $view ) {
		$this->title      = esc_html__( 'Subscribers', 'shopmagic-for-woocommerce' );
		$this->action_url = AutomationPostType::get_url() . '&' . http_build_query( [ 'page' => $this->page ] );
		parent::__construct( $view );
	}
}
