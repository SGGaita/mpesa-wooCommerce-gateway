<?php

/**
 * Plugin Name: Lipa Na M-Pesa Payment Gateway
 * Plugin URI: https://ascensiondynamics.co.ke
 * Author Name: Steve Gaita
 * Author URI: https://ascensiondynamics.co.ke
 * Description: Lipa Na MPesa mobile payment powered by Safaricom
 * Version: 0.1.0
 * Lincense: GPL2
 * Lincence URL: http://www.gnu.org/licenses/gpl-2.0.txt
 * text-domain: mpesa-woo-pay 
 * 
 * Class WC_Gateway_LipaNaMpesa file.
 *
 * @package WooCommerce\LipaNaMpesa
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters(
    'active_plugins', get_option( 'active_plugins' ) ) ) ) return;

add_action('plugins_loaded', 'mpesa_payment_init', 11);

function mpesa_payment_init(){
    if( class_exists( 'WC_Payment_Gateway')){
       
		require_once plugin_dir_path(__FILE__) . '/includes/class-wc-payment-gateway-mpesa.php';
        require_once plugin_dir_path(__FILE__) . '/includes/mpesa-order-statuses.php';
        require_once plugin_dir_path(__FILE__) . '/includes/mpesa-checkout-description-fields.php';

	}
}

add_filter('woocommerce_payment_gateways',
     'add_to_woo_mpesa_payment_getway');

     function add_to_woo_mpesa_payment_getway( $gateways ){
         $gateways[] = 'WC_Gateway_LipaNaMpesa';
        return $gateways;
     }
