<?php

use Tygh\Http;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if (defined('PAYMENT_NOTIFICATION')) {
    $processor_data = Registry::get('addons.cp_gestpay_integration');
    
    $test_env = $processor_data['mode'] == 'test';

    $shop_login = $processor_data['shop_login'];

    if ($test_env) {
        $wsdl = "https://sandbox.gestpay.net/gestpay/GestPayWS/WsCryptDecrypt.asmx?wsdl"; //TESTCODES
        $action_pagamento = "https://sandbox.gestpay.net/pagam/pagam.aspx";
    } else {
        $wsdl = "https://ecommS2S.sella.it/gestpay/GestPayWS/WsCryptDecrypt.asmx?wsdl"; //PRODUCTION
        $action_pagamento = "https://ecomm.sella.it/pagam/pagam.aspx";
    }

    $param = array(
        'shopLogin' => $shop_login,
        'CryptedString' => $_REQUEST['b']
    );

    $client = new SoapClient($wsdl);
    $object_result = null;
    try {
        $object_result = $client->Decrypt($param);
    } catch (SoapFault $fault) {
        trigger_error("SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR);
    }

    //parse the XML result
    $result = simplexml_load_string($object_result->DecryptResult->any);
    
    if (!empty($result)) {
        $pp_response = array();
        if ($result->TransactionResult == 'OK') {
            $pp_response['order_status'] = 'P';
            $pp_response['cp_gestpay_integration_shop_transaction_id'] = reset($result->ShopTransactionID);
            $pp_response['cp_gestpay_integration_bank_transaction_id'] = reset($result->BankTransactionID);
            $pp_response['cp_gestpay_integration_authorization_code'] = reset($result->AuthorizationCode);
        } else {
            $pp_response['order_status'] = 'F';
            $pp_response['reason_text'] = $result->ErrorCode . ': ' . $result->ErrorDescription;
        }
    }
    $order_id = explode('_', $result->ShopTransactionID);
    $order_id = reset($order_id);
    
    fn_finish_payment($order_id, $pp_response, false);
    $area = db_get_field("SELECT cp_gestpay_from FROM ?:orders WHERE order_id = ?i", $order_id);
    if ($area == 'C') {
        fn_order_placement_routines('route', $order_id, false);
    } else {
        header("Location: " . Registry::get('config.admin_index'). '?dispatch=orders.details&order_id=' . $order_id);
    }
    exit;
} else {

    $processor_data = Registry::get('addons.cp_gestpay_integration');
    $test_env = $processor_data['mode'] == 'test';

    $shop_login = $processor_data['shop_login'];

    $currency = $processor_data['currency'];

    if ($test_env) {
        $wsdl = "https://sandbox.gestpay.net/gestpay/GestPayWS/WsCryptDecrypt.asmx?wsdl"; //TESTCODES
        $action_pagamento = "https://sandbox.gestpay.net/pagam/pagam.aspx";
    } else {
        $wsdl = "https://ecommS2S.sella.it/gestpay/GestPayWS/WsCryptDecrypt.asmx?wsdl"; //PRODUCTION
        $action_pagamento = "https://ecomm.sella.it/pagam/pagam.aspx";
    }

    $param = array(
        'shopLogin' => $shop_login,
        'uicCode' => $currency,
        'amount' => $order_info['total'],
        'shopTransactionId' => $order_info['order_id'] . '_' . time(),
    );

    $client = new SoapClient($wsdl);
    $object_result = null;
    try {
        $object_result = $client->Encrypt($param);
    } catch (SoapFault $fault) {
        trigger_error("SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR);
    }

    //parse the XML result
    $result = simplexml_load_string($object_result->EncryptResult->any);

    // if there is an error trying to contact Gestpay Server
    // (e.g. your IP address is not recognized, or the shopLogin is invalid) you'll see it here.

    $err_code= $result->ErrorCode;
    $err_desc= $result->ErrorDescription;
    $pp_response = array();
    
    
    if ($err_code != 0) {
        $pp_response['order_status'] = 'F';
        $pp_response['reason_text'] = $err_code . ' - ' . $err_desc;
    } else {

        $enc_string = $result->CryptDecryptString;
        
        if (AREA == 'A') {
            db_query("UPDATE ?:orders SET cp_gestpay_from = 'A' WHERE order_id = ?i", $order_info['order_id']);
        }
        
        header("Location: " . $action_pagamento . '?a=' . $shop_login . '&b=' . reset($enc_string) . '&order_id=' . $order_info['order_id']);
        exit;
    }
}

