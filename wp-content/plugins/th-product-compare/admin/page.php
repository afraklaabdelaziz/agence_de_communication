<?php if (!defined('ABSPATH')) exit; ?>
<div class="th-product-compare-wrap">
    <div class="th-product-compare-container">
        <nav class="th-nav_">
            <span class="logo-detail">
                <div class="img_">
                    <img src="<?php echo esc_url(TH_PRODUCT_URL . 'assets/img/th-logo.png'); ?>">
                </div>
                <span><?php _e('TH Product Compare', 'th-product-compare'); ?></span>
            </span>
            <a data-group-tabs="main" data-tab="general" href="#" class="active"><?php _e('Basic Settings', 'th-product-compare'); ?></a>
            <a data-group-tabs="main" data-tab="setting" href="#"><?php _e('Advance', 'th-product-compare'); ?></a>
            <a data-group-tabs="main" data-tab="pro-feature" href="#"><?php _e('Premium', 'th-product-compare'); ?></a>
            <a data-group-tabs="main" data-tab="help" href="#"><?php _e('Help', 'th-product-compare'); ?></a>
            <a data-group-tabs="main" data-tab="themehunk-useful" href="#"><?php _e('ThemeHunk Useful Plugins', 'th-product-compare'); ?></a>
            <div class="th-save-btn">
                <button class="button th-compare-reset-style-btn"><?php _e("Reset", 'th-product-compare'); ?></button>
                <button class="button button-primary th-option-save-btn"><?php _e("Save", 'th-product-compare'); ?></button>
            </div>
        </nav>
        <div class="container-tabs">
            <!-- general tab -->
            <div data-group-tabs="main" data-tab-container="general" class="active">
                <?php include_once 'pages/general.php'; ?>
            </div>
            <!-- setting tab -->
            <div data-group-tabs="main" data-tab-container="setting">
                <?php include_once 'pages/advance-setting.php'; ?>
            </div>
            <!-- help tab -->
            <div data-group-tabs="main" data-tab-container="help">
                <?php include_once 'pages/help.php'; ?>
            </div>
            <!-- pro feature tab -->
            <div data-group-tabs="main" data-tab-container="pro-feature">
                <?php include_once 'pages/pro-feature.php'; ?>
            </div>
            <!-- useful plugins tab -->
            <div data-group-tabs="main" data-tab-container="themehunk-useful">
                <?php include_once 'pages/themehunk-useful-plugins.php'; ?>
            </div>
        </div>
        <?php
        include 'pages/right-sidebar.php';
        ?>
    </div>

</div>