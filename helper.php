<?php


require_once "./lib/payu.php";

//gets the redirect url by calling the payu api using SOAP
//test credentials

function buildUrl($userdata) {
    $key = '{CE62CE80-0EFD-4035-87C1-8824C5C46E7F}';
    $username = '100032';
    $password = 'PypWWegU';
    $payU = new payU('/service/PayUAPI?wsdl','/rpp.do?PayUReference=');
    $payU->setCred($key, $username, $password);
    $payU->init();

    $amount = $userdata->amount;
    $amount = ($amount*100); //amount in cents
    $returnUrl = $userdata->return;
    $cancelUrl = $userdata->cancel_return;
    $ipnurl = 'http://example.com/ipn.php';
    $data = array(
        'TransactionType' => 'PAYMENT',
        'AdditionalInformation' => array(
            'merchantReference' => $userdata->order_id,                   
            'cancelUrl' => $cancelUrl,
            'notificationUrl' => $ipnurl,
            'returnUrl' => $returnUrl,
            'supportedPaymentMethods' => 'CREDITCARD,EFT',
            'showBudget' => false
        ),
        'Customer' => array(
            'email' => $userdata->user_email,
            'firstName' => $userdata->user_firstname,
            'lastName' => '',
            'mobile' => ''
        ),
        'Basket' => array(
            'amountInCents' => $amount,
            'currencyCode' => 'USD',
            'description' => $userdata->item_name. ' - Payment'
        )
    );
    $redirectUrl = $payU->setTransaction($data);
}



        
function getTransaction($reference) {
    //live cred
    //test credentials
    $key = '{CE62CE80-0EFD-4035-87C1-8824C5C46E7F}';
    $username = '100032';
    $password = 'PypWWegU';
    $payU = new payU('/service/PayUAPI?wsdl','/rpp.do?PayUReference=');
    $payU->setCred($key, $username, $password);
    $payU->init();
    $options['AdditionalInformation'] = array(
        'payUReference' => $reference
    );
    $res = $payU->getTransaction($options);
}
