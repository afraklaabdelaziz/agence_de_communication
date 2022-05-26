<?php

namespace WPDesk\ShopMagic\Admin\Settings;

/**
 * Inform users about Contact Form 7 free add-on
 *
 * @package WPDesk\ShopMagic\Admin\Settings
 */
final class ContactForm7AdSettings extends AdSettings {
	public static function get_tab_slug(): string {
		return 'contact-form-7';
	}

	public function get_tab_name(): string {
		return __( 'Contact Form 7', 'shopmagic-for-woocommerce' );
	}

	public static function ajax_install_action(): string {
		return 'shopmagic_install_contact_form_7';
	}

	public static function template(): string {
		return 'contact-form-7-ad-template';
	}

	public static function plugin_slug(): string {
		return 'shopmagic-for-contact-form-7/shopmagic-for-contact-form-7.php';
	}
}
