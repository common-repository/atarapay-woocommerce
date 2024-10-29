<?php

if (! defined('ABSPATH') ) {
    exit;
}

class At_WC_Atara_Gateway extends WC_Payment_Gateway
{

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->id                 = 'at_atara';
        $this->method_title       = 'Third-Party Trust Payment';
        $this->method_description = sprintf('AtaraPay lets you recieve payments via your website, mobile app or marketplace easily and securely. Learn more about atarapay <a href="%1$s" target="_blank">here</a>.', 'https://plugins.atarapay.com');

        $this->has_fields             = true;

        $this->supports               = array(
        'products',
        'tokenization',
        'subscriptions',
        'multiple_subscriptions',
        'subscription_cancellation',
        'subscription_suspension',
        'subscription_reactivation',
        'subscription_amount_changes',
        'subscription_date_changes',
        'subscription_payment_method_change',
        'subscription_payment_method_change_customer'
        );

        // Load the form fields
        $this->init_form_fields();

        // Load the settings
        $this->init_settings();

        // Get setting values
        $this->title                = $this->get_option('title');
        $this->description          = $this->get_option('description');
        $this->enabled              = $this->get_option('enabled');
        $this->testmode             = $this->get_option('testmode') === 'yes' ? true : false;
        $this->is_marketplace       = $this->get_option('is_marketplace') === 'yes' ? 1 : 0;

        $this->test_public_key      = $this->get_option('test_public_key');
        $this->test_secret_key      = $this->get_option('test_secret_key');

        $this->live_public_key      = $this->get_option('live_public_key');
        $this->live_secret_key      = $this->get_option('live_secret_key');

        $this->public_key           = $this->testmode ? $this->test_public_key : $this->live_public_key;
        $this->secret_key           = $this->testmode ? $this->test_secret_key : $this->live_secret_key;

        $this->test_query_url       = AT_WC_ATARA_API_SANDBOX_URL . '/flwv3-pug/getpaidx/api/verify';

        $this->live_query_url       = AT_WC_ATARA_API_LIVE_URL . '/getpaidx/api/verify';

        $this->query_url            = $this->testmode ? $this->test_query_url : $this->live_query_url;

        $this->test_tokenized_url   = AT_WC_ATARA_API_SANDBOX_URL . '/api/tokenized/charge';

        $this->live_tokenized_url   = AT_WC_ATARA_API_LIVE_URL . '/api/tokenized/charge';

        $this->tokenized_url        = $this->testmode ? $this->test_tokenized_url : $this->live_tokenized_url;

        // Hooks
        add_action('wp_enqueue_scripts', array( $this, 'payment_scripts' ));
        add_action('admin_enqueue_scripts', array( $this, 'admin_scripts' ));

        add_action('admin_notices', array( $this, 'admin_notices' ));
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ));

        add_action('woocommerce_admin_order_totals_after_total', array( $this, 'display_atara_fee' ));
        add_action('woocommerce_admin_order_totals_after_total', array( $this, 'display_order_payout' ), 20);

        add_action('woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ));

        // Payment listener/API hook
        add_action('woocommerce_api_at_wc_atara_gateway', array( $this, 'verify_atara_transaction' ));

        // Check if the gateway can be used
        if (! $this->is_valid_for_use() ) {
            $this->enabled = false;
        }

    }


    /**
     * Check if this gateway is enabled and available in the user's country.
     */
    public function is_valid_for_use()
    {

        return true;

    }


    /**
     * Display the payment icon on the checkout page
     */
    public function get_icon()
    {

        $icon  = '<img src="' . WC_HTTPS::force_https_url(plugins_url('assets/images/atarapay.png', AT_WC_ATARA_MAIN_FILE)) . '" alt="atarapay" />';

        return apply_filters('woocommerce_gateway_icon', $icon, $this->id);

    }


    /**
     * Check if Atarapay seller details is filled
     */
    public function admin_notices()
    {

        if ($this->enabled == 'no' ) {
            return;
        }

        // Check required fields
        if (! ( $this->public_key ) ) {
            echo '<div class="error"><p>' . sprintf('Please enter your AtaraPay API key <a href="%s">here</a> to be able to use the AtaraPay WooCommerce plugin.', admin_url('admin.php?page=wc-settings&tab=checkout&section=at_atara')) . '</p></div>';
            return;
        }

    }


    /**
     * Check if this gateway is enabled
     */
    public function is_available()
    {

        if ($this->enabled == "yes" ) {

            if (! ( $this->public_key ) ) {

                return false;

            }

            return true;

        }

        return false;

    }


    /**
     * Admin Panel Options
     */
    public function admin_options()
    {

        ?>

        <h3>AtaraPay</h3>

        <h4>Note: To generate test api keys visit the atarapay staging platform <a href="http://staging.atarapay.com" target="_blank" rel="noopener noreferrer">here</a>. For live api keys visit the atarapay live platform <a href="https://app.atarapay.com" target="_blank" rel="noopener noreferrer">here</a> </h4>

        <?php
    
        if ($this->is_valid_for_use() ) {

            echo '<table class="form-table">';
            $this->generate_settings_html();
            echo '</table>';

        }
        else {     ?>
            <div class="inline error"><p><strong>Atarapay Payment Plugin Disabled</strong>: <?php echo $this->msg ?></p></div>

        <?php }

    }


    /**
     * Initialise Gateway Settings Form Fields
     */
    public function init_form_fields()
    {

        $this->form_fields = array(
        'enabled'               => array(
        'title'       => 'Enable/Disable',
        'label'       => 'Enable AtaraPay',
        'type'        => 'checkbox',
        'description' => 'Enable Atarapay as a payment option on the checkout page.',
        'default'     => 'no',
        'desc_tip'    => true,
        ),
        'testmode'              => array(
        'title'       => 'Test mode',
        'label'       => 'Enable Test Mode',
        'type'        => 'checkbox',
        'description' => 'Test mode enables you to test payments before going live. <br />Once you are live uncheck this.',
        'default'     => 'yes',
        'desc_tip'    => true,
        ),
        'test_public_key'       => array(
        'title'       => 'Test Public Key',
        'type'        => 'text',
        'description' => 'Required: Enter your Test Public Key here.',
        'default'     => '',
        'desc_tip'    => true,
        ),
        'live_public_key'       => array(
        'title'       => 'Live Public Key',
        'type'        => 'text',
        'description' => 'Required: Enter your Live Public Key here.',
        'default'     => '',
        'desc_tip'    => true,
        ),
        'is_marketplace'              => array(
        'title'       => 'MarketPlace',
        'label'       => 'Enable MarketPlace Mode',
        'type'        => 'checkbox',
        'description' => 'This control enables you to accept payments for multiple AtaraPay sellers,<br />if your store is a marketplace enable this option.',
        'default'     => 'yes',
        'desc_tip'    => true,
        ),
        );

    }


    /**
     * Payment form on checkout page
     */
    public function payment_fields()
    {

        if ($this->description ) {
            echo wpautop(wptexturize($this->description));
        }

        if (! is_ssl() ) {
            return;
        }

        if ($this->supports('tokenization') && is_checkout() && $this->saved_cards && is_user_logged_in() ) {
            $this->tokenization_script();
            $this->saved_payment_methods();
            $this->save_payment_method_checkbox();
        }

    }


    /**
     * Outputs scripts used by AtaraPay
     */
    public function payment_scripts()
    {

        if (! is_checkout_pay_page() ) {
            return;
        }

        if ($this->enabled === 'no' ) {
            return;
        }

        $order_key         = urldecode($_GET['key']);
        $order_id          = absint(get_query_var('order-pay'));

        $order          = wc_get_order($order_id);

        $payment_method = method_exists($order, 'get_payment_method') ? $order->get_payment_method() : $order->payment_method;

        if ($this->id !== $payment_method ) {
            return;
        }

        $suffix = ( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) ? '' : '.min';
    
        wp_enqueue_script('jquery');
        wp_enqueue_style('atarapay-trustpay-style', 'https://plugins.atarapay.com/assets/trustpay.inline.min.css');
        
        if ($this->testmode ) {
            
            wp_enqueue_script('at_atara', AT_WC_ATARA_ASSETS_ORIGIN . '/trustpay.inline.min.js', array( 'jquery' ), AT_WC_ATARA_VERSION, false);

        } else {

            wp_enqueue_script('at_atara', 'https://plugins.atarapay.com/assets/trustpay.inline.min.js', array( 'jquery' ), AT_WC_ATARA_VERSION, false);

        }

        wp_enqueue_script('at_wc_atara', plugins_url('assets/js/atara.js', AT_WC_ATARA_MAIN_FILE), array( 'jquery', 'at_atara' ), AT_WC_ATARA_VERSION, false);

        $atara_params = array(
        'public_key'    => $this->public_key,
        );

        if (is_checkout_pay_page() && get_query_var('order-pay') ) {

            $email          = method_exists($order, 'get_billing_email') ? $order->get_billing_email() : $order->billing_email;
            $billing_phone  = method_exists($order, 'get_billing_phone') ? $order->get_billing_phone() : $order->billing_phone;
            $first_name      = method_exists($order, 'get_billing_first_name') ? $order->get_billing_first_name() : $order->billing_first_name;
            $last_name      = method_exists($order, 'get_billing_last_name') ? $order->get_billing_last_name() : $order->billing_last_name;
            
            $address      = method_exists($order, 'get_billing_address_1') ? $order->get_shipping_address_1() : $order->get_shipping_address_1;
            
            $address2      = method_exists($order, 'get_shipping_address_2') ? $order->get_shipping_address_2() : $order->get_shipping_address_2;
            
            $city      = method_exists($order, 'get_shipping_city') ? $order->get_shipping_city() : $order->get_shipping_city;
            
            $state      = method_exists($order, 'get_shipping_state') ? $order->get_shipping_city() : $order->get_shipping_state;
            
            $country      = method_exists($order, 'get_shipping_country') ? $order->get_shipping_country() : $order->get_shipping_country;
            
            $postcode      = method_exists($order, 'get_shipping_postcode') ? $order->get_shipping_postcode() : $order->get_shipping_postcode;

            $amount         = $order->get_total();

            $txnref             = 'WC|' . $order_id . '|' .time();

            $the_order_id   = method_exists($order, 'get_id') ? $order->get_id() : $order->id;
            $the_order_key  = method_exists($order, 'get_order_key') ? $order->get_order_key() : $order->order_key;

            $base_location  = wc_get_base_location();
            $country        = $base_location['country'];
            
            $address = $address." ".$address2." ".$city." ".$state." ".$country." ".$postcode;

            $payment_methods = $this->payment_methods;

            if (empty($payment_methods) ) {
                $payment_methods = '';
            } else {
                $payment_methods = implode(',', $payment_methods);
            }
            
            // $item_name = $order->get_data();
            foreach( $order->get_items() as $item_id => $item ){
                $product = $item->get_product();
                $item_name[] = $product->get_name();
                $item_desc[] = $product->get_short_description();
                $item_names = implode(", ", $item_name);
                $item_descs = implode(", ", $item_desc);
            }
            
            
            $date = $order->get_date_created()->format('Y-m-d h:i:s');
            

            $meta = array();

            if ($the_order_id == $order_id && $the_order_key == $order_key ) {

                $meta[] = array(
                'metaname'  => 'Order ID',
                'metavalue' => $order_id,
                );

                $atara_params['txref']               = $txnref;
                $atara_params['orderId']             = $order_id;
                $atara_params['payment_options']     = $payment_methods;
                $atara_params['amount']              = get_woocommerce_currency() !== "NGN" ? $this->convert_currency(get_woocommerce_currency(), 'NGN', $amount) : $amount;
                $atara_params['amount_fx']           = $amount;
                $atara_params['currency']            = get_woocommerce_currency();
                $atara_params['customer_email']      = $email;
                $atara_params['customer_phone']      = $billing_phone;
                $atara_params['customer_first_name'] = $first_name;
                $atara_params['customer_last_name']  = $last_name;
                $atara_params['marketplace']        = $this->is_marketplace;
                $atara_params['address']             = $address;
                $atara_params['country']             = $country;
                $atara_params['meta']                = $meta;
                $atara_params['items']               = $item_names;
                $atara_params['items_desc']          = $item_descs;
                $atara_params['date']                = $date;
                $atara_params['hash']                = $this->generate_hash($atara_params);
                $atara_params['url']                = get_site_url();

                update_post_meta($order_id, '_atara_txn_ref', $txnref);

            }

        }

        wp_localize_script('at_wc_atara', 'at_wc_atara_params', $atara_params);

    }


    /**
     * Generate integrity hash
     */
    public function generate_hash( $params )
    {

        $hashedPayload = $params['public_key'];

        unset($params['public_key']);
        unset($params['meta']);

        ksort($params);

        foreach ( $params as $key => $value ) {
            $hashedPayload .= $value;
        }

        $hashedPayload .= $this->secret_key;

        $hashedPayload = html_entity_decode($hashedPayload);

        $hash = hash('sha256', $hashedPayload);

        return $hash;
    }
    
    /**
     * Get currency rate
     */
     
    public function convert_currency($local,$currency,$amount)
    {
        $request = wp_remote_get('https://apilayer.net/api/convert?from='.$local.'&to='.$currency.'&amount='.$amount.'&access_key=4e5adddfc2ade43b82911df9267eab0a');
        if(is_wp_error($request) ) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($request);
        $data = json_decode($body);
        
        if(! empty($data) ) {
            return $data->result;
        }
    
    }

    /**
     * Load admin scripts
     */
    public function admin_scripts()
    {

        if ('woocommerce_page_wc-settings' !== get_current_screen()->id ) {
            return;
        }

        $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        wp_enqueue_script('at_wc_atara_admin', plugins_url('assets/js/atarapay-wp' . $suffix . '.js', AT_WC_ATARA_MAIN_FILE), array(), AT_WC_ATARA_VERSION, true);

    }


    /**
     * Displays the Escrow fee
     *
     * @since 2.1.0
     *
     * @param int $order_id
     */
    public function display_atara_fee( $order_id )
    {

        $order = wc_get_order($order_id);

        if ($this->is_wc_lt('3.0') ) {
            $fee      = get_post_meta($order_id, '_atara_fee', true);
            $currency = get_post_meta($order_id, '_atara_currency', true);
        } else {
            $fee      = $order->get_meta('_atara_fee', true);
            $currency = $order->get_meta('_atara_currency', true);
        }

        if (! $fee || ! $currency ) {
            return;
        }

        ?>

        <tr>
            <td class="label atara-fee">
        <?php echo wc_help_tip('This represents the fee Atara collects for the transaction.'); ?>
        <?php esc_html_e('Atara Fee:'); ?>
            </td>
            <td width="1%"></td>
            <td class="total">
                -&nbsp;<?php echo wc_price($fee, array( 'currency' => $currency )); ?>
            </td>
        </tr>

        <?php
    }


    /**
     * Displays the net total of the transaction without the charges of AtaraPay.
     *
     * @since 2.1.0
     *
     * @param int $order_id
     */
    public function display_order_payout( $order_id )
    {

        $order = wc_get_order($order_id);

        if ($this->is_wc_lt('3.0') ) {
            $net      = get_post_meta($order_id, '_atara_net', true);
            $currency = get_post_meta($order_id, '_atara_currency', true);
        } else {
            $net      = $order->get_meta('_atara_net', true);
            $currency = $order->get_meta('_atara_currency', true);
        }

        if (! $net || ! $currency ) {
            return;
        }

        ?>

        <tr>
            <td class="label atara-payout">
        <?php $message = 'This represents the net total that will be credited to your bank account for this order.'; ?>
        <?php if ($net >= $order->get_total() ) : ?>
            <?php $message .= ' AtaraPay transaction fees was passed to the customer.'; ?>
        <?php endif;?>
        <?php echo wc_help_tip($message); ?>
        <?php esc_html_e('AtaraPay Payout:'); ?>
            </td>
            <td width="1%"></td>
            <td class="total">
        <?php echo wc_price($net, array( 'currency' => $currency )); ?>
            </td>
        </tr>

        <?php
    }

    /**
     * Process the payment
     */
    public function process_payment( $order_id )
    {

        if (isset($_POST['wc-at_atara-payment-token']) && 'new' !== $_POST['wc-at_atara-payment-token'] ) {

            $token_id = wc_clean($_POST['wc-at_atara-payment-token']);
            $token    = WC_Payment_Tokens::get($token_id);

            if ($token->get_user_id() !== get_current_user_id() ) {

                wc_add_notice('Invalid token ID', 'error');

                return;

            } else {

                $status = $this->process_token_payment($token->get_token(), $order_id);

                if($status ) {

                    $order = wc_get_order($order_id);

                    return array(
                     'result'   => 'success',
                     'redirect' => $this->get_return_url($order)
                    );

                }

            }
        } else {

            if (is_user_logged_in() && isset($_POST[ 'wc-at_atara-new-payment-method' ]) && true === (bool) $_POST[ 'wc-at_atara-new-payment-method' ] && $this->saved_cards ) {

                update_post_meta($order_id, '_wc_atara_save_card', true);

            }

            $order = wc_get_order($order_id);

            return array(
            'result'   => 'success',
            'redirect' => $order->get_checkout_payment_url(true)
            );

        }

    }

    /**
     * Show new card can only be added when placing an order notice
     */
    public function add_payment_method()
    {

        wc_add_notice('You can only add a new card when placing an order.', 'error');

        return;

    }

    /**
     * Displays the payment page
     */
    public function receipt_page( $order_id )
    {

        $order = wc_get_order($order_id);

        echo '<p>Thank you for your order, please click the button below to pay with AtaraPay.</p>';

        echo '<div id="at_wc_atara_form"><form id="order_review" method="post" action="'. WC()->api_request_url('At_WC_Atara_Gateway') .'"></form><button class="button alt" id="payWithAtaraPay" onclick="payWithAtaraPay()">Pay Now</button> <a class="button cancel" href="' . esc_url($order->get_cancel_order_url()) . '">Cancel order &amp; restore cart</a></div>
			';

    }


    /**
     * Verify AtaraPay payment
     */
    public function verify_atara_transaction()
    {
        
        
        if (isset($_REQUEST['product']) ) {
            $order = wc_get_order(sanitize_text_field($_REQUEST['product']));
            $order->payment_complete(sanitize_text_field($_REQUEST['order']));
            $order->add_order_note(sprintf('Payment via AtaraPay successful (<strong>Order ID:</strong> %s)', sanitize_text_field($_REQUEST['order'])));
            
            wc_reduce_stock_levels($order_id);
            
            wp_redirect($this->get_return_url($order));
            exit;
        }
        

    }


    /**
     * Process Webhook
     */
    public function process_webhooks()
    {

        if (( strtoupper($_SERVER['REQUEST_METHOD']) != 'POST' ) ) {
            exit;
        }

        sleep(10);
        
        $body = @file_get_contents("php://input");

        if ($this->isJSON($body) ) {
            $_POST = (array) json_decode($body);
        }

        if (! isset($_POST['flwRef']) ) {
            exit;
        }

        $atara_verify_url = $this->query_url;

        $headers = array(
        'Content-Type' => 'application/json'
        );

        $body = array(
        'flw_ref'     => sanitize_key($_POST['flwRef']),
        'SECKEY'     => $this->secret_key,
        'normalize' => '1'
        );

        $args = array(
        'headers'    => $headers,
        'body'        => json_encode($body),
        'timeout'    => 60
        );

        $request = wp_remote_post($atara_verify_url, $args);

        if (! is_wp_error($request) && 200 == wp_remote_retrieve_response_code($request) ) {

            $response               = json_decode(wp_remote_retrieve_body($request));

            $status                  = $response->status;

            $response_code             = $response->data->flwMeta->chargeResponse;

            $payment_currency       = $response->data->transaction_currency;

            $gateway_symbol         = get_woocommerce_currency_symbol($payment_currency);

            $valid_response_code    = array( '0', '00');

            if ('success' === $status && in_array($response_code, $valid_response_code) ) {

                $order_details     = explode('|', $response->data->tx_ref);

                $order_id         = (int) $order_details[1];

                $order             = wc_get_order($order_id);

                $atara_txn_ref     = get_post_meta($order_id, '_atara_txn_ref', true);

                if ($response->data->tx_ref != $atara_txn_ref ) {
                    exit;
                }

                http_response_code(200);

                if (in_array($order->get_status(), array( 'processing', 'completed', 'on-hold' )) ) {
                       exit;
                }

                $order_currency = $order->get_currency();

                $currency_symbol= get_woocommerce_currency_symbol($order_currency);

                $order_total    = $order->get_total();

                $amount_paid    = $response->data->amount;

                $txn_ref         = $response->data->tx_ref;
                $payment_ref     = $response->data->flw_ref;

                $amount_charged = $response->data->charged_amount;

                $atara_fee       = $response->data->appfee;
                $atara_net       = $amount_charged - $atara_fee;

                if ($this->is_wc_lt('3.0') ) {
                    update_post_meta($order_id, '_atara_fee', $atara_fee);
                    update_post_meta($order_id, '_atara_net', $atara_net);
                    update_post_meta($order_id, '_atara_currency', $payment_currency);
                } else {
                    $order->update_meta_data('_atara_fee', $atara_fee);
                    $order->update_meta_data('_atara_net', $atara_net);
                    $order->update_meta_data('_atara_currency', $payment_currency);
                }

                // check if the amount paid is equal to the order amount.
                if ($amount_paid < $order_total ) {

                       $order->update_status('on-hold', '');

                       update_post_meta($order_id, '_transaction_id', $txn_ref);

                       $notice = 'Thank you for shopping with us.<br />Your payment was successful, but the amount paid is not the same as the total order amount.<br />Your order is currently on-hold.<br />Kindly contact us for more information regarding your order and payment status.';

                       // Add Customer Order Note
                     $order->add_order_note($notice, 1);

                               // Add Admin Order Note
                               $order->add_order_note('<strong>Look into this order</strong><br />This order is currently on hold.<br />Reason: Amount paid is less than the total order amount.<br />Amount Paid was <strong>'. $currency_symbol . $amount_paid . '</strong> while the total order amount is <strong>'. $currency_symbol . $order_total . '</strong><br /><strong>Transaction Reference:</strong> ' . $txn_ref . ' | <strong>Payment Reference:</strong> ' . $payment_ref);

                       wc_reduce_stock_levels($order_id);

                } else {

                    if($payment_currency !== $order_currency ) {

                        $order->update_status('on-hold', '');

                        update_post_meta($order_id, '_transaction_id', $txn_ref);

                        $notice = 'Thank you for shopping with us.<br />Your payment was successful, but the payment currency is different from the order currency.<br />Your order is currently on-hold.<br />Kindly contact us for more information regarding your order and payment status.';

                        // Add Customer Order Note
                        $order->add_order_note($notice, 1);

                        // Add Admin Order Note
                        $order->add_order_note('<strong>Look into this order</strong><br />This order is currently on hold.<br />Reason: Order currency is different from the payment currency.<br /> Order Currency is <strong>'. $order_currency . ' ('. $currency_symbol . ')</strong> while the payment currency is <strong>'. $payment_currency . ' ('. $gateway_symbol . ')</strong><br /><strong>Transaction Reference:</strong> ' . $txn_ref . ' | <strong>Payment Reference:</strong> ' . $payment_ref);

                        wc_reduce_stock_levels($order_id);

                    } else {

                               $order->payment_complete($txn_ref);

                               $order->add_order_note(sprintf('Payment via AtaraPay successful (<strong>Transaction Reference:</strong> %s | <strong>Payment Reference:</strong> %s)', $txn_ref, $payment_ref));

                    }

                }

                $this->save_card_details($response, $order->get_user_id(), $order_id);

                wc_empty_cart();

            } else {

                      $order_details     = explode('|', $response->data->tx_ref);

                      $order_id         = (int) $order_details[1];

                   $order             = wc_get_order($order_id);

                      $order->update_status('failed', 'Payment was declined by AtaraPay.');

            }

        }

        exit;
    }


    /**
     * Save Customer Card Details
     */
    public function save_card_details( $atara_response, $user_id, $order_id )
    {

        if (isset($atara_response->data->card->card_tokens[0]->embedtoken) ) {
            $token_code = $atara_response->data->card->card_tokens[0]->embedtoken;
        } else {
            $token_code = '';
        }

        $this->save_subscription_payment_token($order_id, $token_code);

        $save_card = get_post_meta($order_id, '_wc_atara_save_card', true);

        if (isset($atara_response->data->card) && $user_id && $this->saved_cards && $save_card && ! empty($token_code) ) {

            $last4 = $atara_response->data->card->last4digits;

            if (4 !== strlen($atara_response->data->card->expiryyear) ) {
                $exp_year     = substr(date('Y'), 0, 2) . $atara_response->data->card->expiryyear;
            } else {
                $exp_year     = $atara_response->data->card->expiryyear;
            }

            $brand         = $atara_response->data->card->brand;
            $exp_month     = $atara_response->data->card->expirymonth;

            $token = new WC_Payment_Token_CC();
            $token->set_token($token_code);
            $token->set_gateway_id('at_atara');
            $token->set_card_type($brand);
            $token->set_last4($last4);
            $token->set_expiry_month($exp_month);
            $token->set_expiry_year($exp_year);
            $token->set_user_id($user_id);
            $token->save();

        }

        delete_post_meta($order_id, '_wc_atara_save_card');

    }


    /**
     * Save payment token to the order for automatic renewal for further subscription payment
     */
    public function save_subscription_payment_token( $order_id, $payment_token )
    {

        if (! function_exists('wcs_order_contains_subscription') ) {

            return;

        }

        if ($this->order_contains_subscription($order_id) && ! empty($payment_token) ) {

            // Also store it on the subscriptions being purchased or paid for in the order
            if (function_exists('wcs_order_contains_subscription') && wcs_order_contains_subscription($order_id) ) {

                $subscriptions = wcs_get_subscriptions_for_order($order_id);

            } elseif (function_exists('wcs_order_contains_renewal') && wcs_order_contains_renewal($order_id) ) {

                $subscriptions = wcs_get_subscriptions_for_renewal_order($order_id);

            } else {

                $subscriptions = array();

            }

            foreach ( $subscriptions as $subscription ) {

                $subscription_id = $subscription->get_id();

                update_post_meta($subscription_id, '_at_atara_wc_token', $payment_token);

            }

        }

    }

    public function isJSON( $string )
    {
        return is_string($string) && is_array(json_decode($string, true)) ? true : false;
    }

    /**
     * Checks if WC version is less than passed in version.
     *
     * @since  2.1.0
     * @param  string $version Version to check against.
     * @return bool
     */
    public function is_wc_lt( $version )
    {
        return version_compare(WC_VERSION, $version, '<');
    }

}