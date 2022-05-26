<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\Admin\Form\Fields;

final class FileInput extends \ShopMagicVendor\WPDesk\Forms\Field\BasicField {

	public function get_type(): string {
		return 'file';
	}

	public function get_template_name(): string {
		return 'file-input';
	}
}
