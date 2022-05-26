<?php

namespace ShopMagicVendor\WPDesk\Forms\Sanitizer;

use ShopMagicVendor\WPDesk\Forms\Sanitizer;
class NoSanitize implements \ShopMagicVendor\WPDesk\Forms\Sanitizer
{
    public function sanitize($value)
    {
        return $value;
    }
}
