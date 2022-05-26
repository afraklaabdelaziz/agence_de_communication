<?php

namespace WPDesk\ShopMagic\Admin\Outcome;

use WPDesk\ShopMagic\Admin\TableList\AbstractTableList;
use WPDesk\ShopMagic\Admin\TableList\AdminListPage;
use WPDesk\ShopMagic\Automation\AutomationPostType;

/**
 * Admin outcome list.
 */
final class ListPage extends AdminListPage {

	/** @var string */
	protected $post_type = AutomationPostType::TYPE;

	/** @var string */
	protected $page = 'outcome';
	const SLUG      = 'outcome';

	public function __construct( AbstractTableList $view ) {
		$this->title      = __( 'Outcomes', 'shopmagic-for-woocommerce' );
		$this->action_url = self::get_url();
		parent::__construct( $view );
	}


	/**
	 * @param int|null $automation_id Optional id to generate url with automation filter
	 *
	 * @return string
	 * @deprecated 2.37
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
}
