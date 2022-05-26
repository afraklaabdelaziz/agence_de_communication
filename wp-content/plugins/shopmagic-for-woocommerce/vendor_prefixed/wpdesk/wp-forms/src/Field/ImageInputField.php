<?php

namespace ShopMagicVendor\WPDesk\Forms\Field;

class ImageInputField extends \ShopMagicVendor\WPDesk\Forms\Field\BasicField
{
    public function __construct()
    {
        parent::__construct();
        $this->set_default_value('');
        $this->set_attribute('type', 'text');
    }
    /**
     * @return string
     */
    public function get_template_name()
    {
        return 'input-image';
    }
}
