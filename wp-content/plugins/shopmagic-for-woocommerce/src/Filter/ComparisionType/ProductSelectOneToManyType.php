<?php

namespace WPDesk\ShopMagic\Filter\ComparisionType;

use ShopMagicVendor\WPDesk\Forms\Field\ProductSelect;
use ShopMagicVendor\WPDesk\Forms\Serializer\ProductSelectSerializer;

final class ProductSelectOneToManyType extends SelectOneToManyType {
	const VALUE_KEY = 'products_ids';

	public function __construct() {
		// override - using ajax to pass options.
	}

	/**
	 * @inheritDoc
	 */
	protected function get_select_field() {
		return ( new ProductSelect() )
			->set_name( self::VALUE_KEY )
			->set_serializer( new ProductSelectSerializer() )
			->set_placeholder( __( 'Search for a product...', 'shopmagic-for-woocommerce' ) );
	}
}
