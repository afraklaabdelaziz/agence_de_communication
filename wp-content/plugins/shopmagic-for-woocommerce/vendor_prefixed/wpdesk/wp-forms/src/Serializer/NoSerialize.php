<?php

namespace ShopMagicVendor\WPDesk\Forms\Serializer;

use ShopMagicVendor\WPDesk\Forms\Serializer;
class NoSerialize implements \ShopMagicVendor\WPDesk\Forms\Serializer
{
    public function serialize($value)
    {
        return $value;
    }
    public function unserialize($value)
    {
        return $value;
    }
}
