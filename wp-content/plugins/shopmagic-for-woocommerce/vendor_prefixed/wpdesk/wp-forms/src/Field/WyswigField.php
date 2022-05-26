<?php

namespace ShopMagicVendor\WPDesk\Forms\Field;

class WyswigField extends \ShopMagicVendor\WPDesk\Forms\Field\BasicField
{
    public function __construct()
    {
        parent::__construct();
        $this->set_default_value('');
    }
    public function get_template_name()
    {
        return 'wyswig';
    }
    public function should_override_form_template()
    {
        return \true;
    }
}
