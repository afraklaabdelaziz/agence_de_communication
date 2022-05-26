<?php

namespace WPDesk\ShopMagic\Beacon;

use ShopMagicVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use ShopMagicVendor\WPDesk\ShowDecision\ShouldShowStrategy;

/**
 * Can display HelpScout Beacon. For more info check https://secure.helpscout.net/settings/beacons/
 */
class Beacon implements Hookable {

	/**
	 * Beacon UUID from HelpScout.
	 *
	 * @var string
	 */
	private $beacon_id;

	/**
	 * When to display beacon.
	 *
	 * @var ShouldShowStrategy
	 */
	private $activation_strategy;

	/**
	 * @var string
	 */
	private $assets_url;

	/**
	 * Beacon constructor.
	 *
	 * @param string $beacon_id .
	 * @param ShouldShowStrategy $strategy When to display beacon.
	 * @param string $assets_url With ending /
	 */
	public function __construct( $beacon_id, ShouldShowStrategy $strategy, $assets_url ) {
		$this->beacon_id           = $beacon_id;
		$this->activation_strategy = $strategy;
		$this->assets_url          = $assets_url;
	}

	/**
	 * Hooks.
	 */
	public function hooks() {
		if ( $this->should_display_beacon() ) {
			add_action( 'admin_footer', [ $this, 'add_beacon_to_footer' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'add_beacon_js' ] );
		}
	}

	/**
	 * Should display beacon?
	 *
	 * @return bool
	 */
	private function should_display_beacon() {
		return $this->activation_strategy->shouldDisplay();
	}

	public function add_beacon_js() {
		if ( $this->should_display_beacon() ) {
			wp_register_script( 'hs-beacon', $this->assets_url . 'js/hs-bc.js', [] );
			wp_enqueue_script( 'hs-beacon' );
		}
	}

	/**
	 * Display Beacon script.
	 */
	public function add_beacon_to_footer() {
		if ( $this->should_display_beacon() ) {
			$beacon_id = $this->beacon_id;
			include __DIR__ . '/templates/html-beacon-script.php';
		}
	}

}
