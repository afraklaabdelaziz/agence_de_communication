<?php

namespace WPDesk\ShopMagic\HookEmitter;

use WPDesk\ShopMagic\Helper\WordPressFormatHelper;

/**
 * Can emit hooks on given hour. These can be used to check conditions before something happens.
 */
class EventBefore {

	/** @var \WC_Queue_Interface */
	private $queue;

	/**
	 * How frequent we want to check if queue_event is set for valid hour.
	 *
	 * @var int
	 */
	private $frequency_seconds;

	public function __construct( \WC_Queue_Interface $queue, int $frequency_seconds ) {
		$this->queue             = $queue;
		$this->frequency_seconds = $frequency_seconds;
	}

	/**
	 * We have to be sure that in queue is event that will fire our search for subscriptions.
	 *
	 * @param string $hook_to_emit
	 * @param int $hour_offset
	 */
	public function ensure_hook_fire_when_time_comes( string $hook_to_emit, int $hour_offset ) {
		// without mutex there is a race condition - who first adds to queue.
		$mutex_key   = $hook_to_emit . '_mutex';
		$mutex_stamp = get_transient( $mutex_key );
		if ( ! empty( $mutex_stamp ) && $mutex_stamp + $this->frequency_seconds > time() ) {
			return;
		}
		set_transient( $mutex_key, $this->frequency_seconds + time(), $this->frequency_seconds );

		// I have no idea why is this required. AS is crashing randomly without reason.
		global $wpdb;
		if ( empty( $wpdb->actionscheduler_actions ) ) {
			$wpdb->actionscheduler_actions = $wpdb->prefix . 'actionscheduler_actions';
			$wpdb->actionscheduler_groups  = $wpdb->prefix . 'actionscheduler_groups';
		}

		$queue_events     = $this->get_queue_events( $hook_to_emit );
		$events_cancelled = $this->cancel_invalid_hour_queue_events( $queue_events, $hour_offset );

		if ( $events_cancelled || count( $queue_events ) === 0 ) {
			$this->queue->schedule_single(
				$this->get_next_event_stamp( $hour_offset ),
				$hook_to_emit,
				[],
				$this->get_queue_hook_group()
			);
		}
	}

	private function get_queue_events( string $hook_to_emit ): array {
		$queue_event_data = [
			'hook'   => $hook_to_emit,
			'group'  => $this->get_queue_hook_group(),
			'status' => \ActionScheduler_Store::STATUS_PENDING,
		];

		return $this->queue->search( $queue_event_data );
	}

	/**
	 * Shared group for all wcs queue events. Used to upgrade search speed and clarity in db.
	 *
	 * @return string
	 */
	private function get_queue_hook_group(): string {
		return 'shopmagic-automation-internal';
	}

	private function cancel_invalid_hour_queue_events( array $queue_events, int $scheduled_hour ): bool {
		foreach ( $queue_events as $key => $event ) {
			/** @var \ActionScheduler_Abstract_Schedule $schedule */
			$schedule       = $event->get_schedule();
			$scheduled_date = $schedule->get_date();
			if ( $scheduled_date !== null ) {
				$scheduled_utc = ( new \DateTime( 'now', new \DateTimeZone( 'UTC' ) ) )->setTimestamp( $scheduled_date->getTimestamp() );
				$queue_hour    = $scheduled_utc->format( 'G' ) * 60 * 60 + $scheduled_utc->format( 'i' ) * 60;
			}

			if ( $scheduled_date === null || $queue_hour !== $scheduled_hour ) {
				$this->queue->cancel_all( $event->get_hook(), $event->get_args(), $event->get_group() );

				return true;
			}
		}

		return false;
	}

	private function get_next_event_stamp( int $hour_ffset ): int {
		$date = new \DateTimeImmutable( 'today midnight', new \DateTimeZone( 'Etc/UTC' ) );
		$date = $date->setTimestamp( $date->getTimestamp() + $hour_ffset );
		if ( WordPressFormatHelper::convert_all_time_to_utc_stamp( $date ) < time() ) {
			$date = $date->modify( '+1 days' );
		}

		return WordPressFormatHelper::convert_all_time_to_utc_stamp( $date );
	}
}
