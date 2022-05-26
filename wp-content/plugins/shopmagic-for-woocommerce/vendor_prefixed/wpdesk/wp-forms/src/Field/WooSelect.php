<?php

namespace ShopMagicVendor\WPDesk\Forms\Field;

class WooSelect extends \ShopMagicVendor\WPDesk\Forms\Field\SelectField
{
    public function __construct()
    {
        parent::__construct();
        $this->set_multiple();
        $this->add_class('wc-enhanced-select');
    }
    public function get_template_name()
    {
        return 'woo-select';
    }
}
