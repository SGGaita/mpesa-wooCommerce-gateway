<?php

/**
 * Plugin Name: M-Pesa Payment Gateway
 * Plugin URI: https://ascensiondynamics.co.ke
 * Author Name: Steve Gaita
 * Author URI: https://ascensiondynamics.co.ke
 * Description: Lipa Na MPesa mobile payment powered by Safaricom
 * Version: 0.1.0
 * Lincense: GPL2
 * Lincence URL: http://www.gnu.org/licenses/gpl-2.0.txt
 * text-domain: mpesa-woo-pay 
 */

if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters(
    'active_plugins', get_option( 'active_plugins' ) ) ) ) return;

add_action('plugins_loaded', 'mpesa_payment_init', 11);

function mpesa_payment_init(){
    if( class_exists( 'WC_Payment_Gateway')){
        class WC_Mpesa_pay_Gateway extends WC_Payment_Gateway{
            public function __construct(){
                // Setup general properties.
                $this->setup_properties();

                $this->init_form_fields();
                $this->init_settings();

                // Get settings.
		        $this->title              = $this->get_option( 'title' );
		        $this->description        = $this->get_option( 'description' );
		        $this->instructions       = $this->get_option( 'instructions' );

                	// Actions.
		        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
                add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
            }

            /**
	        * Setup general properties for the gateway.
	        */
            public function setup_properties(){
                $this->id = 'mpesa_payment';
                $this->icon = apply_filters( 'woocommerce_mpesa_icon', plugins_url( '/assets/mpesa-icon.png', __FILE__ ));
                $this->method_title = __( 'Mpesa Payment',
                'mpesa-woo-pay' );
                $this->method_description = __( 'Have your customers pay via Safaricom Lipa Na Mpesa Payment gateway.',
                'mpesa-woo-pay' );
                $this->has_fields = false;
            }

             /**
	        * Initialise Gateway Settings Form Fields.
	        */
            public function init_form_fields(){
                $this->form_fields = apply_filters(
                    'woo_mpesa_pay_fields', array(
                        'enabled' => array(
                          'title' => __( 'Enable/Disable',
                          'mpesa-woo-pay'),  
                          'label'       => __( 'Enable or Disable M-Pesa payment', 'mpesa-woo-pay' ),
				          'type'        => 'checkbox',
				          'description' => '',
				          'default'     => 'no',
                        ),
                        'title' => array(
                            'title' => __( 'M-Pesa payment gateway Title',
                            'mpesa-woo-pay'),  
                            'type'        => 'text',
                            'description' => __( 'Add a new title for the M-Pesa payment gateway. Visible to customers in the checkout page.',
                            'mpesa-woo-pay'),
                            'desc_tip' => true,
                             'default'     =>  __( 'M-Pesa payment gateway Title',
                             'mpesa-woo-pay'),
                          ),
                          'description' => array(
                            'title' => __( 'M-Pesa payment gateway description',
                            'mpesa-woo-pay'),  
                            'type'        => 'textarea',
                            'description' => __( 'Add a new description for the M-Pesa payment gateway. Visible to customers in the checkout page.',
                            'mpesa-woo-pay'),
                            'desc_tip' => true,
                             'default'     =>  __( 'Please remit your payment to the shop to allow for delivery to be made.',
                             'mpesa-woo-pay'),
                          ),
                          'instructions' => array(
                            'title' => __( 'Instructions',
                            'mpesa-woo-pay'),  
                            'type'        => 'textarea',
                            'description' => __( 'Instructions that will be added to the thank you page and order email.',
                            'mpesa-woo-pay'),
                            'desc_tip' => true,
                             'default'     =>  __( 'Default Instructions',
                             'mpesa-woo-pay'),
                          ),
                    )
                    );
            }

            /**
             * Process payments
             */
            public function process_payments( $order_id ){
                $order = new WC_Order( $order_id );

             $order->update_status( 'on-hold' , __('Awaiting M-Pesa Payment upon delivery',
             'mpesa-woo-pay') );

             //payment api
             $this->clear_payment_with_mpesa_api();

            //reduce items in stock
             wc_reduce_stock_levels($order);

             // Remove cart.
		    WC()->cart->empty_cart();

            // Return thankyou redirect.
		    return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		    );

            }

            //Mpesa API payment processing
            public function clear_payment_with_mpesa_api(){

            }

            /**
	 * Output for the order received page.
	 */
	public function thankyou_page() {
		if ( $this->instructions ) {
			echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) );
		}
	}
            
        }
        }
    }

    add_filter('woocommerce_payment_gateways',
     'add_to_woo_mpesa_payment_getway');

     function add_to_woo_mpesa_payment_getway( $gateways ){
         $gateways[] = 'WC_Mpesa_pay_Gateway';
        return $gateways;
     }
