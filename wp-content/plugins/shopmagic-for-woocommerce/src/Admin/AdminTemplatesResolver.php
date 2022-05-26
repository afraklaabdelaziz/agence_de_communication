<?php
namespace WPDesk\ShopMagic\Admin;

use ShopMagicVendor\WPDesk\Forms\Resolver\DefaultFormFieldResolver;
use ShopMagicVendor\WPDesk\View\Resolver\ChainResolver;
use ShopMagicVendor\WPDesk\View\Resolver\DirResolver;

/**
 * Class keeps resolver common to reuse templates from core plugin.
 */
class AdminTemplatesResolver {

	public function get_resolver( string $resolver ): ChainResolver {
		return new ChainResolver(
			new DirResolver( $resolver ),
			new DirResolver( __DIR__ . '/SelectAjaxField/templates' ),
			new DirResolver( __DIR__ . '/Form/templates' ),
			new DefaultFormFieldResolver()
		);
	}

}
