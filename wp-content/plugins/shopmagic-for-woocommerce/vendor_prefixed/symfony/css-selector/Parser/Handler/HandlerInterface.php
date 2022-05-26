<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ShopMagicVendor\Symfony\Component\CssSelector\Parser\Handler;

use ShopMagicVendor\Symfony\Component\CssSelector\Parser\Reader;
use ShopMagicVendor\Symfony\Component\CssSelector\Parser\TokenStream;
/**
 * CSS selector handler interface.
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
interface HandlerInterface
{
    /**
     * @return bool
     */
    public function handle(\ShopMagicVendor\Symfony\Component\CssSelector\Parser\Reader $reader, \ShopMagicVendor\Symfony\Component\CssSelector\Parser\TokenStream $stream);
}
