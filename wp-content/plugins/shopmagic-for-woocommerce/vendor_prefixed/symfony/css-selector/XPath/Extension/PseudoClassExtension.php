<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ShopMagicVendor\Symfony\Component\CssSelector\XPath\Extension;

use ShopMagicVendor\Symfony\Component\CssSelector\Exception\ExpressionErrorException;
use ShopMagicVendor\Symfony\Component\CssSelector\XPath\XPathExpr;
/**
 * XPath expression translator pseudo-class extension.
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
class PseudoClassExtension extends \ShopMagicVendor\Symfony\Component\CssSelector\XPath\Extension\AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getPseudoClassTranslators()
    {
        return ['root' => [$this, 'translateRoot'], 'first-child' => [$this, 'translateFirstChild'], 'last-child' => [$this, 'translateLastChild'], 'first-of-type' => [$this, 'translateFirstOfType'], 'last-of-type' => [$this, 'translateLastOfType'], 'only-child' => [$this, 'translateOnlyChild'], 'only-of-type' => [$this, 'translateOnlyOfType'], 'empty' => [$this, 'translateEmpty']];
    }
    /**
     * @return XPathExpr
     */
    public function translateRoot(\ShopMagicVendor\Symfony\Component\CssSelector\XPath\XPathExpr $xpath)
    {
        return $xpath->addCondition('not(parent::*)');
    }
    /**
     * @return XPathExpr
     */
    public function translateFirstChild(\ShopMagicVendor\Symfony\Component\CssSelector\XPath\XPathExpr $xpath)
    {
        return $xpath->addStarPrefix()->addNameTest()->addCondition('position() = 1');
    }
    /**
     * @return XPathExpr
     */
    public function translateLastChild(\ShopMagicVendor\Symfony\Component\CssSelector\XPath\XPathExpr $xpath)
    {
        return $xpath->addStarPrefix()->addNameTest()->addCondition('position() = last()');
    }
    /**
     * @return XPathExpr
     *
     * @throws ExpressionErrorException
     */
    public function translateFirstOfType(\ShopMagicVendor\Symfony\Component\CssSelector\XPath\XPathExpr $xpath)
    {
        if ('*' === $xpath->getElement()) {
            throw new \ShopMagicVendor\Symfony\Component\CssSelector\Exception\ExpressionErrorException('"*:first-of-type" is not implemented.');
        }
        return $xpath->addStarPrefix()->addCondition('position() = 1');
    }
    /**
     * @return XPathExpr
     *
     * @throws ExpressionErrorException
     */
    public function translateLastOfType(\ShopMagicVendor\Symfony\Component\CssSelector\XPath\XPathExpr $xpath)
    {
        if ('*' === $xpath->getElement()) {
            throw new \ShopMagicVendor\Symfony\Component\CssSelector\Exception\ExpressionErrorException('"*:last-of-type" is not implemented.');
        }
        return $xpath->addStarPrefix()->addCondition('position() = last()');
    }
    /**
     * @return XPathExpr
     */
    public function translateOnlyChild(\ShopMagicVendor\Symfony\Component\CssSelector\XPath\XPathExpr $xpath)
    {
        return $xpath->addStarPrefix()->addNameTest()->addCondition('last() = 1');
    }
    /**
     * @return XPathExpr
     *
     * @throws ExpressionErrorException
     */
    public function translateOnlyOfType(\ShopMagicVendor\Symfony\Component\CssSelector\XPath\XPathExpr $xpath)
    {
        $element = $xpath->getElement();
        if ('*' === $element) {
            throw new \ShopMagicVendor\Symfony\Component\CssSelector\Exception\ExpressionErrorException('"*:only-of-type" is not implemented.');
        }
        return $xpath->addCondition(\sprintf('count(preceding-sibling::%s)=0 and count(following-sibling::%s)=0', $element, $element));
    }
    /**
     * @return XPathExpr
     */
    public function translateEmpty(\ShopMagicVendor\Symfony\Component\CssSelector\XPath\XPathExpr $xpath)
    {
        return $xpath->addCondition('not(*) and not(string-length())');
    }
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pseudo-class';
    }
}
