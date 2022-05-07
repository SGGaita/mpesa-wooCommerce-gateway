<?php

/**
 * Plugin Name: M-Pesa WooCommerce Payment Gateway
 * Plugin URI: https://ascensiondynamics.co.ke
 * Author Name: Stephen Gaita
 * Author URI: https://ascensiondynamics.co.ke
 * Description: This plugin allows for payment using MPESA payment Gateway
 * Version: 0.1.0
 * Lincense: 0.1.0
 * Lincence URL: http://www.gnu.org/licenses/gpl-2.0.txt
 * text-domain: mpesa-woo-pay 
 */

if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters(
    'active_plugins', get_option( 'active_plugins' ) ) ) ) return;

add