<?php

namespace ShopMagicVendor\WPDesk\Forms\Sanitizer;

use ShopMagicVendor\WPDesk\Forms\Sanitizer;
class TextFieldSanitizer implements \ShopMagicVendor\WPDesk\Forms\Sanitizer
{
    public function sanitize($value)
    {
        if (\is_array($value)) {
            return \array_map('sanitize_text_field', $value);
        }
        return \sanitize_text_field($value);
    }
}
