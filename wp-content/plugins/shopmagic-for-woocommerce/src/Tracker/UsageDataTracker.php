<?php

namespace WPDesk\ShopMagic\Tracker;

use ShopMagicVendor\WPDesk_Tracker;
use WPDesk\ShopMagic\Tracker\Provider\AutomationDataProvider;
use WPDesk\ShopMagic\Tracker\Provider\ExtensionDataProvider;
use WPDesk\ShopMagic\Tracker\Provider\RecipeDataProvider;
use WPDesk\ShopMagic\Tracker\Provider\SettingsDataProvider;

/**
 * Tracks data about usages.
 *
 * @package WPDesk\ShopMagic\Tracker
 */
class UsageDataTracker {

	/** @var string */
	private $plugin_file_name;

	public function __construct( $plugin_file_name ) {
		$this->plugin_file_name = $plugin_file_name;
	}

	public function hooks() {
		$tracker_factory = new \WPDesk_Tracker_Factory();
		/** @var \WPDesk_Tracker_Interface $tracker */
		$tracker = $tracker_factory->create_tracker( $this->plugin_file_name );

		global $wpdb;
		$tracker->add_data_provider( new AutomationDataProvider( $wpdb ) );
		$tracker->add_data_provider( new ExtensionDataProvider() );
		$tracker->add_data_provider( new SettingsDataProvider() );
		$tracker->add_data_provider( new RecipeDataProvider() );

		add_filter(
			'wpdesk_tracker_enabled',
			function () {
				return true;
			}
		);
	}
}
