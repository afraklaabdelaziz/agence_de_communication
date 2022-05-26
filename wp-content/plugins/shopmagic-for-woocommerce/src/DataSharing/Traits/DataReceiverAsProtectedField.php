<?php

namespace WPDesk\ShopMagic\DataSharing\Traits;

trait DataReceiverAsProtectedField {

	protected $provided_data = [];

	public function set_provided_data( array $provided_data ) {
		$this->provided_data = $provided_data;
	}
}
