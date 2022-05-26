<?php

namespace ShopMagicVendor\WPDesk\Forms\Field;

class MultipleInputTextField extends \ShopMagicVendor\WPDesk\Forms\Field\InputTextField
{
    /**
     * @return string
     */
    public function get_template_name()
    {
        return 'input-text-multiple';
    }
}
