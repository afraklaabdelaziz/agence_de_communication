<?php

namespace ShopMagicVendor\WPDesk\Forms\Serializer;

use ShopMagicVendor\WPDesk\Forms\Serializer;
/**
 * When serializing product select data lets also write product names in keys.
 *
 * @package WPDesk\Forms\Serializer
 */
class ProductSelectSerializer implements \ShopMagicVendor\WPDesk\Forms\Serializer
{
    /**
     * @param $value
     */
    public function serialize($value)
    {
        $products_with_names = [];
        if (\is_array($value)) {
            foreach ($value as $product_id) {
                $product = \wc_get_product($product_id);
                if ($product) {
                    $name = $product->get_formatted_name();
                    $products_with_names[$name] = $product_id;
                }
            }
        }
        return $products_with_names;
    }
    public function unserialize($value)
    {
        return $value;
    }
}
