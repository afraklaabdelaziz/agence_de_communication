<?php

namespace ShopMagicVendor\WPDesk\Forms\Field;

class TimepickerField extends \ShopMagicVendor\WPDesk\Forms\Field\BasicField
{
    /**
     * @inheritDoc
     */
    public function get_template_name()
    {
        return 'timepicker';
    }
}
