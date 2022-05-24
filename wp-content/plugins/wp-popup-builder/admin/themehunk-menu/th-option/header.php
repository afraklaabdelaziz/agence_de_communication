<div class="header th-option-area">

    <!-- themehunkhemes-badge wp-badge-->
    <div class="th-heade th-left-header">
        <div class="th-option-top-hdr">
            <div class="col-1">
                <div class="logo-img">
                    <a target="_blank" href="<?php echo esc_url($theme_header['theme_brand_url']); ?>/?wp=plugins'.). ?>"> <span class="logo-image"><img src="<?php echo esc_url(THEMEHUNK_PURL); ?>/th-option/assets/images/icon.png" /><?php echo $theme_header['theme_brand']; ?></span></a>
                </div>
            </div>
            <div class="col-2">
                <div class="th-option-heading">
                    <h2><?php echo $theme_header['welcome']; ?></h2>
                    <span><?php echo $theme_header['welcome_desc']; ?></span>
                </div>
            </div>
        </div>
        <div class="th-option-bottom-hdr">
            <a class="tablinks active <?php echo esc_attr($tabActiveRp) ?>" onclick="openTab(event, 'Recommended-Plugin')"><?php _e('Plugins', 'wp-popup-builder'); ?> </a>
        </div>
    </div>


    <div class="th-heade th-right-header">
        <div class="th-noticetheme">
        <a target="_blank" href="https://themehunk.com/th-shop-mania/">
        <img src="<?php echo THEMEHUNK_PURL; ?>th-option/assets/images/th-shop-mania.png">
        </a>
    </div>
        <div class="thunk-notice-text">
            <h3><?php _e('TH Shop Mania Free Theme','wp-popup-builder'); ?></h3>
            <p><?php _e('Elementor + WooCommerce Free WordPress Theme.','wp-popup-builder'); ?>
            </p>
            <a class="button" target="_blank" href="<?php echo esc_url("https://themehunk.com/th-shop-mania/"); ?>"> <?php _e('View Detail','wp-popup-builder'); ?></a>

        </div>
    </div>

</div> <!-- /header -->