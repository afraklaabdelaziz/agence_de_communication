<?php

namespace ShopMagicVendor\WPDesk\Forms;

interface Escaper
{
    /**
     * @param mixed $value
     *
     * @return string
     */
    public function escape($value);
}
