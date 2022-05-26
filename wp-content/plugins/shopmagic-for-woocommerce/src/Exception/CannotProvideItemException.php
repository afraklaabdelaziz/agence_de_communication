<?php

namespace WPDesk\ShopMagic\Exception;

class CannotProvideItemException extends \RuntimeException implements ShopMagicException {
	public static function create_for_persistence_gateway( string $method ): ShopMagicException {
		return new self( "Method {$method} cannot provide item." );
	}
}
