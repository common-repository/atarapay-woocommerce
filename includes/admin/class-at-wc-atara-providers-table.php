<?php

if (! defined('ABSPATH') ) {
    exit; // Exit if accessed directly
}

if (!class_exists('WP_List_Table')) require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

class At_WC_Providers_List_Table extends WP_List_Table
{
    private $At_WC_Atara_Service_Provider = null;

    public function __construct()
    {
        parent::__construct();
        $this->At_WC_Atara_Service_Provider = new At_WC_Atara_Service_Provider();
    }

    function get_columns()
    {
        $columns = array(
        "firstname" => 'First Name',
        "lastname" => 'Last Name',
        "email" => "Email",
        "phone" => "Phone",
        "account_id" => "USD Account ID",
        "type" => "Payout Currency",
        "bank_name" => "Bank",
        "bank_account_number" => "Account Number ",
        "bank_account_name" => "Account Name",
        "bank_code" => "Bank Code",
      //  "debug" => "DEBUG"
        );

        

        return $columns;
    }

    function prepare_items()
    {
        $providersList = $this->At_WC_Atara_Service_Provider->get_service_providers();
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $providersList;
    }


    function column_default($item, $column_name)
    {
        switch ($column_name) {
        case 'email':
        case 'firstname':
        case 'lastname':
        case 'phone':
        case 'bank_code':
        case 'bank_account_number':
        case 'bank_account_name':
        case 'bank_name':
        case 'account_id':
            return $item[$column_name];

        case 'type':
            if ( $item[$column_name] == "1" ) {
                return "NGN";
            } else {
                return "USD";
            }

        default:
            return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }
}
