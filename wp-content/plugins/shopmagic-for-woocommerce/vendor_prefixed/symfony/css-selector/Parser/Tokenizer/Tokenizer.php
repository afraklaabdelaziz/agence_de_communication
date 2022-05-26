<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ShopMagicVendor\Symfony\Component\CssSelector\Parser\Tokenizer;

use ShopMagicVendor\Symfony\Component\CssSelector\Parser\Handler;
use ShopMagicVendor\Symfony\Component\CssSelector\Parser\Reader;
use ShopMagicVendor\Symfony\Component\CssSelector\Parser\Token;
use ShopMagicVendor\Symfony\Component\CssSelector\Parser\TokenStream;
/**
 * CSS selector tokenizer.
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
class Tokenizer
{
    /**
     * @var Handler\HandlerInterface[]
     */
    private $handlers;
    public function __construct()
    {
        $patterns = new \ShopMagicVendor\Symfony\Component\CssSelector\Parser\Tokenizer\TokenizerPatterns();
        $escaping = new \ShopMagicVendor\Symfony\Component\CssSelector\Parser\Tokenizer\TokenizerEscaping($patterns);
        $this->handlers = [new \ShopMagicVendor\Symfony\Component\CssSelector\Parser\Handler\WhitespaceHandler(), new \ShopMagicVendor\Symfony\Component\CssSelector\Parser\Handler\IdentifierHandler($patterns, $escaping), new \ShopMagicVendor\Symfony\Component\CssSelector\Parser\Handler\HashHandler($patterns, $escaping), new \ShopMagicVendor\Symfony\Component\CssSelector\Parser\Handler\StringHandler($patterns, $escaping), new \ShopMagicVendor\Symfony\Component\CssSelector\Parser\Handler\NumberHandler($patterns), new \ShopMagicVendor\Symfony\Component\CssSelector\Parser\Handler\CommentHandler()];
    }
    /**
     * Tokenize selector source code.
     *
     * @return TokenStream
     */
    public function tokenize(\ShopMagicVendor\Symfony\Component\CssSelector\Parser\Reader $reader)
    {
        $stream = new \ShopMagicVendor\Symfony\Component\CssSelector\Parser\TokenStream();
        while (!$reader->isEOF()) {
            foreach ($this->handlers as $handler) {
                if ($handler->handle($reader, $stream)) {
                    continue 2;
                }
            }
            $stream->push(new \ShopMagicVendor\Symfony\Component\CssSelector\Parser\Token(\ShopMagicVendor\Symfony\Component\CssSelector\Parser\Token::TYPE_DELIMITER, $reader->getSubstring(1), $reader->getPosition()));
            $reader->moveForward(1);
        }
        return $stream->push(new \ShopMagicVendor\Symfony\Component\CssSelector\Parser\Token(\ShopMagicVendor\Symfony\Component\CssSelector\Parser\Token::TYPE_FILE_END, null, $reader->getPosition()))->freeze();
    }
}
