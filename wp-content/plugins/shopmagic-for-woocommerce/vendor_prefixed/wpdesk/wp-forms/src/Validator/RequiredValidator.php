<?php

namespace ShopMagicVendor\WPDesk\Forms\Validator;

use ShopMagicVendor\WPDesk\Forms\Validator;
class RequiredValidator implements \ShopMagicVendor\WPDesk\Forms\Validator
{
    public function is_valid($value)
    {
        return $value !== null;
    }
    public function get_messages()
    {
        return [];
    }
}
