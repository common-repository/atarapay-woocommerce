<?php
/*
	Plugin Name:            AtaraPay WooCommerce Payment Gateway
	Plugin URI:             https://plugins.atarapay.com/docs/woocommerce/
	Description:            WooCommerce payment plugin for AtaraPay
	Version:                2.0.13
	Author:                 AtaraPay
	Author URI:             https://atarapay.com
	Text Domain:            atarapay-woo
	License:                GPL-2.0+
	License URI:            http://www.gnu.org/licenses/gpl-2.0.txt
	WC requires at least:   4.6.0
	WC tested up to:        8.5.0
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AT_WC_ATARA_MAIN_FILE', __FILE__ );
define( 'AT_WC_ATARA_URL', plugins_url( '/', AT_WC_ATARA_MAIN_FILE ) );
define( 'AT_WC_ATARA_PATH', plugin_dir_path( __FILE__ ) );
define( 'AT_WC_ATARA_TEMPLATES', AT_WC_ATARA_PATH . 'templates/' );
define( 'AT_WC_ATARA_ASSETS', AT_WC_ATARA_URL . 'assets/' );
define( 'AT_WC_ATARA_VERSION', '2.0.13' );

add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

define( 'AT_WC_ATARA_API_LIVE_URL', 'https://api.atarapay.com' );
if ( defined( 'AT_WC_ATARA_USE_INTERNAL_API' ) && AT_WC_ATARA_USE_INTERNAL_API ) {
    define( 'AT_WC_ATARA_API_SANDBOX_URL', 'https://integration.api.atarapay.com' );
    define( 'AT_WC_ATARA_ASSETS_ORIGIN', 'https://plugins.atarapay.com/assets/int' );
} else {
    define( 'AT_WC_ATARA_API_SANDBOX_URL', 'https://test-api.atarapay.com' );
    define( 'AT_WC_ATARA_ASSETS_ORIGIN', 'https://plugins.atarapay.com/assets/test' );
}





function at_wc_atara_init() {
	include_once dirname( __FILE__ ) . '/includes/class-at-wc-atara-gateway.php';
	include_once dirname( __FILE__ ) . '/includes/class-at-wc-atara-order.php';
	include_once dirname( __FILE__ ) . '/includes/class-at-wc-atara-webhook.php';
	include_once dirname( __FILE__ ) . '/includes/class-at-wc-atara-service-provider.php';
	include_once dirname( __FILE__ ) . '/includes/class-at-wc-atara-product-data.php';

	include_once dirname( __FILE__ ) . '/includes/admin/class-at-wc-atara-admin.php';
	include_once dirname( __FILE__ ) . '/includes/utilities.php';
	include_once dirname( __FILE__ ) . '/includes/admin/notices.php';
	include_once dirname( __FILE__ ) . '/includes/polyfill.php';

	add_filter( 'woocommerce_payment_gateways', 'at_wc_add_atara_gateway' );

	new At_WC_Atara_Admin();
	new At_WC_Atara_Order();
	new At_WC_Atara_Webhook();
	new At_WC_Atara_Product_Data();
}

function at_wc_add_atara_gateway( $methods ) {
	$methods[] = 'At_WC_Atara_Gateway';
	return $methods;
}

// Test to see if WooCommerce is active (including network activated).
$plugin_path = trailingslashit( WP_PLUGIN_DIR ) . 'woocommerce/woocommerce.php';

if ( in_array( $plugin_path, wp_get_active_and_valid_plugins() )
) {
	add_action( 'plugins_loaded', 'at_wc_atara_init', 900 );
} else {
	add_action( 'admin_notices', 'at_wc_atara_wc_missing_notice' );
}

/**
 * Add Settings link to the plugin entry in the plugins menu
 **/
function at_wc_atara_plugin_action_links( $links ) {

	$settings_link = array(
		'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=at_atara' ) . '" title="View Settings">Settings</a>',
	);

	return array_merge( $settings_link, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'at_wc_atara_plugin_action_links' );

/**
 * Display a notice if WooCommerce is not installed
 */
function at_wc_atara_wc_missing_notice() {
	echo '<div class="error"><p><strong>' . sprintf( __( 'AtaraPay requires WooCommerce to be installed and active. Click %s to install WooCommerce.', 'atarapay-woo' ), '<a href="' . admin_url( 'plugin-install.php?tab=plugin-information&plugin=woocommerce&TB_iframe=true&width=772&height=539' ) . '" class="thickbox open-plugin-details-modal">here</a>' ) . '</strong></p></div>';
}
