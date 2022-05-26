<?php

namespace WPDesk\ShopMagic\DataSharing\ProviderHelper;

use WPDesk\ShopMagic\DataSharing\DataProvider;

/**
 * Can envelope array of mixed objects into provider.
 *
 * @package WPDesk\ShopMagic\DataSharing
 */
class DataProviderMixed implements DataProvider {

	/** @var mixed[] */
	private $data;

	/**
	 * @param mixed[] $data
	 */
	public function __construct( array $data ) {
		$this->data = $data;
	}

	/**
	 * @inheritDoc
	 */
	public function get_provided_data_domains() {
		return array_map(
			function ( $item ) {
				return get_class( $item );
			},
			$this->data
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_provided_data() {
		return array_combine(
			$this->get_provided_data_domains(),
			array_values( $this->data )
		);
	}

	/**
	 * @inheritDoc
	 */
	public function jsonSerialize() {
		return $this->data;
	}
}
