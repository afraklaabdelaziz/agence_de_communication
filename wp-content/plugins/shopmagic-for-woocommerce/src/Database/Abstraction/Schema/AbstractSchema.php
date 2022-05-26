<?php

declare( strict_types=1 );

namespace WPDesk\ShopMagic\Database\Abstraction\Schema;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\NullLogger;

/**
 * Manages database updates.
 *
 * @package WPDesk\ShopMagic\Database\Abstraction\Schema
 */
abstract class AbstractSchema {

	/** @var string */
	private $base_file_name;

	/** @var string[] */
	private $log;

	/** @var \wpdb */
	private $wpdb;

	public function __construct( string $base_file_name, \wpdb $wpdb ) {
		$this->wpdb           = $wpdb;
		$this->base_file_name = $base_file_name;
		$this->log            = json_decode( get_option( $this->get_log_option_name(), '[]' ), true );
		if ( ! is_array( $this->log ) ) {
			$this->log = [];
		}
	}

	/**
	 * Returns option name that will store the update log.
	 *
	 * @return string
	 */
	abstract protected function get_log_option_name(): string;

	public function register_activation_hook() {
		register_activation_hook( $this->base_file_name, [ $this, 'install' ] );
	}

	/**
	 * Checks if database needs to be updated.
	 *
	 * @return bool
	 */
	public function is_old_database(): bool {
		return $this->get_current_db_version() !== $this->get_target_version();
	}

	private function get_current_db_version(): int {
		return (int) get_option( $this->get_version_option_name(), 0 );
	}

	abstract protected function get_version_option_name(): string;

	/**
	 * Returns the highest version that exists in the updates.
	 */
	private function get_target_version(): int {
		return max(
			1,
			...array_map(
				function ( $item ) {
					return $item::get_version();
				},
				$this->get_updates()
			)
		);
	}

	/**
	 * Returns updates that can be done. Updates should be sorted ascending by ids.
	 *
	 * @return class-string<Update>[]
	 */
	abstract protected function get_updates(): array;

	/**
	 *  Creates tables
	 */
	public function install() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$this->db_log( 'DB update start' );
		$current_version = $this->get_current_db_version();
		$no_errors       = true;

		foreach ( $this->get_updates() as $update ) {
			if ( is_string( $update ) ) {
				$update = new $update();
			}
			if ( $update instanceof LoggerAwareInterface ) {
				$update->setLogger( new NullLogger() );
			}
			$target_version = $update::get_version();
			if ( $no_errors && $current_version < $target_version ) {
				$this->db_log( "DB update {$current_version}:{$target_version}" );
				$no_errors = $update->update( $this->wpdb );
				$this->db_log( "DB update {$current_version}:{$target_version} -> " . ( $no_errors ? 'OK' : 'ERROR: ' . $wpdb->last_error ) );
				update_option( $this->get_version_option_name(), $target_version, true );
			}
		}

		if ( ! $no_errors ) {
			$error_msg = "Error while upgrading a database: {$wpdb->last_error}";
			$this->db_log( $error_msg );
			trigger_error( $error_msg, E_USER_WARNING );
		}
	}

	/**
	 * Save info to special log that should be almost always available (if db is).
	 *
	 * @param string $message
	 */
	private function db_log( string $message ) {
		$max_log_size = 30;
		$this->log[]  = date( 'Y-m-d G:i:s' ) . ": {$message}";
		if ( count( $this->log ) > $max_log_size ) {
			array_shift( $this->log );
		}
		update_option( $this->get_log_option_name(), json_encode( $this->log ), false );
	}
}
