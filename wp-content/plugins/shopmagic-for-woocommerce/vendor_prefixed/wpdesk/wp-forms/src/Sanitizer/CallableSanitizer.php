<?php

namespace ShopMagicVendor\WPDesk\Forms\Sanitizer;

use ShopMagicVendor\WPDesk\Forms\Sanitizer;
class CallableSanitizer implements \ShopMagicVendor\WPDesk\Forms\Sanitizer
{
    private $callable;
    public function __construct($callable)
    {
        $this->callable = $callable;
    }
    public function sanitize($value)
    {
        return \call_user_func($this->callable, $value);
    }
}
