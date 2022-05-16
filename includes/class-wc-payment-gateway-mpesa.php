<?php

use LE_ACME2\Utilities\Base64;

class WC_Gateway_LipaNaMpesa extends WC_Payment_Gateway {

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		// Setup general properties.
		$this->setup_properties();

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Get settings.
		$this->title                 = $this->get_option( 'title' );
		$this->description           = $this->get_option( 'description' );
		$this->instructions          = $this->get_option( 'instructions' );
		$this->consumer_key          = $this->get_option( 'consumer_key' );
		$this->consumer_secret       = $this->get_option( 'consumer_secret' );
		$this->transaction_type		 = $this->get_option( 'transaction_type');
		$this->business_short_code   = $this->get_option( 'business_short_code' );
		$this->accountref			 = $this->get_option( 'accountref' );
		$this->passKey			     = $this->get_option( 'passKey' );
		$this->callback_url			 = $this->get_option( 'callback_url' );
		$this->auth_url			     = $this->get_option( 'auth_url' );
		$this->api_endpoint_url	     = $this->get_option( 'api_endpoint_url');
		$this->passKey			     = $this->get_option( 'passKey' );
		$this->enable_for_methods    = $this->get_option( 'enable_for_methods', array() );
		$this->enable_for_virtual    = $this->get_option( 'enable_for_virtual', 'yes' ) === 'yes';

		// Actions.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		add_filter( 'woocommerce_payment_complete_order_status', array( $this, 'change_payment_complete_order_status' ), 10, 3 );

		// Customer Emails.
		add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
	}

	/**
	 * Setup general properties for the gateway.
	 */
	protected function setup_properties() {
			$this->id                 = 'mpesa_payment';
            $this->icon        		  = apply_filters( 'woocommerce_mpesa_icon', plugins_url( '../assets/mpesa-icon.png', __FILE__ ));
            $this->method_title 	  = __( 'Lipa Na M-Pesa Mobile Payment',
            'mpesa-woo-pay' );
            $this->method_description = __( 'Allow your customers to pay via Safaricom Lipa Na M-Pesa Payment Gateway.',
            'mpesa-woo-pay' );
            $this->has_fields 		  = false;
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'            => array(
				'title'       => __( 'Enable/Disable', 'mpesa-woo-pay' ),
				'label'       => __( 'Enable Lipa Na M-Pesa', 'mpesa-woo-pay' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no',
			),
			'title'              => array(
				'title' => __( 'M-Pesa payment gateway Title',
                            'mpesa-woo-pay'), 
				'type'        => 'text',
				'description' => __( 'Lipa Na M-Pesa Payment method description that the customer will see on your checkout.', 'mpesa-woo-pay' ),
				'default'     => __( 'lipa Na M-Pesa Payment Title', 'mpesa-woo-pay' ),
				'desc_tip'    => true,
			),
			'description'        => array(
				'title' => __( 'M-Pesa payment gateway description',
				'mpesa-woo-pay'),  
				'type'        => 'textarea',
				'description' => __( 'Add a new description for the M-Pesa payment gateway. Visible to customers in the checkout page.',
				'mpesa-woo-pay'),
				'desc_tip' => true,
				'default'     =>  __( 'Please remit your payment to the shop via Lipa Na M-Pesa to allow for delivery to be made.',
				 'mpesa-woo-pay'),
			),
			'instructions'       => array(
				'title'       => __( 'Instructions', 'mpesa-woo-pay' ),
				'type'        => 'textarea',
				'description' => __( 'Instructions that will be added to the thank you page.', 'mpesa-woo-pay' ),
				'default'     => __( 'Default Instructions.', 'mpesa-woo-pay' ),
				'desc_tip'    => true,
			),
			'accountref'              => array(
				'title' => __( 'Account Reference',
                            'mpesa-woo-pay'), 
				'type'        => 'text',
				'description' => __( 'Identifier of the transaction for CustomerPayBillOnline transaction type. Along with the business name, this value is also displayed to the customer in the STK Pin Prompt message', 'woocommerce' ),
				'default'     => __( 'Kidslove Collection', 'mpesa-woo-pay' ),
				'desc_tip'    => true,
			),
			'consumer_key'              => array(
				'title' => __( 'Consumer Key',
                            'mpesa-woo-pay'), 
				'type'        => 'text',
				'description' => __( 'Provided together with Consumer Secret in the Daraja platform on creating an application.', 'mpesa-woo-pay' ),
				'placeholder' => __( 'Enter consumer secret', 'mpesa-woo-pay' ),
				'desc_tip'    => true,
			),
			'consumer_secret'              => array(
				'title' => __( 'Consumer Secret',
                            'mpesa-woo-pay'), 
				'type'        => 'text',
				'description' => __( 'Provided together with Consumer Key in the Daraja platform on creating an application.', 'mpesa-woo-pay' ),
				'placeholder' => __( 'Enter consumer secret', 'mpesa-woo-pay' ),
				'desc_tip'    => true,
			),
			
			'transaction_type'              => array(
				'title' => __( 'Transaction type',
                            'mpesa-woo-pay'), 
				'type'        => 'select',
				'options' => array( 
					/**1 => __( 'MSISDN', 'woocommerce' ),*/
				   'CustomerPayBillOnline' => __( 'Paybill Number', 'mpesa-woo-pay' ),
				   'CustomerBuyGoodsOnline' => __( 'Till Number', 'mpesa-woo-pay' )
			  ),
			  'description' => __( 'MPesa Identifier Type', 'mpesa-woo-pay' ),
			  'desc_tip'    => true,
			),
			'business_short_code'              => array(
				'title' => __( 'Business Shortcode',
                            'mpesa-woo-pay'), 
				'type'        => 'text',
				'description' => __( 'Your MPesa Business Till/Paybill Number. Use "Online Shortcode" in Sandbox', 'mpesa-woo-pay' ),
				'default'     => __( '174379', 'mpesa-woo-pay' ),
				'desc_tip'    => true,
			),
			'passKey'              => array(
				'title' => __( 'Pass Key',
                            'mpesa-woo-pay'), 
				'type'        => 'textarea',
				'description' => __( 'Used to create a password for use when making a Lipa Na M-Pesa Online Payment API call.', 'mpesa-woo-pay' ),
				'default'     => __( 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919', 'mpesa-woo-pay' ),
				'desc_tip'    => true,
			),
			'callback_url'              => array(
				'title' => __( 'Callback Url',
                            'mpesa-woo-pay'), 
				'type'        => 'text',
				'description' => __( 'Callback URL where responce is submitted', 'mpesa-woo-pay' ),
				'default'     => __( 'https://http://kidslove.co.ke/callback/callback.php', 'mpesa-woo-pay' ),
				'desc_tip'    => true,
			),
			'auth_url'              => array(
				'title' => __( 'Authentication Url',
                            'mpesa-woo-pay'), 
				'type'        => 'text',
				'description' => __( 'Authentication Url provided on Daraja platform.', 'mpesa-woo-pay' ),
				'default'     => __( 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials', 'mpesa-woo-pay' ),
				'desc_tip'    => true,
			),
			'api_endpoint_url'              => array(
				'title' => __( 'Endpoint Url',
                            'mpesa-woo-pay'), 
				'type'        => 'text',
				'description' => __( 'Endpoint provided on Daraja platform.', 'mpesa-woo-pay' ),
				'default'     => __( 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest', 'mpesa-woo-pay' ),
				'desc_tip'    => true,
			),
			'enable_for_methods' => array(
				'title'             => __( 'Enable for shipping methods', 'mpesa-woo-pay' ),
				'type'              => 'multiselect',
				'class'             => 'wc-enhanced-select',
				'css'               => 'width: 400px;',
				'default'           => '',
				'description'       => __( 'If Lipa Na Mpesa is only available for certain methods, set it up here. Leave blank to enable for all methods.', 'mpesa-woo-pay' ),
				'options'           => $this->load_shipping_method_options(),
				'desc_tip'          => true,
				'custom_attributes' => array(
					'data-placeholder' => __( 'Select shipping methods', 'mpesa-woo-pay' ),
				),
			),
			'enable_for_virtual' => array(
				'title'   => __( 'Accept for virtual orders', 'mpesa-woo-pay' ),
				'label'   => __( 'Accept Lipa Na Mpesa if the order is virtual', 'mpesa-woo-pay' ),
				'type'    => 'checkbox',
				'default' => 'yes',
			),
		);
	}

	/**
	 * Check If The Gateway Is Available For Use.
	 *
	 * @return bool
	 */
	public function is_available() {
		$order          = null;
		$needs_shipping = false;

		// Test if shipping is needed first.
		if ( WC()->cart && WC()->cart->needs_shipping() ) {
			$needs_shipping = true;
		} elseif ( is_page( wc_get_page_id( 'checkout' ) ) && 0 < get_query_var( 'order-pay' ) ) {
			$order_id = absint( get_query_var( 'order-pay' ) );
			$order    = wc_get_order( $order_id );

			// Test if order needs shipping.
			if ( $order && 0 < count( $order->get_items() ) ) {
				foreach ( $order->get_items() as $item ) {
					$_product = $item->get_product();
					if ( $_product && $_product->needs_shipping() ) {
						$needs_shipping = true;
						break;
					}
				}
			}
		}

		$needs_shipping = apply_filters( 'woocommerce_cart_needs_shipping', $needs_shipping );

		// Virtual order, with virtual disabled.
		if ( ! $this->enable_for_virtual && ! $needs_shipping ) {
			return false;
		}

		// Only apply if all packages are being shipped via chosen method, or order is virtual.
		if ( ! empty( $this->enable_for_methods ) && $needs_shipping ) {
			$order_shipping_items            = is_object( $order ) ? $order->get_shipping_methods() : false;
			$chosen_shipping_methods_session = WC()->session->get( 'chosen_shipping_methods' );

			if ( $order_shipping_items ) {
				$canonical_rate_ids = $this->get_canonical_order_shipping_item_rate_ids( $order_shipping_items );
			} else {
				$canonical_rate_ids = $this->get_canonical_package_rate_ids( $chosen_shipping_methods_session );
			}

			if ( ! count( $this->get_matching_rates( $canonical_rate_ids ) ) ) {
				return false;
			}
		}

		return parent::is_available();
	}

	/**
	 * Checks to see whether or not the admin settings are being accessed by the current request.
	 *
	 * @return bool
	 */
	private function is_accessing_settings() {
		if ( is_admin() ) {
			// phpcs:disable WordPress.Security.NonceVerification
			if ( ! isset( $_REQUEST['page'] ) || 'wc-settings' !== $_REQUEST['page'] ) {
				return false;
			}
			if ( ! isset( $_REQUEST['tab'] ) || 'checkout' !== $_REQUEST['tab'] ) {
				return false;
			}
			if ( ! isset( $_REQUEST['section'] ) || 'mpesa_payment' !== $_REQUEST['section'] ) {
				return false;
			}
			// phpcs:enable WordPress.Security.NonceVerification

			return true;
		}

		return false;
	}

	/**
	 * Loads all of the shipping method options for the enable_for_methods field.
	 *
	 * @return array
	 */
	private function load_shipping_method_options() {
		// Since this is expensive, we only want to do it if we're actually on the settings page.
		if ( ! $this->is_accessing_settings() ) {
			return array();
		}

		$data_store = WC_Data_Store::load( 'shipping-zone' );
		$raw_zones  = $data_store->get_zones();

		foreach ( $raw_zones as $raw_zone ) {
			$zones[] = new WC_Shipping_Zone( $raw_zone );
		}

		$zones[] = new WC_Shipping_Zone( 0 );

		$options = array();
		foreach ( WC()->shipping()->load_shipping_methods() as $method ) {

			$options[ $method->get_method_title() ] = array();

			// Translators: %1$s shipping method name.
			$options[ $method->get_method_title() ][ $method->id ] = sprintf( __( 'Any &quot;%1$s&quot; method', 'mpesa-woo-pay' ), $method->get_method_title() );

			foreach ( $zones as $zone ) {

				$shipping_method_instances = $zone->get_shipping_methods();

				foreach ( $shipping_method_instances as $shipping_method_instance_id => $shipping_method_instance ) {

					if ( $shipping_method_instance->id !== $method->id ) {
						continue;
					}

					$option_id = $shipping_method_instance->get_rate_id();

					// Translators: %1$s shipping method title, %2$s shipping method id.
					$option_instance_title = sprintf( __( '%1$s (#%2$s)', 'mpesa-woo-pay' ), $shipping_method_instance->get_title(), $shipping_method_instance_id );

					// Translators: %1$s zone name, %2$s shipping method instance name.
					$option_title = sprintf( __( '%1$s &ndash; %2$s', 'mpesa-woo-pay' ), $zone->get_id() ? $zone->get_zone_name() : __( 'Other locations', 'mpesa-woo-pay' ), $option_instance_title );

					$options[ $method->get_method_title() ][ $option_id ] = $option_title;
				}
			}
		}

		return $options;
	}

	/**
	 * Converts the chosen rate IDs generated by Shipping Methods to a canonical 'method_id:instance_id' format.
	 *
	 * @since  3.4.0
	 *
	 * @param  array $order_shipping_items  Array of WC_Order_Item_Shipping objects.
	 * @return array $canonical_rate_ids    Rate IDs in a canonical format.
	 */
	private function get_canonical_order_shipping_item_rate_ids( $order_shipping_items ) {

		$canonical_rate_ids = array();

		foreach ( $order_shipping_items as $order_shipping_item ) {
			$canonical_rate_ids[] = $order_shipping_item->get_method_id() . ':' . $order_shipping_item->get_instance_id();
		}

		return $canonical_rate_ids;
	}

	/**
	 * Converts the chosen rate IDs generated by Shipping Methods to a canonical 'method_id:instance_id' format.
	 *
	 * @since  3.4.0
	 *
	 * @param  array $chosen_package_rate_ids Rate IDs as generated by shipping methods. Can be anything if a shipping method doesn't honor WC conventions.
	 * @return array $canonical_rate_ids  Rate IDs in a canonical format.
	 */
	private function get_canonical_package_rate_ids( $chosen_package_rate_ids ) {

		$shipping_packages  = WC()->shipping()->get_packages();
		$canonical_rate_ids = array();

		if ( ! empty( $chosen_package_rate_ids ) && is_array( $chosen_package_rate_ids ) ) {
			foreach ( $chosen_package_rate_ids as $package_key => $chosen_package_rate_id ) {
				if ( ! empty( $shipping_packages[ $package_key ]['rates'][ $chosen_package_rate_id ] ) ) {
					$chosen_rate          = $shipping_packages[ $package_key ]['rates'][ $chosen_package_rate_id ];
					$canonical_rate_ids[] = $chosen_rate->get_method_id() . ':' . $chosen_rate->get_instance_id();
				}
			}
		}

		return $canonical_rate_ids;
	}

	/**
	 * Indicates whether a rate exists in an array of canonically-formatted rate IDs that activates this gateway.
	 *
	 * @since  3.4.0
	 *
	 * @param array $rate_ids Rate ids to check.
	 * @return boolean
	 */
	private function get_matching_rates( $rate_ids ) {
		// First, match entries in 'method_id:instance_id' format. Then, match entries in 'method_id' format by stripping off the instance ID from the candidates.
		return array_unique( array_merge( array_intersect( $this->enable_for_methods, $rate_ids ), array_intersect( $this->enable_for_methods, array_unique( array_map( 'wc_get_string_before_colon', $rate_ids ) ) ) ) );
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( $order->get_total() > 0 ) {
			//process mpesa stk push
			$this->lipa_na_mpesa_payment_processing($order);
			
		} else {
			$order->payment_complete();
		}
			// Remove cart.
		//WC()->cart->empty_cart();

		// Return thankyou redirect.
		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}

	

	function lipa_na_mpesa_payment_processing($order){
		$total = intval($order->get_total());

		$url = $this->api_endpoint_url;
		$curl_post_data = [
			'BusinessShortCode' => $this->business_short_code,
            'Password' => $this->lipaNaMpesaPassword(),
            'Timestamp' => date( 'YmdHis' ),
            'TransactionType' => $this->transaction_type,
            'Amount' => 1,
            'PartyA' => esc_attr( $_POST['payment_number'] ),
            'PartyB' => $this->business_short_code,
            'PhoneNumber' => $_POST['payment_number'],
            'CallBackURL' => $this->callback_url,
            'AccountReference' => $this->accountref,
            'TransactionDesc' => "Payment for Order #: ". $order->get_order_number(),
		];

		$data_string = json_encode($curl_post_data);

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer '.$this->newAccessToken()));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        $response = curl_exec($curl);
		$result = json_decode($response);
    

		if( is_wp_error( $result )){
			$error_message = $result->get_error_message();
			echo "Something went wrong: $error_message";
		} else {
			echo 'Response:<pre>';
			print_r( $result );
			echo '</pre>';
		}
		//die;
}


		//get LipaNaMpesa password
	public function lipaNaMpesaPassword()
	{
		# Get the timestamp, format YYYYmmddhms -> 20181004151020
		$Timestamp = date( 'YmdHis' );
		 //passkey
		 $passKey = $this->passKey;
		 $businessShortCode = $this->business_short_code;
		 //generate password
		 $mpesaPassword = base64_encode($businessShortCode.$passKey.$Timestamp);
		 return $mpesaPassword;
	}

		//get newAccessToken
		public function newAccessToken(){
			$consumer_key = $this->consumer_key;
			$consumer_secret = $this->consumer_secret;
			$credentials = base64_encode($consumer_key.":".$consumer_secret);
			$url = $this->auth_url;

			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Basic ".$credentials,"Content_type:application/json"));
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$curl_response = curl_exec($curl);
			$access_token = json_decode($curl_response);
			curl_close($curl);

			return $access_token->access_token;
		}


		//$order = wc_get_order( $order_id );


		
// if pending payment
//$order->update_status( apply_filters( 'woocommerce_mpesa_process_payment_order_status', $order->has_downloadable_item() ? 'wc-invoiced' : 'processing',$order ), __( 'Payments pending.', 'mpesa-woo-pay' ) );

//if cleared
//$order->payment_complete();
	

	/**
	 * Output for the order received page.
	 */
	public function thankyou_page() {
		if ( $this->instructions ) {
			echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) );
		}
	}

	/**
	 * Change payment complete order status to completed for COD orders.
	 *
	 * @since  3.1.0
	 * @param  string         $status Current order status.
	 * @param  int            $order_id Order ID.
	 * @param  WC_Order|false $order Order object.
	 * @return string
	 */
	public function change_payment_complete_order_status( $status, $order_id = 0, $order = false ) {
		if ( $order && 'mpesa_payment' === $order->get_payment_method() ) {
			$status = 'completed';
		}
		return $status;
	}

	/**
	 * Add content to the WC emails.
	 *
	 * @param WC_Order $order Order object.
	 * @param bool     $sent_to_admin  Sent to admin.
	 * @param bool     $plain_text Email format: plain text or HTML.
	 */
	public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		if ( $this->instructions && ! $sent_to_admin && $this->id === $order->get_payment_method() ) {
			echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) . PHP_EOL );
		}
	}
}
   