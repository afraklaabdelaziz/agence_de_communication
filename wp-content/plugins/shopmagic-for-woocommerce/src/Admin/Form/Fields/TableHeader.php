<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\Admin\Form\Fields;

final class TableHeader extends \ShopMagicVendor\WPDesk\Forms\Field\Header {

	public function get_template_name(): string {
		return 'table-header';
	}

}
