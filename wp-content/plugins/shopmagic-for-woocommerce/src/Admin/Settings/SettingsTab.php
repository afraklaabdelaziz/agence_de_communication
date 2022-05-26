<?php

namespace WPDesk\ShopMagic\Admin\Settings;

use Psr\Container\ContainerInterface;
use ShopMagicVendor\WPDesk\View\Renderer\Renderer;

/**
 * Tab than can be rendered on settings page.
 *
 * Tab have to know how:
 * - to display itself ::render_tab
 * - to save it's data ::save_tab_data
 * And should know how it's called ::get_tab_name
 *
 * @package WPDesk\ShopMagic\Admin\Settings
 */
interface SettingsTab {
	/**
	 * Slug name used for unique url and settings in db.
	 *
	 * @return string
	 */
	public static function get_tab_slug();

	/**
	 * Tab name to show on settings page.
	 *
	 * @return string
	 */
	public function get_tab_name();

	/**
	 * Render tab content and return it as string.
	 *
	 * @param Renderer $renderer
	 *
	 * @return string
	 */
	public function render( Renderer $renderer );

	/**
	 * Use to set settings from database or defaults.
	 *
	 * @param array|ContainerInterface $data Data to render.
	 *
	 * @return void
	 */
	public function set_data( $data );

	/**
	 * Use to handle request data from POST.
	 * Data in POST request should be prefixed with slug.
	 * For example if slug is 'stefan' and the input has name 'color' and value 'red' then the data should be sent as
	 * $_POST = [ 'stefan' => [ 'color' => 'red' ] ].
	 *
	 * @param array $request Data retrieved from POST request.
	 *
	 * @return void
	 */
	public function handle_request( $request );

	/**
	 * Returns valid data from Tab. Can be used after ::handle_request or ::set_data.
	 *
	 * @return array
	 */
	public function get_data();
}
