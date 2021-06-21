<?php

use Tygh\Registry;

function fn_settings_variants_addons_cp_gestpay_integration_currency()
{
    $currencies_list = array (
        'USD' => 1,
        'AUD' => 109,
        'CAD' => 12,
        'CHF' => 3,
        'BRL' => 234,
        'CZK' => 233,
        'DKK' => 7,
        'EUR' => 242,
        'GBP' => 2,
        'HKD' => 103,
        'JPY' => 71,
        'PLN' => 237,
        'RUB' => 244,
        'SEK' => 9,
        'SGD' => 124
    );

    $acceptable_currencies_list = array (
        'USD' => __("currency_code_usd"),
        'EUR' => __("currency_code_eur"),
        'GBP' => __("currency_code_gbp"),
        'RUB' => __("currency_code_rur"),
        'CAD' => __("currency_code_cad"),
        'JPY' => __("currency_code_jpy"),
        'AUD' => __("currency_code_aud"),
        'CHF' => __("currency_code_chf"),
        'HKD' => __("currency_code_hkd"),
        'SGD' => __("currency_code_sgd"),
        'SEK' => __("currency_code_sek"),
        'DKK' => __("currency_code_dkk"),
        'PLN' => __("currency_code_pln"),
        'CZK' => __("currency_code_czk"),
        'BRL' => __("currency_code_brl"),
    );

    $params = array('status'=>array('A','H'));
    $available_currencies_list = fn_get_currencies_list($params);
    
    $result = array();
    $avaialble_code_list = array_keys($available_currencies_list);
    foreach( $acceptable_currencies_list as $currency_code => $currency_item ){
        if (in_array($currency_code, $avaialble_code_list) ){
            $result[$currencies_list[$currency_code]] = $currency_item;
        }
    }
    return $result;
}
