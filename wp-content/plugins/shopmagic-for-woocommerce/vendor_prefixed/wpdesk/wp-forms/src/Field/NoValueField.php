<?php

namespace ShopMagicVendor\WPDesk\Forms\Field;

/**
 * Base class for Fields that can show itself on form but cannot process any value.
 *
 * @package WPDesk\Forms
 */
abstract class NoValueField extends \ShopMagicVendor\WPDesk\Forms\Field\BasicField
{
    public function get_name()
    {
        return '';
    }
}
