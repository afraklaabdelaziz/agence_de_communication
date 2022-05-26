<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\Admin\TableList;

use ShopMagicVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use ShopMagicVendor\WPDesk\View\Renderer\SimplePhpRenderer;
use ShopMagicVendor\WPDesk\View\Resolver\DirResolver;
use WPDesk\ShopMagic\Automation\AutomationPostType;
use WPDesk\ShopMagic\CommunicationList\CommunicationListPostType;
use WPDesk\ShopMagic\Helper\CapabilitiesCheckTrait;

abstract class AdminListPage implements AdminPage {
	use CapabilitiesCheckTrait;

	/** @var AbstractTableList */
	private $view;

	/** @var string */
	protected $title = '';

	/** @var string */
	protected $action_url = '';

	/** @var string */
	protected $post_type = '';

	/** @var string */
	protected $page = '';

	/** @var bool */
	protected $show_in_menu = true;

	public function __construct( AbstractTableList $view ) {
		$this->view = $view;
	}

	/** @return void */
	final public function register() {
		$allowed_capability = $this->allowed_capability();
		if ( $allowed_capability ) {
			add_submenu_page(
				$this->show_in_menu ? AutomationPostType::POST_TYPE_MENU_URL : '',
				$this->title,
				$this->title,
				$allowed_capability,
				$this->page,
				[ $this, 'render' ]
			);
		}
	}

	/** @return void */
	final public function render() {
		$this->view->prepare_items();
		$renderer = ( new SimplePhpRenderer( new DirResolver( __DIR__ . DIRECTORY_SEPARATOR . 'templates' ) ) );
		$renderer->output_render(
			'table',
			[
				'table'      => $this->view,
				'title'      => $this->title,
				'action_url' => $this->action_url,
				'post_type'  => $this->post_type,
				'page'       => $this->page,
			]
		);
	}

}
