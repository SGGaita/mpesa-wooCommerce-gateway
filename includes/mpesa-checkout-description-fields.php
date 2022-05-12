<?php


add_filter('woocommerce_gateway_description', 'lipa_na_mpesa_description_fields', 20, 2);
add_action('woocommerce_checkout_process', 'lipa_na_mpesa_description_fields_validation');
add_action( 'woocommerce_checkout_update_order_meta', 'lipa_na_mpesa_checkout_update_order_meta', 10, 1);
add_action( 'woocommerce_admin_order_data_after_billing_address', 'lipa_na_mpesa_order_data_after_billing_address', 10, 1);
add_action( 'woocommerce_order_item_meta_end', 'lipa_na_mpesa_order_item_meta_end', 10, 3);

function lipa_na_mpesa_description_fields($description, $payment_id){

    if ('mpesa_payment' !== $payment_id){
        return $description;
    }

    ob_start();
   
    woocommerce_form_field(
        'payment_number',
        array(
            'type' => 'text',
            'label' =>__('Enter your M-Pesa number', 'mpesa-woo-pay'),
            'required' => true,
            'placeholder' => 'Enter your phone number',
        )
      );

    $description .= ob_get_clean();
    return $description;

}

function lipa_na_mpesa_description_fields_validation(){
if('mpesa_payment' === $_POST['payment_method'] && ! isset( $_POST['payment_number']) || empty( $_POST['payment_number'])){
    wc_add_notice( 'Please enter a phone number that is to be billed.', 'error' );
}
}

function lipa_na_mpesa_checkout_update_order_meta($order_id){
    if( isset( $_POST['payment_number']) || ! empty( $_POST['payment_number'])){
        update_post_meta($order_id, 'payment_number', $_POST[ 'payment_number' ]);
    }
}

function lipa_na_mpesa_order_data_after_billing_address( $order ){
    echo '<p> <strong>'  . __( 'Payment Phone Number: ', 'mpesa-woo-pay' ) . '</strong> <br>' . get_post_meta( $order->get_id(), 'payment_number', true) . '</p>';
}

function lipa_na_mpesa_order_item_meta_end( $item_id, $item, $order ){
    echo '<p> <strong>'  . __( 'Payment Phone Number: ', 'mpesa-woo-pay' ) . '</strong> <br>' . get_post_meta( $order->get_id(), 'payment_number', true) . '</p>';
}
