<?php

require 'Payhesap.php'; 

$payhesap = new Payhesap();

$payhesap->set_config([
    'hash'         => '', # Payhesap Hash
    'callback_url' => 'http://127.0.0.1/CallBack.php' # CallBack Url
]);

$payhesap->set_order_id('2');
$payhesap->set_price('1');

$payhesap->set_card([
    'fullname'  => "Test İşlem", # Card Holder Fullname
    'number'    => "11111111111", # Card Number
    'exp_month' => 22, # Card Exp Montg
    'exp_year'  => 24, # Card Exp Year
    'cvv'       => 222 # Card CVV Number
]);

$payhesap->set_buyer([
    'name'    => "", # Buyer Fullname
    'email'   => "", # Buyer Email
    'phone'   => "", # Buyer Phone
    'city'    => "", # Buyer City
    'state'   => "", # Buyer State
    'address' => "" # Buyer Address
]);


$init = $payhesap->init();

if ($init == null) {
    print_r($payhesap->get_error());
} else {
    header("Location: " . $init);
}