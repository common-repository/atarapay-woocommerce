<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once dirname(__FILE__) . '/lib/class-at-wc-httpMultipartParser.php';

class  At_WC_Atara_Webhook
{
    public function __construct()
    {
        add_action('woocommerce_api_at_wc_atara_gateway', array($this, 'process_callback_webhook'), 9999 );
    }

    public function process_callback_webhook()
    {
        if ((strtoupper($_SERVER['REQUEST_METHOD']) != 'POST')) {
            exit;
        }

        $request = file_get_contents('php://input');
        error_log( print_r( $request, true ) );
        
        HttpMultipartParser::populate_post_stdin();

        $event = $_POST;
        $data = json_decode($event["data"]);
        error_log( print_r( $event, true ) );

        header('Content-Type: application/json');

       

        $security_token = $this->generate_token($data->id, $data->amount_payed);

        //validate event to avoid timing attack.
        if ($event['token'] !== $security_token) {
            exit;
        }

        do_action('at_wc_atara_order_notification', $event['status'], (int) $data->product_id, $data->status->title);
        // do_action('at_wc_atara_order_notification', "canceled", 38, "Yayyyyy x222");
        exit;
    }

    protected function generate_token($order_id, $amount_paid)
    {
        $atara_gateway = WC()->payment_gateways->payment_gateways()["at_atara"];
        $public_key = $atara_gateway->testmode ? $atara_gateway->test_public_key : $atara_gateway->live_public_key;
        $secret_key  = $atara_gateway->testmode ? $atara_gateway->test_secret_key : $atara_gateway->live_secret_key;

        return  base64_encode($public_key . ":" . $secret_key . ":" . $order_id . ":" .  $amount_paid);
    }
}
