<?php

if (! defined('ABSPATH') ) {
    exit; // Exit if accessed directly
}

/**
 * Display the test mode notice
 **/
function at_wc_atara_testmode_notice()
{
    $settings = get_option('woocommerce_at_atara_settings');
    $test_mode = isset($settings['testmode']) ? $settings['testmode'] : '';
    if ('yes' == $test_mode) {
        ?>
      <div class="update-nag">
        Atarapay testmode is still enabled, Click <a href="<?php echo admin_url('admin.php?page=wc-settings&tab=checkout&section=at_atara') ?>">here</a> to disable it when you want to start accepting live payment on your site.
      </div>
        <?php
    }
}
add_action('admin_notices', 'at_wc_atara_testmode_notice');
