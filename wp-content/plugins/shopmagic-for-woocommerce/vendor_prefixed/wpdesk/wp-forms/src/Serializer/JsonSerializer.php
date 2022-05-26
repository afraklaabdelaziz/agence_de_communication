<?php

namespace ShopMagicVendor\WPDesk\Forms\Serializer;

use ShopMagicVendor\WPDesk\Forms\Serializer;
class JsonSerializer implements \ShopMagicVendor\WPDesk\Forms\Serializer
{
    public function serialize($value)
    {
        return \json_encode($value);
    }
    public function unserialize($value)
    {
        return \json_decode($value, \true);
    }
}
