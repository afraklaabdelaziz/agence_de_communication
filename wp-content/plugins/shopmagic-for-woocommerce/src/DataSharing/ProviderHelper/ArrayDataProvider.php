<?php

namespace WPDesk\ShopMagic\DataSharing\ProviderHelper;

use WPDesk\ShopMagic\DataSharing\DataProvider;

/**
 * Can envelope array of [class => object] into data provider.
 *
 * @package WPDesk\ShopMagic\DataSharing
 */
class ArrayDataProvider implements DataProvider {

	/** @var mixed[] */
	private $data;

	/**
	 * @param object[] $data
	 */
	public function __construct( array $data ) {
		$this->data = $data;
	}

	/**
	 * @inheritDoc
	 */
	public function get_provided_data_domains() {
		return array_keys( $this->data );
	}

	/**
	 * @inheritDoc
	 */
	public function get_provided_data() {
		return $this->data;
	}

	/**
	 * @inheritDoc
	 */
	public function jsonSerialize() {
		return $this->data;
	}
}
