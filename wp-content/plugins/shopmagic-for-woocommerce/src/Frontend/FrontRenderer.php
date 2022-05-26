<?php

namespace WPDesk\ShopMagic\Frontend;

use ShopMagicVendor\WPDesk\View\Renderer\SimplePhpRenderer;
use ShopMagicVendor\WPDesk\View\Resolver\ChainResolver;
use ShopMagicVendor\WPDesk\View\Resolver\DirResolver;
use ShopMagicVendor\WPDesk\View\Resolver\WPThemeResolver;

/**
 * Renders templates that can be overridden in theme.
 *
 * @deprecated 2.37
 */
final class FrontRenderer extends SimplePhpRenderer {
	const THEME_TEMPLATE_SUBDIR = 'shopmagic';

	public function __construct() {
		parent::__construct(
			new ChainResolver(
				new WPThemeResolver( self::THEME_TEMPLATE_SUBDIR ),
				new DirResolver( __DIR__ . DIRECTORY_SEPARATOR . 'templates' )
			)
		);
	}
}
