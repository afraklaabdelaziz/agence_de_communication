<?php

namespace ShopMagicVendor\WPDesk\View\Resolver;

use ShopMagicVendor\WPDesk\View\Renderer\Renderer;
use ShopMagicVendor\WPDesk\View\Resolver\Exception\CanNotResolve;
/**
 * This resolver never finds the file
 *
 * @package WPDesk\View\Resolver
 */
class NullResolver implements \ShopMagicVendor\WPDesk\View\Resolver\Resolver
{
    public function resolve($name, \ShopMagicVendor\WPDesk\View\Renderer\Renderer $renderer = null)
    {
        throw new \ShopMagicVendor\WPDesk\View\Resolver\Exception\CanNotResolve("Null Cannot resolve");
    }
}
