<?php

namespace WPDesk\ShopMagic;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class LoggerFactory {

	/** @var ?LoggerInterface */
	private static $logger;

	public static function set_logger( LoggerInterface $logger ) {
		self::$logger = $logger;
	}

	/** @return LoggerInterface */
	public static function get_logger() {
		return self::$logger ?? new NullLogger();
	}
}

