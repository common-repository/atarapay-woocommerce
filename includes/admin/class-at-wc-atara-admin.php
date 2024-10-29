<?php

if (!defined('ABSPATH')) {
    exit;
}

class At_WC_Atara_Admin
{
    public function __construct()
    {
        $this->atara_gateway = WC()->payment_gateways->payment_gateways()["at_atara"];

        $this->plugin_name  = $this->atara_gateway->method_title;

        if ($this->atara_gateway->testmode) {
            $this->atara_service_provider_url =  AT_WC_ATARA_API_SANDBOX_URL . "/api/serviceprovider";
            $this->public_key = $this->atara_gateway->test_public_key;
            $this->secret_key  = $this->atara_gateway->test_secret_key;
        } else {
            $this->atara_service_provider_url =  AT_WC_ATARA_API_LIVE_URL . "/api/serviceprovider";
            $this->public_key = $this->atara_gateway->live_public_key;
            $this->secret_key  = $this->atara_gateway->live_secret_key;
        }

        add_action('admin_post_at_wc_atara_save_service_provider', [$this, 'save_service_provider'], 10);
        add_action('admin_menu', array($this, 'create_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_style'));
    }

    public function create_menu()
    {
        $menu_title = 'AtaraPay';
        $capability = 'manage_woocommerce';
        $icon       = AT_WC_ATARA_URL .   "assets/images/icon.png";
        $position   = 100;
        add_menu_page("atarapay",  $menu_title, $capability, "atarapay", array($this, 'plugin_admin_view'), $icon, $position);
    }

    public function plugin_admin_view()
    {
        global $pagenow;

        if (isset($_GET['page']) && $_GET['page'] == "atarapay" && $pagenow == "admin.php") {
            at_wc_atara_load_template("admin/at_wc_atara_settings");
        }
    }

    public function enqueue_style()
    {
        global $pagenow;

        if (isset($_GET['page']) && $_GET['page'] == "atarapay" && $pagenow == "admin.php") {
            $src = AT_WC_ATARA_ASSETS . "css/style.css";
            wp_enqueue_style('at_wc_atara_admin_style',  $src, array(), AT_WC_ATARA_VERSION);
        }
    }

    public function save_service_provider()
    {
        $At_WC_Atara_Service_Provider = new At_WC_Atara_Service_Provider();
        $At_WC_Atara_Service_Provider->save_service_provider();
    }

    public function delete_service_provider()
    {
        $At_WC_Atara_Service_Provider = new At_WC_Atara_Service_Provider();
        $At_WC_Atara_Service_Provider->delete_service_provider();
    }
}
