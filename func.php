<?php

use Tygh\Registry;
use Tygh\Embedded;
use Tygh\Session;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_cp_gestpay_integration_install(){
    fn_cp_gestpay_integration_uninstall();
    $_data = array(
        'processor' => 'GestPay Gateway',
        // cp.modif 11.12.19 start
        // 'processor_script' => 'cp_gestpay_integration.php',
        'processor_script' => 'cp_gestpay.php',
        // cp.modif 11.12.19 end
        'processor_template' => 'views/orders/components/payments/cc_outside.tpl',// default
        'template' => 'views/orders/components/payments/cc_outside.tpl',
        'admin_template' => 'cp_gestpay_integration.tpl', // with config in admin side
        'callback' => 'N',
        'type' => 'P',
        'addon' => 'cp_gestpay_integration'
    );
    
    db_query("INSERT INTO ?:payment_processors ?e", $_data);
}


function fn_cp_gestpay_integration_uninstall(){
    db_query("DELETE FROM ?:payment_processors where processor_script = ?s", "cp_gestpay_integration.php");
}


function fn_cp_gestpay_integration_save_log($type, $action, $data, $user_id, &$content, $event_type, $object_primary_keys)
{
    if ($type == 'gestpay_integration') {
        $content = $data;
    }
}