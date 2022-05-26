<?php

namespace ShopMagicVendor\WPDesk\Forms\Validator;

use ShopMagicVendor\WPDesk\Forms\Validator;
class NoValidateValidator implements \ShopMagicVendor\WPDesk\Forms\Validator
{
    public function is_valid($value)
    {
        return \true;
    }
    public function get_messages()
    {
        return [];
    }
}
