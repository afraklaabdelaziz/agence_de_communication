<?php

namespace WPDesk\ShopMagic\Placeholder;

/**
 * Thanks to this, placeholder can get parameters from frontend and is it in get_supported_parameters.
 *
 * @TODO: in 3.0 merge PlaceholderVariableParameters as parameter to get_supported_parameters
 *
 * @package WPDesk\ShopMagic\Placeholder
 */
interface PlaceholderVariableParameters {
	public function set_parameters_values( $values);
}
