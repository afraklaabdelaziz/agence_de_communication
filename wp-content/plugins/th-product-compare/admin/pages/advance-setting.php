<?php
if (!defined('ABSPATH')) exit;
$defaultAttributes = [
    'image' => ["active" => 1],
    'title' => ["active" => 1],
    'rating' => ["active" => 1],
    'price' => ["active" => 1],
    'add-to-cart' => ["active" => 1],
    'description' => ["active" => 0],
    'availability' => ["active" => 1],
    'SKU' => ["active" => 1],
];

if (is_array($th_compare_option)) {
    if (isset($th_compare_option['attributes'])) {
        $defaultAttributes = $th_compare_option['attributes'];
    }
}
$fieldRepeatPrice = isset($th_compare_option['field-repeat-price']) && $th_compare_option['field-repeat-price'] == '1' ? 'checked="checked"' : '';
$fieldrepeatAddToCart = isset($th_compare_option['field-repeat-add-to-cart']) && $th_compare_option['field-repeat-add-to-cart'] == '1' ? 'checked="checked"' : '';

function th_compare_productsAttributes($defaultAttributes)
{
    foreach ($defaultAttributes as $key => $value) {
        $uniqId = 'compare-attributes-' . $key;
        $name_ = ucfirst(str_replace("-", " ", $key));
        $checkActive = $value['active'] == "1" ? "checked='checked'" : '';
?>
        <div class="th-compare-radio">
            <input type="checkbox" data-th-save="compare-attributes" <?php echo esc_attr($checkActive); ?> id="<?php echo esc_attr($uniqId); ?>" value="<?php echo esc_attr($key); ?>">
            <label class="th-color-title" for="<?php echo esc_attr($uniqId); ?>"> <?php _e($name_, 'th-product-compare') ?> </label>
        </div>
<?php
    }
}

?>
<div class="setting_">
    <div class="field-to-show">
        <span class="th-tab-heading"><?php _e('Advance Settings', 'th-product-compare'); ?></span>
        <div>
            <div class="row_">
                <div>
                    <span class="bold-heading"><?php _e('Fields to Show in Comparison Table', 'th-product-compare') ?></span>
                </div>
                <div class="th-compare-field-wrap woocommerce-th-attributes">
                    <?php th_compare_productsAttributes($defaultAttributes) ?>
                </div>
            </div>
            <div class="row_">
                <div>
                    <span class="bold-heading"><?php _e('Repeat Fields', 'th-product-compare') ?></span>
                </div>
                <div>
                    <div class="th-compare-radio">
                        <input type="checkbox" data-th-save='compare-field' id="compare-fields-repeat-price" <?php esc_html_e($fieldRepeatPrice, 'th-product-compare'); ?> value="repeat-price">
                        <label class="th-color-title" for="compare-fields-repeat-price"><?php _e('Repeat the <b> &#160 price &#160 </b> at the end of the table', 'th-product-compare') ?></label>
                    </div>
                </div>
            </div>
            <div class="row_">
                <div>
                </div>
                <div>
                    <div class="th-compare-radio">
                        <input type="checkbox" data-th-save='compare-field' id="compare-fields-repeat-add-to-cart" <?php esc_html_e($fieldrepeatAddToCart, 'th-product-compare'); ?> value="repeat-add-to-cart">
                        <label class="th-color-title" for="compare-fields-repeat-add-to-cart"><?php _e('Repeat  <b>&#160 add to cart &#160</b> at the end of the table', 'th-product-compare') ?></label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>