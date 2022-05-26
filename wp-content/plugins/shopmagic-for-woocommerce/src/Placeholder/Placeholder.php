<?php

namespace WPDesk\ShopMagic\Placeholder;

use ShopMagicVendor\WPDesk\Forms\Field;
use WPDesk\ShopMagic\DataSharing\DataReceiver;

/**
 * Static function are responsible for the info that is required to establish a contract:
 * what should be prepared for this class to successfully instantiate and will the instance be used.
 * We should avoid changes in these static conditions during runtime. If these conditions needs to change then we
 * should refactor the static part to another class. Now it's here to greatly simplify the extending of the class for external devs.
 * er ins
 * Three responsibilities:
 * - Has info how the placeholder should look in admin panel: name, description, parameters to render
 * - DataReceiver.
 * - Receives data for processing placeholder shortcode and processes it.
 *
 * @TODO: in 3.0 merge PlaceholderVariableParameters as parameter to get_supported_parameters
 *
 * @package WPDesk\ShopMagic\Placeholder
 */
interface Placeholder extends DataReceiver {
	/**
	 * Shortcode for the placeholder. Have to be unique. Can be in any format but
	 * most placeholder should use groupname.name-of-the-placeholder format.
	 * In form input the groupname.name-of-the-placeholder should looks like {{ groupname.name-of-the-placeholder }}
	 *
	 * @return string
	 */
	public function get_slug();

	/**
	 * Description of the placeholder that will be shown in admin panel.
	 * Can return void if there is no description.
	 *
	 * @return string|void
	 */
	public function get_description();

	/**
	 * @return Field[]
	 */
	public function get_supported_parameters();

	/**
	 * Placeholder value to replace the shortcode of given name.
	 *
	 * @param string[] $parameters
	 *
	 * @return string
	 */
	public function value( array $parameters );

}
