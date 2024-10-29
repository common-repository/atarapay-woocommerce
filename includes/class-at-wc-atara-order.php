<?php

if (!defined('ABSPATH')) {
    exit;
}
use Automattic\WooCommerce\Utilities\OrderUtil;

class At_WC_Atara_Order
{
    const PENDING_STATUS = "Pending";
    const PENDING_II_STATUS = "Pending II";
    const CANCELLED_STATUS = "Cancelled";
    const AWAITING_BUYER_RESPONSE_STATUS = "Awaiting Buyer Response";
    const ACCEPTED_STATUS = "Accepted";
    const REJECTED_STATUS = "Rejected";
    const DISPUTE_RESOLVED_STATUS = "Dispute Resolved";

    public function __construct()
    {
        $this->resgister_at_wc_atara_order_status();
        add_filter('wc_order_statuses',  array($this, 'add_at_wc_atara_order_statuses'));

        $this->atara_gateway = WC()->payment_gateways->payment_gateways()["at_atara"];

        if ($this->atara_gateway->testmode) {
            $this->atara_order_webhook_url =  AT_WC_ATARA_API_SANDBOX_URL . "/api/callback/order";
            $this->public_key = $this->atara_gateway->test_public_key;
            $this->secret_key  = $this->atara_gateway->test_secret_key;
        } else {
            $this->atara_order_webhook_url =  AT_WC_ATARA_API_LIVE_URL . "/api/callback/order";
            $this->public_key = $this->atara_gateway->live_public_key;
            $this->secret_key  = $this->atara_gateway->live_secret_key;
        }

        add_filter('manage_edit-shop_order_columns', array($this, 'add_shop_order_columns'), 20);
       
        add_action('manage_shop_order_posts_custom_column', array($this, 'populate_shop_order_column'), 20, 2);
        add_filter('woocommerce_my_account_my_orders_columns', array($this, 'add_my_account_orders_columns'));
        add_action('woocommerce_my_account_my_orders_column_atarapay-status', array($this, 'add_my_account_orders_atarapay_status'));
        add_action('woocommerce_my_account_my_orders_column_atarapay-id', array($this, 'add_my_account_orders_atarapay_id'));
        add_filter("manage_edit-shop_order_sortable_columns", array($this, 'sort_shop_order_columns'));
        add_action('at_wc_atara_order_notification', array($this, "change_order_status_on_notification"), 10, 3);
        add_filter('woocommerce_before_order_object_save',  array($this, "change_atara_order_status"), 10, 1);


        add_filter('woocommerce_shop_order_list_table_columns', array($this, 'add_shop_order_columns'), 20);
        add_action('manage_woocommerce_page_wc-orders_custom_column', array($this, 'populate_shop_order_column'), 20, 2);

        // 'manage_' . wc_get_page_screen_id( $this->order_type ) . '_custom_column'
        // woocommerce_woocommerce_page_wc-orders_list_table_columns
    }

    public function add_shop_order_columns($columns)
    {
        $new_columns = array();
        foreach ($columns as $key => $name) {
            $new_columns[$key] = $name;
            if ('order_status' === $key) {
                $new_columns['atarapay_status'] = 'AtaraPay Status';
                $new_columns['atarapay_id'] = 'AtaraPay ID#';
            }
        }
       
        return $new_columns;
    }

    public function populate_shop_order_column($column, $order_id)
    {
        switch ($column) {
        case 'atarapay_status':
            $value = get_post_meta($order_id, '_atara_status', true);
            break;
        case 'atarapay_id':
            $value = get_post_meta($order_id, '_transaction_id', true);
            break;
        default:
            return;
        }

        echo empty($value) ? "-" : $value;
    }

    public function add_my_account_orders_columns($columns)
    {
        $new_columns = array();
        foreach ($columns as $key => $name) {
            $new_columns[$key] = $name;
            if ('order-status' === $key) {
                $new_columns['atarapay-status'] = 'AtaraPay Status';
                $new_columns['atarapay-id'] = 'AtaraPay ID#';
            }
        }

        return $new_columns;
    }

    public function add_my_account_orders_atarapay_status($order)
    {
        $order_id   = method_exists($order, 'get_id') ? $order->get_id() : $order->id;
        $status = get_post_meta($order_id, '_atara_status', true);
        echo empty($status) ? '-' : $status;
    }

    public function add_my_account_orders_atarapay_id($order)
    {
        $order_id   = method_exists($order, 'get_id') ? $order->get_id() : $order->id;
        $id = get_post_meta($order_id, '_transaction_id', true);
        echo empty($id) ? '-' : $id;
    }

    public function sort_shop_order_columns($columns)
    {
        $custom = array(
        'atarapay_id'    => 'id',
        );

        return wp_parse_args($custom, $columns);
    }

    public function resgister_at_wc_atara_order_status()
    {
        register_post_status(
            'wc-delivered', array(
            'label'                     => 'Delivered',
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            )
        );
    }

    public function add_at_wc_atara_order_statuses($order_statuses)
    {
        $new_order_statuses = array();

        // add new order status after processing
        foreach ($order_statuses as $key => $status) {

            $new_order_statuses[$key] = $status;

            if ('wc-processing' === $key) {
                $new_order_statuses['wc-delivered'] = 'Delivered';
            }
        }

        return $new_order_statuses;
    }

    public function change_order_status_on_notification($topic, $order_id, $note)
    {
        $order = wc_get_order($order_id);

        if (empty($order)) {
            return false;
        }

        update_post_meta($order_id, '_atara_change_order_status_by', "change_order_status_on_notification");

        switch ($topic) {
        case 'Delivered':
            $order->update_meta_data('_atara_status', self::AWAITING_BUYER_RESPONSE_STATUS);
            $order->update_status("delivered");
            break;
        case 'Accepted':
            $order->update_meta_data('_atara_status', self::ACCEPTED_STATUS);
            $order->update_status("completed");
            break;
        case 'Rejected':
            $order->update_meta_data('_atara_status', self::REJECTED_STATUS);
            $order->update_status("processing");
            break;
        case 'Refunded':
            $order->update_meta_data('_atara_status', self::REJECTED_STATUS);
            $order->update_status("completed");
            break;
        case "Canceled":
            $order->update_meta_data('_atara_status', self::CANCELLED_STATUS);
            $order->update_status("cancelled");
            break;
        case 'Complete':
            $order->update_meta_data('_atara_status', self::DISPUTE_RESOLVED_STATUS);
            $order->update_status("completed");
            break;
        case 'Pending II':
            $order->update_meta_data('_atara_status', self::PENDING_II_STATUS);
            $order->update_status("processing");
            break;
        case 'Dispute':
            $order->update_meta_data('_atara_status', "Dispute");
            $order->update_status("processing");
            break;
        case 'Disputed': 
            $order->update_meta_data('_atara_status', "Disputed");
            $order->update_status("processing");
            break;

        default:
            delete_post_meta($order_id, "_atara_change_order_status_by");
            return false;
        }

        $order->add_order_note($note, 1);
        return;
    }

    public function change_atara_order_status($order)
    {
        if ("change_order_status_on_notification" === get_post_meta($order->get_id(), '_atara_change_order_status_by', true)) {
            delete_post_meta($order->get_id(), "_atara_change_order_status_by");
            return $order;
        }

        $changes = $order->get_changes();

        if (!empty($changes) && isset($changes['status'])) {
            $authtoken = base64_encode($this->public_key . ":" . $this->secret_key);

            switch ($changes['status']) {
            case 'delivered':
                $params = array();
                $params['authtoken'] = $authtoken;
                $params['status'] = $changes['status'];
                $params['order'] =  $order->transaction_id;
                $params['email'] = $order->data['billing']['email'];
                $params['phone_number'] = $order->data['billing']['phone'];
                $query = http_build_query($params);

                $args = array('headers' => array('Content-Type' => 'application/x-www-form-urlencoded'), 'body' => $query);
                $response = wp_remote_post($this->atara_order_webhook_url, $args);
                $body = (array) json_decode(wp_remote_retrieve_body($response), true);

                if ($body["status"] == "error") {
                    add_action(
                        'woocommerce_order_status_' . $changes['status'],  function () {
                            throw new Exception();
                        }
                    );

                    throw new Exception(sprintf(__($body["message"], "woocommerce")));
                }

                break;

            // action when seller cancels from woocommerce dashboard
            case "cancelled":
                $params = array();
                $params['authtoken'] = $authtoken;
                $params['order'] = $order->transaction_id;
                $params['status'] = 'cancelled';
                $params['comment'] = 'No comment';
                $query = http_build_query($params);

                $args = array('headers' => array('Content-Type' => 'application/x-www-form-urlencoded'), 'body' => $query);
                $response = wp_remote_post($this->atara_order_webhook_url, $args);
                $body = (array) json_decode(wp_remote_retrieve_body($response), true);
                

                break;
            default:
                return $order;
            }
        }

        return $order;
    }
}



function at_get_page_screen_id( $for ) {
	$screen_id = '';
	$for       = str_replace( '-', '_', $for );

	if ( in_array( $for, wc_get_order_types( 'admin-menu' ), true ) ) {
		if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
			$screen_id = 'woocommerce_page_wc-orders' . ( 'shop_order' === $for ? '' : '--' . $for );
		} else {
			$screen_id = $for;
		}
	}

	return $screen_id;
}
