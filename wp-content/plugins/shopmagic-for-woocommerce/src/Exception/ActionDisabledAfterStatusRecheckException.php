<?php

namespace WPDesk\ShopMagic\Exception;

/**
 * Thrown by SupportsDeferredCheck event when event status is no loger valid.
 *
 * @package WPDesk\ShopMagic\Exception
 */
class ActionDisabledAfterStatusRecheckException extends \RuntimeException implements ShopMagicException {

}
