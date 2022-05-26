<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\Exception;

/**
 * Exception is thrown when referenced field is not found in contact form entry.
 *
 * @package WPDesk\ShopMagic\Exceptions
 */
class FieldNotFound extends \RuntimeException implements ShopMagicException {}
