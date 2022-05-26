<?php

namespace WPDesk\ShopMagic\Admin\Settings;

final class TwilioAdSettings extends AdSettings {
	public static function get_tab_slug(): string {
		return 'twilio';
	}

	public function get_tab_name(): string {
		return __( 'Twilio', 'shopmagic-for-woocommerce' );
	}

	public static function ajax_install_action(): string {
		return 'shopmagic_install_twilio';
	}

	public static function template(): string {
		return 'twilio-ad-template';
	}

	public static function plugin_slug(): string {
		return 'shopmagic-for-twilio/shopmagic-for-twilio.php';
	}
}
