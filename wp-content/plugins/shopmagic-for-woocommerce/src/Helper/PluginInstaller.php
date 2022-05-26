<?php

namespace WPDesk\ShopMagic\Helper;

final class PluginInstaller {

	private $plugin_slug;

	private $ajax_slug;

	private $nonce;

	public function __construct( string $plugin_slug, string $ajax_slug, string $nonce ) {
		$this->plugin_slug = $plugin_slug;
		$this->ajax_slug   = $ajax_slug;
		$this->nonce       = $nonce;
	}

	public function hook() {
		add_action( 'wp_ajax_' . $this->ajax_slug, [ $this, 'install' ] );
	}

	/**
	 * @internal
	 */
	public function install() {
		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], $this->nonce ) ) {
			wp_send_json_error( __( 'Error: Nonce verification failed.', 'shopmagic-for-woocommerce' ) );
		}

		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		$api = plugins_api(
			'plugin_information',
			[
				'slug'   => explode( '/', $this->plugin_slug )[0],
				'fields' => [ 'sections' => false ],
			]
		);

		$upgrader = new \Plugin_Upgrader( new \WP_Ajax_Upgrader_Skin() );
		$result   = $upgrader->install( $api->download_link );
		activate_plugin( $this->plugin_slug );

		wp_send_json_success();
	}
}
