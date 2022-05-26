<?php

namespace WPDesk\ShopMagic\Admin\Settings;

use ShopMagicVendor\WPDesk\Forms\Field;
use ShopMagicVendor\WPDesk\Forms\Form\FormWithFields;
use ShopMagicVendor\WPDesk\View\Renderer\Renderer;

/**
 * Tab than can be rendered on settings page.
 * This abstraction should be used by tabs that want to use Form Fields to render its content.
 *
 * @package WPDesk\ShopMagic\Admin\Settings
 */
abstract class FieldSettingsTab implements SettingsTab {

	/** @var FormWithFields */
	private $form;

	/**
	 * @return Field[]
	 */
	abstract protected function get_fields();

	/**
	 * @return FormWithFields
	 */
	protected function get_form() {
		if ( $this->form === null ) {
			$fields     = $this->get_fields();
			$this->form = new FormWithFields( $fields, static::get_tab_slug() );
		}

		return $this->form;
	}

	public function render( Renderer $renderer ) {
		return $this->get_form()->render_form( $renderer );
	}

	public function set_data( $data ) {
		$this->get_form()->set_data( $data );
	}

	public function handle_request( $request ) {
		$this->get_form()->handle_request( $request );
	}

	public function get_data() {
		return $this->get_form()->get_data();
	}

	/**
	 * Simple access to settings. Just like WordPress style get_option.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public static function get_option( $key, $default = false ) {
		$persistence = Settings::get_settings_persistence( static::get_tab_slug() );
		if ( $persistence->has( $key ) ) {
			return $persistence->get( $key );
		}
		return $default;
	}
}
