<?php

namespace WPDesk\ShopMagic\AutomationOutcome;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;

final class OutcomeLogger implements LoggerInterface {
	use LoggerTrait;

	/** @var LoggerInterface */
	private $logger;

	/** @var OutcomeReposistory */
	private $repository;

	/** @var string */
	private $execution_id;

	public function __construct( LoggerInterface $logger, OutcomeReposistory $respository, string $execution_id ) {
		$this->logger       = $logger;
		$this->repository   = $respository;
		$this->execution_id = $execution_id;
	}

	public function log( $level, $message, array $context = [] ) {
		if ( in_array( $level, [ LogLevel::ERROR, LogLevel::EMERGENCY, LogLevel::CRITICAL ], true ) ) {
			$this->repository->log_note(
				$this->execution_id,
				"{$level}: {$message}",
				$context
			);
		}
		$this->logger->log( $level, $message, $context );
	}
}

