<?
IncludeModuleLangFile(__FILE__);

// !!! ПОЖАЛУЙСТА НЕ ИСПОЛЬЗУЙТЕ В ЭТОМ ФАЙЛЕ КИРРИЛИЧЕСКИЕ СИМВОЛЫ !!!
// !!! PLEASE DO NOT USE INSIDE THIS FILE CYRILLIC SYMBOLS !!!

define('RBS_BANK_NAME', 'Sberbank'); //
define('RBS_MODULE_ID', 'sberbank.ecom');

define('API_PROD_URL', 'https://securepayments.sberbank.ru/payment/rest/');
define('API_TEST_URL', 'https://3dsec.sberbank.ru/payment/rest/');
define('API_RETURN_PAGE', '/sale/payment/result.php');
define('API_GATE_TRY',30);

$status = COption::GetOptionString("sberbank.ecom", "result_order_status", "P");
if (!defined('RESULT_ORDER_STATUS'))
    define('RESULT_ORDER_STATUS', $status);

$arDefaultIso = array(
    'USD' => 840,
    'EUR' => 978,
    'RUB' => 643,
    'RUR' => 643,
);

if (!defined('DEFAULT_ISO'))
    define(DEFAULT_ISO, serialize($arDefaultIso));