<?php

namespace WPDesk\ShopMagic\HookEmitter;

use ShopMagicVendor\WPDesk\PluginBuilder\Plugin\Hookable;

/**
 * Emits periodic hooks for tasks that should but not have to run at a given time.
 *
 * @package WPDesk\ShopMagic\HookEmitter
 */
class CronHeartbeat implements Hookable {
	const TOO_MUCH_INTERVAL    = 30;
	const OPTION_NAME_LAST_RUN = 'shopmagic_cron_last_run';

	private function get_workers(): array {
		return [
			'shopmagic/core/cron/one_minute'      => [
				'interval' => 60,
				'display'  => __( 'Every minute', 'shopmagic-for-woocommerce' ),
			],
			'shopmagic/core/cron/two_minutes'     => [
				'interval' => 2 * 60,
				'display'  => __( 'Every two minutes', 'shopmagic-for-woocommerce' ),
			],
			'shopmagic/core/cron/five_minutes'    => [
				'interval' => 5 * 60,
				'display'  => __( 'Every five minutes', 'shopmagic-for-woocommerce' ),
			],
			'shopmagic/core/cron/fifteen_minutes' => [
				'interval' => 15 * 60,
				'display'  => __( 'Every fifteen minutes', 'shopmagic-for-woocommerce' ),
			],
			'shopmagic/core/cron/thirty_minutes'  => [
				'interval' => 30 * 60,
				'display'  => __( 'Every thirty minutes', 'shopmagic-for-woocommerce' ),
			],
			'shopmagic/core/cron/hourly'          => [
				'interval' => 60 * 60,
				'display'  => __( 'Every hour', 'shopmagic-for-woocommerce' ),
			],
			'shopmagic/core/cron/four_hours'      => [
				'interval' => 4 * 60 * 60,
				'display'  => __( 'Every for hours', 'shopmagic-for-woocommerce' ),
			],
			'shopmagic/core/cron/daily'           => [
				'interval' => 86400,
				'display'  => __( 'Every day', 'shopmagic-for-woocommerce' ),
			],
			'shopmagic/core/cron/two_days'        => [
				'interval' => 2 * 86400,
				'display'  => __( 'Every two days', 'shopmagic-for-woocommerce' ),
			],
			'shopmagic/core/cron/weekly'          => [
				'interval' => 7 * 86400,
				'display'  => __( 'Every week', 'shopmagic-for-woocommerce' ),
			],
		];
	}

	public function hooks() {
		add_filter( 'cron_schedules', [ $this, 'prepare_schedules' ], 100 );
		foreach ( $this->get_workers() as $hook => $interval ) {
			add_action( $hook, [ $this, 'prevent_from_run_too_much' ], 1 );
		}
		add_action( 'admin_init', [ $this, 'add_cron_events' ] );
	}

	/**
	 * Add cron events.
	 *
	 * @internal
	 */
	public function add_cron_events() {
		foreach ( $this->get_workers() as $hook => $interval ) {
			if ( ! wp_next_scheduled( $hook ) ) {
				wp_schedule_event( time(), $hook, $hook );
			}
		}
	}

	/**
	 * Prepare cron schedules to use in wp_schedule_event.
	 *
	 * @param array $schedules
	 *
	 * @return array
	 *
	 * @internal
	 */
	public function prepare_schedules( $schedules ) {
		return array_merge( $schedules, $this->get_workers() );
	}


	/**
	 * Prevents workers from working if they have done so in the past 30 seconds
	 *
	 * @internal
	 */
	public function prevent_from_run_too_much() {

		$action = current_action();

		if ( $this->is_worker_locked( $action ) ) {
			remove_all_actions( $action ); // prevent actions from running.

			return;
		}

		@set_time_limit( 300 );

		$this->update_last_run( $action );
	}

	/**
	 * Prevent cron events to run too frequent. Allow only one at a time.
	 */
	private function is_worker_locked( string $action ): bool {
		$time_unlocked = $this->get_last_run( $action )
				->modify( '+' . self::TOO_MUCH_INTERVAL . ' seconds' );

		return $time_unlocked->getTimestamp() > time();
	}

	private function update_last_run( string $action ) {
		$last_runs = $this->cron_run_last_time();

		if ( ! $last_runs ) {
			$last_runs = [];
		}

		$last_runs[ $action ] = time();

		update_option( self::OPTION_NAME_LAST_RUN, $last_runs, false );
	}

	/**
	 * @return int[] Keys are action names and values are unix timestamp.
	 */
	private function cron_run_last_time(): array {
		return get_option( self::OPTION_NAME_LAST_RUN, [] );
	}

	private function get_last_run( string $action ): \DateTimeImmutable {
		$last_runs = $this->cron_run_last_time();
		if ( is_array( $last_runs ) && isset( $last_runs[ $action ] ) ) {
			$date = new \DateTimeImmutable();

			return $date->setTimestamp( $last_runs[ $action ] );
		}

		return new \DateTimeImmutable( '-100 years' );
	}
}
