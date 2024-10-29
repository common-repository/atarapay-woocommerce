<?php

if (!defined('ABSPATH')) {
    exit;
}

class At_WC_Atara_Service_Provider
{
    private $atara_gateway;
    private $atara_service_provider_url;
    private $atara_banks_url;
    private $public_key;
    private $secret_key;

    public function __construct()
    {
        $this->atara_gateway = WC()->payment_gateways->payment_gateways()["at_atara"];

        if ($this->atara_gateway->testmode) {
            $this->atara_service_provider_url =  AT_WC_ATARA_API_SANDBOX_URL . "/api/serviceprovider";
            $this->atara_banks_url =  AT_WC_ATARA_API_SANDBOX_URL . "/api/external/getBanks";
            $this->public_key = $this->atara_gateway->test_public_key;
            $this->secret_key  = $this->atara_gateway->test_secret_key;
        } else {
            $this->atara_service_provider_url =  AT_WC_ATARA_API_LIVE_URL.  "/api/serviceprovider";
            $this->atara_banks_url =  AT_WC_ATARA_API_LIVE_URL ."/api/external/getBanks";
            $this->public_key = $this->atara_gateway->live_public_key;
            $this->secret_key  = $this->atara_gateway->live_secret_key;
        }
    }

    public function get_service_providers()
    {
        $query = http_build_query(
            array(
            'authtoken' => base64_encode($this->public_key . ":" . $this->secret_key),
            )
        );
        $args = array('headers' => array('Content-Type' => 'application/x-www-form-urlencoded'), 'body' => $query);
        $response = wp_remote_post($this->atara_service_provider_url, $args);
        $body = (array) json_decode(wp_remote_retrieve_body($response));

        $data = $body["status"] == "success" ? $body["data"] : [];

        $providersList = array();

        if (count($data) > 0) {
            foreach ($data as $key => $value) {
                $provider = (array) $value;
                array_push($providersList, $provider);
            }
        }

        return $providersList;
    }

    public function save_service_provider()
    {
        if (!current_user_can('administrator')) {
            die(esc_html__('You are not allowed to save changes!', 'atarapay-woo'));
        }

        // Security Check
        if (!wp_verify_nonce($_POST['_nonce'], 'at_wc_atara_service_provider')) {
            die(esc_html__('Security check Failed', 'at-wc-atara'));
        }

        $bank_name = (array) json_decode(stripslashes($_POST['bank_name']));

        $options  =  array(
            'authtoken' => base64_encode($this->public_key . ":" . $this->secret_key),
            'bank_name'  => $bank_name['name'],
            'bank_account_name'  => sanitize_text_field($_POST["bank_account_name"]),
            'bank_account_number'  => sanitize_text_field($_POST["bank_account_number"]),
            'nip_bank_code'  => $bank_name['nip_bank_code'],
            'bank_code'  => $bank_name['code'],
            'email'  => sanitize_text_field($_POST["email"]),
            'phone'  => sanitize_text_field($_POST["phone"]),
            'firstname'  => sanitize_text_field($_POST["firstname"]),
            'lastname'  => sanitize_text_field($_POST["lastname"])
        );


        if ( isset(  $_POST["type"] ) &&  "" !=  $_POST["type"] ) {
            $options['type'] = sanitize_text_field( $_POST["type"] );
        }

        if ( isset(  $_POST["account_id"] ) &&  "" !=  $_POST["account_id"] ) {
            $options['account_id'] = sanitize_text_field( $_POST["account_id"] );
        }

        if ( $_POST["type"] == "2" ) {
            $options['account_type'] = "wise";
        }

        $query = http_build_query(
           $options
        );

        $args = array('headers' => array('Content-Type' => 'application/x-www-form-urlencoded'), 'body' => $query);
        $response = wp_remote_post($this->atara_service_provider_url . "/create", $args);
        $body = (array) json_decode(wp_remote_retrieve_body($response));

        if ($body['status'] == "error") {
            $goback = add_query_arg('error', $body['message'],  wp_get_referer()) . "&" . $query;
        } else {
            $goback = add_query_arg('updated', 'true',  wp_get_referer());
        }

        wp_redirect($goback);
        exit;
    }

    public function set_service_providers_commissions($order)
    {
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $service_provider_id = get_post_meta($product_id, '_at_wc_atara_service_provider', true) ? get_post_meta($product_id, '_at_wc_atara_service_provider', true) : null;
            $provider_commission = get_post_meta($product_id, '_at_wc_atara_service_provider_commission', true) ? get_post_meta($product_id, '_at_wc_atara_service_provider_commission', true) : 0;
            $seller_commission = 100 - $provider_commission;

            if ($service_provider_id &&  $provider_commission) {
                $this->add_order_commission($seller_commission, $service_provider_id, $order->transaction_id);
            }
        }
    }

    public function add_order_commission($seller_commission, $service_provider_id, $order_id)
    {
        $query = http_build_query(
            array(
            'authtoken' => base64_encode($this->public_key . ":" . $this->secret_key),
            'commission' => $seller_commission,
            'sp_id' => $service_provider_id,
            'order_id' => $order_id
            )
        );

        $args = array('headers' => array('Content-Type' => 'application/x-www-form-urlencoded'), 'body' => $query, 'method' => 'PUT');
        $response = wp_remote_request($this->atara_service_provider_url . "/order/update", $args);
        $body = (array) json_decode(wp_remote_retrieve_body($response));
        return $body;
    }

    public function get_banks()
    {
        $query = http_build_query(
            array(
            'authtoken' => base64_encode($this->public_key . ":" . $this->secret_key),
            )
        );
        $args = array('headers' => array('Content-Type' => 'application/x-www-form-urlencoded'), 'body' => $query);
        $response = wp_remote_post($this->atara_banks_url, $args);
        $body = (array) json_decode(wp_remote_retrieve_body($response), true);
        return $body['data'];
    }
}
