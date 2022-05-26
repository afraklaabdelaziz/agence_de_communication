<?php

namespace WPDesk\ShopMagic\Admin\Settings;

use ShopMagicVendor\WPDesk\Forms\Resolver\DefaultFormFieldResolver;
use ShopMagicVendor\WPDesk\Notice\Notice;
use ShopMagicVendor\WPDesk\Persistence\Adapter\WordPress\WordpressOptionsContainer;
use ShopMagicVendor\WPDesk\Persistence\PersistentContainer;
use ShopMagicVendor\WPDesk\View\Renderer\SimplePhpRenderer;
use ShopMagicVendor\WPDesk\View\Resolver\ChainResolver;
use ShopMagicVendor\WPDesk\View\Resolver\DirResolver;
use WPDesk\ShopMagic\Automation\AutomationPostType;
use WPDesk\ShopMagic\Helper\CapabilitiesCheckTrait;
use WPDesk\ShopMagic\Helper\WordPressPluggableHelper;
use WPDesk\ShopMagic\Integration;

/**
 * Adds settings to the menu and manages how and what is shown on the settings page.
 *
 * @package WPDesk\ShopMagic\Admin\Settings
 */
final class Settings {
	use CapabilitiesCheckTrait;

	/** @var string */
	private static $settings_slug = 'shopmagic-settings';

	/**
	 * Get URL to plugin settings, optionally to specific tab.
	 *
	 * @param string|null $tab_slug When null returns URL to general settings.
	 *
	 * @return string
	 */
	public static function get_url( $tab_slug = null ) {
		$url = admin_url( add_query_arg( [ 'page' => self::$settings_slug ], AutomationPostType::POST_TYPE_MENU_URL ) );

		if ( $tab_slug !== null ) {
			$url = add_query_arg( [ 'tab' => $tab_slug ], $url );
		}

		return $url;
	}

	public function hooks() {
		add_action(
			'admin_menu',
			function () {
				$allowed_capability = $this->allowed_capability();
				if ( $allowed_capability ) {
					add_submenu_page(
						AutomationPostType::POST_TYPE_MENU_URL,
						__( 'Settings', 'shopmagic-for-woocommerce' ),
						__( 'Settings', 'shopmagic-for-woocommerce' ),
						$allowed_capability,
						self::$settings_slug,
						[ $this, 'render_page_action' ]
					);
				}
			}
		);

		add_action( 'admin_init', [ $this, 'save_settings_action' ], 5 );
	}

	/**
	 * Save POST tab data. Before render.
	 *
	 * @return void
	 */
	public function save_settings_action() {
		if ( isset( $_GET['page'] ) && $_GET['page'] !== self::$settings_slug ) {
			return;
		}
		$tab            = $this->get_active_tab();
		$data_container = self::get_settings_persistence( $tab::get_tab_slug() );
		if ( ! empty( $_POST ) && isset( $_POST[ $tab::get_tab_slug() ] ) ) {
			$tab->handle_request( $_POST[ $tab::get_tab_slug() ] );
			$this->save_tab_data( $tab, $data_container );
			do_action( 'shopmagic/core/settings/tabs/saved', $tab, $data_container );

			new Notice(
				__( 'Your settings have been saved.', 'shopmagic-for-woocommerce' ),
				Notice::NOTICE_TYPE_SUCCESS
			);
		} else {
			$tab->set_data( $data_container );
		}
		/**
		 * Fires when ShopMagic settings can be accessed using GeneralSettings::get_option.
		 *
		 * @since 2.15
		 */
		do_action( 'shopmagic/core/settings/ready' );
	}

	/**
	 * Render
	 *
	 * @return void
	 */
	public function render_page_action() {
		$tab      = $this->get_active_tab();
		$renderer = $this->get_renderer();
		$renderer->output_render(
			'menu',
			[
				'base_url'   => self::get_url(),
				'menu_items' => $this->get_tabs_menu_items(),
				'selected'   => $this->get_active_tab()->get_tab_slug(),
			]
		);
		echo $tab->render( $renderer );
		$renderer->output_render( 'footer' );
	}

	/**
	 * @return SettingsTab
	 */
	private function get_active_tab() {
		$selected_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : null;
		$tabs         = $this->get_settings_tabs();
		if ( ! empty( $selected_tab ) && isset( $tabs[ $selected_tab ] ) ) {
			return $tabs[ $selected_tab ];
		}

		return reset( $tabs );
	}

	/**
	 * @return SettingsTab[]
	 */
	private function get_settings_tabs(): array {
		static $tabs = [];
		if ( empty( $tabs ) ) {
			$tabs = apply_filters(
				'shopmagic/core/settings/tabs',
				[
					GeneralSettings::get_tab_slug() => new GeneralSettings(),
					Integration\Mailchimp\Settings::get_tab_slug() => new Integration\MailChimp\Settings(),
				]
			);
		}

		return $tabs;
	}

	/**
	 * Returns writable container with saved settings.
	 *
	 * @param string $tab_slug Unique slug of a settings tab.
	 *
	 * @return PersistentContainer
	 */
	public static function get_settings_persistence( $tab_slug ) {
		if ( in_array( $tab_slug, [ 'mailchimp', 'general' ] ) ) { // special case for backward compatibility.
			return new WordpressOptionsContainer();
		}

		// TODO: deferred option container with serialization.
		return new WordpressOptionsContainer( 'shopmagic-settings-' . $tab_slug );
	}

	/**
	 * Save data from tab to persistent container.
	 *
	 * @param SettingsTab $tab
	 * @param PersistentContainer $container
	 */
	private function save_tab_data( SettingsTab $tab, PersistentContainer $container ) {
		$tab_data = $tab->get_data();
		array_walk(
			$tab_data,
			static function ( $value, $key ) use ( $container ) {
				if ( ! empty( $key ) ) {
					$container->set( $key, $value );
				}
			}
		);
	}

	/**
	 * @return \ShopMagicVendor\WPDesk\View\Renderer\Renderer
	 */
	private function get_renderer() {
		$chain         = new ChainResolver();
		$resolver_list =
			/**
			 * Use when you want to to change how fields are rendered in in ShopMagic settings.
			 *
			 * @param \ShopMagicVendor\WPDesk\View\Resolver\Resolver[] List of default resolvers. Order is important as the first found will be used.
			 *
			 * @return \ShopMagicVendor\WPDesk\View\Resolver\Resolver[] List of resolvers.
			 * @internal Every template resolver must implement Resolver interface.
			 */
			apply_filters(
				'shopmagic/core/settings/template_resolvers',
				[
					new DirResolver( __DIR__ . '/settings-templates' ),
					new DefaultFormFieldResolver(),
				]
			);
		foreach ( $resolver_list as $resolver ) {
			$chain->appendResolver( $resolver );
		}

		return new SimplePhpRenderer( $chain );
	}

	/**
	 * @return string[]
	 */
	private function get_tabs_menu_items() {
		$menu_items = [];

		foreach ( $this->get_settings_tabs() as $tab ) {
			$menu_items[ $tab::get_tab_slug() ] = $tab->get_tab_name();
		}

		return $menu_items;
	}
}
