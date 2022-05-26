<?php

namespace ShopMagicVendor\WPDesk\Forms\Serializer;

use ShopMagicVendor\WPDesk\Forms\Serializer;
class SerializeSerializer implements \ShopMagicVendor\WPDesk\Forms\Serializer
{
    public function serialize($value)
    {
        return \serialize($value);
    }
    public function unserialize($value)
    {
        return \unserialize($value);
    }
}
