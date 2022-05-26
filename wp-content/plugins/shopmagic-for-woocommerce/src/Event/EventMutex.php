<?php

namespace WPDesk\ShopMagic\Event;

/**
 * Can check if the event has been already launched during the request.
 * This class should be used when you want to ensure that some action is executed only once per hook per request.
 * ie. without it when hooked into new_order and also to new_api_order the action could be executed twice per one new order.
 *
 * @package WPDesk\ShopMagic\Event
 */
class EventMutex {

	/** @var array */
	private $event_log = [];

	private function report_event( string $event_name, array $args ) {
		$this->event_log[] = [
			'name' => $event_name,
			'args' => $args,
		];
	}

	private function is_unique_event( string $event_name, array $args ): bool {
		foreach ( $this->event_log as $event ) {
			if ( $event['name'] === $event_name ) {
				$found = true;
				foreach ( $event['args'] as $key => $arg ) {
					if ( $args[ $key ] !== $arg ) {
						$found = false;
						break;
					}
				}
				if ( $found ) {
					return false;
				}
			}
		}

		return true;
	}

	public function check_uniqueness_once( string $event_name, array $args ): bool {
		$uniqueness = $this->is_unique_event( $event_name, $args );
		if ( $uniqueness ) {
			$this->report_event( $event_name, $args );
		}

		return $uniqueness;
	}
}
