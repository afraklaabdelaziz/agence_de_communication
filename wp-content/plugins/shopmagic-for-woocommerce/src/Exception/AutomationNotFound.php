<?php

namespace WPDesk\ShopMagic\Exception;

/**
 * Thrown when referenced Automation (e.g. by ID) cannot be found.
 *
 * @package WPDesk\ShopMagic\Exception
 */
class AutomationNotFound extends \RuntimeException implements ShopMagicException {}
