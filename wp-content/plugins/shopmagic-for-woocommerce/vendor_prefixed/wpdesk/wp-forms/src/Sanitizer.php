<?php

namespace ShopMagicVendor\WPDesk\Forms;

interface Sanitizer
{
    /**
     * @param mixed $value
     *
     * @return string
     */
    public function sanitize($value);
}
