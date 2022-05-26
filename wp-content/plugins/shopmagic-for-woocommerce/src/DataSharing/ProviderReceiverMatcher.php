<?php

namespace WPDesk\ShopMagic\DataSharing;

final class ProviderReceiverMatcher {
	/**
	 * @param DataProvider $provider
	 * @param DataReceiver[] $receivers
	 *
	 * @return DataReceiver[]
	 */
	public static function matchReceivers( $provider, array $receivers ) {
		if ( is_a( $provider, DataProvider::class, true ) ) {
			$provided_data_domains = $provider->get_provided_data_domains();

			return array_filter(
				$receivers,
				/** @param DataReceiver $item */
				function ( $item ) use ( $provided_data_domains ) {

					foreach ( $item->get_required_data_domains() as $required_domain ) {
						$provided_data_found = array_reduce(
							$provided_data_domains,
							function ( $carry, $provided_domain ) use ( $required_domain ) {
								// Allow only for classes declared in get_required_data_domains, without inheritance.
								if ( is_a( $provided_domain, $required_domain, true ) && ! is_subclass_of($provided_domain, $required_domain, true) ) {
									return true;
								}

								return $carry;
							},
							false
						);

						if ( ! $provided_data_found ) {
							return false;
						}
					}

					return true;
				}
			);
		}

		return [];
	}
}
