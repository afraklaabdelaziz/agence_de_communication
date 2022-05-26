<?php

namespace WPDesk\ShopMagic\DataSharing\Traits;

use Psr\Container\ContainerInterface;

trait FieldsDataAsProtectedField {

	/** @var ContainerInterface */
	protected $fields_data;

	public function update_fields_data( ContainerInterface $data ) {
		$this->fields_data = $data;
	}
}
