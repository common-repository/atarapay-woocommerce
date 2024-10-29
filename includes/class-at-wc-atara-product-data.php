<?php

if (!defined('ABSPATH')) {
    exit;
}

class At_WC_Atara_Product_Data
{
    private $At_WC_Atara_Service_Provider = null;

    function __construct()
    {
        $this->At_WC_Atara_Service_Provider = new At_WC_Atara_Service_Provider();
        add_action('woocommerce_product_options_general_product_data', array($this, "add_fields_to_general_tab"));
        add_action('woocommerce_process_product_meta', array($this, 'process_product_meta'), 10, 1);
    }

    function add_fields_to_general_tab( $post_id )
    {
        $providersList = $this->At_WC_Atara_Service_Provider->get_service_providers();
        $options = array(
        ''        => __('Select Service Provider', 'atarapay-woo'),
        );

        if (!empty($providersList)) {
            foreach ($providersList as $key => $value) {
                $currency = "NGN";
                if ( $value['type'] == "2" ) {
                    $currency = "USD";
                }
                $options[$value['id']] = $value['firstname'] . " " . $value['lastname'] . " (" . $currency . ") ";
            }
        }

        woocommerce_wp_select(
            array(
            'id'          => '_at_wc_atara_service_provider',
            'label' => __('Service Providers', 'atarapay-woo'),
            'options'     => $options
            )
        );


       
        woocommerce_wp_text_input(
            [
            'id' => '_at_wc_atara_service_provider_commission',
            'label' => __("Service Provider's Commission (%)", 'atarapay-woo'),
            'type' => "number",
            'desc_tip'    => true,
            'description' => __('This is the commission the provider will recieve on payout.', 'atarapay-woo'),
            'placeholder' => "0 - 100%"
            ]
        );
    }

    function process_product_meta($post_id)
    {
        $product = wc_get_product($post_id);
        $service_provider = isset($_POST['_at_wc_atara_service_provider']) ? $_POST['_at_wc_atara_service_provider'] : '';
        $service_provider_commission = isset($_POST['_at_wc_atara_service_provider_commission']) ? $_POST['_at_wc_atara_service_provider_commission'] : '';

        $product->update_meta_data('_at_wc_atara_service_provider', sanitize_text_field($service_provider));
        $product->update_meta_data('_at_wc_atara_service_provider_commission', sanitize_text_field($service_provider_commission));
        $product->save();
    }
}
