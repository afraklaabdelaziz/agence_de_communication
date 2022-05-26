<?php

namespace WPDesk\ShopMagic\Tracker;

/**
 * Tracks data about deactivations.
 *
 * @package WPDesk\ShopMagic\Tracker
 */
class DeactivationTracker {

	/** @var string */
	private $plugin_file_name;

	public function __construct( $plugin_file_name ) {
		$this->plugin_file_name = $plugin_file_name;
	}

	public function hooks() {
		$tracker_factory = new \WPDesk_Tracker_Factory();
		$tracker_factory->create_tracker( $this->plugin_file_name );

		add_filter( 'wpdesk_track_plugin_deactivation', [ $this, 'wpdesk_track_plugin_deactivation' ] );
	}

	/**
	 * @param array $plugins
	 *
	 * @return array
	 */
	public function wpdesk_track_plugin_deactivation( $plugins ) {
		$plugins['shopmagic-for-woocommerce/shopMagic.php'] = 'shopmagic-for-woocommerce/shopMagic.php';

		return $plugins;
	}
}

