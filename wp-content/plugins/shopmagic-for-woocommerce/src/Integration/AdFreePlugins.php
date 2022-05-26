<?php

namespace WPDesk\ShopMagic\Integration;

use WPDesk\ShopMagic\Admin\Settings\CartAdSettings;
use WPDesk\ShopMagic\Admin\Settings\ContactForm7AdSettings;
use WPDesk\ShopMagic\Admin\Settings\TwilioAdSettings;
use WPDesk\ShopMagic\Helper\PluginInstaller;
use WPDesk\ShopMagic\Helper\WordPressPluggableHelper;

/**
 * Add setting tabs with free plugins installer.
 *
 * @package WPDesk\ShopMagic\Integration
 */
final class AdFreePlugins {
	/** @return void */
	public function hooks() {
		( new PluginInstaller(
			CartAdSettings::plugin_slug(),
			CartAdSettings::ajax_install_action(),
			CartAdSettings::nonce()
		) )->hook();

		( new PluginInstaller(
			TwilioAdSettings::plugin_slug(),
			TwilioAdSettings::ajax_install_action(),
			TwilioAdSettings::nonce()
		) )->hook();

		( new PluginInstaller(
			ContactForm7AdSettings::plugin_slug(),
			ContactForm7AdSettings::ajax_install_action(),
			ContactForm7AdSettings::nonce()
		) )->hook();

		add_filter( 'shopmagic/core/settings/tabs', [ $this, 'append_settings' ] );
	}

	/**
	 * @param object[] $tabs
	 * @return object[]
	 * @internal
	 */
	public function append_settings( array $tabs ): array {
		if ( ! WordPressPluggableHelper::is_plugin_active( CartAdSettings::plugin_slug() ) ) {
			$tabs[ CartAdSettings::get_tab_slug() ] = new CartAdSettings();
		}
		if ( ! WordPressPluggableHelper::is_plugin_active( TwilioAdSettings::plugin_slug() ) ) {
			$tabs[ TwilioAdSettings::get_tab_slug() ] = new TwilioAdSettings();
		}

		if ( ! WordPressPluggableHelper::is_plugin_active( ContactForm7AdSettings::plugin_slug() ) ) {
			$tabs[ ContactForm7AdSettings::get_tab_slug() ] = new ContactForm7AdSettings();
		}

		return $tabs;
	}
}
