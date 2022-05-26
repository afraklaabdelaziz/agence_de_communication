<?php

namespace ShopMagicVendor\WPDesk\Forms\Resolver;

use ShopMagicVendor\WPDesk\View\Renderer\Renderer;
use ShopMagicVendor\WPDesk\View\Resolver\DirResolver;
use ShopMagicVendor\WPDesk\View\Resolver\Resolver;
/**
 * Use with View to resolver form fields to default templates.
 *
 * @package WPDesk\Forms\Resolver
 */
class DefaultFormFieldResolver implements \ShopMagicVendor\WPDesk\View\Resolver\Resolver
{
    /** @var Resolver */
    private $dir_resolver;
    public function __construct()
    {
        $this->dir_resolver = new \ShopMagicVendor\WPDesk\View\Resolver\DirResolver(__DIR__ . '/../../templates');
    }
    public function resolve($name, \ShopMagicVendor\WPDesk\View\Renderer\Renderer $renderer = null)
    {
        return $this->dir_resolver->resolve($name, $renderer);
    }
}
