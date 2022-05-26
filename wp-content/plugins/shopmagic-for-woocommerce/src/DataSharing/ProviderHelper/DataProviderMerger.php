<?php

namespace WPDesk\ShopMagic\DataSharing\ProviderHelper;

use WPDesk\ShopMagic\DataSharing\DataProvider;

/**
 * Can merge multiple data from provider into one provider.
 *
 * @package WPDesk\ShopMagic\DataSharing
 */
class DataProviderMerger implements DataProvider {

	/** @var DataProvider[] */
	private $providers;

	/**
	 * @param DataProvider[] $providers
	 */
	public function __construct( array $providers ) {
		$this->providers = $providers;
	}

	/**
	 * @inheritDoc
	 */
	public function get_provided_data_domains() {
		return array_merge(
			...array_map(
				function ( DataProvider $provider ) {
					return $provider->get_provided_data_domains();
				},
				$this->providers
			)
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_provided_data() {
		return array_merge(
			...array_map(
				function ( DataProvider $provider ) {
					return $provider->get_provided_data();
				},
				$this->providers
			)
		);
	}

	/**
	 * @inheritDoc
	 */
	public function jsonSerialize() {
		return array_merge(
			...array_map(
				function ( DataProvider $provider ) {
					return $provider->jsonSerialize();
				},
				$this->providers
			)
		);
	}
}
