<?php

namespace ShopMagicVendor\WPDesk\Forms;

interface Serializer
{
    public function serialize($value);
    public function unserialize($value);
}
