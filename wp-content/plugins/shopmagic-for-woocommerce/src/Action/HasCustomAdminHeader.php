<?php

namespace WPDesk\ShopMagic\Action;

/**
 * Use this interface when an action wants to render something in metabox action header ie. send test mail.
 *
 * @package WPDesk\ShopMagic\Action
 */
interface HasCustomAdminHeader {
	/**
	 * @param int $action_index Unique index of action on metabox.
	 *
	 * @return string
	 */
	public function render_header( $action_index );
}
