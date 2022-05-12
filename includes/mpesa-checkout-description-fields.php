<?php


add_filter('woocommerce_gateway_description', 'lipa_na_mpesa_description_fields', 20, 2);

function lipa_na_mpesa_description_fields($description, $payment_id){

    if ('mpesa_payment' !== $payment_id){
        return $description;
    }

    ob_start();
   
    woocommerce_form_field(
        'phone_number',
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
