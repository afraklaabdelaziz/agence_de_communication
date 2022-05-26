<?php

namespace WPDesk\ShopMagic\Filter;

use WPDesk\ShopMagic\LoggerFactory;

/**
 * Receives two level deep array of filters and can apply group logic to these filters.
 *
 * @package WPDesk\ShopMagic\Filter
 */
class FilterGroupLogic implements FilterLogic {

	/** @var array */
	private $filters;

	/**
	 * @param array $filters Array of Array of filters. Outer array is for OR conditionals.
	 *                       [$or_condition_index][$and_condition_index] = Filter instance
	 */
	public function __construct( array $filters ) {
		$this->filters = $filters;
	}

	public function get_required_data_domains(): array {
		return [];
	}

	public function set_provided_data( array $data ) {
		foreach ( $this->filters as $or_group ) {
			foreach ( $or_group as $filter ) {
				$filter->set_provided_data( $data );
			}
		}
	}

	public function passed(): bool {
		if ( empty( $this->filters ) ) {
			return true;
		}

		foreach ( $this->filters as $or_group ) {
			$or_success = false;
			foreach ( $or_group as $filter ) {
				$or_success = true;
				try {
					/** @var FilterLogic $filter */
					if ( ! $filter->passed() ) {
						$or_success = false;
						break;
					}
				} catch ( \Exception $e ) {
					LoggerFactory::get_logger()->warning( "Exception {$e->getMessage()} in filter." );
					$or_success = false;
				}
			}
			if ( $or_success ) {
				return true;
			}
		}

		return false;
	}
}
