<?php
if (!defined('ABSPATH')) exit;
$th_product_compare_btn_txt = isset($th_compare_option['compare-btn-text']) ? sanitize_text_field($th_compare_option['compare-btn-text']) : __('Compare', 'th-product-compare');
$th_product_limit = isset($th_compare_option['compare-product-limit']) ? sanitize_text_field($th_compare_option['compare-product-limit']) : 8;
$th_product_btn_type = isset($th_compare_option['compare-btn-type']) ? sanitize_text_field($th_compare_option['compare-btn-type']) : '';
$checkChecked = [
    'field-product-page' => 'checked="checked"',
    'field-product-single-page' => 'checked="checked"',
];

if (is_array($th_compare_option)) {
    foreach ($checkChecked as $key => $value) {
        if (isset($th_compare_option[$key])) {
            if ($th_compare_option[$key] == '1') {
                $checkChecked[$key] = 'checked="checked"';
            } else {
                $checkChecked[$key] = '';
            }
        }
    }
}
?>
<div class="th-general">
    <div class="th-option_">
        <span class="th-tab-heading"><?php _e('Appearance', 'th-product-compare'); ?></span>
        <table>
            <tr>
                <td><span class="th-color-title"><?php _e('Link or Button', 'th-product-compare') ?></span></td>
                <td>
                    <select data-th-save='compare-btn-type'>
                        <option value="button" <?php esc_html_e($th_product_btn_type == 'button' ? "selected" : '', 'th-product-compare') ?>><?php _e('Button', 'th-product-compare') ?></option>
                        <option value="link" <?php esc_html_e($th_product_btn_type == 'link' ? "selected" : '', 'th-product-compare') ?>><?php _e('Link', 'th-product-compare') ?></option>
                    </select>
                    <i class="description"><?php _e('How you want to display compare trigger (Like a Link or a Button).', 'th-product-compare') ?></i>
                </td>
            </tr>
            <tr>
                <td><span class="th-color-title"><?php _e('Link / Button Text', 'th-product-compare') ?></span></td>
                <td>
                    <input data-th-save='compare-btn-text' type="text" placeholder="Compare" value="<?php esc_html_e($th_product_compare_btn_txt, 'th-product-compare'); ?>">
                    <i class="description"><?php _e('This value define maximum number of products you want to add in the compare table.', 'th-product-compare') ?></i>
                </td>
            </tr>
            <tr>
                <td><span class="th-color-title"><?php _e('Number of Product to Compare', 'th-product-compare') ?></span></td>
                <td>
                    <input data-th-save='compare-product-limit' type="number" placeholder="8" value="<?php esc_html_e($th_product_limit, 'th-product-compare'); ?>">
                </td>
            </tr>
            <tr>
                <td><span class="th-color-title"><?php _e('Display Compare Button', 'th-product-compare') ?></span></td>
                <td>
                    <div class="th-compare-radio">
                        <!--title-->
                        <input type="checkbox" data-th-save='compare-field' id="field-show-product-page" <?php esc_html_e($checkChecked['field-product-single-page'], 'th-product-compare'); ?> value="product-single-page">
                        <label class="th-color-title" for="field-show-product-page"><?php _e('Product Single Page.', 'th-product-compare') ?></label>
                    </div>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <div class="th-compare-radio">
                        <!--title-->
                        <input type="checkbox" data-th-save='compare-field' id="compare-fields-product-list" <?php esc_html_e($checkChecked['field-product-page'], 'th-product-compare'); ?> value="product-page">
                        <label class="th-color-title" for="compare-fields-product-list"><?php _e('Shop and Archive Pages.', 'th-product-compare') ?></label>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>