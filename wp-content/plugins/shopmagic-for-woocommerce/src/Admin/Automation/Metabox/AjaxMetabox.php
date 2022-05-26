<?php

declare(strict_types=1);

namespace WPDesk\ShopMagic\Admin\Automation\Metabox;

use WPDesk\ShopMagic\Admin\Automation\Metabox;

interface AjaxMetabox extends Metabox {

	/**
	 * Handle ajax request and return JSON schema for live content rendering on admin page.
	 *
	 * @return void
	 */
	public function render_from_post();
}
